<?php
require_once __DIR__ . '/includes/functions.php';
if (is_post()) {
    check_csrf();
    $_SESSION = [];
    session_regenerate_id(true);
    flash('success', 'You have been logged out.');
}
redirect('login.php');
