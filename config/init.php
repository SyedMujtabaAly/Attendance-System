<?php
/**
 * Bootstrap: session, config, helpers
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

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

// CSRF protection for every state-changing form.
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('The request could not be verified. Please go back, refresh the page, and try again.');
    }
}

function working_days_elapsed($year, $month) {
    $today = new DateTimeImmutable('today');
    $start = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
    $end = $start->modify('last day of this month');
    if ($start > $today) return 0;
    if ($end > $today) $end = $today;

    $count = 0;
    for ($day = $start; $day <= $end; $day = $day->modify('+1 day')) {
        if ((int) $day->format('N') <= 5) $count++;
    }
    return $count;
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
