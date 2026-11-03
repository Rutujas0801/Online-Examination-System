<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

if (is_post()) {
    check_csrf();
    q('DELETE FROM exams WHERE id = ?', [(int)post('id')]);
    flash('success', 'Exam deleted.');
    redirect('admin/exams.php');
}

$exams = q('SELECT e.*,
              (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id = e.id) AS qn,
              (SELECT COUNT(*) FROM results r WHERE r.exam_id = e.id) AS attempts
            FROM exams e ORDER BY e.id DESC')->fetchAll();

$title = 'Exams';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
  <h1>Exams</h1>
  <a class="btn" href="<?= url('admin/exam_form.php') ?>">Create exam</a>
</div>
<div class="card">
  <?php if ($exams): ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Title</th><th>Questions</th><th>Duration</th><th>Total marks</th><th>Attempts</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($exams as $x): ?>
      <tr>
        <td><?= e($x['title']) ?></td>
        <td><?= (int)$x['qn'] ?></td>
        <td><?= (int)$x['duration_minutes'] ?> min</td>
        <td><?= (int)$x['total_marks'] ?></td>
        <td><?= (int)$x['attempts'] ?></td>
        <td class="row-actions">
          <a class="btn btn-secondary btn-sm" href="<?= url('admin/exam_form.php?id=' . (int)$x['id']) ?>">Edit</a>
          <form method="post" data-confirm="Delete this exam? All student results for it will be deleted too." data-yes="Delete" data-danger="1">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?><p class="empty">No exams yet. Create one and assign questions to it.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
