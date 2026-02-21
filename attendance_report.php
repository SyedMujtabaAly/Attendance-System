<?php
require_once __DIR__ . '/config/init.php';
require_login();

if (empty($current_user['is_staff'])) {
    set_flash('error', 'You do not have permission to access this page.');
    redirect('dashboard.php');
}

$department_id = isset($_GET['department']) ? (int)$_GET['department'] : null;
$selected_month = $_GET['month'] ?? date('Y-m');
$month_parts = explode('-', $selected_month);
$year = (int)($month_parts[0] ?? date('Y'));
$month = (int)($month_parts[1] ?? date('m'));
$month_start = sprintf('%04d-%02d-01', $year, $month);
$month_end = date('Y-m-t', strtotime($month_start));
$total_days = (int) date('t', strtotime($month_start));

$sql = 'SELECT u.id, u.username, u.first_name, u.last_name, d.name AS department_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1';
$params = [];
if ($department_id) {
    $sql .= ' AND u.department_id = ?';
    $params[] = $department_id;
}
$sql .= ' ORDER BY d.name, u.username';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

$attendance_data = [];
foreach ($employees as $emp) {
    // Present days in selected month
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM attendance WHERE user_id = ? AND date >= ? AND date <= ? AND punch_in_time IS NOT NULL');
    $stmt->execute([$emp['id'], $month_start, $month_end]);
    $present_days = (int) $stmt->fetch()['cnt'];

    // Late days in selected month
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM attendance WHERE user_id = ? AND date >= ? AND date <= ? AND is_late = 1');
    $stmt->execute([$emp['id'], $month_start, $month_end]);
    $late_days = (int) $stmt->fetch()['cnt'];

    $absent_days = max(0, $total_days - $present_days);

    // Late consistency for the month (percentage of present days that were late)
    $late_rate = $present_days > 0 ? round(($late_days * 100) / $present_days, 1) : 0;

    // All-time late days (for deeper admin insight)
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM attendance WHERE user_id = ? AND is_late = 1');
    $stmt->execute([$emp['id']]);
    $late_all_time = (int) $stmt->fetch()['cnt'];

    $attendance_data[] = [
        'employee' => $emp,
        'present_days' => $present_days,
        'late_days' => $late_days,
        'absent_days' => $absent_days,
        'late_rate' => $late_rate,
        'late_all_time' => $late_all_time,
        'total_days' => $total_days,
    ];
}

$departments = $pdo->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();

$page_title = 'Attendance Report';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <h1>Attendance Report</h1>

        <form method="get" action="attendance_report.php" class="report-filters">
            <div class="form-group">
                <label for="month">Month</label>
                <input type="month" id="month" name="month" class="form-control" value="<?php echo e($selected_month); ?>">
            </div>
            <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department" class="form-control">
                    <option value="">All</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo $department_id == $d['id'] ? 'selected' : ''; ?>><?php echo e($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>

        <p><strong>Period:</strong> <?php echo e(date('F Y', strtotime($month_start))); ?></p>
        <p><a href="import_attendance.php" class="btn btn-primary">📥 Import from Excel/CSV</a></p>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Late</th>
                    <th>Late % (month)</th>
                    <th>Total Late (all time)</th>
                    <th>Absent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_data as $row): ?>
                <tr>
                    <td><?php echo e($row['employee']['username']); ?></td>
                    <td><?php echo e($row['employee']['first_name'] . ' ' . $row['employee']['last_name']); ?></td>
                    <td><?php echo e($row['employee']['department_name'] ?? '—'); ?></td>
                    <td><?php echo $row['present_days']; ?></td>
                    <td><?php echo $row['late_days']; ?></td>
                    <td><?php echo $row['late_rate']; ?>%</td>
                    <td><?php echo $row['late_all_time']; ?></td>
                    <td><?php echo $row['absent_days']; ?></td>
                    <td><a href="employee_attendance.php?employee_id=<?php echo e(urlencode($row['employee']['username'])); ?>&month=<?php echo e($selected_month); ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($attendance_data)): ?>
                    <tr><td colspan="9">No employees found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
