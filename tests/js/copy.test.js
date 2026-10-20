import { readFileSync } from 'node:fs'
import { describe, expect, it } from 'vitest'

const source = readFileSync(new URL('../../assets/copy.js', import.meta.url), 'utf8')

function element(attrs = {}, textContent = '') {
  const listeners = {}
  return {
    attrs: { ...attrs },
    hidden: true,
    textContent,
    getAttribute(name) { return name in this.attrs ? this.attrs[name] : null },
    addEventListener(type, fn) { listeners[type] = fn },
    click() { return listeners.click?.() },
  }
}

function run({ clipboard } = {}) {
  const button = element({ 'data-copy': 'curl -fsSL https://dev-null.sh/commit | bash', 'data-copy-status': 'cli-status' }, 'Copy')
  const status = element({}, '')
  const copied = []
  const timers = []
  const document = {
    querySelectorAll: (selector) => (selector === '[data-copy]' ? [button] : []),
    getElementById: (id) => (id === 'cli-status' ? status : null),
  }
  const navigator = clipboard === undefined
    ? { clipboard: { writeText: (text) => { copied.push(text); return Promise.resolve() } } }
    : { clipboard }
  const setTimeout = (fn, ms) => timers.push({ fn, ms })

  new Function('document', 'navigator', 'setTimeout', source)(document, navigator, setTimeout)

  return { button, status, copied, timers, settle: () => new Promise((resolve) => queueMicrotask(resolve)) }
}

describe('copy script', () => {
  it('shows the Copy button when the clipboard can be written', () => {
    expect(run().button.hidden).toBe(false)
  })

  it('keeps the Copy button hidden without a clipboard', () => {
    expect(run({ clipboard: null }).button.hidden).toBe(true)
    expect(run({ clipboard: {} }).button.hidden).toBe(true)
  })

  it('copies the command and says so', async () => {
    const page = run()

    page.button.click()
    await page.settle()

    expect(page.copied).toEqual(['curl -fsSL https://dev-null.sh/commit | bash'])
    expect(page.button.textContent).toBe('Copied')
    expect(page.status.textContent).toBe('Copied to the clipboard.')
  })

  it('goes back to Copy after two seconds', async () => {
    const page = run()

    page.button.click()
    await page.settle()
    page.timers.at(-1).fn()

    expect(page.timers.at(-1).ms).toBe(2000)
    expect(page.button.textContent).toBe('Copy')
    expect(page.status.textContent).toBe('')
  })

  it('says so when the browser refuses to copy', async () => {
    const page = run({ clipboard: { writeText: () => Promise.reject(new Error('denied')) } })

    page.button.click()
    await page.settle()
    await page.settle()

    expect(page.button.textContent).toBe('Not copied')
    expect(page.status.textContent).toBe('Could not copy. Select the command and copy it.')
  })
})
