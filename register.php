<?php
require_once __DIR__ . '/includes/functions.php';
if (user()) redirect(home_for(user()['role']));

$errors = [];
$name = $email = '';
if (is_post()) {
    check_csrf();
    $name = post('name');
    $email = strtolower(post('email'));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!is_string($password) || !is_string($confirm)) { $password = $confirm = ''; }

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors['name'] = 'Enter your full name (2 to 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) $errors['email'] = 'Enter a valid email address.';
    if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors['confirm'] = 'Passwords do not match.';

    if (!$errors && q('SELECT id FROM users WHERE email = ?', [$email])->fetch()) {
        $errors['email'] = 'This email is already registered.';
    }
    if (!$errors) {
        try {
            q('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "student")',
              [$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Account created. You can log in now.');
            redirect('login.php');
        } catch (PDOException $ex) {
            // Two people registering the same email at the same moment hit the UNIQUE key
            $errors['email'] = 'This email is already registered.';
        }
    }
}

$title = 'Register';
$body_class = 'auth-page';
require __DIR__ . '/includes/header.php';
?>
<section class="auth auth-narrow">
  <div class="auth-form card">
    <h2>Student registration</h2>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <label for="name">Full name</label>
      <input id="name" name="name" value="<?= e($name) ?>" autocomplete="name" required>
      <?php if (isset($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="email" required>
      <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>

      <label for="password">Password <span class="muted">(min 6 characters)</span></label>
      <input id="password" name="password" type="password" autocomplete="new-password" required>
      <?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>

      <label for="confirm">Confirm password</label>
      <input id="confirm" name="confirm" type="password" autocomplete="new-password" required>
      <?php if (isset($errors['confirm'])): ?><p class="field-error"><?= e($errors['confirm']) ?></p><?php endif; ?>

      <button class="btn btn-block">Create account</button>
    </form>
    <p class="muted center">Already registered? <a href="<?= url('login.php') ?>">Log in</a></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
