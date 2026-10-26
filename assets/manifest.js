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

  function find(f, value) {
    for (var j = 0; j < f.choices.length; j++) if (f.choices[j].value === value) return f.choices[j];
    return null;
  }

  // An entry matches when it has the picked stack and the picked topic. An empty pick matches all.
  function matches(item) {
    var stack = filters.stack.value;
    var topic = filters.topic.value;
    if (stack && item.getAttribute('data-stack') !== stack) return false;
    if (topic && (' ' + (item.getAttribute('data-topics') || '') + ' ').indexOf(' ' + topic + ' ') === -1) return false;
    return true;
  }

  function stored() {
    try { return localStorage.getItem(VIEW_KEY); } catch (e) { return null; }
  }

  function save(view) {
    try { localStorage.setItem(VIEW_KEY, view); } catch (e) {}
  }

  // The state lives in the link, as #stack=laravel&topic=testing&view=list. Any other hash (#devnull) is left alone.
  var params = {};
  var hash = location.hash.replace(/^#/, '');
  if (hash.indexOf('=') !== -1) {
    hash.split('&').forEach(function (pair) {
      var parts = pair.split('=');
      params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1] || '');
    });
  }

  filters.stack.value = params.stack && find(filters.stack, params.stack) ? params.stack : '';
  filters.topic.value = params.topic && find(filters.topic, params.topic) ? params.topic : '';
  var view = params.view === 'list' || params.view === 'labels' ? params.view : (stored() === 'list' ? 'list' : 'labels');
  var touched = false;

  function render() {
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

    if (status) {
      var total = Number(status.getAttribute('data-total'));
      var labels = [];
      if (filters.stack.value) labels.push(find(filters.stack, filters.stack.value).label);
      if (filters.topic.value) labels.push(find(filters.topic, filters.topic.value).label);
      // Each entry shows once in each view, so the items are a whole number of copies of the list.
      var copies = total ? items.length / total : 1;
      status.textContent = labels.length
        ? 'Showing ' + Math.round(shown / copies) + ' of ' + total + ' parcels: ' + labels.join(', ') + '.'
        : '';
    }

    if (touched) {
      var parts = [];
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
