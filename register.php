<?php
require_once __DIR__ . '/config/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$post = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $employee_id = trim($post['employee_id'] ?? '');
    $first_name = trim($post['first_name'] ?? '');
    $last_name = trim($post['last_name'] ?? '');
    $email = trim($post['email'] ?? '');
    $department_id = (int)($post['department_id'] ?? 0);
    $shift_id = (int)($post['shift_id'] ?? 0);
    $password = $post['password'] ?? '';
    $password2 = $post['password2'] ?? '';

    if ($employee_id === '') $errors[] = 'Employee ID is required.';
    if ($first_name === '') $errors[] = 'First name is required.';
    if ($last_name === '') $errors[] = 'Last name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    if ($department_id < 1) $errors[] = 'Please select a department.';
    if ($shift_id < 1) $errors[] = 'Please select a shift.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $password2) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$employee_id]);
        if ($stmt->fetch()) $errors[] = 'This Employee ID already exists.';

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'This email is already registered.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, first_name, last_name, email, department_id, shift_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$employee_id, $hash, $first_name, $last_name, $email, $department_id, $shift_id]);
        set_flash('success', 'Registration successful! Please login with your Employee ID and password.');
        redirect('login.php');
    }
}

$departments = $pdo->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
$shifts = $pdo->query('SELECT id, name, start_time, end_time FROM shifts ORDER BY start_time')->fetchAll();

$page_title = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="auth-page">
        <div class="card">
            <h1>Employee Registration</h1>
            <?php foreach ($errors as $err): ?>
                <div class="alert alert-error"><?php echo e($err); ?></div>
            <?php endforeach; ?>
            <form method="post" action="register.php">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id" class="form-control" placeholder="e.g. EMP001" value="<?php echo e($post['employee_id'] ?? ''); ?>" autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo e($post['first_name'] ?? ''); ?>" autocomplete="given-name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo e($post['last_name'] ?? ''); ?>" autocomplete="family-name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($post['email'] ?? ''); ?>" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id" class="form-control">
                        <option value="">-- Select --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d['id']; ?>" <?php echo (($post['department_id'] ?? '') == $d['id']) ? 'selected' : ''; ?>><?php echo e($d['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="shift_id">Shift</label>
                    <select id="shift_id" name="shift_id" class="form-control">
                        <option value="">-- Select --</option>
                        <?php foreach ($shifts as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo (($post['shift_id'] ?? '') == $s['id']) ? 'selected' : ''; ?>><?php echo e($s['name']); ?> (<?php echo e(format_time($s['start_time'])); ?> - <?php echo e(format_time($s['end_time'])); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" minlength="6" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="password2">Confirm Password</label>
                    <input type="password" id="password2" name="password2" class="form-control" minlength="6" autocomplete="new-password" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <p class="form-footer">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
