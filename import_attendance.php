<?php
require_once __DIR__ . '/config/init.php';
require_login();

if (empty($current_user['is_staff'])) {
    set_flash('error', 'You do not have permission to access this page.');
    redirect('dashboard.php');
}

$errors = [];
$success_count = 0;
$error_count = 0;
$imported_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    verify_csrf();
    $file = $_FILES['excel_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error.';
    } else {
        $file_path = $file['tmp_name'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, ['csv', 'xlsx', 'xls'])) {
            $errors[] = 'Invalid file format. Please upload CSV, XLSX, or XLS file.';
        } else {
            // Read CSV file (simplest approach - Excel can be saved as CSV)
            if ($file_ext === 'csv') {
                $handle = fopen($file_path, 'r');
                if ($handle) {
                    $row_num = 0;
                    $col_map = null;
                    while (($row = fgetcsv($handle)) !== false) {
                        $row_num++;
                        if ($row_num === 1) {
                            // Build column map from header row (flexible order)
                            $headers = array_map(function ($h) {
                                $h = strtolower(trim((string)$h));
                                $h = preg_replace('/\s+/', ' ', $h);
                                return $h;
                            }, $row);

                            $find = function($names) use ($headers) {
                                foreach ($names as $n) {
                                    $idx = array_search($n, $headers, true);
                                    if ($idx !== false) return $idx;
                                }
                                return null;
                            };

                            $col_map = [
                                'emp_id'   => $find(['employee id', 'emp id', 'employee_id', 'emp_id']),
                                'emp_name' => $find(['employee name', 'name', 'employee']),
                                'date'     => $find(['date', 'attendance date']),
                                'punch_in' => $find(['punch in time', 'punch in', 'time in', 'punch_in']),
                                'punch_out'=> $find(['punch out time', 'punch out', 'time out', 'punch_out']),
                            ];

                            // Fallback to old fixed-order template if header doesn't match
                            if ($col_map['emp_id'] === null && isset($row[0]) && strtolower(trim((string)$row[0])) === 'employee id') {
                                // old template: Employee ID,Date,Punch In Time,Punch Out Time
                                $col_map['emp_id'] = 0;
                                $col_map['date'] = 1;
                                $col_map['punch_in'] = 2;
                                $col_map['punch_out'] = 3;
                            }

                            // Required columns
                            if ($col_map['emp_id'] === null || $col_map['date'] === null || $col_map['punch_in'] === null) {
                                $errors[] = 'CSV header is missing required columns. Required: Employee ID, Date, Punch In Time. Optional: Employee Name, Punch Out Time.';
                                break;
                            }

                            continue; // Skip header
                        }

                        if (!$col_map) continue;

                        $emp_id = trim((string)($row[$col_map['emp_id']] ?? ''));
                        $emp_name = $col_map['emp_name'] !== null ? trim((string)($row[$col_map['emp_name']] ?? '')) : '';
                        $date = trim((string)($row[$col_map['date']] ?? ''));
                        $punch_in = trim((string)($row[$col_map['punch_in']] ?? ''));
                        $punch_out = $col_map['punch_out'] !== null ? trim((string)($row[$col_map['punch_out']] ?? '')) : '';
                        
                        if (empty($emp_id) || empty($date)) continue;
                        
                        // Get employee
                        $stmt = $pdo->prepare('SELECT id, shift_id FROM users WHERE username = ?');
                        $stmt->execute([$emp_id]);
                        $employee = $stmt->fetch();
                        
                        if (!$employee) {
                            $error_count++;
                            $imported_data[] = ['row' => $row_num, 'emp_id' => $emp_id, 'status' => 'error', 'msg' => 'Employee not found'];
                            continue;
                        }
                        
                        // Format time (ensure HH:MM:SS)
                        if ($punch_in && strlen($punch_in) <= 5) $punch_in .= ':00';
                        if ($punch_out && strlen($punch_out) <= 5) $punch_out .= ':00';
                        
                        // Calculate is_late
                        $is_late = 0;
                        if (!empty($punch_in) && $employee['shift_id']) {
                            $stmt = $pdo->prepare('SELECT start_time FROM shifts WHERE id = ?');
                            $stmt->execute([$employee['shift_id']]);
                            $shift = $stmt->fetch();
                            if ($shift && $punch_in > $shift['start_time']) {
                                $is_late = 1;
                            }
                        }
                        
                        // Insert or update attendance
                        $stmt = $pdo->prepare('INSERT OR REPLACE INTO attendance (user_id, date, punch_in_time, punch_out_time, is_late, updated_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)');
                        try {
                            $stmt->execute([
                                $employee['id'],
                                $date,
                                $punch_in ?: null,
                                $punch_out ?: null, // optional (your machine may not provide it)
                                $is_late
                            ]);
                            $success_count++;
                            $imported_data[] = ['row' => $row_num, 'emp_id' => $emp_id, 'date' => $date, 'status' => 'success'];
                        } catch (Exception $e) {
                            $error_count++;
                            $imported_data[] = ['row' => $row_num, 'emp_id' => $emp_id, 'status' => 'error', 'msg' => $e->getMessage()];
                        }
                    }
                    fclose($handle);
                }
            } else {
                // For XLSX/XLS, user needs to convert to CSV or install PhpSpreadsheet
                $errors[] = 'XLSX/XLS files require PhpSpreadsheet library. Please save your Excel file as CSV and upload again.';
                $errors[] = 'In Excel: File → Save As → CSV (Comma delimited)';
            }
        }
    }
}

$page_title = 'Import Attendance';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <h1>Import Attendance from Excel/CSV</h1>
        <p><a href="attendance_report.php" class="btn btn-secondary">← Back to Report</a></p>

        <div class="card" style="background: #f0f7ff; border-left: 4px solid #1a365d; padding: 1rem; margin-bottom: 1.5rem;">
            <h3 style="margin-top: 0;">CSV File Format</h3>
            <p><a href="attendance_template.csv" download class="btn btn-secondary">Download template CSV</a></p>
            <p>Your CSV file should have this format (with header row):</p>
            <pre style="background: #fff; padding: 0.75rem; border-radius: 4px; overflow-x: auto;">Employee ID,Employee Name,Date,Punch In Time
EMP001,Ali Khan,2026-02-18,09:00
EMP002,Sara Ahmed,2026-02-18,09:15
ADMIN,Admin User,2026-02-18,08:45</pre>
            <p><strong>Note:</strong> If you have an Excel (.xlsx) file, save it as CSV first:<br>
            In Excel: <strong>File → Save As → CSV (Comma delimited)</strong></p>
        </div>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-error"><?php echo e($err); ?></div>
        <?php endforeach; ?>

        <?php if ($success_count > 0 || $error_count > 0): ?>
            <div class="alert alert-<?php echo $error_count > 0 ? 'error' : 'success'; ?>">
                <strong>Import Complete:</strong><br>
                ✅ Successfully imported: <?php echo $success_count; ?> records<br>
                <?php if ($error_count > 0): ?>
                    ❌ Errors: <?php echo $error_count; ?> records
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="import_attendance.php">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="excel_file">Select CSV File</label>
                <input type="file" id="excel_file" name="excel_file" class="form-control" accept=".csv,.xlsx,.xls" required>
                <small>Supported formats: CSV, XLSX, XLS (XLSX/XLS will prompt to convert to CSV)</small>
            </div>
            <button type="submit" class="btn btn-primary">Import Attendance</button>
        </form>

        <?php if (!empty($imported_data)): ?>
            <h3>Import Details</h3>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Employee ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imported_data as $item): ?>
                    <tr>
                        <td><?php echo $item['row']; ?></td>
                        <td><?php echo e($item['emp_id']); ?></td>
                        <td><?php echo e($item['date'] ?? '—'); ?></td>
                        <td>
                            <?php if ($item['status'] === 'success'): ?>
                                <span class="badge badge-success">Success</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Error</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($item['msg'] ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
