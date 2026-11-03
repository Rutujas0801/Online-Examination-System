<?php
require_once __DIR__ . '/../includes/functions.php';
$me = require_role('student');

$id = get_id();
$exam = q('SELECT e.*, (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id = e.id) AS qn FROM exams e WHERE e.id = ?', [$id])->fetch();
if (!$exam || !$exam['qn']) { flash('error', 'Exam not found.'); redirect('student/index.php'); }

$existing = q('SELECT id FROM results WHERE user_id = ? AND exam_id = ?', [$me['id'], $id])->fetchColumn();
if ($existing) {
    flash('info', 'You have already completed this exam.');
    redirect('student/result.php?id=' . $existing);
}

// Starting the exam stores the start time on the server (only the first time)
if (is_post()) {
    check_csrf();
    q('INSERT IGNORE INTO exam_starts (user_id, exam_id, started_at) VALUES (?, ?, NOW())', [$me['id'], $id]);
    redirect('student/exam.php?id=' . $id);
}
$started = q('SELECT 1 FROM exam_starts WHERE user_id = ? AND exam_id = ?', [$me['id'], $id])->fetchColumn();

$title = 'Instructions';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1><?= e($exam['title']) ?></h1></div>
<div class="card form-card">
  <div class="facts">
    <div><span>Questions</span><b><?= (int)$exam['qn'] ?></b></div>
    <div><span>Duration</span><b><?= (int)$exam['duration_minutes'] ?> min</b></div>
    <div><span>Total marks</span><b><?= (int)$exam['total_marks'] ?></b></div>
    <div><span>Pass mark</span><b><?= PASS_PERCENT ?>%</b></div>
  </div>
  <h2>Before you begin</h2>
  <ul class="rules">
    <li>Every question has four options and exactly one correct answer.</li>
    <li>The timer starts when you press Start and keeps running even if you refresh or leave the page.</li>
    <li>Use the question numbers on the right to move around. Green numbers are answered.</li>
    <li>The exam is submitted automatically when the time is over.</li>
    <li>You can attempt each exam only once. Marks are calculated by the server when you submit.</li>
  </ul>
  <?php if ($started): ?><div class="alert alert-info">You already started this exam. Continue where you left off; the timer has not stopped.</div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="form-actions">
      <button class="btn"><?= $started ? 'Continue exam' : 'Start exam' ?></button>
      <a class="btn btn-secondary" href="<?= url('student/index.php') ?>">Back</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
