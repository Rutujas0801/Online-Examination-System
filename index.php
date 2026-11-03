<?php
require_once __DIR__ . '/includes/functions.php';
$u = user();
redirect($u ? home_for($u['role']) : 'login.php');
