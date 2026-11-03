<?php
require_once __DIR__ . '/../includes/functions.php';
$me = require_role('student');

$id = get_id();
$exam = q('SELECT * FROM exams WHERE id = ?', [$id])->fetch();
if (!$exam) { flash('error', 'Exam not found.'); redirect('student/index.php'); }

$existing = q('SELECT id FROM results WHERE user_id = ? AND exam_id = ?', [$me['id'], $id])->fetchColumn();
if ($existing) redirect('student/result.php?id=' . $existing);

// Seconds used so far, measured by the database clock
$used = q('SELECT TIMESTAMPDIFF(SECOND, started_at, NOW()) FROM exam_starts WHERE user_id = ? AND exam_id = ?', [$me['id'], $id])->fetchColumn();
if ($used === false || $used === null) redirect('student/instructions.php?id=' . $id);
$left = max(0, $exam['duration_minutes'] * 60 - (int)$used);

// The correct answer column is deliberately NOT selected here
$questions = q('SELECT q.id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d
                FROM questions q JOIN exam_questions eq ON eq.question_id = q.id
                WHERE eq.exam_id = ? ORDER BY q.id', [$id])->fetchAll();
$total = count($questions);

$title = $exam['title'];
$body_class = 'exam-page';
$page_js = 'exam.js';
require __DIR__ . '/../includes/header.php';
?>
<form id="exam-form" method="post" action="<?= url('student/submit.php') ?>" data-left="<?= (int)$left ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="exam_id" value="<?= (int)$id ?>">

  <div class="exam-bar">
    <div>
      <h1><?= e($exam['title']) ?></h1>
      <p class="muted small"><span id="answered-count">0</span> of <?= $total ?> answered</p>
    </div>
    <div class="timer" role="timer"><span>Time left</span><b id="timer">--:--</b></div>
  </div>

  <div class="exam-layout">
    <section class="card question-area" aria-live="polite">
      <?php foreach ($questions as $i => $row): ?>
      <fieldset class="question"<?= $i ? ' hidden' : '' ?>>
        <legend>Question <?= $i + 1 ?> of <?= $total ?></legend>
        <p class="q-text"><?= e($row['question_text']) ?></p>
        <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
          <label class="option">
            <input type="radio" name="answer[<?= (int)$row['id'] ?>]" value="<?= $opt ?>">
            <span class="letter"><?= $opt ?></span>
            <span><?= e($row['option_' . strtolower($opt)]) ?></span>
          </label>
        <?php endforeach; ?>
        <button type="button" class="btn-link clear-answer">Clear my answer</button>
      </fieldset>
      <?php endforeach; ?>
      <div class="q-nav">
        <button type="button" class="btn btn-secondary" id="prev">Previous</button>
        <button type="button" class="btn btn-secondary" id="next">Next</button>
      </div>
    </section>

    <aside class="card palette-card">
      <h2>Questions</h2>
      <div class="palette">
        <?php for ($i = 1; $i <= $total; $i++): ?>
          <button type="button" class="pal-btn" aria-label="Go to question <?= $i ?>"><?= $i ?></button>
        <?php endfor; ?>
      </div>
      <p class="legend"><span class="key answered"></span> Answered <span class="key"></span> Not answered</p>
      <button class="btn btn-block">Submit exam</button>
    </aside>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
