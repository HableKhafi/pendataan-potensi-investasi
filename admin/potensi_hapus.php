<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

$id_potensi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_potensi > 0) {
    try {
        // Hard Delete: Hapus data secara permanen dari database
        $stmt = $pdo->prepare("DELETE FROM potensi_investasi WHERE id_potensi = :id");
        $stmt->execute([':id' => $id_potensi]);

        header("Location: potensi.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        header("Location: potensi.php?msg=error");
        exit;
    }
} else {
    header("Location: potensi.php");
    exit;
}
?>