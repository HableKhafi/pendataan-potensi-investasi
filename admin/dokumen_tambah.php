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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_dokumen = trim($_POST['judul_dokumen'] ?? '');
    $kategori      = trim($_POST['kategori'] ?? 'Umum');
    $deskripsi     = trim($_POST['deskripsi'] ?? '');
    $created_by    = $_SESSION['id_user'] ?? null;

    if (empty($judul_dokumen) || empty($_FILES['file_dokumen']['name'])) {
        $error = "Judul dokumen dan berkas file wajib diisi!";
    } else {
        $file = $_FILES['file_dokumen'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp  = $file['tmp_name'];
        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'];

        if (!in_array($ext, $allowed)) {
            $error = "Format file tidak diizinkan! (Hanya PDF, Word, Excel, PowerPoint, ZIP, RAR)";
        } elseif ($fileSize > 15 * 1024 * 1024) { // Max 15MB
            $error = "Ukuran file terlalu besar! Maksimal 15 MB.";
        } else {
            // Buat direktori jika belum ada
            $targetDir = "../uploads/dokumen/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $newFileName = time() . '_' . uniqid() . '.' . $ext;
            $targetFilePath = $targetDir . $newFileName;

            if (move_uploaded_file($fileTmp, $targetFilePath)) {
                $stmt = $pdo->prepare("INSERT INTO dokumen (judul_dokumen, kategori, deskripsi, file_dokumen, tipe_file, ukuran_file, created_by) 
                                       VALUES (:judul, :kategori, :deskripsi, :file, :tipe, :ukuran, :created_by)");
                $stmt->execute([
                    ':judul'      => $judul_dokumen,
                    ':kategori'   => $kategori,
                    ':deskripsi'  => $deskripsi,
                    ':file'       => $newFileName,
                    ':tipe'       => $ext,
                    ':ukuran'     => $fileSize,
                    ':created_by' => $created_by
                ]);

                header("Location: dokumen.php?msg=added");
                exit;
            } else {
                $error = "Gagal mengunggah file ke server!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Dokumen Baru - DPMPTSP Banjarbaru</title>
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
                <h4 class="mb-0 fw-bold">Upload Dokumen Baru</h4>
                <a href="dokumen.php" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Judul Dokumen *</label>
                    <input type="text" name="judul_dokumen" class="form-control" required placeholder="Contoh: Regulasi Penanaman Modal 2026" value="<?= htmlspecialchars($_POST['judul_dokumen'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kategori Dokumen *</label>
                    <select name="kategori" class="form-select" required>
                        <option value="Regulasi">Regulasi / Peraturan</option>
                        <option value="Laporan">Laporan Kinerja</option>
                        <option value="Panduan">Panduan / SOP</option>
                        <option value="Umum" selected>Umum</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Pilih Berkas File * <small class="text-muted">(Format: PDF, Word, Excel, PPT, ZIP. Maksimal 15 MB)</small></label>
                    <input type="file" name="file_dokumen" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan / Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tambahkan rincian singkat mengenai berkas ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-cloud-upload me-1"></i> Upload Berkas</button>
                    <a href="dokumen.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>