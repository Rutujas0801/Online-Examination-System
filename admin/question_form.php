<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$id = get_id();
$q = ['question_text' => '', 'option_a' => '', 'option_b' => '', 'option_c' => '', 'option_d' => '', 'correct_option' => 'A'];
if ($id) {
    $q = q('SELECT * FROM questions WHERE id = ?', [$id])->fetch();
    if (!$q) { flash('error', 'Question not found.'); redirect('admin/questions.php'); }
}

$errors = [];
if (is_post()) {
    check_csrf();
    $q['question_text'] = post('question_text');
    foreach (['a', 'b', 'c', 'd'] as $k) $q['option_' . $k] = post('option_' . $k);
    $q['correct_option'] = post('correct_option');

    if ($q['question_text'] === '' || mb_strlen($q['question_text']) > 1000) $errors[] = 'Enter the question (up to 1000 characters).';
    foreach (['a', 'b', 'c', 'd'] as $k) {
        $len = mb_strlen($q['option_' . $k]);
        if ($len < 1 || $len > 255) $errors[] = 'Option ' . strtoupper($k) . ' is required (up to 255 characters).';
    }
    if (!in_array($q['correct_option'], ['A', 'B', 'C', 'D'], true)) $errors[] = 'Choose the correct answer.';

    if (!$errors) {
        $values = [$q['question_text'], $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'], $q['correct_option']];
        if ($id) {
            q('UPDATE questions SET question_text=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=? WHERE id=?', array_merge($values, [$id]));
            flash('success', 'Question updated.');
        } else {
            q('INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)', $values);
            flash('success', 'Question added.');
        }
        redirect('admin/questions.php');
    }
}

$title = $id ? 'Edit question' : 'Add question';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1><?= e($title) ?></h1></div>
<div class="card form-card">
  <?php if ($errors): ?>
    <div class="alert alert-error" role="alert"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
  <?php endif; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <label for="question_text">Question</label>
    <textarea id="question_text" name="question_text" rows="3" required><?= e($q['question_text']) ?></textarea>
    <?php foreach (['a', 'b', 'c', 'd'] as $k): ?>
      <label for="option_<?= $k ?>">Option <?= strtoupper($k) ?></label>
      <input id="option_<?= $k ?>" name="option_<?= $k ?>" value="<?= e($q['option_' . $k]) ?>" required>
    <?php endforeach; ?>
    <label for="correct_option">Correct answer</label>
    <select id="correct_option" name="correct_option">
      <?php foreach (['A', 'B', 'C', 'D'] as $c): ?>
        <option value="<?= $c ?>"<?= $q['correct_option'] === $c ? ' selected' : '' ?>>Option <?= $c ?></option>
      <?php endforeach; ?>
    </select>
    <div class="form-actions">
      <button class="btn"><?= $id ? 'Save changes' : 'Add question' ?></button>
      <a class="btn btn-secondary" href="<?= url('admin/questions.php') ?>">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
