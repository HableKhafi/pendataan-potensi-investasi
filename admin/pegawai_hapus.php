<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

if (!$can_edit_data) {
    header("Location: pegawai.php?msg=unauthorized");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM pegawai WHERE id_pegawai = :id");
        $stmt->execute([':id' => $id]);
        header("Location: pegawai.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        header("Location: pegawai.php?msg=error");
        exit;
    }
} else {
    header("Location: pegawai.php");
    exit;
}
?>