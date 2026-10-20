(function () {
  // Without this script, the page shows every entry as labels, and the tools stay hidden.
  var VIEW_KEY = 'view';
  var items = document.querySelectorAll('[data-stack]');
  var chips = document.querySelectorAll('[data-stack-choice]');
  var dropdown = document.querySelector('select[data-stack-select]');
  var viewButtons = document.querySelectorAll('[data-view-choice]');
  var panes = document.querySelectorAll('[data-view]');
  var status = document.querySelector('[data-filter-status]');
  var tools = document.querySelectorAll('[data-tools]');

  // Each choice: { stack, label, count }. The first one is "All".
  var choices = [];
  var i;
  for (i = 0; i < chips.length; i++) choices.push(read(chips[i], chips[i].getAttribute('data-stack-choice')));
  if (dropdown) for (i = 0; i < dropdown.options.length; i++) choices.push(read(dropdown.options[i], dropdown.options[i].getAttribute('value')));

  function read(el, stack) {
    return { stack: stack || '', label: el.getAttribute('data-stack-label') || '', count: el.getAttribute('data-count') };
  }

  function find(stack) {
    for (var j = 0; j < choices.length; j++) if (choices[j].stack === stack) return choices[j];
    return null;
  }

  function stored() {
    try { return localStorage.getItem(VIEW_KEY); } catch (e) { return null; }
  }

  function save(view) {
    try { localStorage.setItem(VIEW_KEY, view); } catch (e) {}
  }

  // The state lives in the link, as #stack=laravel&view=list. Any other hash (#devnull) is left alone.
  var params = {};
  var hash = location.hash.replace(/^#/, '');
  if (hash.indexOf('=') !== -1) {
    hash.split('&').forEach(function (pair) {
      var parts = pair.split('=');
      params[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1] || '');
    });
  }

  var stack = params.stack && find(params.stack) ? params.stack : '';
  var view = params.view === 'list' || params.view === 'labels' ? params.view : (stored() === 'list' ? 'list' : 'labels');
  var touched = false;

  function render() {
    for (var j = 0; j < items.length; j++) items[j].hidden = stack !== '' && items[j].getAttribute('data-stack') !== stack;
    for (j = 0; j < chips.length; j++) chips[j].setAttribute('aria-pressed', chips[j].getAttribute('data-stack-choice') === stack ? 'true' : 'false');
    if (dropdown) dropdown.value = stack;
    for (j = 0; j < viewButtons.length; j++) viewButtons[j].setAttribute('aria-pressed', viewButtons[j].getAttribute('data-view-choice') === view ? 'true' : 'false');
    for (j = 0; j < panes.length; j++) panes[j].hidden = panes[j].getAttribute('data-view') !== view;

    if (status) {
      var choice = find(stack);
      status.textContent = stack && choice
        ? 'Showing ' + choice.count + ' of ' + status.getAttribute('data-total') + ' parcels: ' + choice.label + '.'
        : '';
    }

    if (touched) {
      var parts = [];
      if (stack) parts.push('stack=' + encodeURIComponent(stack));
      if (panes.length && view === 'list') parts.push('view=list');
      var own = location.hash.indexOf('=') !== -1;
      if (parts.length) history.replaceState(null, '', location.pathname + '#' + parts.join('&'));
      else if (own) history.replaceState(null, '', location.pathname);
    }
  }

  function pick(next) {
    stack = next === stack ? '' : next;
    touched = true;
    render();
  }

  for (i = 0; i < chips.length; i++) {
    (function (chip) {
      chip.addEventListener('click', function () { pick(chip.getAttribute('data-stack-choice') || ''); });
    })(chips[i]);
  }

  if (dropdown) {
    dropdown.addEventListener('change', function () {
      stack = find(dropdown.value) ? dropdown.value : '';
      touched = true;
      render();
    });
  }

  for (i = 0; i < viewButtons.length; i++) {
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
