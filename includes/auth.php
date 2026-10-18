<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah session login sudah terbentuk
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) {
    header('Location: ../login.php');
    exit;
}
?>