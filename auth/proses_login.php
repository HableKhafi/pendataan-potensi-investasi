<?php
session_start();
require_once '../config/database.php';

/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        header('Location: ../login.php?error=empty');
        exit;
    }

    // Ambil data user berdasarkan email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'aktif' LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session login sementara
        $_SESSION['is_login'] = true;
        $_SESSION['id_user']  = $user['id_user'];
        $_SESSION['nama']     = $user['nama'];
        $_SESSION['role']     = $user['role'];

        // Update waktu login terakhir
        $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id_user = :id");
        $update->execute([':id' => $user['id_user']]);

        header('Location: ../admin/dashboard.php');
        exit;
    } else {
        header('Location: ../login.php?error=failed');
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
?>