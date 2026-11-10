<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

if (!$can_edit_data) {
    header("Location: dokumen.php?msg=unauthorized");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Ambil info file fisik
    $stmt = $pdo->prepare("SELECT file_dokumen FROM dokumen WHERE id_dokumen = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $dok = $stmt->fetch();

    if ($dok) {
        // Hapus file fisik jika ada
        $filePath = "../uploads/dokumen/" . $dok['file_dokumen'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Hapus record di database
        $delete = $pdo->prepare("DELETE FROM dokumen WHERE id_dokumen = :id");
        $delete->execute([':id' => $id]);

        header("Location: dokumen.php?msg=deleted");
        exit;
    }
}

header("Location: dokumen.php");
exit;
?>