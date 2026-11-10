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
            $stmt = $pdo->prepare("INSERT INTO pegawai (nip, nama_pegawai, status_pegawai, jabatan, golongan, tipe_pppk, no_reg_absen, no_hp, status) 
                                   VALUES (:nip, :nama, :status_pegawai, :jabatan, :golongan, :tipe_pppk, :no_reg, :no_hp, 1)");
            $stmt->execute([
                ':nip'            => $nip,
                ':nama'           => $nama_pegawai,
                ':status_pegawai' => $status_pegawai,
                ':jabatan'        => $jabatan,
                ':golongan'       => $golongan,
                ':tipe_pppk'      => $tipe_pppk,
                ':no_reg'         => $no_reg_absen,
                ':no_hp'          => $no_hp
            ]);

            header("Location: pegawai.php?msg=added");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal menyimpan data pegawai.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pegawai Baru - DPMPTSP Banjarbaru</title>
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
                <h4 class="mb-0 fw-bold">Tambah Pegawai Baru</h4>
                <a href="pegawai.php" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status Pegawai *</label>
                    <select name="status_pegawai" id="status_pegawai" class="form-select" required>
                        <option value="ASN">ASN / PNS</option>
                        <option value="PPPK">PPPK</option>
                        <option value="Tenaga Kontrak">Tenaga Kontrak</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama Pegawai & Gelar *</label>
                    <input type="text" name="nama_pegawai" class="form-control" required placeholder="Contoh: Drs. Bambang Supriyanto, MT" value="<?= htmlspecialchars($_POST['nama_pegawai'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">NIP / NIPPPK <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="nip" class="form-control" placeholder="Contoh: 19710410 199003 1 003" value="<?= htmlspecialchars($_POST['nip'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Kepala Dinas / Penata Layanan Operasional" value="<?= htmlspecialchars($_POST['jabatan'] ?? '') ?>">
                </div>

                <div class="col-md-4" id="box_golongan">
                    <label class="form-label">Golongan / Pangkat <small class="text-muted">(Khusus ASN)</small></label>
                    <input type="text" name="golongan" class="form-control" placeholder="Contoh: IV/c, III/d" value="<?= htmlspecialchars($_POST['golongan'] ?? '') ?>">
                </div>

                <div class="col-md-4" id="box_pppk" style="display: none;">
                    <label class="form-label">Tipe PPPK <small class="text-muted">(Khusus PPPK)</small></label>
                    <select name="tipe_pppk" class="form-select">
                        <option value="">-- Pilih Tipe PPPK --</option>
                        <option value="PPPK Penuh Waktu">PPPK Penuh Waktu</option>
                        <option value="PPPK Paruh Waktu">PPPK Paruh Waktu</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">No. Registrasi Absen <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="no_reg_absen" class="form-control" placeholder="Contoh: 201401" value="<?= htmlspecialchars($_POST['no_reg_absen'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">No. HP / WhatsApp <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="no_hp" class="form-control" placeholder="081234567890" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Data Pegawai</button>
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