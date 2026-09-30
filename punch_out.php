<?php
require_once __DIR__ . '/config/init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}
verify_csrf();

$user = $current_user;
$today = date('Y-m-d');
$now_time = date('H:i:s');

$stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? AND date = ?');
$stmt->execute([$user['id'], $today]);
$att = $stmt->fetch();

if (!$att || !$att['punch_in_time']) {
    set_flash('error', 'Please punch in before punching out.');
    redirect('dashboard.php');
}

if ($att['punch_out_time']) {
    set_flash('error', 'You have already punched out at ' . format_time($att['punch_out_time']));
    redirect('dashboard.php');
}

$stmt = $pdo->prepare('UPDATE attendance SET punch_out_time = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
$stmt->execute([$now_time, $att['id']]);

set_flash('success', 'Punch-out recorded at ' . format_time($now_time));
redirect('dashboard.php');
