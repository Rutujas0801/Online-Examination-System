<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$stats = q('SELECT
    (SELECT COUNT(*) FROM users WHERE role = "student") AS students,
    (SELECT COUNT(*) FROM questions) AS questions,
    (SELECT COUNT(*) FROM exams) AS exams,
    (SELECT COUNT(*) FROM results) AS results')->fetch();
$recent = q('SELECT r.id, r.score, r.total_marks, r.percentage, r.submitted_at, u.name, e.title
             FROM results r JOIN users u ON u.id = r.user_id JOIN exams e ON e.id = r.exam_id
             ORDER BY r.submitted_at DESC LIMIT 5')->fetchAll();

$title = 'Admin dashboard';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
  <h1>Dashboard</h1>
  <div class="actions">
    <a class="btn btn-secondary" href="<?= url('admin/question_form.php') ?>">Add question</a>
    <a class="btn" href="<?= url('admin/exam_form.php') ?>">Create exam</a>
  </div>
</div>

<div class="stats">
  <div class="stat"><span>Students</span><b><?= (int)$stats['students'] ?></b></div>
  <div class="stat"><span>Questions</span><b><?= (int)$stats['questions'] ?></b></div>
  <div class="stat"><span>Exams</span><b><?= (int)$stats['exams'] ?></b></div>
  <div class="stat"><span>Results</span><b><?= (int)$stats['results'] ?></b></div>
</div>

<div class="card">
  <div class="card-head"><h2>Latest submissions</h2><a href="<?= url('admin/results.php') ?>">View all results</a></div>
  <?php if ($recent): ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Student</th><th>Exam</th><th>Score</th><th>Status</th><th>Submitted</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $r): ?>
      <tr>
        <td><?= e($r['name']) ?></td><td><?= e($r['title']) ?></td>
        <td><?= num($r['score']) ?> / <?= (int)$r['total_marks'] ?> <span class="muted">(<?= num($r['percentage']) ?>%)</span></td>
        <td><span class="badge <?= is_pass($r['percentage']) ? 'pass' : 'fail' ?>"><?= is_pass($r['percentage']) ? 'Pass' : 'Fail' ?></span></td>
        <td><?= e(format_date($r['submitted_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?><p class="empty">No exams have been submitted yet.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
