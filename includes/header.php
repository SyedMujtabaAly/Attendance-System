<?php
if (!isset($page_title)) $page_title = 'Attendly';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Attendly is a lightweight PHP attendance management system for employees and HR teams.">
    <meta name="theme-color" content="#111827">
    <title><?php echo e($page_title); ?> | Attendly</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="index.php" class="logo" aria-label="Attendly home"><span class="logo-mark">A</span><span>Attendly</span></a>
            <nav class="main-nav" aria-label="Primary navigation">
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <?php if (!empty($current_user['is_staff'])): ?>
                        <a href="attendance_report.php">Attendance Report</a>
                        <a href="import_attendance.php">Import Attendance</a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-cta">Sign out</a>
                <?php else: ?>
                    <a href="index.php">Home</a>
                    <a href="login.php">Sign in</a>
                    <a href="register.php" class="nav-cta">Get started</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="main-content" id="main-content">
        <?php
        $success = get_flash('success');
        $error = get_flash('error');
        if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif;
        if ($error): ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>
