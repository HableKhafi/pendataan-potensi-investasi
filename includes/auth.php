<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek status login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Global Role & Hak Akses
$user_role        = $_SESSION['role'] ?? '';
$can_manage_users = ($user_role === 'admin');
$can_edit_data    = in_array($user_role, ['admin', 'pegawai']);
$is_read_only     = in_array($user_role, ['kadis', 'kabid']);

// Helper Pembatas Akses Halaman
function check_permission($allowed_roles = []) {
    global $user_role;
    if (!in_array($user_role, $allowed_roles)) {
        header("Location: dashboard.php?error=unauthorized");
        exit;
    }
}
?>