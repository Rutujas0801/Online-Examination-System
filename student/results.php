<?php
require_once __DIR__ . '/../includes/functions.php';
$me = require_role('student');

$results = q('SELECT r.*, e.title FROM results r JOIN exams e ON e.id = r.exam_id
              WHERE r.user_id = ? ORDER BY r.submitted_at DESC', [$me['id']])->fetchAll();

$title = 'My results';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>My results</h1></div>
<div class="card">
  <?php if ($results): ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Exam</th><th>Score</th><th>Percentage</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($results as $r): ?>
      <tr>
        <td><?= e($r['title']) ?></td>
        <td><?= num($r['score']) ?> / <?= (int)$r['total_marks'] ?></td>
        <td><?= num($r['percentage']) ?>%</td>
        <td><span class="badge <?= is_pass($r['percentage']) ? 'pass' : 'fail' ?>"><?= is_pass($r['percentage']) ? 'Pass' : 'Fail' ?></span></td>
        <td><?= e(format_date($r['submitted_at'])) ?></td>
        <td class="row-actions"><a class="btn btn-secondary btn-sm" href="<?= url('student/result.php?id=' . (int)$r['id']) ?>">Review</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?><p class="empty">You have not taken any exams yet.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
