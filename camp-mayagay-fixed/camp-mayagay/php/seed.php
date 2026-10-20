<?php
/**
 * seed.php - CLI only. Creates the first admin + staff accounts with real
 * password hashes. Safe to run on every start: it never overwrites an
 * existing account. Called automatically by docker/entrypoint.sh.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/db.php';

$accounts = [
    [
        'role' => 'admin', 'firstname' => 'Camp', 'lastname' => 'Administrator',
        'username' => envOr('ADMIN_USERNAME', 'admin'),
        'email'    => envOr('ADMIN_EMAIL', 'admin@campmayagay.com'),
        'password' => envOr('ADMIN_PASSWORD', 'Admin@123'),
    ],
    [
        'role' => 'staff', 'firstname' => 'Camp', 'lastname' => 'Staff',
        'username' => envOr('STAFF_USERNAME', 'staff'),
        'email'    => envOr('STAFF_EMAIL', 'staff@campmayagay.com'),
        'password' => envOr('STAFF_PASSWORD', 'Staff@123'),
    ],
];

$firstRun = ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()) === 0;

$exists = $pdo->prepare('SELECT 1 FROM users WHERE username = :u OR email = :e LIMIT 1');
$insert = $pdo->prepare(
    'INSERT INTO users (role, firstname, lastname, username, email, password, id_verification_status)
     VALUES (:role, :firstname, :lastname, :username, :email, :password, \'verified\')'
);

foreach ($accounts as $a) {
    $exists->execute(['u' => $a['username'], 'e' => $a['email']]);
    if ($exists->fetchColumn()) {
        echo "[seed] {$a['role']} '{$a['username']}' already exists - skipped\n";
        continue;
    }
    $insert->execute([
        'role' => $a['role'], 'firstname' => $a['firstname'], 'lastname' => $a['lastname'],
        'username' => $a['username'], 'email' => $a['email'],
        'password' => password_hash($a['password'], PASSWORD_DEFAULT),
    ]);
    echo "[seed] created {$a['role']} '{$a['username']}'\n";
}

// Admin/staff login uses an emailed 2FA code. If SMTP isn't configured yet,
// turn 2FA off on the very first run so the admin isn't locked out; it can
// be switched back on in Security Management once email works.
if ($firstRun && (envOr('MAIL_USERNAME') === '' || envOr('MAIL_PASSWORD') === '')) {
    $pdo->exec("UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'two_factor_enabled'");
    echo "[seed] SMTP not configured -> two-factor login disabled for now (enable it in Security Management after setting MAIL_* in .env)\n";
}
