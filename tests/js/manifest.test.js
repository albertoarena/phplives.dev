import { readFileSync } from 'node:fs'
import { afterEach, describe, expect, it, vi } from 'vitest'

const source = readFileSync(new URL('../../assets/manifest.js', import.meta.url), 'utf8')

function element(attrs = {}, { hidden = false, value = '', tagName = 'DIV' } = {}) {
  const listeners = {}
  return {
    listeners,
    attrs: { ...attrs },
    hidden,
    value,
    tagName,
    focused: 0,
    blurred: 0,
    textContent: '',
    focus() { this.focused++ },
    blur() { this.blurred++ },
    getAttribute(name) { return name in this.attrs ? this.attrs[name] : null },
    setAttribute(name, value) { this.attrs[name] = String(value) },
    addEventListener(type, fn) { listeners[type] = fn },
    click() { listeners.click?.() },
    change(value) { this.value = value; listeners.change?.() },
    type(value) { this.value = value; listeners.input?.() },
    key(key, extra = {}) {
      const event = { key, target: this, defaultPrevented: false, preventDefault() { this.defaultPrevented = true }, ...extra }
      listeners.keydown?.(event)
      return event
    },
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

// The words of each entry, as the build writes them for the search.
const INDEX = {
  mollie: 'Mollie Payment Module for Magento 2 Takes payments in Magento. Mollie mollie mollie/magento2 plain PHP Testing',
  vigilance: 'Laravel Vigilance A control center for Laravel queues and jobs. Anas B. anasb Laravel E-commerce Testing MCP',
  excel: 'Laravel Excel 4.0 A new major version. Queued imports with #[Queue]. Café Crème Spartner maatwebsite/excel Laravel MCP',
}

// The parts of the Map view. Stack hubs: php, laravel. Topic hubs: testing, mcp, none (Other).
// Sorted by stack, the bins are topics; sorted by topic, they are stacks. "All" has the value "".
function mapParts(limit, week) {
  const counted = (attrs) => {
    const el = element(attrs, { tagName: 'BUTTON' })
    const count = element({ 'data-map-count': '' })
    el.count = count
    el.querySelector = (selector) => (selector === '[data-map-count]' ? count : null)
    return el
  }
  const hub = (kind, value, label) => counted({ [`data-${kind}-choice`]: value, [`data-${kind}-label`]: label, 'data-map-hub': '', 'aria-pressed': 'false', 'aria-expanded': 'false' })
  const bin = (sort, kind, value, label) => counted({ [`data-${kind}-choice`]: value, [`data-${kind}-label`]: label, 'data-map-bin': sort, 'aria-pressed': value === '' ? 'true' : 'false' })

  const stackHubs = [hub('stack', 'php', 'plain PHP'), hub('stack', 'laravel', 'Laravel')]
  const topicHubs = [hub('topic', 'testing', 'Testing'), hub('topic', 'mcp', 'MCP'), hub('topic', 'none', 'Other')]
  const topicBins = [bin('stack', 'topic', '', ''), bin('stack', 'topic', 'testing', 'Testing'), bin('stack', 'topic', 'mcp', 'MCP'), bin('stack', 'topic', 'none', 'Other')]
  const stackBins = [bin('topic', 'stack', '', ''), bin('topic', 'stack', 'php', 'plain PHP'), bin('topic', 'stack', 'laravel', 'Laravel')]

  // The This Week hub, first in each list. It is not a stack or a topic choice.
  const weekHubs = week ? ['stack', 'topic'].map((sort) => counted({ 'data-map-hub': '', 'data-map-week': sort, 'data-map-week-label': 'This week', 'aria-expanded': 'false' })) : []

  const nodes = [...stackHubs, ...topicHubs, ...weekHubs].map((button) => {
    const li = element({ 'data-map-node': '' })
    li.hub = button
    li.children = []
    li.querySelector = (selector) => (selector === '[data-map-hub]' ? button : null)
    li.appendChild = (child) => { li.children.push(child); child.parent = li }
    return li
  })
  const hubLists = ['stack', 'topic'].map((sort) => element({ 'data-map-hubs': sort }, { hidden: sort !== 'stack' }))
  const binGroups = ['stack', 'topic'].map((sort) => element({ 'data-map-bins': sort }, { hidden: sort !== 'stack' }))
  const sortButtons = ['stack', 'topic'].map((sort) => element({ 'data-map-sort-choice': sort, 'aria-pressed': sort === 'stack' ? 'true' : 'false' }, { tagName: 'BUTTON' }))
  const sortGroup = element({ 'data-map-sort': '' }, { hidden: true })
  const filterBoxes = [element({ 'data-filter': '' }), element({ 'data-filter': '' })]
  const root = element({ 'data-map': '', 'data-map-limit': String(limit) })
  const branch = element({ 'data-map-branch': '' }, { hidden: true })
  const title = element({ 'data-map-title': '' })
  const total = element({ 'data-map-total': '' })
  const depot = element({ 'data-map-depot': '' })
  const more = element({ 'data-map-more': '' }, { hidden: true, tagName: 'BUTTON' })
  const only = element({ 'data-map-only': '' }, { hidden: true })
  const onlyKind = element({ 'data-map-only-kind': '' })
  const onlyLabel = element({ 'data-map-only-label': '' })
  const onlyClear = element({ 'data-map-only-clear': '' }, { tagName: 'BUTTON' })
  // The stacks on the manifest slip: links that open the Map on a branch.
  const slipLinks = ['laravel', 'php'].map((stack) => {
    const link = element({ 'data-map-open': stack, href: `/#stack=${stack}&view=map` }, { tagName: 'A' })
    link.click = function () { const event = { defaultPrevented: false, preventDefault() { this.defaultPrevented = true } }; this.listeners?.click?.(event); return event }
    return link
  })

  const find = (list, value) => list.find((el) => (el.getAttribute('data-stack-choice') ?? el.getAttribute('data-topic-choice')) === value)
  return {
    stackButtons: [...stackHubs, ...stackBins],
    topicButtons: [...topicHubs, ...topicBins],
    nodes, hubLists, binGroups, sortButtons, sortGroup, filterBoxes, root, branch, title, total, depot, more, only, onlyKind, onlyLabel, onlyClear, slipLinks, weekHubs,
    weekHub: (sort) => weekHubs.find((hub) => hub.getAttribute('data-map-week') === sort),
    weekNode: (sort) => nodes.find((li) => li.hub.getAttribute('data-map-week') === sort),
    lists: {
      '[data-map-node]': nodes,
      '[data-map-bin]': [...topicBins, ...stackBins],
      '[data-map-hubs]': hubLists,
      '[data-map-bins]': binGroups,
      '[data-map-sort-choice]': sortButtons,
      '[data-map-sort]': [sortGroup],
      '[data-filter]': filterBoxes,
      '[data-map-open]': slipLinks,
    },
    singles: {
      '[data-map]': root,
      '[data-map-branch]': branch,
      '[data-map-title]': title,
      '[data-map-total]': total,
      '[data-map-depot]': depot,
      '[data-map-more]': more,
      '[data-map-only]': only,
      '[data-map-only-kind]': onlyKind,
      '[data-map-only-label]': onlyLabel,
      '[data-map-only-clear]': onlyClear,
    },
    stackHub: (value) => find(stackHubs, value),
    topicHub: (value) => find(topicHubs, value),
    topicBin: (value) => find(topicBins, value),
    stackBin: (value) => find(stackBins, value),
    sort: (value) => sortButtons.find((el) => el.getAttribute('data-map-sort-choice') === value),
    // The count each hub or bin shows, or null when it is hidden. A hub hides with its node.
    counts: (list) => Object.fromEntries(list.map((el) => {
      const node = nodes.find((li) => li.hub === el)
      const gone = el.hidden || (node && node.hidden)
      return [el.getAttribute('data-stack-choice') ?? el.getAttribute('data-topic-choice'), gone ? null : el.count.textContent]
    })),
    hubs: { stack: stackHubs, topic: topicHubs },
    bins: { stack: topicBins, topic: stackBins },
  }
}

// A main page with three entries in two views, or the archive (one view).
// Entry 0 is php and testing, entry 1 is laravel with testing and mcp, entry 2 is laravel and mcp.
function run({ hash = '', stored = {}, broken = false, select = false, topicSelect = false, views = true, index = INDEX, map = false, topics = ['testing', 'testing mcp', 'mcp'], limit = 6, week = null } = {}) {
  const stacks = ['php', 'laravel', 'laravel']
  const slugs = ['mollie', 'vigilance', 'excel']
  const items = []
  const make = (attrs = {}) => stacks.map((stack, i) => element({ 'data-stack': stack, 'data-topics': topics[i], 'data-slug': slugs[i], ...attrs }))
  if (views) items.push(...make())
  items.push(...make())
  // The Map view holds one more copy of the entries: the stubs on its shelf.
  if (map) {
    const shelf = make({ 'data-map-stub': '' })
    // The stubs added this week carry data-map-this-week.
    if (week) shelf.forEach((stub) => { if (week.includes(stub.getAttribute('data-slug'))) stub.setAttribute('data-map-this-week', '') })
    items.push(...shelf)
  }

  const choices = [['', '', 3], ['laravel', 'Laravel', 2], ['php', 'plain PHP', 1]]
  const chips = select ? [] : choices.map(([stack, label, count]) => element({
    'data-stack-choice': stack, 'data-stack-label': label, 'data-count': count, 'aria-pressed': stack === '' ? 'true' : 'false',
  }))
  const dropdown = select ? element({ 'data-stack-select': '' }, { value: '' }) : null
  if (dropdown) {
    dropdown.options = choices.map(([stack, label, count]) => element({ value: stack, 'data-stack-label': label, 'data-count': count }))
  }

  const topicChoices = [['', '', 3], ['mcp', 'MCP', 2], ['testing', 'Testing', 2]]
  const topicChips = topicSelect ? [] : topicChoices.map(([topic, label, count]) => element({
    'data-topic-choice': topic, 'data-topic-label': label, 'data-count': count, 'aria-pressed': topic === '' ? 'true' : 'false',
  }))
  const topicDropdown = topicSelect ? element({ 'data-topic-select': '' }, { value: '' }) : null
  if (topicDropdown) {
    topicDropdown.options = topicChoices.map(([topic, label, count]) => element({ value: topic, 'data-topic-label': label, 'data-count': count }))
  }

  const viewNames = map ? ['labels', 'list', 'map'] : ['labels', 'list']
  const viewButtons = views ? viewNames.map((view) => element({ 'data-view-choice': view, 'aria-pressed': view === 'labels' ? 'true' : 'false' })) : []
  const panes = views ? viewNames.map((view) => element({ 'data-view': view }, { hidden: view !== 'labels' })) : []

  // The Map: a list of hubs and a group of bins for each sort, and the branch that opens
  // under a hub. Hubs and bins are stack and topic choices, like the chips of the filters.
  const m = map ? mapParts(limit, week) : null
  if (m) {
    chips.push(...m.stackButtons)
    topicChips.push(...m.topicButtons)
  }
  const tools = element({ 'data-tools': '' }, { hidden: true })
  const slip = element({ 'data-tools': '' }, { hidden: true })
  const status = element({ 'data-filter-status': '', 'data-total': '3' })
  const input = element({ 'data-search': '' }, { tagName: 'INPUT' })
  const clear = element({ 'data-search-clear': '' }, { hidden: true, tagName: 'BUTTON' })
  const key = element({ 'data-search-key': '' })
  const none = element({ 'data-search-none': '' }, { hidden: true })
  const noneTitle = element({ 'data-search-none-title': '' })
  noneTitle.textContent = 'No project matches.'
  const reset = element({ 'data-search-reset': '' }, { tagName: 'BUTTON' })
  reset.textContent = 'Clear the search'
  const indexScript = index === null ? null : element({ id: 'search-index' })
  if (indexScript) indexScript.textContent = JSON.stringify(index)

  // Two bundles in the first view: one with the php entry, one with the two laravel entries.
  // The second has a note with one text for each number of entries it can show, like This week.
  const groups = [[items[0]], [items[1], items[2]]].map((members, g) => {
    const group = element({ 'data-group': '' })
    const count = element({ 'data-group-count': '' })
    count.textContent = members.length + (members.length === 1 ? ' parcel' : ' parcels')
    group.count = count
    const note = g === 1 ? element({ 'data-group-notes': JSON.stringify(['Here is the newest 1.', 'Here are the newest 2.']) }) : null
    if (note) note.textContent = 'Here are the newest 2.'
    group.note = note
    group.querySelectorAll = (selector) => (selector === '[data-stack]' ? members : [])
    group.querySelector = (selector) => ({ '[data-group-count]': count, '[data-group-notes]': note })[selector] ?? null
    return group
  })

  const lists = {
    '[data-stack]': items,
    '[data-stack-choice]': chips,
    '[data-topic-choice]': topicChips,
    '[data-view-choice]': viewButtons,
    '[data-view]': panes,
    '[data-tools]': [slip, tools],
    '[data-group]': groups,
    ...(m ? m.lists : {}),
  }
  const singles = {
    '[data-filter-status]': status,
    'select[data-stack-select]': dropdown,
    'select[data-topic-select]': topicDropdown,
    '[data-search]': input,
    '[data-search-clear]': clear,
    '[data-search-key]': key,
    '[data-search-none]': none,
    '[data-search-none-title]': noneTitle,
    '[data-search-reset]': reset,
    '#search-index': indexScript,
    ...(m ? m.singles : {}),
  }
  const page = element({}, { tagName: 'BODY' })
  const document = {
    querySelectorAll: (selector) => lists[selector] ?? [],
    querySelector: (selector) => singles[selector] ?? null,
    addEventListener: (type, fn) => page.addEventListener(type, fn),
  }
  // A key pressed on the page, outside the search field.
  const press = (k, extra = {}) => page.key(k, { target: page, ...extra })
  const location = { pathname: '/', hash }
  const urls = []
  const history = { replaceState: (state, title, url) => { urls.push(url); location.hash = url.includes('#') ? url.slice(url.indexOf('#')) : '' } }
  const localStorage = storage(stored, { broken })

  new Function('document', 'location', 'history', 'localStorage', source)(document, location, history, localStorage)

  const shown = () => items.slice(0, 3).filter((item) => !item.hidden).map((item) => item.getAttribute('data-stack'))
  // The numbers of the entries that show in the first view.
  const visible = () => items.slice(0, 3).flatMap((item, i) => (item.hidden ? [] : [i]))

  return {
    items, groups, chips, dropdown, topicChips, topicDropdown, viewButtons, panes, tools, slip, status, urls, localStorage, shown, visible,
    input, clear, key, none, noneTitle, reset, press,
    // The slugs of the entries that show, in each view.
    found: () => items.slice(0, 3).filter((item) => !item.hidden).map((item) => item.getAttribute('data-slug')),
    foundInList: () => items.slice(3, 6).filter((item) => !item.hidden).map((item) => item.getAttribute('data-slug')),
    // The stubs on the Map shelf that show: not hidden by a filter, and not cut by the limit.
    onShelf: () => items.slice(6).filter((item) => !item.hidden && item.getAttribute('data-cut') !== 'true').map((item) => item.getAttribute('data-slug')),
    map: m,
    chip: (stack) => chips.find((chip) => chip.getAttribute('data-stack-choice') === stack),
    topic: (name) => topicChips.find((chip) => chip.getAttribute('data-topic-choice') === name),
    view: (name) => viewButtons.find((button) => button.getAttribute('data-view-choice') === name),
    pressed: (list) => list.filter((el) => el.getAttribute('aria-pressed') === 'true').map((el) => el.getAttribute('data-stack-choice') ?? el.getAttribute('data-topic-choice') ?? el.getAttribute('data-view-choice')),
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

  it('fits the note of a bundle to the number of entries it shows', () => {
    const page = run()

    page.topic('testing').click()
    expect(page.groups[1].note.textContent).toBe('Here is the newest 1.')

    page.topic('').click()
    expect(page.groups[1].note.textContent).toBe('Here are the newest 2.')

    page.input.type('excel')
    expect(page.groups[1].note.textContent).toBe('Here is the newest 1.')
  })

  it('leaves the note alone when its bundle shows nothing', () => {
    const page = run()

    page.chip('php').click()
    expect(page.groups[1].hidden).toBe(true)
    expect(page.groups[1].note.textContent).toBe('Here are the newest 2.')
  })

  it('keeps only the entries of the picked topic, in both views', () => {
    const page = run()

    page.topic('mcp').click()

    expect(page.visible()).toEqual([1, 2])
    expect(page.items.filter((item) => !item.hidden)).toHaveLength(4)
    expect(page.pressed(page.topicChips)).toEqual(['mcp'])
    expect(page.pressed(page.chips)).toEqual([''])
    expect(page.status.textContent).toBe('Showing 2 of 3 parcels: MCP.')
    expect(page.urls.at(-1)).toBe('/#topic=mcp')
  })

  it('keeps only the entries of both the picked stack and the picked topic', () => {
    const page = run()

    page.chip('laravel').click()
    page.topic('testing').click()

    expect(page.visible()).toEqual([1])
    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: Laravel, Testing.')
    expect(page.urls.at(-1)).toBe('/#stack=laravel&topic=testing')

    page.chip('php').click()
    expect(page.visible()).toEqual([0])
    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: plain PHP, Testing.')
  })

  it('clears the topic with All, or by picking the same topic again, and keeps the stack', () => {
    const page = run()

    page.chip('laravel').click()
    page.topic('testing').click()
    page.topic('').click()
    expect(page.visible()).toEqual([1, 2])
    expect(page.pressed(page.topicChips)).toEqual([''])
    expect(page.status.textContent).toBe('Showing 2 of 3 parcels: Laravel.')
    expect(page.urls.at(-1)).toBe('/#stack=laravel')

    page.topic('mcp').click()
    page.topic('mcp').click()
    expect(page.visible()).toEqual([1, 2])
  })

  it('opens the stack, the topic and the view that the link asks for', () => {
    const page = run({ hash: '#stack=laravel&topic=testing&view=list' })

    expect(page.visible()).toEqual([1])
    expect(page.panes.map((pane) => pane.hidden)).toEqual([true, false])
    expect(page.pressed(page.topicChips)).toEqual(['testing'])
    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: Laravel, Testing.')
    expect(run({ hash: '#topic=mcp' }).visible()).toEqual([1, 2])
  })

  it('writes the stack, the topic and the view into the link, in that order', () => {
    const page = run()

    page.topic('testing').click()
    page.view('list').click()
    expect(page.urls.at(-1)).toBe('/#topic=testing&view=list')

    page.chip('php').click()
    expect(page.urls.at(-1)).toBe('/#stack=php&topic=testing&view=list')
  })

  it('ignores a topic it does not know', () => {
    const page = run({ hash: '#topic=robots' })

    expect(page.visible()).toEqual([0, 1, 2])
    expect(page.pressed(page.topicChips)).toEqual([''])
    expect(page.status.textContent).toBe('')

    const both = run({ hash: '#stack=php&topic=robots' })
    expect(both.visible()).toEqual([0])
    expect(both.status.textContent).toBe('Showing 1 of 3 parcels: plain PHP.')
  })

  it('hides a bundle with no entry of the picked topic, and counts the rest', () => {
    const page = run()

    page.topic('mcp').click()
    expect(page.groups.map((group) => group.hidden)).toEqual([true, false])
    expect(page.groups.map((group) => group.count.textContent)).toEqual(['0 parcels', '2 parcels'])

    page.chip('php').click()
    expect(page.groups.map((group) => group.hidden)).toEqual([true, true])
    expect(page.status.textContent).toBe('Showing 0 of 3 parcels: plain PHP, MCP.')

    page.topic('').click()
    expect(page.groups.map((group) => group.hidden)).toEqual([false, true])
  })

  it('filters by topic from a dropdown when there are too many topics', () => {
    const page = run({ topicSelect: true })

    page.topicDropdown.change('testing')

    expect(page.visible()).toEqual([0, 1])
    expect(page.status.textContent).toBe('Showing 2 of 3 parcels: Testing.')
    expect(run({ topicSelect: true, hash: '#topic=mcp' }).topicDropdown.value).toBe('mcp')
  })

  it('filters the archive by topic', () => {
    const page = run({ views: false })

    page.topic('mcp').click()

    expect(page.visible()).toEqual([1, 2])
    expect(page.urls.at(-1)).toBe('/#topic=mcp')
  })

  describe('search', () => {
    afterEach(() => { vi.useRealTimers() })

    it('shows the search field with the other tools', () => {
      expect(run().slip.hidden).toBe(false)
    })

    it('keeps only the entries whose words start with what the visitor types, in both views', () => {
      const page = run()

      page.input.type('magento')
      expect(page.found()).toEqual(['mollie'])
      expect(page.foundInList()).toEqual(['mollie'])

      page.input.type('pay')
      expect(page.found()).toEqual(['mollie'])

      page.input.type('queue')
      expect(page.found()).toEqual(['vigilance', 'excel'])

      page.input.type('agento')
      expect(page.found()).toEqual([])
    })

    it('needs every word to match', () => {
      const page = run()

      page.input.type('laravel queue')
      expect(page.found()).toEqual(['vigilance', 'excel'])

      page.input.type('  Mollie   QUEUE ')
      expect(page.found()).toEqual([])
    })

    it('drops a plural s from a long word, but not from a short one', () => {
      const page = run()

      page.input.type('payments')
      expect(page.found()).toEqual(['mollie'])

      page.input.type('queues')
      expect(page.found()).toEqual(['vigilance', 'excel'])

      // "news" stays "news", so it does not find every "new major version".
      page.input.type('news')
      expect(page.found()).toEqual([])
    })

    it('ignores case, accents and punctuation', () => {
      const page = run()

      page.input.type('CAFE')
      expect(page.found()).toEqual(['excel'])

      page.input.type('crème')
      expect(page.found()).toEqual(['excel'])

      page.input.type('mollie/magento2')
      expect(page.found()).toEqual(['mollie'])

      page.input.type('#[queue]')
      expect(page.found()).toEqual(['vigilance', 'excel'])
    })

    it('finds e-commerce from other words for it', () => {
      const page = run()

      for (const words of ['e-commerce', 'ecommerce', 'shop', 'shops']) {
        page.input.type(words)
        expect(page.found()).toEqual(['vigilance'])
      }
    })

    it('shows every entry again when the field is empty', () => {
      const page = run()

      page.input.type('magento')
      page.input.type('   ')
      expect(page.found()).toEqual(['mollie', 'vigilance', 'excel'])
    })

    it('keeps only the entries that match the search, the stack and the topic', () => {
      const page = run()

      page.input.type('queue')
      page.chip('laravel').click()
      expect(page.found()).toEqual(['vigilance', 'excel'])

      page.topic('testing').click()
      expect(page.found()).toEqual(['vigilance'])

      page.chip('php').click()
      expect(page.found()).toEqual([])
    })

    it('opens the search that the link asks for', () => {
      const page = run({ hash: '#q=laravel%20queue&stack=laravel' })

      expect(page.input.value).toBe('laravel queue')
      expect(page.found()).toEqual(['vigilance', 'excel'])
      expect(page.status.textContent).toBe('Showing 2 of 3 parcels: “laravel queue”, Laravel.')
      expect(page.clear.hidden).toBe(false)
      expect(page.key.hidden).toBe(true)
    })

    it('skips a part of the link that does not decode, and keeps the rest', () => {
      const page = run({ hash: '#q=50%&stack=laravel' })

      expect(page.input.value).toBe('')
      expect(page.pressed(page.chips)).toEqual(['laravel'])
      expect(page.tools.hidden).toBe(false)

      expect(run({ hash: '#q=50%25' }).input.value).toBe('50%')
    })

    it('writes the search into the link, before the stack and the topic', () => {
      const page = run()

      page.input.type('laravel queue')
      expect(page.urls.at(-1)).toBe('/#q=laravel%20queue')

      page.chip('laravel').click()
      expect(page.urls.at(-1)).toBe('/#q=laravel%20queue&stack=laravel')

      page.input.type('')
      expect(page.urls.at(-1)).toBe('/#stack=laravel')
    })

    it('updates the status line a moment after the visitor stops typing', () => {
      vi.useFakeTimers()
      const page = run()

      page.input.type('q')
      page.input.type('qu')
      page.input.type('queue')
      expect(page.status.textContent).toBe('')

      vi.advanceTimersByTime(399)
      expect(page.status.textContent).toBe('')

      vi.advanceTimersByTime(1)
      expect(page.status.textContent).toBe('Showing 2 of 3 parcels: “queue”.')
    })

    it('hides a bundle with no match, and counts the rest', () => {
      const page = run()

      page.input.type('queue')
      expect(page.groups.map((group) => group.hidden)).toEqual([true, false])
      expect(page.groups[1].count.textContent).toBe('2 parcels')

      page.input.type('vigilance')
      expect(page.groups[1].count.textContent).toBe('1 parcel')
    })

    it('says so when nothing matches, and clears the search', () => {
      const page = run()

      page.input.type('stripe')
      expect(page.none.hidden).toBe(false)
      expect(page.noneTitle.textContent).toBe('No project matches “stripe”.')
      expect(page.reset.textContent).toBe('Clear the search')

      page.reset.click()
      expect(page.none.hidden).toBe(true)
      expect(page.input.value).toBe('')
      expect(page.found()).toEqual(['mollie', 'vigilance', 'excel'])
      expect(page.input.focused).toBe(1)
    })

    it('names the filters when nothing matches, and clears them all', () => {
      const page = run()

      page.chip('laravel').click()
      page.input.type('stripe')
      expect(page.noneTitle.textContent).toBe('No project matches “stripe” in Laravel.')
      expect(page.reset.textContent).toBe('Clear all filters')

      page.reset.click()
      expect(page.pressed(page.chips)).toEqual([''])
      expect(page.found()).toEqual(['mollie', 'vigilance', 'excel'])
      expect(page.urls.at(-1)).toBe('/')
    })

    it('says so when a stack and a topic have no entry in common', () => {
      const page = run()

      page.chip('php').click()
      page.topic('mcp').click()
      expect(page.none.hidden).toBe(false)
      expect(page.noneTitle.textContent).toBe('No project matches plain PHP and MCP.')
      expect(page.reset.textContent).toBe('Clear all filters')
    })

    it('shows the Clear button only when the field has text, in place of the key hint', () => {
      const page = run()
      expect([page.clear.hidden, page.key.hidden]).toEqual([true, false])

      page.input.type('queue')
      expect([page.clear.hidden, page.key.hidden]).toEqual([false, true])

      page.clear.click()
      expect([page.clear.hidden, page.key.hidden]).toEqual([true, false])
      expect(page.input.value).toBe('')
      expect(page.input.focused).toBe(1)
      expect(page.found()).toEqual(['mollie', 'vigilance', 'excel'])
    })

    it('goes to the field when the visitor presses /, unless they are typing somewhere', () => {
      const page = run()

      const event = page.press('/')
      expect(page.input.focused).toBe(1)
      expect(event.defaultPrevented).toBe(true)

      page.press('/', { target: element({}, { tagName: 'TEXTAREA' }) })
      page.press('/', { target: element({}, { tagName: 'SELECT' }) })
      page.press('/', { target: page.input })
      page.press('/', { target: { tagName: 'DIV', isContentEditable: true } })
      page.press('/', { ctrlKey: true })
      page.press('/', { metaKey: true })
      expect(page.input.focused).toBe(1)
    })

    it('clears the field with Escape', () => {
      const page = run()

      page.input.type('queue')
      const event = page.input.key('Escape')
      expect(page.input.value).toBe('')
      expect(event.defaultPrevented).toBe(true)
      expect(page.found()).toEqual(['mollie', 'vigilance', 'excel'])
    })

    it('closes the phone keyboard with Enter, and keeps the search', () => {
      const page = run()

      page.input.type('queue')
      page.input.key('Enter')
      expect(page.input.blurred).toBe(1)
      expect(page.input.value).toBe('queue')
      expect(page.found()).toEqual(['vigilance', 'excel'])
    })

    it('searches the archive, which has no views', () => {
      const page = run({ views: false })

      page.input.type('magento')
      expect(page.items.filter((item) => !item.hidden).map((item) => item.getAttribute('data-slug'))).toEqual(['mollie'])
    })

    it('matches nothing for an entry missing from the index, and still works without one', () => {
      const page = run({ index: { mollie: INDEX.mollie } })
      page.input.type('laravel')
      expect(page.found()).toEqual([])

      const bare = run({ index: null })
      expect(bare.found()).toEqual(['mollie', 'vigilance', 'excel'])
      bare.input.type('magento')
      expect(bare.found()).toEqual([])
    })
  })
})

describe('map view', () => {
  it('adds Map to the views, keeps it in the link and remembers it', () => {
    const page = run({ map: true })

    page.view('map').click()

    expect(page.panes.map((pane) => pane.hidden)).toEqual([true, true, false])
    expect(page.urls.at(-1)).toBe('/#view=map')
    expect(page.localStorage.data.view).toBe('map')
    expect(page.map.sortGroup.hidden).toBe(false)
    expect(page.map.filterBoxes.map((box) => box.hidden)).toEqual([true, true])

    page.view('labels').click()

    expect(page.map.sortGroup.hidden).toBe(true)
    expect(page.map.filterBoxes.map((box) => box.hidden)).toEqual([false, false])
  })

  it('opens the Map from the link or from the stored view', () => {
    expect(run({ map: true, hash: '#view=map' }).panes[2].hidden).toBe(false)
    expect(run({ map: true, stored: { view: 'map' } }).panes[2].hidden).toBe(false)
  })

  it('falls back to Labels for a stored Map on a page without one', () => {
    const page = run({ stored: { view: 'map' } })

    expect(page.panes.map((pane) => pane.hidden)).toEqual([false, true])
  })

  it('opens with every branch closed, and counts the parcels of each hub', () => {
    const page = run({ map: true, hash: '#view=map' })

    expect(page.map.branch.hidden).toBe(true)
    expect(page.map.counts(page.map.hubs.stack)).toEqual({ php: '1', laravel: '2' })
    expect(page.map.hubs.stack.map((hub) => hub.getAttribute('aria-expanded'))).toEqual(['false', 'false'])
    expect(page.map.depot.textContent).toBe('3')
  })

  it('opens a stack under its hub, with its parcels and its topics', () => {
    const page = run({ map: true, hash: '#view=map' })

    page.map.stackHub('laravel').click()

    expect(page.map.branch.hidden).toBe(false)
    expect(page.map.branch.parent).toBe(page.map.nodes[1])
    expect(page.map.stackHub('laravel').getAttribute('aria-expanded')).toBe('true')
    expect(page.onShelf()).toEqual(['vigilance', 'excel'])
    expect(page.map.counts(page.map.bins.stack)).toEqual({ '': '2', testing: '1', mcp: '2', none: null })
    expect(page.map.title.textContent).toBe('Laravel')
    expect(page.map.total.textContent).toBe('2 parcels')
    expect(page.status.textContent).toBe('Showing 2 of 3 parcels: Laravel.')
    expect(page.urls.at(-1)).toBe('/#stack=laravel&view=map')
  })

  it('narrows a branch with a topic bin, and closes it from its hub', () => {
    const page = run({ map: true, hash: '#view=map' })

    page.map.stackHub('laravel').click()
    page.map.topicBin('testing').click()

    expect(page.onShelf()).toEqual(['vigilance'])
    expect(page.map.title.textContent).toBe('Laravel, Testing')
    expect(page.map.total.textContent).toBe('1 parcel')
    expect(page.map.counts(page.map.hubs.stack)).toEqual({ php: '1', laravel: '1' })
    expect(page.urls.at(-1)).toBe('/#stack=laravel&topic=testing&view=map')

    page.map.stackHub('laravel').click()

    expect(page.map.branch.hidden).toBe(true)
  })

  it('keeps the same choice when the visitor goes back to Labels', () => {
    const page = run({ map: true, hash: '#view=map' })

    page.map.stackHub('laravel').click()
    page.view('labels').click()

    expect(page.found()).toEqual(['vigilance', 'excel'])
    expect(page.pressed(page.chips.slice(0, 3))).toEqual(['laravel'])
    expect(page.urls.at(-1)).toBe('/#stack=laravel')
  })

  it('sorts by topic, with stacks as the bins', () => {
    const page = run({ map: true, hash: '#view=map' })

    page.map.sort('topic').click()

    expect(page.map.hubLists.map((list) => list.hidden)).toEqual([true, false])
    expect(page.map.binGroups.map((group) => group.hidden)).toEqual([true, false])
    expect(page.map.sortButtons.map((button) => button.getAttribute('aria-pressed'))).toEqual(['false', 'true'])
    expect(page.map.counts(page.map.hubs.topic)).toEqual({ testing: '2', mcp: '2', none: null })
    expect(page.urls.at(-1)).toBe('/#view=map&sort=topic')

    page.map.topicHub('mcp').click()

    expect(page.map.branch.parent).toBe(page.map.nodes[3])
    expect(page.map.counts(page.map.bins.topic)).toEqual({ '': '2', php: null, laravel: '2' })
    expect(page.map.title.textContent).toBe('MCP')
    expect(page.urls.at(-1)).toBe('/#topic=mcp&view=map&sort=topic')
  })

  it('reads the sort from the link, and ignores an unknown one', () => {
    expect(run({ map: true, hash: '#view=map&sort=topic' }).map.hubLists[1].hidden).toBe(false)
    expect(run({ map: true, hash: '#view=map&sort=colour' }).map.hubLists[0].hidden).toBe(false)
  })

  it('finds the parcels with no topic under Other', () => {
    const page = run({ map: true, hash: '#view=map&sort=topic', topics: ['testing', 'testing mcp', ''] })

    expect(page.map.counts(page.map.hubs.topic)).toEqual({ testing: '2', mcp: '1', none: '1' })

    page.map.topicHub('none').click()

    expect(page.onShelf()).toEqual(['excel'])
    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: Other.')
    expect(page.urls.at(-1)).toBe('/#topic=none&view=map&sort=topic')
  })

  it('opens Other from a link', () => {
    const page = run({ map: true, hash: '#topic=none&view=map', topics: ['testing', 'testing mcp', ''] })

    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: Other.')
    expect(page.map.counts(page.map.hubs.stack)).toEqual({ php: null, laravel: '1' })
  })

  it('prunes the map while the visitor types', () => {
    const page = run({ map: true, hash: '#view=map' })

    page.input.type('excel')

    expect(page.map.counts(page.map.hubs.stack)).toEqual({ php: null, laravel: '1' })
    expect(page.map.depot.textContent).toBe('1')
  })

  it('keeps an open hub in view while a search leaves it empty', () => {
    const page = run({ map: true, hash: '#stack=php&view=map' })

    page.input.type('excel')

    expect(page.map.counts(page.map.hubs.stack)).toEqual({ php: '0', laravel: '1' })
    expect(page.none.hidden).toBe(false)
  })

  it('shows the first stubs of a branch, then all of them', () => {
    const page = run({ map: true, hash: '#stack=laravel&view=map', limit: 1 })

    expect(page.onShelf()).toEqual(['vigilance'])
    expect(page.map.more.hidden).toBe(false)
    expect(page.map.more.textContent).toBe('Show all 2')

    page.map.more.click()

    expect(page.onShelf()).toEqual(['vigilance', 'excel'])
    expect(page.map.more.textContent).toBe('Show fewer')

    page.map.topicBin('mcp').click()

    expect(page.onShelf()).toEqual(['vigilance'])
    expect(page.map.more.textContent).toBe('Show all 2')
  })

  it('hides the button when the branch fits', () => {
    const page = run({ map: true, hash: '#stack=php&view=map', limit: 1 })

    expect(page.map.more.hidden).toBe(true)
  })
})

describe('counts that follow the picks', () => {
  it('counts each choice of one filter within the pick of the other', () => {
    const page = run({ select: true, topicSelect: true })

    page.topicDropdown.change('mcp')

    expect(page.dropdown.options.map((option) => option.textContent)).toEqual(['All (2)', 'Laravel (2)', 'plain PHP (0)'])
    expect(page.topicDropdown.options.map((option) => option.textContent)).toEqual(['All (3)', 'MCP (2)', 'Testing (2)'])

    page.dropdown.change('php')

    expect(page.topicDropdown.options.map((option) => option.textContent)).toEqual(['All (1)', 'MCP (0)', 'Testing (1)'])
  })

  it('counts within the search too', () => {
    const page = run({ select: true, topicSelect: true })

    page.input.type('excel')

    expect(page.dropdown.options.map((option) => option.textContent)).toEqual(['All (1)', 'Laravel (1)', 'plain PHP (0)'])
  })

  it('writes the count of a chip in its number and its name for screen readers', () => {
    const page = run()
    const chip = page.chip('php')
    const n = element({})
    chip.querySelector = (selector) => (selector === '.n' ? n : null)
    chip.setAttribute('aria-label', 'PHP, plain PHP, 1 entry')

    page.topic('mcp').click()

    expect(n.textContent).toBe('0')
    expect(chip.getAttribute('aria-label')).toBe('PHP, plain PHP, 0 entries')
  })
})

describe('map filter note', () => {
  it('shows a filter that no open hub shows, and clears it', () => {
    const page = run({ map: true, hash: '#topic=mcp&view=map' })

    expect(page.map.only.hidden).toBe(false)
    expect(page.map.onlyKind.textContent).toBe('Topic')
    expect(page.map.onlyLabel.textContent).toBe('MCP')
    expect(page.map.depot.textContent).toBe('2')
    expect(page.map.counts(page.map.hubs.stack)).toEqual({ php: null, laravel: '2' })

    page.map.onlyClear.click()

    expect(page.map.only.hidden).toBe(true)
    expect(page.map.counts(page.map.hubs.stack)).toEqual({ php: '1', laravel: '2' })
    expect(page.urls.at(-1)).toBe('/#view=map')
  })

  it('names a stack when the map is sorted by topic', () => {
    const page = run({ map: true, hash: '#stack=laravel&view=map&sort=topic' })

    expect(page.map.onlyKind.textContent).toBe('Stack')
    expect(page.map.onlyLabel.textContent).toBe('Laravel')
  })

  it('keeps the note while a hub is open, since the pick narrows every hub', () => {
    const page = run({ map: true, hash: '#stack=laravel&topic=mcp&view=map' })

    expect(page.map.only.hidden).toBe(false)
    expect(page.map.onlyLabel.textContent).toBe('MCP')

    page.map.onlyClear.click()

    expect(page.map.branch.hidden).toBe(false)
    expect(page.onShelf()).toEqual(['vigilance', 'excel'])
    expect(page.urls.at(-1)).toBe('/#stack=laravel&view=map')
  })

  it('has no note when no bin is picked', () => {
    expect(run({ map: true, hash: '#view=map' }).map.only.hidden).toBe(true)
    expect(run({ map: true, hash: '#stack=laravel&view=map' }).map.only.hidden).toBe(true)
  })
})

describe('map this week', () => {
  const WEEK = ['vigilance', 'mollie']

  it('opens This Week when the Map has no other choice', () => {
    const page = run({ map: true, week: WEEK, hash: '#view=map' })

    expect(page.map.branch.hidden).toBe(false)
    expect(page.map.branch.parent).toBe(page.map.weekNode('stack'))
    expect(page.map.weekHub('stack').getAttribute('aria-expanded')).toBe('true')
    expect(page.map.weekHub('stack').count.textContent).toBe('2')
    expect(page.onShelf()).toEqual(['mollie', 'vigilance'])
    expect(page.map.title.textContent).toBe('This week')
    expect(page.map.total.textContent).toBe('2 parcels')
    expect(page.status.textContent).toBe('Showing 2 of 3 parcels: This week.')
    expect(page.map.counts(page.map.bins.stack)).toEqual({ '': '2', testing: '2', mcp: '1', none: null })
  })

  it('leaves Labels and List whole', () => {
    const page = run({ map: true, week: WEEK, hash: '#view=map' })

    page.view('labels').click()

    expect(page.found()).toEqual(['mollie', 'vigilance', 'excel'])
    expect(page.status.textContent).toBe('')
  })

  it('opens This Week when the visitor switches to the Map', () => {
    const page = run({ map: true, week: WEEK })

    page.view('map').click()

    expect(page.map.weekHub('stack').getAttribute('aria-expanded')).toBe('true')
    expect(page.urls.at(-1)).toBe('/#view=map')
  })

  it('narrows This Week with a bin', () => {
    const page = run({ map: true, week: WEEK, hash: '#view=map' })

    page.map.topicBin('mcp').click()

    expect(page.onShelf()).toEqual(['vigilance'])
    expect(page.map.title.textContent).toBe('This week, MCP')
    expect(page.map.weekHub('stack').count.textContent).toBe('1')
    expect(page.status.textContent).toBe('Showing 1 of 3 parcels: This week, MCP.')
  })

  it('gives way to a stack, and comes back when the stack closes', () => {
    const page = run({ map: true, week: WEEK, hash: '#view=map' })

    page.map.stackHub('laravel').click()

    expect(page.map.weekHub('stack').getAttribute('aria-expanded')).toBe('false')
    expect(page.onShelf()).toEqual(['vigilance', 'excel'])

    page.map.stackHub('laravel').click()

    expect(page.map.weekHub('stack').getAttribute('aria-expanded')).toBe('true')
  })

  it('closes when its hub is picked, and leaves every branch closed', () => {
    const page = run({ map: true, week: WEEK, hash: '#view=map' })

    page.map.weekHub('stack').click()

    expect(page.map.branch.hidden).toBe(true)
    expect(page.status.textContent).toBe('')

    page.map.weekHub('stack').click()

    expect(page.map.branch.hidden).toBe(false)
  })

  it('closes an open stack when its hub is picked', () => {
    const page = run({ map: true, week: WEEK, hash: '#stack=laravel&view=map' })

    expect(page.map.weekHub('stack').getAttribute('aria-expanded')).toBe('false')

    page.map.weekHub('stack').click()

    expect(page.map.weekHub('stack').getAttribute('aria-expanded')).toBe('true')
    expect(page.urls.at(-1)).toBe('/#view=map')
  })

  it('has a This Week hub for each sort', () => {
    const page = run({ map: true, week: WEEK, hash: '#view=map&sort=topic' })

    expect(page.map.branch.parent).toBe(page.map.weekNode('topic'))
    expect(page.map.counts(page.map.bins.topic)).toEqual({ '': '2', php: '1', laravel: '1' })
  })

  it('opens the Map on a branch from a stack on the manifest slip', () => {
    const page = run({ map: true, week: WEEK })

    const event = page.map.slipLinks[0].click()

    expect(event.defaultPrevented).toBe(true)
    expect(page.panes.map((pane) => pane.hidden)).toEqual([true, true, false])
    expect(page.map.stackHub('laravel').getAttribute('aria-expanded')).toBe('true')
    expect(page.urls.at(-1)).toBe('/#stack=laravel&view=map')
    expect(page.localStorage.data.view).toBe('map')
  })

  it('opens a slip stack sorted by stack, and keeps the topic', () => {
    const page = run({ map: true, week: WEEK, hash: '#topic=mcp&view=map&sort=topic' })

    page.map.slipLinks[0].click()

    expect(page.map.hubLists.map((list) => list.hidden)).toEqual([false, true])
    expect(page.urls.at(-1)).toBe('/#stack=laravel&topic=mcp&view=map')
  })
})
