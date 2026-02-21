<?php
require_once __DIR__ . '/config/init.php';

$msg = 'You have been logged out successfully.';
if (is_logged_in()) {
    session_destroy();
}
session_start();
set_flash('success', $msg);
redirect('login.php');
