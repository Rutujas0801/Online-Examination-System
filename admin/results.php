<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// Deleting a result also clears the stored start time, so the student can retake the exam
if (is_post()) {
    check_csrf();
    $row = q('SELECT user_id, exam_id FROM results WHERE id = ?', [(int)post('id')])->fetch();
    if ($row) {
        q('DELETE FROM results WHERE id = ?', [(int)post('id')]);
        q('DELETE FROM exam_starts WHERE user_id = ? AND exam_id = ?', [$row['user_id'], $row['exam_id']]);
        flash('success', 'Result deleted. The student can attempt this exam again.');
    }
    redirect('admin/results.php');
}

$examId = get_id('exam');
$studentId = get_id('student');
$where = [];
$params = [];
if ($examId) { $where[] = 'r.exam_id = ?'; $params[] = $examId; }
if ($studentId) { $where[] = 'r.user_id = ?'; $params[] = $studentId; }

$results = q('SELECT r.*, u.name, u.email, e.title FROM results r
              JOIN users u ON u.id = r.user_id JOIN exams e ON e.id = r.exam_id'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY r.submitted_at DESC', $params)->fetchAll();
$exams = q('SELECT id, title FROM exams ORDER BY title')->fetchAll();
$students = q('SELECT id, name FROM users WHERE role = "student" ORDER BY name')->fetchAll();

$title = 'Results';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Results</h1></div>
<form method="get" class="filters card">
  <div><label for="exam">Exam</label>
    <select id="exam" name="exam"><option value="">All exams</option>
      <?php foreach ($exams as $x): ?><option value="<?= (int)$x['id'] ?>"<?= $examId == $x['id'] ? ' selected' : '' ?>><?= e($x['title']) ?></option><?php endforeach; ?>
    </select></div>
  <div><label for="student">Student</label>
    <select id="student" name="student"><option value="">All students</option>
      <?php foreach ($students as $s): ?><option value="<?= (int)$s['id'] ?>"<?= $studentId == $s['id'] ? ' selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="filter-actions"><button class="btn">Filter</button><a class="btn btn-secondary" href="<?= url('admin/results.php') ?>">Reset</a></div>
</form>
<div class="card">
  <?php if ($results): ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Student</th><th>Exam</th><th>Correct</th><th>Score</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($results as $r): ?>
      <tr>
        <td><?= e($r['name']) ?><br><span class="muted small"><?= e($r['email']) ?></span></td>
        <td><?= e($r['title']) ?></td>
        <td><?= (int)$r['correct_count'] ?> / <?= (int)$r['total_questions'] ?></td>
        <td><?= num($r['score']) ?> / <?= (int)$r['total_marks'] ?> <span class="muted">(<?= num($r['percentage']) ?>%)</span></td>
        <td><span class="badge <?= is_pass($r['percentage']) ? 'pass' : 'fail' ?>"><?= is_pass($r['percentage']) ? 'Pass' : 'Fail' ?></span></td>
        <td><?= e(format_date($r['submitted_at'])) ?></td>
        <td class="row-actions">
          <form method="post" data-confirm="Delete this result? The student will be able to retake the exam." data-yes="Delete" data-danger="1">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?><p class="empty">No results match these filters.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
