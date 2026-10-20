(function () {
  var KEY = 'theme';
  var root = document.documentElement;

  function stored() {
    try {
      var value = localStorage.getItem(KEY);
      return value === 'light' || value === 'dark' ? value : 'system';
    } catch (e) {
      return 'system';
    }
  }

  function save(choice) {
    try {
      if (choice === 'system') localStorage.removeItem(KEY);
      else localStorage.setItem(KEY, choice);
    } catch (e) {}
  }

  function apply(choice) {
    if (choice === 'system') root.removeAttribute('data-theme');
    else root.setAttribute('data-theme', choice);
  }

  var choice = stored();
  apply(choice);

  document.addEventListener('DOMContentLoaded', function () {
    var buttons = document.querySelectorAll('[data-theme-choice]');

    function mark() {
      for (var i = 0; i < buttons.length; i++) {
        buttons[i].setAttribute('aria-pressed', buttons[i].getAttribute('data-theme-choice') === choice ? 'true' : 'false');
      }
    }

    for (var i = 0; i < buttons.length; i++) {
      buttons[i].addEventListener('click', function (button) {
        return function () {
          choice = button.getAttribute('data-theme-choice');
          save(choice);
          apply(choice);
          mark();
        };
      }(buttons[i]));
    }

    mark();
    var group = document.querySelector('.theme');
    if (group) group.hidden = false;
  });
})();
