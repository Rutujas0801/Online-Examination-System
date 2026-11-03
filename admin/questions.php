<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

if (is_post()) {
    check_csrf();
    $id = (int)post('id');
    if (q('SELECT 1 FROM student_answers WHERE question_id = ? LIMIT 1', [$id])->fetch()) {
        flash('error', 'This question is part of submitted results and cannot be deleted. Edit it instead.');
    } else {
        q('DELETE FROM questions WHERE id = ?', [$id]);
        flash('success', 'Question deleted.');
    }
    redirect('admin/questions.php');
}

$questions = q('SELECT q.*, (SELECT COUNT(*) FROM exam_questions eq WHERE eq.question_id = q.id) AS used
                FROM questions q ORDER BY q.id DESC')->fetchAll();

$title = 'Questions';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
  <h1>Question bank</h1>
  <a class="btn" href="<?= url('admin/question_form.php') ?>">Add question</a>
</div>
<div class="card">
  <?php if ($questions): ?>
  <div class="table-wrap"><table>
    <thead><tr><th>#</th><th>Question</th><th>Correct answer</th><th>Used in</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($questions as $q): ?>
      <tr>
        <td><?= (int)$q['id'] ?></td>
        <td><?= e($q['question_text']) ?></td>
        <td><strong><?= e($q['correct_option']) ?>.</strong> <?= e($q['option_' . strtolower($q['correct_option'])]) ?></td>
        <td><?= (int)$q['used'] ?> exam<?= $q['used'] == 1 ? '' : 's' ?></td>
        <td class="row-actions">
          <a class="btn btn-secondary btn-sm" href="<?= url('admin/question_form.php?id=' . (int)$q['id']) ?>">Edit</a>
          <form method="post" data-confirm="Delete this question? It will also be removed from any exam that uses it." data-yes="Delete" data-danger="1">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?><p class="empty">No questions yet. Add the first one.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
