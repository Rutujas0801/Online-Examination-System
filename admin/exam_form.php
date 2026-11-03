<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$id = get_id();
$exam = ['title' => '', 'duration_minutes' => 30, 'total_marks' => 10];
$selected = [];
$attempts = 0;
if ($id) {
    $exam = q('SELECT * FROM exams WHERE id = ?', [$id])->fetch();
    if (!$exam) { flash('error', 'Exam not found.'); redirect('admin/exams.php'); }
    $selected = array_column(q('SELECT question_id FROM exam_questions WHERE exam_id = ?', [$id])->fetchAll(), 'question_id');
    $attempts = (int)q('SELECT COUNT(*) FROM results WHERE exam_id = ?', [$id])->fetchColumn();
}
$all = q('SELECT id, question_text FROM questions ORDER BY id')->fetchAll();

$errors = [];
if (is_post()) {
    check_csrf();
    $exam['title'] = post('title');
    $exam['duration_minutes'] = post('duration_minutes');
    $exam['total_marks'] = post('total_marks');
    $validIds = array_column($all, 'id');
    $picked = $_POST['questions'] ?? [];
    $selected = is_array($picked) ? array_values(array_intersect($validIds, array_map('intval', $picked))) : [];

    if ($exam['title'] === '' || mb_strlen($exam['title']) > 150) $errors[] = 'Enter an exam title (up to 150 characters).';
    $dur = filter_var($exam['duration_minutes'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 300]]);
    $marks = filter_var($exam['total_marks'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);
    if ($dur === false) $errors[] = 'Duration must be a whole number from 1 to 300 minutes.';
    if ($marks === false) $errors[] = 'Total marks must be a whole number from 1 to 1000.';
    if (!$selected) $errors[] = 'Select at least one question.';

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($id) {
                q('UPDATE exams SET title=?, duration_minutes=?, total_marks=? WHERE id=?', [$exam['title'], $dur, $marks, $id]);
                q('DELETE FROM exam_questions WHERE exam_id = ?', [$id]);
            } else {
                q('INSERT INTO exams (title, duration_minutes, total_marks) VALUES (?, ?, ?)', [$exam['title'], $dur, $marks]);
                $id = (int)$pdo->lastInsertId();
            }
            foreach ($selected as $qid) {
                q('INSERT INTO exam_questions (exam_id, question_id) VALUES (?, ?)', [$id, $qid]);
            }
            $pdo->commit();
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $errors[] = 'Could not save the exam. Please try again.';
        }
        if (!$errors) {
            flash('success', 'Exam saved.');
            redirect('admin/exams.php');
        }
    }
}

$title = get_id() ? 'Edit exam' : 'Create exam';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1><?= e($title) ?></h1></div>
<div class="card form-card">
  <?php if ($errors): ?>
    <div class="alert alert-error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
  <?php endif; ?>
  <?php if ($attempts): ?>
    <div class="alert alert-info"><?= $attempts ?> student<?= $attempts == 1 ? ' has' : 's have' ?> already submitted this exam. Changes will not alter stored results.</div>
  <?php endif; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <label for="title">Exam title</label>
    <input id="title" name="title" value="<?= e($exam['title']) ?>" required>
    <div class="two-col">
      <div><label for="duration_minutes">Duration (minutes)</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="1" max="300" value="<?= e($exam['duration_minutes']) ?>" required></div>
      <div><label for="total_marks">Total marks</label>
        <input id="total_marks" name="total_marks" type="number" min="1" max="1000" value="<?= e($exam['total_marks']) ?>" required></div>
    </div>
    <p class="muted small">Marks are shared equally between the selected questions.</p>

    <fieldset class="picker">
      <legend>Questions in this exam</legend>
      <?php if ($all): foreach ($all as $row): ?>
        <label class="check">
          <input type="checkbox" name="questions[]" value="<?= (int)$row['id'] ?>"<?= in_array((int)$row['id'], $selected, true) ? ' checked' : '' ?>>
          <span><?= e($row['question_text']) ?></span>
        </label>
      <?php endforeach; else: ?>
        <p class="empty">Add some questions first.</p>
      <?php endif; ?>
    </fieldset>
    <div class="form-actions">
      <button class="btn">Save exam</button>
      <a class="btn btn-secondary" href="<?= url('admin/exams.php') ?>">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
