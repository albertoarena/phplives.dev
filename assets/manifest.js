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

  // An entry matches when it has the picked stack and the picked topic, and the words
  // typed in the search. An empty pick or an empty search matches all.
  function matches(item) {
    var stack = filters.stack.value;
    var topic = filters.topic.value;
    if (stack && item.getAttribute('data-stack') !== stack) return false;
    if (topic && (' ' + (item.getAttribute('data-topics') || '') + ' ').indexOf(' ' + topic + ' ') === -1) return false;
    if (search.terms.length && !found(item)) return false;
    return true;
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
  var view = params.view === 'list' || params.view === 'labels' ? params.view : (stored() === 'list' ? 'list' : 'labels');
  var touched = false;
  var later = null;

  // While the visitor types, the status line waits for a pause, so a screen reader does not read every letter.
  function render(typing) {
    var shown = 0;
    for (var j = 0; j < items.length; j++) {
      items[j].hidden = !matches(items[j]);
      if (!items[j].hidden) shown++;
    }
    for (j = 0; j < groups.length; j++) count(groups[j]);
    sync(filters.stack);
    sync(filters.topic);
    for (j = 0; j < viewButtons.length; j++) viewButtons[j].setAttribute('aria-pressed', viewButtons[j].getAttribute('data-view-choice') === view ? 'true' : 'false');
    for (j = 0; j < panes.length; j++) panes[j].hidden = panes[j].getAttribute('data-view') !== view;

    var labels = [];
    if (filters.stack.value) labels.push(find(filters.stack, filters.stack.value).label);
    if (filters.topic.value) labels.push(find(filters.topic, filters.topic.value).label);
    var query = search.terms.length ? '“' + search.value.trim().replace(/\s+/g, ' ') + '”' : '';

    if (status) {
      var total = Number(status.getAttribute('data-total'));
      // Each entry shows once in each view, so the items are a whole number of copies of the list.
      var copies = total ? items.length / total : 1;
      var text = query || labels.length
        ? 'Showing ' + Math.round(shown / copies) + ' of ' + total + ' parcels: ' + (query ? [query] : []).concat(labels).join(', ') + '.'
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
      if (panes.length && view === 'list') parts.push('view=list');
      var own = location.hash.indexOf('=') !== -1;
      if (parts.length) history.replaceState(null, '', location.pathname + '#' + parts.join('&'));
      else if (own) history.replaceState(null, '', location.pathname);
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

  render();
  for (i = 0; i < tools.length; i++) tools[i].hidden = false;
})();
