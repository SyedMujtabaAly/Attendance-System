<?php
require_once __DIR__ . '/config/init.php';
require_login();

$employee_id = $_GET['employee_id'] ?? '';
$selected_month = $_GET['month'] ?? date('Y-m');

if ($employee_id === '') {
    set_flash('error', 'Employee not specified.');
    redirect('dashboard.php');
}

$stmt = $pdo->prepare('SELECT u.*, d.name AS department_name, s.name AS shift_name FROM users u LEFT JOIN departments d ON u.department_id = d.id LEFT JOIN shifts s ON u.shift_id = s.id WHERE u.username = ?');
$stmt->execute([$employee_id]);
$employee = $stmt->fetch();

if (!$employee) {
    set_flash('error', 'Employee not found.');
    redirect('dashboard.php');
}

// Permission: own record or staff
if ($current_user['id'] != $employee['id'] && empty($current_user['is_staff'])) {
    set_flash('error', 'You do not have permission to view this employee\'s records.');
    redirect('dashboard.php');
}

$month_parts = explode('-', $selected_month);
$year = (int)($month_parts[0] ?? date('Y'));
$month = (int)($month_parts[1] ?? date('m'));
$month_start = sprintf('%04d-%02d-01', $year, $month);
$month_end = date('Y-m-t', strtotime($month_start));

// Records for selected month
$stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? AND date >= ? AND date <= ? ORDER BY date DESC');
$stmt->execute([$employee['id'], $month_start, $month_end]);
$attendance_records = $stmt->fetchAll();

// All-time stats for this employee
$stmt = $pdo->prepare('SELECT 
    COUNT(*) AS total_present,
    SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) AS total_late
    FROM attendance
    WHERE user_id = ? AND punch_in_time IS NOT NULL');
$stmt->execute([$employee['id']]);
$stats = $stmt->fetch() ?: ['total_present' => 0, 'total_late' => 0];
$total_present_all = (int) $stats['total_present'];
$total_late_all = (int) $stats['total_late'];
$late_rate_all = $total_present_all > 0 ? round(($total_late_all * 100) / $total_present_all, 1) : 0;

$page_title = $employee['first_name'] . ' ' . $employee['last_name'] . ' - Attendance';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <h1>Attendance Details</h1>
        <p><strong><?php echo e($employee['first_name'] . ' ' . $employee['last_name']); ?></strong> (<?php echo e($employee['username']); ?>) &bull; <?php echo e($employee['department_name'] ?? '—'); ?></p>
        <?php if (!empty($current_user['is_staff'])): ?>
            <p>
                <strong>Total late days (all time):</strong> <?php echo $total_late_all; ?>
                <?php if ($total_present_all > 0): ?>
                    &nbsp;•&nbsp;<strong>Late consistency:</strong> <?php echo $late_rate_all; ?>% of <?php echo $total_present_all; ?> present days
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <form method="get" action="employee_attendance.php" class="report-filters">
            <input type="hidden" name="employee_id" value="<?php echo e($employee_id); ?>">
            <div class="form-group">
                <label for="month">Month</label>
                <input type="month" id="month" name="month" class="form-control" value="<?php echo e($selected_month); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>

        <h2><?php echo e(date('F Y', strtotime($month_start))); ?></h2>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Punch In</th>
                    <th>Punch Out</th>
                    <th>Status</th>
                    <?php if (!empty($current_user['is_staff'])): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_records as $r): ?>
                <tr>
                    <td><?php echo e($r['date']); ?></td>
                    <td><?php echo e(format_time($r['punch_in_time'])); ?></td>
                    <td><?php echo e(format_time($r['punch_out_time'])); ?></td>
                    <td>
                        <?php if (!$r['punch_in_time']): ?>
                            <span class="badge badge-secondary">Absent</span>
                        <?php elseif ($r['is_late']): ?>
                            <span class="badge badge-danger">Late</span>
                        <?php else: ?>
                            <span class="badge badge-success">On Time</span>
                        <?php endif; ?>
                    </td>
                    <?php if (!empty($current_user['is_staff'])): ?>
                        <td><a href="admin_edit_attendance.php?id=<?php echo $r['id']; ?>">Edit</a></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($attendance_records)): ?>
                    <tr><td colspan="4">No records for this month.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($current_user['is_staff'])): ?>
            <p><a href="attendance_report.php" class="btn btn-secondary">Back to Report</a></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
