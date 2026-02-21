<?php
require_once __DIR__ . '/config/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$page_title = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <section class="hero card">
        <h1>Welcome to AIMS HRMS</h1>
        <p>AIMS Services &amp; Consultancy – Human Resource Management System. Manage attendance, shifts, and reports in one place.</p>
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-secondary">Register</a>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
