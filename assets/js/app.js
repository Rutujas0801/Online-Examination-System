// Shared behaviour: theme toggle, confirm dialog, button loading state
(function () {
  var root = document.documentElement;
  var toggle = document.getElementById('theme-toggle');

  function theme() {
    return root.dataset.theme || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  }
  function showLabel() {
    if (toggle) toggle.textContent = theme() === 'dark' ? 'Light mode' : 'Dark mode';
  }
  if (toggle) {
    toggle.addEventListener('click', function () {
      var next = theme() === 'dark' ? 'light' : 'dark';
      root.dataset.theme = next;
      try { localStorage.setItem('theme', next); } catch (e) {}
      showLabel();
    });
  }
  showLabel();

  // askConfirm(message, buttonText, callback, isDanger)
  var dialog = document.getElementById('confirm-dialog');
  window.askConfirm = function (message, yesText, onYes, danger) {
    if (!dialog || !dialog.showModal) {
      if (confirm(message)) onYes();
      return;
    }
    var yes = document.getElementById('confirm-yes');
    document.getElementById('confirm-text').textContent = message;
    yes.textContent = yesText || 'Confirm';
    yes.className = 'btn' + (danger ? ' btn-danger' : '');
    dialog.returnValue = '';
    dialog.addEventListener('close', function done() {
      dialog.removeEventListener('close', done);
      if (dialog.returnValue === 'yes') onYes();
    });
    dialog.showModal();
  };

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (e.defaultPrevented) return;
    if (form.dataset.confirm && !form.dataset.confirmed) {
      e.preventDefault();
      askConfirm(form.dataset.confirm, form.dataset.yes, function () {
        form.dataset.confirmed = '1';
        form.submit();
      }, form.dataset.danger === '1');
      return;
    }
    var button = form.querySelector('button.btn:not(.btn-secondary)');
    if (button) {
      setTimeout(function () { button.disabled = true; button.classList.add('loading'); }, 0);
    }
  });

  // Restore buttons if the browser shows this page again from its back/forward cache
  window.addEventListener('pageshow', function () {
    document.querySelectorAll('button.loading').forEach(function (b) {
      b.disabled = false; b.classList.remove('loading');
    });
    document.querySelectorAll('form[data-confirmed]').forEach(function (f) { delete f.dataset.confirmed; });
  });
})();
