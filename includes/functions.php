<?php
require_once __DIR__ . '/../config/config.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// ---------- Database ----------
function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed. Start MySQL in XAMPP, import database/exam_db.sql and check config/config.php.');
        }
    }
    return $pdo;
}

// Run a prepared statement and return it
function q($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// ---------- Small helpers ----------
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . url($path));
    exit;
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function post($key) {
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

// Positive integer from the query string (0 when missing or invalid)
function get_id($key = 'id') {
    $value = filter_var($_GET[$key] ?? 0, FILTER_VALIDATE_INT);
    return $value && $value > 0 ? $value : 0;
}

function flash($type, $message) {
    $_SESSION['flash'] = [$type, $message];
}

function num($n) {
    return rtrim(rtrim(number_format((float)$n, 2, '.', ''), '0'), '.');
}

function format_date($d) {
    return date('d M Y, h:i A', strtotime($d));
}

// ---------- CSRF ----------
function csrf_field() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function check_csrf() {
    $sent = $_POST['csrf'] ?? '';
    if (empty($_SESSION['csrf']) || !is_string($sent) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(400);
        exit('Invalid or expired form. Go back, refresh the page and try again.');
    }
}

// ---------- Authentication and roles ----------
function user() {
    return $_SESSION['user'] ?? null;
}

function home_for($role) {
    return $role === 'admin' ? 'admin/index.php' : 'student/index.php';
}

// Call at the top of every protected page
function require_role($role) {
    $u = user();
    if (!$u) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
    if ($u['role'] !== $role) {
        flash('error', 'You do not have access to that page.');
        redirect(home_for($u['role']));
    }
    return $u;
}

// ---------- Results ----------
function is_pass($percentage) {
    return $percentage >= PASS_PERCENT;
}

function performance($percentage) {
    if ($percentage >= 80) return 'Excellent';
    if ($percentage >= 60) return 'Good';
    if ($percentage >= PASS_PERCENT) return 'Satisfactory';
    return 'Needs improvement';
}
