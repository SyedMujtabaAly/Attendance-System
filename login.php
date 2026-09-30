<?php
require_once __DIR__ . '/config/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $employee_id = trim($_POST['employee_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($employee_id === '' || $password === '') {
        $error = 'Please enter Employee ID and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, first_name, is_active FROM users WHERE username = ?');
        $stmt->execute([$employee_id]);
        $user = $stmt->fetch();

        if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            set_flash('success', 'Welcome back, ' . $user['first_name'] . '!');
            redirect('dashboard.php');
        } else {
            $error = 'Invalid Employee ID or password. Please try again.';
        }
    }
}

$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="auth-page">
        <div class="card">
            <h1>Employee Login</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id" class="form-control" placeholder="e.g. EMP001" value="<?php echo e($_POST['employee_id'] ?? ''); ?>" autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p class="form-footer">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
