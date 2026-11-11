(function () {
  // Without this script, the page shows every entry as labels, and the tools stay hidden.
  var VIEW_KEY = 'view';
  var items = document.querySelectorAll('[data-stack]');
  var viewButtons = document.querySelectorAll('[data-view-choice]');
  var panes = document.querySelectorAll('[data-view]');
  var status = document.querySelector('[data-filter-status]');
  var tools = document.querySelectorAll('[data-tools]');
  // Bundles of entries (a week, a month). A bundle with no entry left to show is hidden.
  var groups = document.querySelectorAll('[data-group]');
  var search = searchField();
  var map = mapParts();

  // Two filters, by stack and by topic. Each has chips or a dropdown, and its choices:
  // { value, label }. The first choice is "All".
  var filters = { stack: filter('stack'), topic: filter('topic') };

  function filter(kind) {
    var chips = document.querySelectorAll('[data-' + kind + '-choice]');
    var dropdown = document.querySelector('select[data-' + kind + '-select]');
    var choices = [];
    var j;
    function read(el, value) {
      return { value: value || '', label: el.getAttribute('data-' + kind + '-label') || '' };
    }
    for (j = 0; j < chips.length; j++) choices.push(read(chips[j], chips[j].getAttribute('data-' + kind + '-choice')));
    if (dropdown) for (j = 0; j < dropdown.options.length; j++) choices.push(read(dropdown.options[j], dropdown.options[j].getAttribute('value')));
    return { kind: kind, chips: chips, dropdown: dropdown, choices: choices, value: '' };
  }

  // One copy of the entries (the page shows each one in every view), to count them.
  var once = [];
  var seen = {};
  for (var o = 0; o < items.length; o++) {
    var slug = items[o].getAttribute('data-slug');
    if (seen[slug]) continue;
    seen[slug] = true;
    once.push(items[o]);
  }

  // The search field, and the words of each entry on the page, which the build writes as JSON.
  function searchField() {
    var input = document.querySelector('[data-search]');
    var source = document.querySelector('#search-index');
    var words = {};
    var index = {};
    try { index = source ? JSON.parse(source.textContent) : {}; } catch (e) {}
    for (var slug in index) if (Object.prototype.hasOwnProperty.call(index, slug)) words[slug] = ' ' + normalise(index[slug]) + ' ';
    return {
      input: input,
      clear: document.querySelector('[data-search-clear]'),
      key: document.querySelector('[data-search-key]'),
      none: document.querySelector('[data-search-none]'),
      noneTitle: document.querySelector('[data-search-none-title]'),
      reset: document.querySelector('[data-search-reset]'),
      words: words,
      value: '',
      terms: [],
    };
  }

  // The Map view: hubs that open a branch, with bins that narrow it, and a shelf of stubs.
  // Its hubs and bins are stack and topic choices too, so the filters drive them.
  function mapParts() {
    var root = document.querySelector('[data-map]');
    var stubs = [];
    for (var j = 0; j < items.length; j++) if (items[j].getAttribute('data-map-stub') !== null) stubs.push(items[j]);
    return {
      root: root,
      stubs: stubs,
      nodes: document.querySelectorAll('[data-map-node]'),
      bins: document.querySelectorAll('[data-map-bin]'),
      hubLists: document.querySelectorAll('[data-map-hubs]'),
      binGroups: document.querySelectorAll('[data-map-bins]'),
      sortButtons: document.querySelectorAll('[data-map-sort-choice]'),
      sortGroups: document.querySelectorAll('[data-map-sort]'),
      filterBoxes: document.querySelectorAll('[data-filter]'),
      branch: document.querySelector('[data-map-branch]'),
      title: document.querySelector('[data-map-title]'),
      total: document.querySelector('[data-map-total]'),
      depot: document.querySelector('[data-map-depot]'),
      more: document.querySelector('[data-map-more]'),
      route: document.querySelector('[data-map-route]'),
      only: document.querySelector('[data-map-only]'),
      onlyKind: document.querySelector('[data-map-only-kind]'),
      onlyLabel: document.querySelector('[data-map-only-label]'),
      onlyClear: document.querySelector('[data-map-only-clear]'),
      slipLinks: document.querySelectorAll('[data-map-open]'),
      // This Week: the branch that opens when no hub is picked, until the visitor closes it.
      weekOpen: false,
      weekShut: false,
      weekLabel: '',
      limit: root ? Number(root.getAttribute('data-map-limit')) || 6 : 6,
      sort: 'stack',
      all: false,
      key: '',
    };
  }

  // Lower case, no accents, and a space for anything that is not a letter or a digit.
  function normalise(text) {
    return String(text).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, ' ').trim();
  }

  // Other words for a word, where the entries say it another way.
  var ALSO = { ecommerce: ['e commerce'], shop: ['e commerce'] };

  // Each word typed, with the other ways to find it. A plural "s" goes from a word of 5
  // letters or more, so "payments" finds "payment", but "news" stays "news".
  function terms(text) {
    var words = normalise(text).split(' ');
    var out = [];
    for (var j = 0; j < words.length; j++) {
      var word = words[j];
      if (!word) continue;
      if (word.length >= 5 && word.charAt(word.length - 1) === 's') word = word.slice(0, -1);
      out.push([word].concat(ALSO[word] || []));
    }
    return out;
  }

  // Every word typed must start a word of the entry.
  function found(item) {
    var text = search.words[item.getAttribute('data-slug')];
    if (!text) return false;
    for (var j = 0; j < search.terms.length; j++) {
      var ok = false;
      for (var k = 0; k < search.terms[j].length; k++) if (text.indexOf(' ' + search.terms[j][k]) !== -1) ok = true;
      if (!ok) return false;
    }
    return true;
  }

  function setSearch(value) {
    search.value = value;
    search.terms = terms(value);
    if (search.input && search.input.value !== value) search.input.value = value;
  }

  function find(f, value) {
    for (var j = 0; j < f.choices.length; j++) if (f.choices[j].value === value) return f.choices[j];
    return null;
  }

  // Whether an entry has a stack or a topic. An empty value is "All". The topic "none"
  // (Other) is the entries with no topic.
  function has(kind, value, item) {
    if (!value) return true;
    if (kind === 'stack') return item.getAttribute('data-stack') === value;
    var topics = item.getAttribute('data-topics') || '';
    if (value === 'none') return topics.trim() === '';
    return (' ' + topics + ' ').indexOf(' ' + value + ' ') !== -1;
  }

  function inWeek(item) {
    return item.getAttribute('data-map-this-week') !== null;
  }

  function typed(item) {
    return !search.terms.length || found(item);
  }

  // An entry matches when it has the picked stack and the picked topic, and the words
  // typed in the search. An empty pick or an empty search matches all.
  function matches(item) {
    return has('stack', filters.stack.value, item) && has('topic', filters.topic.value, item) && typed(item);
  }

  function stored() {
    try { return localStorage.getItem(VIEW_KEY); } catch (e) { return null; }
  }

  function save(view) {
    try { localStorage.setItem(VIEW_KEY, view); } catch (e) {}
  }

  // The state lives in the link, as #q=queue&stack=laravel&topic=testing&view=list. Any other hash (#devnull) is left alone.
  var params = {};
  var hash = location.hash.replace(/^#/, '');
  if (hash.indexOf('=') !== -1) {
    hash.split('&').forEach(function (pair) {
      var parts = pair.split('=');
      // A link edited by hand can hold a bad escape, such as "50%". That part is skipped.
      try { params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1] || ''); } catch (e) {}
    });
  }

  filters.stack.value = params.stack && find(filters.stack, params.stack) ? params.stack : '';
  filters.topic.value = params.topic && find(filters.topic, params.topic) ? params.topic : '';
  if (search.input) setSearch(params.q || '');
  // A view the page does not have (a stored Map on the archive) falls back to Labels.
  var views = ['labels'];
  for (var p = 0; p < panes.length; p++) if (views.indexOf(panes[p].getAttribute('data-view')) === -1) views.push(panes[p].getAttribute('data-view'));
  if (!panes.length) views.push('list');
  function known(name) { return name !== null && views.indexOf(name) !== -1; }
  var view = known(params.view) ? params.view : (known(stored()) ? stored() : 'labels');
  if (map.root && params.sort === 'topic') map.sort = 'topic';
  var touched = false;
  var later = null;

  // While the visitor types, the status line waits for a pause, so a screen reader does not read every letter.
  function render(typing) {
    for (var j = 0; j < items.length; j++) items[j].hidden = !matches(items[j]);
    for (j = 0; j < groups.length; j++) count(groups[j]);
    sync(filters.stack);
    sync(filters.topic);
    recount(filters.stack);
    recount(filters.topic);
    for (j = 0; j < viewButtons.length; j++) viewButtons[j].setAttribute('aria-pressed', viewButtons[j].getAttribute('data-view-choice') === view ? 'true' : 'false');
    for (j = 0; j < panes.length; j++) panes[j].hidden = panes[j].getAttribute('data-view') !== view;
    if (map.root) renderMap();

    var shown = 0;
    for (j = 0; j < once.length; j++) if (matches(once[j])) shown++;
    var labels = [];
    if (filters.stack.value) labels.push(find(filters.stack, filters.stack.value).label);
    if (filters.topic.value) labels.push(find(filters.topic, filters.topic.value).label);
    // In the Map, an open This Week narrows the shelf too.
    if (view === 'map' && map.weekOpen) {
      shown = 0;
      for (j = 0; j < map.stubs.length; j++) if (!map.stubs[j].hidden) shown++;
      labels.unshift(map.weekLabel);
    }
    var query = search.terms.length ? '“' + search.value.trim().replace(/\s+/g, ' ') + '”' : '';

    if (status) {
      var total = Number(status.getAttribute('data-total'));
      var text = query || labels.length
        ? 'Showing ' + shown + ' of ' + total + ' parcels: ' + (query ? [query] : []).concat(labels).join(', ') + '.'
        : '';
      if (later) clearTimeout(later);
      later = null;
      if (typing) later = setTimeout(function () { status.textContent = text; }, 400);
      else status.textContent = text;
    }

    if (search.input) {
      if (search.clear) search.clear.hidden = search.value === '';
      if (search.key) search.key.hidden = search.value !== '';
    }
    if (search.none) {
      search.none.hidden = shown > 0;
      if (search.noneTitle) {
        search.noneTitle.textContent = query
          ? 'No project matches ' + query + (labels.length ? ' in ' + labels.join(' and ') : '') + '.'
          : 'No project matches ' + labels.join(' and ') + '.';
      }
      if (search.reset) search.reset.textContent = labels.length ? 'Clear all filters' : 'Clear the search';
    }

    if (touched) {
      var parts = [];
      if (search.terms.length) parts.push('q=' + encodeURIComponent(search.value.trim().replace(/\s+/g, ' ')));
      if (filters.stack.value) parts.push('stack=' + encodeURIComponent(filters.stack.value));
      if (filters.topic.value) parts.push('topic=' + encodeURIComponent(filters.topic.value));
      if (panes.length && view !== 'labels') parts.push('view=' + view);
      if (view === 'map' && map.sort === 'topic') parts.push('sort=topic');
      var own = location.hash.indexOf('=') !== -1;
      if (parts.length) history.replaceState(null, '', location.pathname + '#' + parts.join('&'));
      else if (own) history.replaceState(null, '', location.pathname);
    }
  }

  function label(kind, value) {
    var choice = find(filters[kind], value);
    return choice ? choice.label : '';
  }

  function setCount(el, n) {
    var count = el.querySelector('[data-map-count]');
    if (count) count.textContent = String(n);
  }

  // The hubs of the picked sort, each with its count, and the branch of the open hub.
  // A hub or a bin with nothing left hides, unless it is the one picked.
  function renderMap() {
    var hubKind = map.sort;
    var binKind = hubKind === 'stack' ? 'topic' : 'stack';
    var open = filters[hubKind].value;
    var picked = filters[binKind].value;
    var j;
    function tally(test) {
      var n = 0;
      for (var k = 0; k < map.stubs.length; k++) if (typed(map.stubs[k]) && test(map.stubs[k])) n++;
      return n;
    }

    for (j = 0; j < map.filterBoxes.length; j++) map.filterBoxes[j].hidden = view === 'map';
    for (j = 0; j < map.sortGroups.length; j++) map.sortGroups[j].hidden = view !== 'map';
    for (j = 0; j < map.sortButtons.length; j++) map.sortButtons[j].setAttribute('aria-pressed', map.sortButtons[j].getAttribute('data-map-sort-choice') === hubKind ? 'true' : 'false');
    for (j = 0; j < map.hubLists.length; j++) map.hubLists[j].hidden = map.hubLists[j].getAttribute('data-map-hubs') !== hubKind;
    for (j = 0; j < map.binGroups.length; j++) map.binGroups[j].hidden = map.binGroups[j].getAttribute('data-map-bins') !== hubKind;
    // The depot counts what the search and the bin leave, across every hub.
    if (map.depot) map.depot.textContent = String(tally(function (s) { return has(binKind, picked, s); }));

    var openNode = null;
    var weekNode = null;
    for (j = 0; j < map.nodes.length; j++) {
      var hub = map.nodes[j].querySelector('[data-map-hub]');
      if (hub && hub.getAttribute('data-map-week') !== null) {
        if (hub.getAttribute('data-map-week') === hubKind) weekNode = map.nodes[j];
        continue;
      }
      var value = hub ? hub.getAttribute('data-' + hubKind + '-choice') : null;
      if (value === null) continue;
      var n = tally(function (s) { return has(hubKind, value, s) && has(binKind, picked, s); });
      var isOpen = open !== '' && value === open;
      setCount(hub, n);
      hub.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      map.nodes[j].hidden = n === 0 && !isOpen;
      if (isOpen) openNode = map.nodes[j];
    }

    // This Week opens when no hub of this sort is picked, unless the visitor closed it.
    map.weekOpen = !!weekNode && open === '' && !map.weekShut;
    if (weekNode) {
      var weekHub = weekNode.querySelector('[data-map-hub]');
      var w = tally(function (s) { return inWeek(s) && has(binKind, picked, s); });
      map.weekLabel = weekHub.getAttribute('data-map-week-label') || 'This week';
      setCount(weekHub, w);
      weekHub.setAttribute('aria-expanded', map.weekOpen ? 'true' : 'false');
      weekNode.hidden = w === 0 && !map.weekOpen;
      if (map.weekOpen) {
        openNode = weekNode;
        for (j = 0; j < map.stubs.length; j++) if (!inWeek(map.stubs[j])) map.stubs[j].hidden = true;
      }
    }

    map.open = openNode;
    if (map.branch) {
      map.branch.hidden = !openNode;
      if (openNode && map.branch.parentNode !== openNode && openNode.appendChild) openNode.appendChild(map.branch);
    }

    for (j = 0; j < map.bins.length; j++) {
      var bin = map.bins[j];
      if (bin.getAttribute('data-map-bin') !== hubKind) continue;
      var binValue = bin.getAttribute('data-' + binKind + '-choice') || '';
      var m = tally(function (s) { return (map.weekOpen ? inWeek(s) : has(hubKind, open, s)) && has(binKind, binValue, s); });
      setCount(bin, m);
      bin.hidden = m === 0 && binValue !== picked;
    }

    // The picked bin (a topic, or a stack when sorted by topic) narrows every hub, so the
    // depot names it, with a button to clear it, whether a hub is open or not.
    if (map.only) {
      var orphan = picked !== '';
      map.only.hidden = !orphan;
      if (orphan) {
        if (map.onlyKind) map.onlyKind.textContent = binKind === 'topic' ? 'Topic' : 'Stack';
        if (map.onlyLabel) map.onlyLabel.textContent = label(binKind, picked);
      }
    }

    var head = map.weekOpen ? map.weekLabel : (open ? label(hubKind, open) : '');
    if (map.title) map.title.textContent = head ? head + (picked ? ', ' + label(binKind, picked) : '') : '';

    // The shelf shows the first stubs of the branch, then all of them on request.
    // A new branch starts short again.
    var key = hubKind + '|' + (map.weekOpen ? 'week' : open) + '|' + picked;
    if (key !== map.key) { map.key = key; map.all = false; }
    var shown = 0;
    for (j = 0; j < map.stubs.length; j++) {
      var cut = false;
      if (!map.stubs[j].hidden) { shown++; cut = !map.all && shown > map.limit; }
      map.stubs[j].setAttribute('data-cut', cut ? 'true' : 'false');
    }
    if (map.total) map.total.textContent = shown + (shown === 1 ? ' parcel' : ' parcels');
    if (map.more) {
      map.more.hidden = shown <= map.limit;
      map.more.textContent = map.all ? 'Show fewer' : 'Show all ' + shown;
    }
    place();
  }

  // Where the branch sits. On a narrow screen it opens under its hub, as an outline.
  // On a wide one it is the third column of the chart, level with its hub, and the
  // line from the picked bin runs to the shelf. The stylesheet sets --map-outline.
  function place() {
    var node = map.open;
    var branch = map.branch;
    if (map.route && !node) map.route.setAttribute('hidden', '');
    if (!node || !branch || !map.root.style || typeof getComputedStyle !== 'function') return;
    var outline = getComputedStyle(map.root).getPropertyValue('--map-outline').trim() === '1';
    var hub = node.querySelector('[data-map-hub]');
    var first = null;
    var picked = null;
    for (var j = 0; j < map.bins.length; j++) {
      var bin = map.bins[j];
      if (bin.getAttribute('data-map-bin') !== map.sort || bin.hidden) continue;
      if (!first) first = bin;
      if (bin.getAttribute('aria-pressed') === 'true') picked = bin;
    }
    if (outline) {
      if (branch.parentNode !== node) node.appendChild(branch);
      branch.style.marginTop = '';
    } else {
      if (branch.parentNode !== map.root) map.root.appendChild(branch);
      branch.style.marginTop = (node.offsetTop + (hub.offsetHeight - (first ? first.offsetHeight : hub.offsetHeight)) / 2) + 'px';
    }
    draw(outline, hub, picked || first);
  }

  // The bold route, as one path: depot, open hub, picked bin and the shelf head. Each
  // corner sits halfway across the gap between two boxes, so the lines meet. On a narrow
  // screen there is no route, and when the depot sits above the hubs it starts at the hub.
  function draw(outline, hub, bin) {
    var svg = map.route;
    if (!svg) return;
    var path = svg.querySelector('path');
    var head = map.root.querySelector('.map-shelf .bundle-head h3');
    var depot = map.root.querySelector('.map-depot');
    if (outline || view !== 'map' || !hub || !bin || !head || !path) { svg.setAttribute('hidden', ''); return; }
    svg.removeAttribute('hidden');
    var chart = map.root.getBoundingClientRect();
    function box(el) {
      var r = el.getBoundingClientRect();
      return { l: r.left - chart.left, r: r.right - chart.left, y: (r.top + r.bottom) / 2 - chart.top };
    }
    // A 3px stroke is crisp on a half pixel.
    function px(n) { return Math.round(n) + 0.5; }
    var h = box(hub);
    var b = box(bin);
    var t = box(head);
    // A head that is nearly level with its bin gets a straight line.
    var ty = Math.abs(t.y - b.y) < 6 ? b.y : t.y;
    var d = '';
    if (depot) {
      var p = box(depot);
      if (p.r < h.l) d += 'M' + px(p.r) + ' ' + px(p.y) + 'H' + px((p.r + h.l) / 2) + 'V' + px(h.y) + 'H' + px(h.l);
    }
    d += 'M' + px(h.r) + ' ' + px(h.y) + 'H' + px((h.r + b.l) / 2) + 'V' + px(b.y) + 'H' + px(b.l);
    d += 'M' + px(b.r) + ' ' + px(b.y) + 'H' + px((b.r + t.l) / 2) + 'V' + px(ty) + 'H' + px(t.l - 6);
    path.setAttribute('d', d);
  }

  // Each choice of a filter counts its entries within the pick of the other filter and
  // the search, so a choice that leads to nothing says 0.
  function recount(f) {
    var other = f.kind === 'stack' ? 'topic' : 'stack';
    function size(value) {
      var n = 0;
      for (var k = 0; k < once.length; k++) if (typed(once[k]) && has(other, filters[other].value, once[k]) && has(f.kind, value, once[k])) n++;
      return n;
    }
    for (var j = 0; j < f.chips.length; j++) {
      var chip = f.chips[j];
      if (chip.getAttribute('data-map-hub') !== null || chip.getAttribute('data-map-bin') !== null) continue;
      var n = size(chip.getAttribute('data-' + f.kind + '-choice') || '');
      var number = typeof chip.querySelector === 'function' ? chip.querySelector('.n') : null;
      if (number) number.textContent = String(n);
      var name = chip.getAttribute('aria-label');
      if (name) chip.setAttribute('aria-label', name.replace(/\d+ entr(y|ies)$/, n + (n === 1 ? ' entry' : ' entries')));
    }
    if (f.dropdown) {
      for (j = 0; j < f.dropdown.options.length; j++) {
        var option = f.dropdown.options[j];
        option.textContent = (option.getAttribute('data-' + f.kind + '-label') || 'All') + ' (' + size(option.getAttribute('value') || '') + ')';
      }
    }
  }

  function sync(f) {
    for (var j = 0; j < f.chips.length; j++) f.chips[j].setAttribute('aria-pressed', f.chips[j].getAttribute('data-' + f.kind + '-choice') === f.value ? 'true' : 'false');
    if (f.dropdown) f.dropdown.value = f.value;
  }

  function count(group) {
    var members = group.querySelectorAll('[data-stack]');
    var shown = 0;
    for (var k = 0; k < members.length; k++) if (!members[k].hidden) shown++;
    group.hidden = shown === 0;
    var label = group.querySelector('[data-group-count]');
    if (label) label.textContent = shown + (shown === 1 ? ' parcel' : ' parcels');
    // A note that counts the entries (This week) has one text for each number the bundle can show.
    var note = group.querySelector('[data-group-notes]');
    var notes = [];
    try { notes = note ? JSON.parse(note.getAttribute('data-group-notes')) : []; } catch (e) {}
    if (shown > 0 && notes[shown - 1]) note.textContent = notes[shown - 1];
  }

  function listen(f) {
    for (var j = 0; j < f.chips.length; j++) {
      (function (chip) {
        chip.addEventListener('click', function () {
          var next = chip.getAttribute('data-' + f.kind + '-choice') || '';
          f.value = next === f.value ? '' : next;
          touched = true;
          render();
        });
      })(f.chips[j]);
    }

    if (f.dropdown) {
      f.dropdown.addEventListener('change', function () {
        f.value = find(f, f.dropdown.value) ? f.dropdown.value : '';
        touched = true;
        render();
      });
    }
  }

  listen(filters.stack);
  listen(filters.topic);

  function clearSearch(all) {
    setSearch('');
    if (all) { filters.stack.value = ''; filters.topic.value = ''; }
    touched = true;
    render();
    search.input.focus();
  }

  if (search.input) {
    search.input.addEventListener('input', function () {
      setSearch(search.input.value);
      touched = true;
      render(true);
    });
    search.input.addEventListener('keydown', function (event) {
      // Enter closes the keyboard on a phone. The entries are already filtered.
      if (event.key === 'Enter') search.input.blur();
      if (event.key !== 'Escape' || search.input.value === '') return;
      event.preventDefault();
      clearSearch(false);
    });
    if (search.clear) search.clear.addEventListener('click', function () { clearSearch(false); });
    if (search.reset) search.reset.addEventListener('click', function () { clearSearch(true); });

    // "/" goes to the search field, unless the visitor is typing somewhere already.
    document.addEventListener('keydown', function (event) {
      if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey) return;
      var target = event.target || {};
      if (/^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName || '') || target.isContentEditable) return;
      event.preventDefault();
      search.input.focus();
    });
  }

  for (var i = 0; i < viewButtons.length; i++) {
    (function (button) {
      button.addEventListener('click', function () {
        view = button.getAttribute('data-view-choice');
        save(view);
        touched = true;
        render();
      });
    })(viewButtons[i]);
  }

  for (i = 0; i < map.sortButtons.length; i++) {
    (function (button) {
      button.addEventListener('click', function () {
        map.sort = button.getAttribute('data-map-sort-choice') === 'topic' ? 'topic' : 'stack';
        touched = true;
        render();
      });
    })(map.sortButtons[i]);
  }

  // The This Week hub of each sort opens and closes its branch. Opening it closes a
  // picked hub of the same sort.
  for (i = 0; i < map.nodes.length; i++) {
    (function (hub) {
      if (!hub || hub.getAttribute('data-map-week') === null) return;
      hub.addEventListener('click', function () {
        if (map.weekOpen) map.weekShut = true;
        else { filters[map.sort].value = ''; map.weekShut = false; }
        touched = true;
        render();
      });
    })(map.nodes[i].querySelector('[data-map-hub]'));
  }

  // A stack on the manifest slip opens the Map on its branch, sorted by stack.
  for (i = 0; i < map.slipLinks.length; i++) {
    (function (link) {
      link.addEventListener('click', function (event) {
        var stack = link.getAttribute('data-map-open');
        if (!map.root || !find(filters.stack, stack)) return;
        if (event && event.preventDefault) event.preventDefault();
        view = 'map';
        save(view);
        map.sort = 'stack';
        filters.stack.value = stack;
        touched = true;
        render();
        var section = typeof document.getElementById === 'function' ? document.getElementById('manifest') : null;
        if (section && section.scrollIntoView) section.scrollIntoView({ block: 'start' });
      });
    })(map.slipLinks[i]);
  }

  if (map.onlyClear) {
    map.onlyClear.addEventListener('click', function () {
      filters[map.sort === 'stack' ? 'topic' : 'stack'].value = '';
      touched = true;
      render();
    });
  }

  if (map.more) {
    map.more.addEventListener('click', function () {
      map.all = !map.all;
      render();
    });
  }

  render();
  for (i = 0; i < tools.length; i++) tools[i].hidden = false;
  if (map.root && typeof window !== 'undefined') {
    window.addEventListener('resize', place);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(place);
  }
})();
