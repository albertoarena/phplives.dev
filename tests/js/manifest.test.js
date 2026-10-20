import { readFileSync } from 'node:fs'
import { describe, expect, it } from 'vitest'

const source = readFileSync(new URL('../../assets/manifest.js', import.meta.url), 'utf8')

function element(attrs = {}, { hidden = false, value = '' } = {}) {
  const listeners = {}
  return {
    attrs: { ...attrs },
    hidden,
    value,
    textContent: '',
    getAttribute(name) { return name in this.attrs ? this.attrs[name] : null },
    setAttribute(name, value) { this.attrs[name] = String(value) },
    addEventListener(type, fn) { listeners[type] = fn },
    click() { listeners.click?.() },
    change(value) { this.value = value; listeners.change?.() },
  }
}

function storage(initial = {}, { broken = false } = {}) {
  const data = { ...initial }
  return {
    data,
    getItem(key) { if (broken) throw new Error('blocked'); return key in data ? data[key] : null },
    setItem(key, value) { if (broken) throw new Error('blocked'); data[key] = String(value) },
  }
}

// A main page with three entries (php, laravel, laravel) in two views, or the archive (one view).
function run({ hash = '', stored = {}, broken = false, select = false, views = true } = {}) {
  const stacks = ['php', 'laravel', 'laravel']
  const items = []
  if (views) items.push(...stacks.map((stack) => element({ 'data-stack': stack })))
  items.push(...stacks.map((stack) => element({ 'data-stack': stack })))

  const choices = [['', '', 3], ['laravel', 'Laravel', 2], ['php', 'plain PHP', 1]]
  const chips = select ? [] : choices.map(([stack, label, count]) => element({
    'data-stack-choice': stack, 'data-stack-label': label, 'data-count': count, 'aria-pressed': stack === '' ? 'true' : 'false',
  }))
  const dropdown = select ? element({ 'data-stack-select': '' }, { value: '' }) : null
  if (dropdown) {
    dropdown.options = choices.map(([stack, label, count]) => element({ value: stack, 'data-stack-label': label, 'data-count': count }))
  }

  const viewButtons = views ? ['labels', 'list'].map((view) => element({ 'data-view-choice': view, 'aria-pressed': view === 'labels' ? 'true' : 'false' })) : []
  const panes = views ? [element({ 'data-view': 'labels' }), element({ 'data-view': 'list' }, { hidden: true })] : []
  const tools = element({ 'data-tools': '' }, { hidden: true })
  const status = element({ 'data-filter-status': '', 'data-total': '3' })

  // Two bundles in the first view: one with the php entry, one with the two laravel entries.
  const groups = [[items[0]], [items[1], items[2]]].map((members) => {
    const group = element({ 'data-group': '' })
    const count = element({ 'data-group-count': '' })
    count.textContent = members.length + (members.length === 1 ? ' parcel' : ' parcels')
    group.count = count
    group.querySelectorAll = (selector) => (selector === '[data-stack]' ? members : [])
    group.querySelector = (selector) => (selector === '[data-group-count]' ? count : null)
    return group
  })

  const lists = {
    '[data-stack]': items,
    '[data-stack-choice]': chips,
    '[data-view-choice]': viewButtons,
    '[data-view]': panes,
    '[data-tools]': [tools],
    '[data-group]': groups,
  }
  const document = {
    querySelectorAll: (selector) => lists[selector] ?? [],
    querySelector: (selector) => ({ '[data-filter-status]': status, 'select[data-stack-select]': dropdown })[selector] ?? null,
  }
  const location = { pathname: '/', hash }
  const urls = []
  const history = { replaceState: (state, title, url) => { urls.push(url); location.hash = url.includes('#') ? url.slice(url.indexOf('#')) : '' } }
  const localStorage = storage(stored, { broken })

  new Function('document', 'location', 'history', 'localStorage', source)(document, location, history, localStorage)

  const shown = () => items.slice(0, 3).filter((item) => !item.hidden).map((item) => item.getAttribute('data-stack'))

  return {
    items, groups, chips, dropdown, viewButtons, panes, tools, status, urls, localStorage, shown,
    chip: (stack) => chips.find((chip) => chip.getAttribute('data-stack-choice') === stack),
    view: (name) => viewButtons.find((button) => button.getAttribute('data-view-choice') === name),
    pressed: (list) => list.filter((el) => el.getAttribute('aria-pressed') === 'true').map((el) => el.getAttribute('data-stack-choice') ?? el.getAttribute('data-view-choice')),
  }
}

describe('manifest script', () => {
  it('shows the tools once it runs', () => {
    expect(run().tools.hidden).toBe(false)
  })

  it('shows every entry and says nothing until a stack is picked', () => {
    const page = run()

    expect(page.shown()).toEqual(['php', 'laravel', 'laravel'])
    expect(page.status.textContent).toBe('')
    expect(page.urls).toEqual([])
  })

  it('keeps only the entries of the picked stack, in both views', () => {
    const page = run()

    page.chip('laravel').click()

    expect(page.items.filter((item) => !item.hidden).map((item) => item.getAttribute('data-stack'))).toEqual(['laravel', 'laravel', 'laravel', 'laravel'])
    expect(page.pressed(page.chips)).toEqual(['laravel'])
    expect(page.status.textContent).toBe('Showing 2 of 3 parcels: Laravel.')
    expect(page.urls.at(-1)).toBe('/#stack=laravel')
  })

  it('clears the filter with All, or by picking the same stack again', () => {
    const page = run()

    page.chip('laravel').click()
    page.chip('').click()
    expect(page.shown()).toEqual(['php', 'laravel', 'laravel'])
    expect(page.pressed(page.chips)).toEqual([''])
    expect(page.status.textContent).toBe('')
    expect(page.urls.at(-1)).toBe('/')

    page.chip('php').click()
    page.chip('php').click()
    expect(page.shown()).toEqual(['php', 'laravel', 'laravel'])
  })

  it('switches between labels and list, and remembers the choice', () => {
    const page = run()

    page.view('list').click()

    expect(page.panes.map((pane) => pane.hidden)).toEqual([true, false])
    expect(page.pressed(page.viewButtons)).toEqual(['list'])
    expect(page.localStorage.data.view).toBe('list')
    expect(page.urls.at(-1)).toBe('/#view=list')

    page.view('labels').click()
    expect(page.panes.map((pane) => pane.hidden)).toEqual([false, true])
    expect(page.localStorage.data.view).toBe('labels')
    expect(page.urls.at(-1)).toBe('/')
  })

  it('opens the view and the stack that the link asks for', () => {
    const page = run({ hash: '#stack=php&view=list' })

    expect(page.shown()).toEqual(['php'])
    expect(page.panes.map((pane) => pane.hidden)).toEqual([true, false])
    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: plain PHP.')
  })

  it('opens the view the visitor chose last time, unless the link says otherwise', () => {
    expect(run({ stored: { view: 'list' } }).panes.map((pane) => pane.hidden)).toEqual([true, false])
    expect(run({ stored: { view: 'list' }, hash: '#view=labels' }).panes.map((pane) => pane.hidden)).toEqual([false, true])
  })

  it('ignores a stack it does not know, and other links on the page', () => {
    const unknown = run({ hash: '#stack=wordpress' })
    expect(unknown.shown()).toEqual(['php', 'laravel', 'laravel'])
    expect(unknown.pressed(unknown.chips)).toEqual([''])

    const anchor = run({ hash: '#devnull' })
    expect(anchor.urls).toEqual([])
    expect(anchor.shown()).toEqual(['php', 'laravel', 'laravel'])
  })

  it('works without storage', () => {
    const page = run({ broken: true })

    page.view('list').click()

    expect(page.panes.map((pane) => pane.hidden)).toEqual([true, false])
  })

  it('filters from a dropdown when there are too many stacks', () => {
    const page = run({ select: true })

    page.dropdown.change('php')

    expect(page.shown()).toEqual(['php'])
    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: plain PHP.')
    expect(run({ select: true, hash: '#stack=laravel' }).dropdown.value).toBe('laravel')
  })

  it('filters the archive, which has no views', () => {
    const page = run({ views: false })

    page.chip('php').click()

    expect(page.shown()).toEqual(['php'])
    expect(page.urls.at(-1)).toBe('/#stack=php')
  })

  it('hides a bundle with no entry of the picked stack, and shows it again', () => {
    const page = run()

    page.chip('laravel').click()
    expect(page.groups.map((group) => group.hidden)).toEqual([true, false])

    page.chip('').click()
    expect(page.groups.map((group) => group.hidden)).toEqual([false, false])
  })

  it('counts only the entries a bundle shows', () => {
    const page = run()

    page.chip('php').click()
    expect(page.groups.map((group) => group.count.textContent)).toEqual(['1 parcel', '0 parcels'])

    page.chip('').click()
    expect(page.groups.map((group) => group.count.textContent)).toEqual(['1 parcel', '2 parcels'])
  })
})
