<?php
require_once __DIR__ . '/../includes/functions.php';
$me = require_role('student');

// The user_id condition stops students from opening each other's results
$r = q('SELECT r.*, e.title FROM results r JOIN exams e ON e.id = r.exam_id WHERE r.id = ? AND r.user_id = ?', [get_id(), $me['id']])->fetch();
if (!$r) { flash('error', 'Result not found.'); redirect('student/results.php'); }

$answers = q('SELECT q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, sa.selected_option, sa.is_correct
              FROM student_answers sa JOIN questions q ON q.id = sa.question_id
              WHERE sa.result_id = ? ORDER BY q.id', [$r['id']])->fetchAll();

$wrong = 0; $skipped = 0;
foreach ($answers as $a) {
    if ($a['selected_option'] === null) $skipped++;
    elseif (!$a['is_correct']) $wrong++;
}
$pass = is_pass($r['percentage']);
$circ = 2 * M_PI * 60;
$offset = $circ * (1 - $r['percentage'] / 100);

$title = 'Result';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
  <h1><?= e($r['title']) ?></h1>
  <a class="btn btn-secondary" href="<?= url('student/index.php') ?>">Back to exams</a>
</div>

<div class="card result-card">
  <svg class="ring" viewBox="0 0 140 140" role="img" aria-label="Score <?= num($r['percentage']) ?> percent">
    <circle class="ring-bg" cx="70" cy="70" r="60"/>
    <circle class="ring-fg <?= $pass ? 'pass' : 'fail' ?>" cx="70" cy="70" r="60"
            style="--full:<?= round($circ, 2) ?>;--offset:<?= round($offset, 2) ?>"/>
    <text x="70" y="76"><?= num($r['percentage']) ?>%</text>
  </svg>
  <div class="result-info">
    <p class="score"><?= num($r['score']) ?> <small>out of <?= (int)$r['total_marks'] ?> marks</small></p>
    <p><span class="badge <?= $pass ? 'pass' : 'fail' ?>"><?= $pass ? 'Pass' : 'Fail' ?></span>
       <strong><?= e(performance($r['percentage'])) ?></strong></p>
    <div class="facts">
      <div><span>Correct</span><b><?= (int)$r['correct_count'] ?></b></div>
      <div><span>Incorrect</span><b><?= $wrong ?></b></div>
      <div><span>Unanswered</span><b><?= $skipped ?></b></div>
    </div>
    <p class="muted small">Submitted <?= e(format_date($r['submitted_at'])) ?></p>
  </div>
</div>

<h2 class="section-title">Answer review</h2>
<?php foreach ($answers as $i => $a): ?>
<article class="card review">
  <p class="q-text"><?= $i + 1 ?>. <?= e($a['question_text']) ?>
    <?php if ($a['selected_option'] === null): ?><span class="badge">Not answered</span>
    <?php elseif ($a['is_correct']): ?><span class="badge pass">Correct</span>
    <?php else: ?><span class="badge fail">Incorrect</span><?php endif; ?></p>
  <?php foreach (['A', 'B', 'C', 'D'] as $opt):
    $cls = $opt === $a['correct_option'] ? 'is-correct' : ($opt === $a['selected_option'] ? 'is-wrong' : '');
    $note = $opt === $a['correct_option'] ? 'Correct answer' : ($opt === $a['selected_option'] ? 'Your answer' : ''); ?>
    <div class="review-opt <?= $cls ?>"><span class="letter"><?= $opt ?></span><span><?= e($a['option_' . strtolower($opt)]) ?></span>
      <?php if ($note): ?><em><?= $note ?><?= $opt === $a['correct_option'] && $opt === $a['selected_option'] ? ' (yours)' : '' ?></em><?php endif; ?></div>
  <?php endforeach; ?>
</article>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
