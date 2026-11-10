<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

// Proteksi akses jika role tidak berhak mengedit
if (!$can_edit_data) {
    header("Location: wilayah.php?msg=unauthorized");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kecamatan   = (int)($_POST['id_kecamatan'] ?? 0);
    $nama_kelurahan = trim($_POST['nama_kelurahan'] ?? '');

    if ($id_kecamatan <= 0 || empty($nama_kelurahan)) {
        $error = "Kecamatan dan nama kelurahan wajib diisi!";
    } else {
        $stmt_check = $pdo->prepare("SELECT id_kelurahan FROM kelurahan WHERE id_kecamatan = :id_kec AND nama_kelurahan = :nama LIMIT 1");
        $stmt_check->execute([':id_kec' => $id_kecamatan, ':nama' => $nama_kelurahan]);

        if ($stmt_check->fetch()) {
            $error = "Kelurahan '$nama_kelurahan' sudah ada pada kecamatan tersebut!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO kelurahan (id_kecamatan, nama_kelurahan, status) VALUES (:id_kec, :nama, 1)");
                $stmt->execute([':id_kec' => $id_kecamatan, ':nama' => $nama_kelurahan]);
                header("Location: wilayah.php?tab=kelurahan&msg=added");
                exit;
            } catch (PDOException $e) {
                $error = "Gagal menyimpan data kelurahan.";
            }
        }
    }
}

$kecamatan_list = $pdo->query("SELECT * FROM kecamatan WHERE status = 1 ORDER BY nama_kecamatan ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kelurahan Baru - DPMPTSP Banjarbaru</title>
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
                <h4 class="mb-0 fw-bold">Tambah Kelurahan Baru</h4>
                <a href="wilayah.php?tab=kelurahan" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pilih Kecamatan *</label>
                    <select name="id_kecamatan" class="form-select" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        <?php foreach ($kecamatan_list as $k): ?>
                            <option value="<?= $k['id_kecamatan'] ?>" <?= (isset($_POST['id_kecamatan']) && $_POST['id_kecamatan'] == $k['id_kecamatan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kecamatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Kelurahan *</label>
                    <input type="text" name="nama_kelurahan" class="form-control" required placeholder="Contoh: Kemuning" value="<?= htmlspecialchars($_POST['nama_kelurahan'] ?? '') ?>">
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Kelurahan</button>
                    <a href="wilayah.php?tab=kelurahan" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>