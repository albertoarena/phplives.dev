(function () {
  // The marker under This week: which label of the row is in view, as "2 / 3".
  // It shows only while the row scrolls sideways, as on a phone. Without this script it stays hidden.
  var row = document.querySelector('[data-week-row]');
  var pos = document.querySelector('[data-week-pos]');
  if (!row || !pos) return;
  var n = pos.querySelector('[data-week-n]');
  var dots = pos.querySelector('[data-week-dots]');
  var hint = pos.querySelector('[data-week-hint]');

  function update() {
    var all = row.querySelectorAll('[data-stack]');
    var shown = [];
    for (var i = 0; i < all.length; i++) if (!all[i].hidden) shown.push(all[i]);
    pos.hidden = shown.length < 2 || row.scrollWidth <= row.clientWidth + 1;
    if (pos.hidden) return;

    // The label in view is the one nearest to where the first label starts, or the last one at the end of the row.
    var left = row.getBoundingClientRect().left;
    var start = shown[0].getBoundingClientRect().left - left + row.scrollLeft;
    var at = 0;
    var gap = Infinity;
    for (i = 0; i < shown.length; i++) {
      var d = Math.abs(shown[i].getBoundingClientRect().left - left - start);
      if (d < gap) { gap = d; at = i; }
    }
    if (row.scrollLeft >= row.scrollWidth - row.clientWidth - 1) at = shown.length - 1;

    n.textContent = (at + 1) + ' / ' + shown.length;
    var marks = '';
    for (i = 0; i < shown.length; i++) marks += i === at ? '<i class="on"></i>' : '<i></i>';
    dots.innerHTML = marks;
    hint.hidden = at === shown.length - 1;
  }

  row.addEventListener('scroll', update, { passive: true });
  window.addEventListener('resize', update);
  // The stack and topic filters hide labels.
  new MutationObserver(update).observe(row, { attributes: true, attributeFilter: ['hidden'], subtree: true });
  update();
})();
