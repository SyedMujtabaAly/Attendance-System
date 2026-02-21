<?php
/**
 * One-time setup: create data folder and SQLite DB, run schema, optional admin user
 */
$base = __DIR__;
$dataDir = $base . '/data';
$dbPath = $dataDir . '/hrms.sqlite';

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "ERROR: SQLite driver missing for PDO.\n");
    fwrite(STDERR, "Fix:\n");
    fwrite(STDERR, "1) Run: php --ini\n");
    fwrite(STDERR, "2) Open the loaded php.ini and enable:\n");
    fwrite(STDERR, "   extension=pdo_sqlite\n");
    fwrite(STDERR, "   extension=sqlite3\n");
    fwrite(STDERR, "3) Restart your terminal and re-run: php setup.php\n");
    exit(1);
}

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$schema = file_get_contents($base . '/sql/schema.sql');
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec($schema);

// Create default admin user (staff) if no users exist - for testing / first run
$count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($count == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (username, password_hash, first_name, last_name, email, department_id, shift_id, is_staff) VALUES ('ADMIN', '$hash', 'Admin', 'User', 'admin@aims.local', 1, 1, 1)");
    echo "Default admin user created: Employee ID = ADMIN, Password = admin123 (is_staff = 1). Change in production!\n";
}

echo "Database created successfully at data/hrms.sqlite\n";
echo "You can now use the application. Delete or protect setup.php in production.\n";
