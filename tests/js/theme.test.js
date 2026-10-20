import { readFileSync } from 'node:fs'
import { describe, expect, it } from 'vitest'

const source = readFileSync(new URL('../../assets/theme.js', import.meta.url), 'utf8')

function element(attrs = {}) {
  const listeners = {}
  return {
    attrs: { ...attrs },
    hidden: false,
    getAttribute(name) { return name in this.attrs ? this.attrs[name] : null },
    setAttribute(name, value) { this.attrs[name] = String(value) },
    removeAttribute(name) { delete this.attrs[name] },
    addEventListener(type, fn) { listeners[type] = fn },
    click() { listeners.click?.() },
  }
}

function storage(initial = {}, { broken = false } = {}) {
  const data = { ...initial }
  return {
    data,
    getItem(key) { if (broken) throw new Error('blocked'); return key in data ? data[key] : null },
    setItem(key, value) { if (broken) throw new Error('blocked'); data[key] = String(value) },
    removeItem(key) { if (broken) throw new Error('blocked'); delete data[key] },
  }
}

function run({ stored = {}, broken = false } = {}) {
  const root = element()
  const group = element()
  group.hidden = true
  const buttons = ['light', 'dark', 'system'].map((choice) => element({ 'data-theme-choice': choice }))
  let ready
  const document = {
    documentElement: root,
    querySelector: (selector) => (selector === '.theme' ? group : null),
    querySelectorAll: (selector) => (selector === '[data-theme-choice]' ? buttons : []),
    addEventListener: (type, fn) => { if (type === 'DOMContentLoaded') ready = fn },
  }
  const localStorage = storage(stored, { broken })

  new Function('document', 'localStorage', source)(document, localStorage)

  return {
    root,
    group,
    localStorage,
    ready: () => ready(),
    button: (choice) => buttons.find((b) => b.getAttribute('data-theme-choice') === choice),
    pressed: () => buttons.filter((b) => b.getAttribute('aria-pressed') === 'true').map((b) => b.getAttribute('data-theme-choice')),
  }
}

describe('theme script', () => {
  it('applies a stored choice before the page paints', () => {
    expect(run({ stored: { theme: 'dark' } }).root.getAttribute('data-theme')).toBe('dark')
    expect(run({ stored: { theme: 'light' } }).root.getAttribute('data-theme')).toBe('light')
  })

  it('leaves the theme to the device when nothing is stored', () => {
    expect(run().root.getAttribute('data-theme')).toBeNull()
  })

  it('ignores a stored value it does not know', () => {
    expect(run({ stored: { theme: 'purple' } }).root.getAttribute('data-theme')).toBeNull()
  })

  it('still works when storage is blocked', () => {
    const page = run({ broken: true })
    page.ready()
    page.button('dark').click()

    expect(page.root.getAttribute('data-theme')).toBe('dark')
  })

  it('shows the toggle and marks the current choice', () => {
    const page = run({ stored: { theme: 'light' } })
    page.ready()

    expect(page.group.hidden).toBe(false)
    expect(page.pressed()).toEqual(['light'])
  })

  it('marks System when nothing is stored', () => {
    const page = run()
    page.ready()

    expect(page.pressed()).toEqual(['system'])
  })

  it('applies and remembers a picked theme', () => {
    const page = run()
    page.ready()
    page.button('dark').click()

    expect(page.root.getAttribute('data-theme')).toBe('dark')
    expect(page.localStorage.data.theme).toBe('dark')
    expect(page.pressed()).toEqual(['dark'])
  })

  it('goes back to the device setting when System is picked', () => {
    const page = run({ stored: { theme: 'dark' } })
    page.ready()
    page.button('system').click()

    expect(page.root.getAttribute('data-theme')).toBeNull()
    expect(page.localStorage.data).not.toHaveProperty('theme')
    expect(page.pressed()).toEqual(['system'])
  })
})
