(function () {
  // Without a device share menu, the Share button stays hidden and Copy link does the job.
  if (typeof navigator.share !== 'function') return;

  var buttons = document.querySelectorAll('[data-share]');

  for (var i = 0; i < buttons.length; i++) {
    (function (button) {
      button.addEventListener('click', function () {
        // A closed share menu rejects the promise. That is not an error to show.
        return navigator.share({
          title: button.getAttribute('data-share-title'),
          url: button.getAttribute('data-share'),
        }).then(function () {}, function () {});
      });

      button.hidden = false;
    })(buttons[i]);
  }
})();
