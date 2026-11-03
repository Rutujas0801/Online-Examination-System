<?php
require_once __DIR__ . '/../includes/functions.php';
$me = require_role('student');

$exams = q('SELECT e.*, r.id AS result_id, r.score, r.percentage,
              (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id = e.id) AS qn
            FROM exams e LEFT JOIN results r ON r.exam_id = e.id AND r.user_id = ?
            WHERE EXISTS (SELECT 1 FROM exam_questions eq WHERE eq.exam_id = e.id)
            ORDER BY e.id', [$me['id']])->fetchAll();
$done = count(array_filter($exams, fn($x) => $x['result_id']));
$avg = q('SELECT AVG(percentage) FROM results WHERE user_id = ?', [$me['id']])->fetchColumn();

$title = 'My exams';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Hello, <?= e(strtok($me['name'], ' ')) ?></h1></div>
<div class="stats">
  <div class="stat"><span>Available exams</span><b><?= count($exams) - $done ?></b></div>
  <div class="stat"><span>Completed</span><b><?= $done ?></b></div>
  <div class="stat"><span>Average score</span><b><?= $avg === null ? '-' : num($avg) . '%' ?></b></div>
</div>

<h2 class="section-title">Exams</h2>
<?php if ($exams): ?>
<div class="exam-grid">
  <?php foreach ($exams as $x): ?>
  <article class="card exam-card">
    <h3><?= e($x['title']) ?></h3>
    <p class="muted"><?= (int)$x['qn'] ?> questions &middot; <?= (int)$x['duration_minutes'] ?> minutes &middot; <?= (int)$x['total_marks'] ?> marks</p>
    <?php if ($x['result_id']): ?>
      <p>Your score: <strong><?= num($x['score']) ?> / <?= (int)$x['total_marks'] ?></strong>
        <span class="badge <?= is_pass($x['percentage']) ? 'pass' : 'fail' ?>"><?= is_pass($x['percentage']) ? 'Pass' : 'Fail' ?></span></p>
      <a class="btn btn-secondary" href="<?= url('student/result.php?id=' . (int)$x['result_id']) ?>">View result</a>
    <?php else: ?>
      <a class="btn" href="<?= url('student/instructions.php?id=' . (int)$x['id']) ?>">View instructions</a>
    <?php endif; ?>
  </article>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card"><p class="empty">No exams are available yet. Please check again later.</p></div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
