<?php
require_once __DIR__ . '/config/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}

$user = $current_user;
$today = date('Y-m-d');
$now_time = date('H:i:s');

// Get or create today's attendance
$stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? AND date = ?');
$stmt->execute([$user['id'], $today]);
$att = $stmt->fetch();

if ($att && $att['punch_in_time']) {
    set_flash('error', 'You have already punched in at ' . format_time($att['punch_in_time']));
    redirect('dashboard.php');
}

$is_late = 0;
if (!empty($user['shift_start_time'])) {
    $shift_start = $user['shift_start_time'];
    if (is_string($shift_start)) $shift_start = substr($shift_start, 0, 8);
    if ($now_time > $shift_start) $is_late = 1;
}

if ($att) {
    $stmt = $pdo->prepare('UPDATE attendance SET punch_in_time = ?, is_late = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$now_time, $is_late, $att['id']]);
} else {
    $stmt = $pdo->prepare('INSERT INTO attendance (user_id, date, punch_in_time, is_late) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user['id'], $today, $now_time, $is_late]);
}

$msg = 'Punch-in recorded at ' . format_time($now_time) . ($is_late ? ' (Late)' : '');
set_flash('success', $msg);
redirect('dashboard.php');
