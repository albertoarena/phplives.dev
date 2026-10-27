import { readFileSync } from 'node:fs'
import { describe, expect, it } from 'vitest'

const source = readFileSync(new URL('../../assets/week.js', import.meta.url), 'utf8')

const STEP = 310
const PAD = 16

function element(attrs = {}, { hidden = false } = {}) {
  const listeners = {}
  return {
    attrs: { ...attrs },
    hidden,
    textContent: '',
    innerHTML: '',
    listeners,
    getAttribute(name) { return name in this.attrs ? this.attrs[name] : null },
    addEventListener(type, fn) { listeners[type] = fn },
  }
}

// The This week row: three labels, each 296px wide, 310px apart, in a row 393px wide.
// The row scrolls sideways when it is narrower than its labels, as on a phone.
function run({ overflows = true, marker = true, width = 393 } = {}) {
  const row = element({ 'data-week-row': '' })
  const labels = [0, 1, 2].map(() => element({ 'data-stack': 'php' }))
  const shown = () => labels.filter((label) => !label.hidden)
  row.scrollLeft = 0
  row.clientWidth = width
  Object.defineProperty(row, 'scrollWidth', { get: () => (overflows ? PAD * 2 + shown().length * STEP - 14 : 393) })
  row.getBoundingClientRect = () => ({ left: 0, width })
  row.querySelectorAll = (selector) => (selector === '[data-stack]' ? labels : [])
  labels.forEach((label) => {
    label.getBoundingClientRect = () => ({ left: PAD + shown().indexOf(label) * STEP - row.scrollLeft, width: 296 })
  })

  const pos = element({ 'data-week-pos': '' }, { hidden: true })
  const n = element({ 'data-week-n': '' })
  const dots = element({ 'data-week-dots': '' })
  const hint = element({ 'data-week-hint': '' })
  pos.querySelector = (selector) => ({ '[data-week-n]': n, '[data-week-dots]': dots, '[data-week-hint]': hint })[selector] ?? null

  const document = {
    querySelector: (selector) => ({ '[data-week-row]': row, '[data-week-pos]': marker ? pos : null })[selector] ?? null,
  }
  const window = element()
  const observers = []
  class MutationObserver {
    constructor(fn) { this.fn = fn; observers.push(this) }
    observe(target, options) { this.target = target; this.options = options }
  }

  new Function('document', 'window', 'MutationObserver', source)(document, window, MutationObserver)

  return {
    row, labels, pos, n, dots, hint, window, observers,
    scrollTo(left) { row.scrollLeft = left; row.listeners.scroll() },
    filter(...hidden) { labels.forEach((label, i) => { label.hidden = hidden.includes(i) }); observers.forEach((o) => o.fn([])) },
  }
}

describe('This week marker', () => {
  it('stays hidden while the row does not scroll', () => {
    expect(run({ overflows: false }).pos.hidden).toBe(true)
  })

  it('shows the first of three labels on a phone', () => {
    const page = run()

    expect(page.pos.hidden).toBe(false)
    expect(page.n.textContent).toBe('1 / 3')
    expect(page.dots.innerHTML).toBe('<i class="on"></i><i></i><i></i>')
    expect(page.hint.hidden).toBe(false)
  })

  it('follows the row as it is swiped', () => {
    const page = run()

    page.scrollTo(STEP)

    expect(page.n.textContent).toBe('2 / 3')
    expect(page.dots.innerHTML).toBe('<i></i><i class="on"></i><i></i>')
  })

  it('marks the first label in view when two fit, as on a tablet', () => {
    const page = run({ width: 768 })

    expect(page.n.textContent).toBe('1 / 3')
    page.scrollTo(page.row.scrollWidth - page.row.clientWidth)
    expect(page.n.textContent).toBe('3 / 3')
  })

  it('marks the last label at the end of the row, and drops the hint', () => {
    const page = run()

    page.scrollTo(page.row.scrollWidth - page.row.clientWidth)

    expect(page.n.textContent).toBe('3 / 3')
    expect(page.hint.hidden).toBe(true)
  })

  it('counts only the labels that the filter shows', () => {
    const page = run()

    page.filter(1)

    expect(page.n.textContent).toBe('1 / 2')
    expect(page.dots.innerHTML).toBe('<i class="on"></i><i></i>')
  })

  it('hides itself when the filter leaves one label', () => {
    const page = run()

    page.filter(0, 2)

    expect(page.pos.hidden).toBe(true)
  })

  it('watches the labels for the filter, and the window for a new width', () => {
    const page = run()

    expect(page.observers[0].target).toBe(page.row)
    expect(page.observers[0].options).toEqual({ attributes: true, attributeFilter: ['hidden'], subtree: true })
    expect(typeof page.window.listeners.resize).toBe('function')
  })

  it('does nothing on a page without the row', () => {
    expect(() => run({ marker: false })).not.toThrow()
  })
})
