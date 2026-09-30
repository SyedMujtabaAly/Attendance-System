<?php
require_once __DIR__ . '/config/init.php';
require_login();

$user = $current_user;
$today = date('Y-m-d');

// Today's attendance
$stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? AND date = ?');
$stmt->execute([$user['id'], $today]);
$today_attendance = $stmt->fetch();

// Current month range
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

$stmt = $pdo->prepare('SELECT * FROM attendance WHERE user_id = ? AND date >= ? AND date <= ? ORDER BY date DESC');
$stmt->execute([$user['id'], $month_start, $month_end]);
$monthly_attendance = $stmt->fetchAll();

$total_present = 0;
$total_late = 0;
foreach ($monthly_attendance as $r) {
    if (!empty($r['punch_in_time'])) $total_present++;
    if (!empty($r['is_late'])) $total_late++;
}
$working_days = working_days_elapsed((int) date('Y'), (int) date('m'));
$total_absent = max(0, $working_days - $total_present);

$attendance_status = 'Not Punched In';
if ($today_attendance) {
    if ($today_attendance['punch_out_time']) $attendance_status = 'Checked Out';
    else $attendance_status = 'Checked In';
}
$is_late_today = $today_attendance && !empty($today_attendance['is_late']);

$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <h1>Dashboard</h1>
        <p><strong><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></strong> (<?php echo e($user['username']); ?>) &bull; <?php echo e($user['department_name'] ?? '—'); ?> &bull; <?php echo e($user['shift_name'] ?? '—'); ?></p>
        <p class="stat-card label"><?php echo e(date('l, F j, Y')); ?></p>

        <h2>Today's Status</h2>
        <p>
            <strong>Status:</strong>
            <span class="badge badge-<?php echo $attendance_status === 'Checked Out' ? 'success' : ($attendance_status === 'Checked In' ? 'warning' : 'secondary'); ?>">
                <?php echo e($attendance_status); ?>
            </span>
            <?php if ($today_attendance && $today_attendance['punch_in_time']): ?>
                &bull; <?php echo $is_late_today ? '<span class="badge badge-danger">Late</span>' : '<span class="badge badge-success">On Time</span>'; ?>
            <?php endif; ?>
        </p>
        <?php if ($today_attendance): ?>
            <p>
                Punch In: <strong><?php echo e(format_time($today_attendance['punch_in_time'])); ?></strong>
                <?php if ($today_attendance['punch_out_time']): ?>
                    &bull; Punch Out: <strong><?php echo e(format_time($today_attendance['punch_out_time'])); ?></strong>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <div class="punch-actions">
            <?php if (!$today_attendance || !$today_attendance['punch_in_time']): ?>
                <form method="post" action="punch_in.php">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-success">Punch In</button>
                </form>
            <?php endif; ?>
            <?php if ($today_attendance && $today_attendance['punch_in_time'] && !$today_attendance['punch_out_time']): ?>
                <form method="post" action="punch_out.php">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-warning">Punch Out</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h2>Month Summary (<?php echo e(date('F Y')); ?>)</h2>
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="number"><?php echo $total_present; ?></div>
                <div class="label">Present</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $total_late; ?></div>
                <div class="label">Late</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $total_absent; ?></div>
                <div class="label">Absent</div>
            </div>
        </div>

        <p><a href="employee_attendance.php?employee_id=<?php echo e(urlencode($user['username'])); ?>&month=<?php echo e(date('Y-m')); ?>" class="btn btn-secondary">View my attendance details</a></p>

        <h3>Attendance Records</h3>
        <div class="table-wrap"><table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Punch In</th>
                    <th>Punch Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthly_attendance as $r): ?>
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
                </tr>
                <?php endforeach; ?>
                <?php if (empty($monthly_attendance)): ?>
                    <tr><td colspan="4">No records this month.</td></tr>
                <?php endif; ?>
            </tbody>
        </table></div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
