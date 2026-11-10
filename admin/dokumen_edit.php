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
$stmt = $pdo->prepare("SELECT * FROM dokumen WHERE id_dokumen = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$dok = $stmt->fetch();

if (!$dok) {
    header("Location: dokumen.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_dokumen = trim($_POST['judul_dokumen'] ?? '');
    $kategori      = trim($_POST['kategori'] ?? 'Umum');
    $deskripsi     = trim($_POST['deskripsi'] ?? '');

    if (empty($judul_dokumen)) {
        $error = "Judul dokumen wajib diisi!";
    } else {
        $fileNameDb = $dok['file_dokumen'];
        $tipeDb     = $dok['tipe_file'];
        $ukuranDb   = $dok['ukuran_file'];

        // Jika user mengunggah berkas pengganti
        if (!empty($_FILES['file_dokumen']['name'])) {
            $file = $_FILES['file_dokumen'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

            if (!in_array($ext, $allowed)) {
                $error = "Format file tidak diizinkan!";
            } elseif ($file['size'] > 15 * 1024 * 1024) {
                $error = "Ukuran file terlalu besar! Maksimal 15 MB.";
            } else {
                $targetDir = "../uploads/dokumen/";
                $newFileName = time() . '_' . uniqid() . '.' . $ext;
                
                if (move_uploaded_file($file['tmp_name'], $targetDir . $newFileName)) {
                    // Hapus file lama fisik jika ada
                    if (file_exists($targetDir . $dok['file_dokumen'])) {
                        @unlink($targetDir . $dok['file_dokumen']);
                    }
                    $fileNameDb = $newFileName;
                    $tipeDb     = $ext;
                    $ukuranDb   = $file['size'];
                }
            }
        }

        if (empty($error)) {
            $update = $pdo->prepare("UPDATE dokumen SET judul_dokumen = :judul, kategori = :kategori, deskripsi = :deskripsi, 
                                     file_dokumen = :file, tipe_file = :tipe, ukuran_file = :ukuran WHERE id_dokumen = :id");
            $update->execute([
                ':judul'     => $judul_dokumen,
                ':kategori'  => $kategori,
                ':deskripsi' => $deskripsi,
                ':file'      => $fileNameDb,
                ':tipe'      => $tipeDb,
                ':ukuran'    => $ukuranDb,
                ':id'        => $id
            ]);

            header("Location: dokumen.php?msg=updated");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Dokumen - DPMPTSP Banjarbaru</title>
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
                <h4 class="mb-0 fw-bold">Edit Data Dokumen</h4>
                <a href="dokumen.php" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Judul Dokumen *</label>
                    <input type="text" name="judul_dokumen" class="form-control" required value="<?= htmlspecialchars($dok['judul_dokumen']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kategori Dokumen *</label>
                    <select name="kategori" class="form-select" required>
                        <option value="Regulasi" <?= $dok['kategori'] === 'Regulasi' ? 'selected' : '' ?>>Regulasi / Peraturan</option>
                        <option value="Laporan" <?= $dok['kategori'] === 'Laporan' ? 'selected' : '' ?>>Laporan Kinerja</option>
                        <option value="Panduan" <?= $dok['kategori'] === 'Panduan' ? 'selected' : '' ?>>Panduan / SOP</option>
                        <option value="Umum" <?= $dok['kategori'] === 'Umum' ? 'selected' : '' ?>>Umum</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Ganti Berkas File <small class="text-muted">(Kosongkan jika tidak ingin mengganti file lama)</small></label>
                    <input type="file" name="file_dokumen" class="form-control">
                    <small class="text-muted mt-1 d-block">File saat ini: <a href="../uploads/dokumen/<?= $dok['file_dokumen'] ?>" target="_blank"><?= htmlspecialchars($dok['file_dokumen']) ?></a></small>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan / Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($dok['deskripsi'] ?? '') ?></textarea>
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i> Update Dokumen</button>
                    <a href="dokumen.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>