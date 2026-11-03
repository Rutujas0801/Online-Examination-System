// Exam screen: one question at a time, palette, countdown timer, submit confirmation
(function () {
  var form = document.getElementById('exam-form');
  if (!form) return;

  var questions = [].slice.call(form.querySelectorAll('.question'));
  var palette = [].slice.call(form.querySelectorAll('.pal-btn'));
  var prev = document.getElementById('prev');
  var next = document.getElementById('next');
  var counter = document.getElementById('answered-count');
  var timer = document.getElementById('timer');
  var current = 0;
  var submitting = false;

  function show(i) {
    current = i;
    questions.forEach(function (q, k) { q.hidden = k !== i; });
    palette.forEach(function (b, k) {
      b.classList.toggle('current', k === i);
      if (k === i) b.setAttribute('aria-current', 'true'); else b.removeAttribute('aria-current');
    });
    prev.disabled = i === 0;
    next.disabled = i === questions.length - 1;
  }

  function answered(i) { return !!questions[i].querySelector('input:checked'); }

  function refresh() {
    var count = 0;
    questions.forEach(function (q, k) {
      var done = answered(k);
      if (done) count++;
      palette[k].classList.toggle('answered', done);
    });
    counter.textContent = count;
    return count;
  }

  prev.addEventListener('click', function () { if (current > 0) show(current - 1); });
  next.addEventListener('click', function () { if (current < questions.length - 1) show(current + 1); });
  palette.forEach(function (b, k) { b.addEventListener('click', function () { show(k); }); });
  form.addEventListener('change', refresh);
  form.querySelectorAll('.clear-answer').forEach(function (btn, k) {
    btn.addEventListener('click', function () {
      questions[k].querySelectorAll('input').forEach(function (r) { r.checked = false; });
      refresh();
    });
  });

  // Manual submit asks for confirmation and mentions unanswered questions
  form.addEventListener('submit', function (e) {
    if (submitting) return;
    e.preventDefault();
    var left = questions.length - refresh();
    var text = left
      ? 'You have ' + left + ' unanswered question' + (left === 1 ? '' : 's') + '. Submit anyway? You cannot change answers afterwards.'
      : 'You have answered every question. Submit now? You cannot change answers afterwards.';
    askConfirm(text, 'Submit exam', function () { submitting = true; form.submit(); });
  });

  // Countdown: the server sends the seconds left; time is up -> submit automatically
  var end = Date.now() + (parseInt(form.dataset.left, 10) || 0) * 1000;
  function tick() {
    var s = Math.max(0, Math.ceil((end - Date.now()) / 1000));
    timer.textContent = Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
    timer.parentNode.classList.toggle('low', s <= 60);
    if (s === 0 && !submitting) {
      submitting = true;
      clearInterval(clock);
      form.submit();
    }
  }
  var clock = setInterval(tick, 500);
  tick();

  window.addEventListener('beforeunload', function (e) {
    if (!submitting) { e.preventDefault(); e.returnValue = ''; }
  });

  show(0);
  refresh();
})();
