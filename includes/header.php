<?php
// Expects: $title (page title), optional $body_class
$me = user();
$self = basename($_SERVER['SCRIPT_NAME']);
$links = [];
if ($me && $me['role'] === 'admin') {
    $links = ['admin/index.php' => 'Dashboard', 'admin/questions.php' => 'Questions', 'admin/exams.php' => 'Exams',
              'admin/students.php' => 'Students', 'admin/results.php' => 'Results'];
} elseif ($me) {
    $links = ['student/index.php' => 'My exams', 'student/results.php' => 'My results'];
} else {
    $links = ['login.php' => 'Log in', 'register.php' => 'Register'];
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Online Examination') ?> | ExamPortal</title>
<script>try{var t=localStorage.getItem('theme');if(t)document.documentElement.dataset.theme=t}catch(e){}</script>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="<?= e($body_class ?? '') ?>">
<a class="skip" href="#main">Skip to content</a>
<header class="topbar">
  <div class="wrap bar">
    <a class="brand" href="<?= url($me ? home_for($me['role']) : 'login.php') ?>">ExamPortal</a>
    <nav class="nav" aria-label="Main">
      <?php foreach ($links as $file => $label): ?>
        <a href="<?= url($file) ?>"<?= basename($file) === $self ? ' class="on" aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="bar-right">
      <?php if ($me): ?>
        <span class="who"><?= e($me['name']) ?> <small><?= e($me['role']) ?></small></span>
        <form method="post" action="<?= url('logout.php') ?>"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">Log out</button></form>
      <?php endif; ?>
      <button type="button" id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Switch colour theme"></button>
    </div>
  </div>
</header>
<main id="main" class="wrap">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?></div>
<?php endif; ?>
