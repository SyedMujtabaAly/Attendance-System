<?php
if (!isset($page_title)) $page_title = 'HRMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> - AIMS HRMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="index.php" class="logo">AIMS HRMS</a>
            <nav class="main-nav">
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <?php if (!empty($current_user['is_staff'])): ?>
                        <a href="attendance_report.php">Attendance Report</a>
                        <a href="import_attendance.php">Import Attendance</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="index.php">Home</a>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="main-content">
        <?php
        $success = get_flash('success');
        $error = get_flash('error');
        if ($success): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif;
        if ($error): ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>
