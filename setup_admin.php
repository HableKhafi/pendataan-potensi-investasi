<?php

require_once 'config/database.php';

$nama = 'Super Admin';
$email = 'superadmin@dpmptsp.go.id';
$password = 'Admin123!';
$role = 'super_admin';
$status = 'aktif';

$cek = $pdo->prepare("
    SELECT id_user
    FROM users
    WHERE email = ?
");

$cek->execute([$email]);

if ($cek->fetch()) {
    die('Super Admin sudah tersedia.');
}

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$query = $pdo->prepare("
    INSERT INTO users
    (
        nama,
        email,
        password,
        role,
        status
    )
    VALUES (?, ?, ?, ?, ?)
");

$query->execute([
    $nama,
    $email,
    $passwordHash,
    $role,
    $status
]);

echo "Super Admin berhasil dibuat.";