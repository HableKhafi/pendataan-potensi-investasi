<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

// Proteksi akses jika role tidak berhak mengedit
if (!$can_edit_data) {
    header("Location: sektor.php?msg=unauthorized");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_sektor = trim($_POST['kode_sektor'] ?? '');
    $nama_sektor = trim($_POST['nama_sektor'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');

    if (empty($kode_sektor) || empty($nama_sektor)) {
        $error = "Kode Sektor dan Nama Sektor wajib diisi!";
    } else {
        $stmt_check = $pdo->prepare("SELECT id_sektor FROM sektor WHERE kode_sektor = :kode LIMIT 1");
        $stmt_check->execute([':kode' => $kode_sektor]);
        
        if ($stmt_check->fetch()) {
            $error = "Kode sektor '$kode_sektor' sudah digunakan!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO sektor (kode_sektor, nama_sektor, deskripsi, status) VALUES (:kode, :nama, :deskripsi, 1)");
                $stmt->execute([':kode' => $kode_sektor, ':nama' => $nama_sektor, ':deskripsi' => $deskripsi]);
                header("Location: sektor.php?msg=added");
                exit;
            } catch (PDOException $e) {
                $error = "Gagal menyimpan data sektor.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Sektor Baru - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 fw-bold">Tambah Sektor Baru</h4>
                <a href="sektor.php" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kode Sektor *</label>
                    <input type="text" name="kode_sektor" class="form-control" required placeholder="Contoh: S11" value="<?= htmlspecialchars($_POST['kode_sektor'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Sektor *</label>
                    <input type="text" name="nama_sektor" class="form-control" required placeholder="Contoh: Energi Terbarukan" value="<?= htmlspecialchars($_POST['nama_sektor'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi Sektor</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan gambaran umum sektor ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Sektor</button>
                    <a href="sektor.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>