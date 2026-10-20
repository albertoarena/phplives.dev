(function () {
  // Without a clipboard, the Copy buttons stay hidden and the command can be selected by hand.
  var clipboard = navigator.clipboard;
  if (!clipboard || !clipboard.writeText) return;

  var buttons = document.querySelectorAll('[data-copy]');

  for (var i = 0; i < buttons.length; i++) {
    (function (button) {
      var status = document.getElementById(button.getAttribute('data-copy-status'));

      function say(label, message) {
        button.textContent = label;
        if (status) status.textContent = message;
      }

      button.addEventListener('click', function () {
        return clipboard.writeText(button.getAttribute('data-copy')).then(
          function () { say('Copied', 'Copied to the clipboard.'); },
          function () { say('Not copied', 'Could not copy. Select the command and copy it.'); }
        ).then(function () {
          setTimeout(function () { say('Copy', ''); }, 2000);
        });
      });

      button.hidden = false;
    })(buttons[i]);
  }
})();
