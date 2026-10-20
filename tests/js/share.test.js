import { readFileSync } from 'node:fs'
import { describe, expect, it } from 'vitest'

const source = readFileSync(new URL('../../assets/share.js', import.meta.url), 'utf8')

function run(share) {
  const listeners = {}
  const button = {
    hidden: true,
    attrs: { 'data-share': 'https://phplives.dev/entries/pest-5/', 'data-share-title': 'Pest 5: shipped with PHP' },
    getAttribute(name) { return this.attrs[name] ?? null },
    addEventListener(type, fn) { listeners[type] = fn },
    click() { return listeners.click?.() },
  }
  const document = { querySelectorAll: (selector) => (selector === '[data-share]' ? [button] : []) }
  const navigator = share ? { share } : {}

  new Function('document', 'navigator', source)(document, navigator)

  return button
}

describe('share script', () => {
  it('keeps the Share button hidden when the browser has no share menu', () => {
    expect(run(undefined).hidden).toBe(true)
  })

  it('shows the Share button when the browser has a share menu', () => {
    expect(run(() => Promise.resolve()).hidden).toBe(false)
  })

  it('opens the share menu with the page title and address', async () => {
    const shared = []
    const button = run((data) => { shared.push(data); return Promise.resolve() })

    await button.click()

    expect(shared).toEqual([{ title: 'Pest 5: shipped with PHP', url: 'https://phplives.dev/entries/pest-5/' }])
  })

  it('stays quiet when the visitor closes the share menu', async () => {
    const button = run(() => Promise.reject(new Error('AbortError')))

    await expect(button.click()).resolves.toBeUndefined()
  })
})
