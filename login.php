<?php
require_once __DIR__ . '/includes/functions.php';
if (user()) redirect(home_for(user()['role']));

$error = '';
$email = '';
if (is_post()) {
    check_csrf();
    $email = strtolower(post('email'));
    $password = $_POST['password'] ?? '';
    $row = q('SELECT id, name, password, role FROM users WHERE email = ?', [$email])->fetch();
    if ($row && is_string($password) && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$row['id'], 'name' => $row['name'], 'role' => $row['role']];
        redirect(home_for($row['role']));
    }
    $error = 'Incorrect email or password.';
}

$title = 'Log in';
$body_class = 'auth-page';
require __DIR__ . '/includes/header.php';
?>
<section class="auth">
  <div class="auth-side">
    <h1>Online examinations, marked instantly.</h1>
    <p>Sit timed multiple-choice exams, see your score the moment you submit, and keep a record of every attempt.</p>
    <p class="demo"><strong>Demo accounts</strong><br>
      Admin: admin@exam.com / admin123<br>
      Student: student@exam.com / student123</p>
  </div>
  <div class="auth-form card">
    <h2>Log in</h2>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <label for="email">Email</label>
      <input id="email" name="email" type="email" value="<?= e($email) ?>" autocomplete="email" required autofocus>
      <label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="current-password" required>
      <button class="btn btn-block">Log in</button>
    </form>
    <p class="muted center">New student? <a href="<?= url('register.php') ?>">Create an account</a></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
