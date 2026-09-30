<?php
/**
 * One-time script to create extra admin accounts.
 *
 * Run from the Attendance-System folder:
 *   php create_admin_users.php
 */

require_once __DIR__ . '/config/init.php';

if (!extension_loaded('pdo_sqlite')) {
    die("PDO SQLite driver missing. Enable pdo_sqlite in php.ini.\n");
}

$admins = [
    [
        'username' => 'HRADMIN1',
        'password' => 'Admin@123',
        'first_name' => 'HR',
        'last_name' => 'Manager',
        'email' => 'hradmin1@example.com',
    ],
    [
        'username' => 'HRADMIN2',
        'password' => 'Admin@456',
        'first_name' => 'Shift',
        'last_name' => 'Supervisor',
        'email' => 'hradmin2@example.com',
    ],
];

foreach ($admins as $admin) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute([$admin['username']]);
    $exists = (int) $stmt->fetchColumn() > 0;

    if ($exists) {
        echo "User {$admin['username']} already exists. Skipping.\n";
        continue;
    }

    $hash = password_hash($admin['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, first_name, last_name, email, department_id, shift_id, is_staff) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
    $stmt->execute([
        $admin['username'],
        $hash,
        $admin['first_name'],
        $admin['last_name'],
        $admin['email'],
        1, // default department
        1, // default shift
    ]);

    echo "Created admin user: {$admin['username']}\n";
}

echo "Done.\n";

