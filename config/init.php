<?php
/**
 * Bootstrap: session, config, helpers
 */
session_start();

$base_path = dirname(__DIR__);
$pdo = require $base_path . '/config/database.php';

// Helper: redirect
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

// Helper: check if logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper: require login
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please log in to continue.';
        redirect('login.php');
    }
}

// Helper: get current user from DB
function current_user($pdo) {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare('SELECT u.*, d.name AS department_name, s.name AS shift_name, s.start_time AS shift_start_time FROM users u LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN shifts s ON u.shift_id = s.id WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

// Set global current user for layout (call after including init)
$current_user = is_logged_in() ? current_user($pdo) : null;

// Flash messages
function get_flash($key) {
    $k = 'flash_' . $key;
    if (isset($_SESSION[$k])) {
        $msg = $_SESSION[$k];
        unset($_SESSION[$k]);
        return $msg;
    }
    return null;
}
function set_flash($key, $message) {
    $_SESSION['flash_' . $key] = $message;
}

// Escape for HTML
function e($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// Format time for display
function format_time($time) {
    if (!$time) return '—';
    if (is_string($time)) return date('H:i', strtotime($time));
    return $time;
}
