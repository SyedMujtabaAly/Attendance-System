<?php
require_once __DIR__ . '/config/init.php';
require_login();

if (empty($current_user['is_staff'])) {
    set_flash('error', 'You do not have permission to access this page.');
    redirect('dashboard.php');
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $attendance_id = (int)($_POST['attendance_id'] ?? 0);
    $employee_id = trim($_POST['employee_id'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $punch_in = trim($_POST['punch_in_time'] ?? '');
    $punch_out = trim($_POST['punch_out_time'] ?? '');

    if ($attendance_id < 1) {
        $errors[] = 'Invalid attendance record.';
    }

    if (empty($errors)) {
        // Get employee
        $stmt = $pdo->prepare('SELECT id, shift_id FROM users WHERE username = ?');
        $stmt->execute([$employee_id]);
        $employee = $stmt->fetch();

        if (!$employee) {
            $errors[] = 'Employee not found.';
        } else {
            // Calculate is_late based on shift
            $is_late = 0;
            if (!empty($punch_in)) {
                $stmt = $pdo->prepare('SELECT start_time FROM shifts WHERE id = ?');
                $stmt->execute([$employee['shift_id']]);
                $shift = $stmt->fetch();
                if ($shift && $punch_in > $shift['start_time']) {
                    $is_late = 1;
                }
            }

            // Update attendance
            $stmt = $pdo->prepare('UPDATE attendance SET punch_in_time = ?, punch_out_time = ?, is_late = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([
                $punch_in ?: null,
                $punch_out ?: null,
                $is_late,
                $attendance_id
            ]);

            $success = true;
            set_flash('success', 'Attendance updated successfully.');
        }
    }
}

// Get attendance record to edit
$attendance_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$attendance = null;
$employee = null;

if ($attendance_id > 0) {
    $stmt = $pdo->prepare('SELECT a.*, u.username, u.first_name, u.last_name FROM attendance a JOIN users u ON a.user_id = u.id WHERE a.id = ?');
    $stmt->execute([$attendance_id]);
    $attendance = $stmt->fetch();
    if ($attendance) {
        $employee = [
            'username' => $attendance['username'],
            'name' => $attendance['first_name'] . ' ' . $attendance['last_name']
        ];
    }
}

$page_title = 'Edit Attendance';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <h1>Edit Attendance</h1>
        <p><a href="attendance_report.php" class="btn btn-secondary">← Back to Report</a></p>

        <?php if ($success): ?>
            <div class="alert alert-success">Attendance updated successfully!</div>
        <?php endif; ?>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?php echo e($err); ?></div>
        <?php endforeach; ?>

        <?php if (!$attendance): ?>
            <p>Please select an attendance record to edit from the <a href="attendance_report.php">Attendance Report</a>.</p>
        <?php else: ?>
            <form method="post" action="admin_edit_attendance.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="attendance_id" value="<?php echo $attendance['id']; ?>">
                <input type="hidden" name="employee_id" value="<?php echo e($attendance['username']); ?>">

                <div class="form-group">
                    <label>Employee</label>
                    <p><strong><?php echo e($employee['name']); ?></strong> (<?php echo e($attendance['username']); ?>)</p>
                </div>

                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" class="form-control" value="<?php echo e($attendance['date']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="punch_in_time">Punch In Time</label>
                    <input type="time" id="punch_in_time" name="punch_in_time" class="form-control" value="<?php echo e($attendance['punch_in_time'] ? substr($attendance['punch_in_time'], 0, 5) : ''); ?>">
                    <small>Leave empty if absent</small>
                </div>

                <div class="form-group">
                    <label for="punch_out_time">Punch Out Time</label>
                    <input type="time" id="punch_out_time" name="punch_out_time" class="form-control" value="<?php echo e($attendance['punch_out_time'] ? substr($attendance['punch_out_time'], 0, 5) : ''); ?>">
                    <small>Leave empty if not punched out</small>
                </div>

                <button type="submit" class="btn btn-primary">Update Attendance</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
