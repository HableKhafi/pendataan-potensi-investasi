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
$stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id_pegawai = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$peg = $stmt->fetch();

if (!$peg) {
    header("Location: pegawai.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip            = !empty($_POST['nip']) ? trim($_POST['nip']) : null;
    $nama_pegawai   = trim($_POST['nama_pegawai'] ?? '');
    $status_pegawai = trim($_POST['status_pegawai'] ?? 'ASN');
    $jabatan        = !empty($_POST['jabatan']) ? trim($_POST['jabatan']) : null;
    $golongan       = !empty($_POST['golongan']) ? trim($_POST['golongan']) : null;
    $tipe_pppk      = !empty($_POST['tipe_pppk']) ? trim($_POST['tipe_pppk']) : null;
    $no_reg_absen   = !empty($_POST['no_reg_absen']) ? trim($_POST['no_reg_absen']) : null;
    $no_hp          = !empty($_POST['no_hp']) ? trim($_POST['no_hp']) : null;

    if (empty($nama_pegawai)) {
        $error = "Nama pegawai wajib diisi!";
    } else {
        try {
            $stmt_update = $pdo->prepare("UPDATE pegawai SET nip = :nip, nama_pegawai = :nama, status_pegawai = :status_pegawai, 
                                          jabatan = :jabatan, golongan = :golongan, tipe_pppk = :tipe_pppk, no_reg_absen = :no_reg, no_hp = :no_hp 
                                          WHERE id_pegawai = :id");
            $stmt_update->execute([
                ':nip'            => $nip,
                ':nama'           => $nama_pegawai,
                ':status_pegawai' => $status_pegawai,
                ':jabatan'        => $jabatan,
                ':golongan'       => $golongan,
                ':tipe_pppk'      => $tipe_pppk,
                ':no_reg'         => $no_reg_absen,
                ':no_hp'          => $no_hp,
                ':id'             => $id
            ]);

            header("Location: pegawai.php?msg=updated");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal memperbarui data pegawai.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Pegawai - DPMPTSP Banjarbaru</title>
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
                <h4 class="mb-0 fw-bold">Edit Data Pegawai</h4>
                <a href="pegawai.php" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status Pegawai *</label>
                    <select name="status_pegawai" id="status_pegawai" class="form-select" required>
                        <option value="ASN" <?= $peg['status_pegawai'] === 'ASN' ? 'selected' : '' ?>>ASN / PNS</option>
                        <option value="PPPK" <?= $peg['status_pegawai'] === 'PPPK' ? 'selected' : '' ?>>PPPK</option>
                        <option value="Tenaga Kontrak" <?= $peg['status_pegawai'] === 'Tenaga Kontrak' ? 'selected' : '' ?>>Tenaga Kontrak</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama Pegawai & Gelar *</label>
                    <input type="text" name="nama_pegawai" class="form-control" required value="<?= htmlspecialchars($peg['nama_pegawai']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">NIP / NIPPPK <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($peg['nip'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-control" value="<?= htmlspecialchars($peg['jabatan'] ?? '') ?>">
                </div>

                <div class="col-md-4" id="box_golongan" style="display: <?= $peg['status_pegawai'] === 'ASN' ? 'block' : 'none' ?>;">
                    <label class="form-label">Golongan / Pangkat <small class="text-muted">(Khusus ASN)</small></label>
                    <input type="text" name="golongan" class="form-control" value="<?= htmlspecialchars($peg['golongan'] ?? '') ?>">
                </div>

                <div class="col-md-4" id="box_pppk" style="display: <?= $peg['status_pegawai'] === 'PPPK' ? 'block' : 'none' ?>;">
                    <label class="form-label">Tipe PPPK <small class="text-muted">(Khusus PPPK)</small></label>
                    <select name="tipe_pppk" class="form-select">
                        <option value="">-- Pilih Tipe PPPK --</option>
                        <option value="PPPK Penuh Waktu" <?= $peg['tipe_pppk'] === 'PPPK Penuh Waktu' ? 'selected' : '' ?>>PPPK Penuh Waktu</option>
                        <option value="PPPK Paruh Waktu" <?= $peg['tipe_pppk'] === 'PPPK Paruh Waktu' ? 'selected' : '' ?>>PPPK Paruh Waktu</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">No. Registrasi Absen <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="no_reg_absen" class="form-control" value="<?= htmlspecialchars($peg['no_reg_absen'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">No. HP / WhatsApp <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($peg['no_hp'] ?? '') ?>">
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i> Update Data Pegawai</button>
                    <a href="pegawai.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('status_pegawai').addEventListener('change', function() {
        let val = this.value;
        let boxGol = document.getElementById('box_golongan');
        let boxPppk = document.getElementById('box_pppk');

        if (val === 'ASN') {
            boxGol.style.display = 'block';
            boxPppk.style.display = 'none';
        } else if (val === 'PPPK') {
            boxGol.style.display = 'none';
            boxPppk.style.display = 'block';
        } else {
            boxGol.style.display = 'none';
            boxPppk.style.display = 'none';
        }
    });
</script>
</body>
</html>