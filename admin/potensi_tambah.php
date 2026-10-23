<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */
$sektor    =$pdo->query("SELECT * FROM sektor WHERE status = 1")->fetchAll();
$kecamatan =$pdo->query("SELECT * FROM kecamatan WHERE status = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {$kode_potensi      = 'POT-' . time();
    $id_sektor         = (int)$_POST['id_sektor'];$id_kecamatan      = (int)$_POST['id_kecamatan'];$id_kelurahan      = !empty($_POST['id_kelurahan']) ? (int)$_POST['id_kelurahan'] : null;
    $deskripsi         = trim($_POST['deskripsi']);
    $kondisi_eksisting = trim($_POST['kondisi_eksisting']);
    $peluang_investasi = trim($_POST['peluang_investasi']);
    $kendala           = trim($_POST['kendala']);
    $rekomendasi       = trim($_POST['rekomendasi']);
    $sumber_data       = trim($_POST['sumber_data']);
    $tahun_data        = (int)$_POST['tahun_data'];
    $created_by        =$_SESSION['id_user'] ?? 1;

    $sql = "INSERT INTO potensi_investasi 
        (kode_potensi, id_sektor, id_kecamatan, id_kelurahan, deskripsi, kondisi_eksisting, peluang_investasi, kendala, rekomendasi, sumber_data, tahun_data, created_by) 
        VALUES 
        (:kode_potensi, :id_sektor, :id_kecamatan, :id_kelurahan, :deskripsi, :kondisi_eksisting, :peluang_investasi, :kendala, :rekomendasi, :sumber_data, :tahun_data, :created_by)";

    $stmt =$pdo->prepare($sql);$stmt->execute([
        ':kode_potensi'      => $kode_potensi,
        ':id_sektor'         => $id_sektor,
        ':id_kecamatan'      => $id_kecamatan,
        ':id_kelurahan'      => $id_kelurahan,
        ':deskripsi'         => $deskripsi,
        ':kondisi_eksisting' => $kondisi_eksisting,
        ':peluang_investasi' => $peluang_investasi,
        ':kendala'           => $kendala,
        ':rekomendasi'       => $rekomendasi,
        ':sumber_data'       => $sumber_data,
        ':tahun_data'        => $tahun_data,
        ':created_by'        => $created_by
    ]);

    header("Location: potensi.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Potensi Investasi</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="card card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-3 fw-bold">Tambah Potensi Investasi Baru</h4>
            <a href="potensi.php" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Sektor *</label>
                    <select name="id_sektor" class="form-select" required>
                        <option value="">-- Pilih Sektor --</option>
                        <?php foreach ($sektor as$s): ?>
                            <option value="<?= $s['id_sektor'] ?>"><?= htmlspecialchars($s['nama_sektor']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kecamatan *</label>
                    <select name="id_kecamatan" id="kecamatan" class="form-select" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        <?php foreach ($kecamatan as$k): ?>
                            <option value="<?= $k['id_kecamatan'] ?>"><?= htmlspecialchars($k['nama_kecamatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kelurahan</label>
                    <select name="id_kelurahan" id="kelurahan" class="form-select">
                        <option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi Potensi *</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Jelaskan gambaran potensi lokasi/wilayah..."></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kondisi Eksisting</label>
                    <textarea name="kondisi_eksisting" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Peluang Investasi</label>
                    <textarea name="peluang_investasi" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kendala</label>
                    <textarea name="kendala" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rekomendasi</label>
                    <textarea name="rekomendasi" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sumber Data</label>
                    <input type="text" name="sumber_data" class="form-control" >
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tahun Data</label>
                    <input type="number" name="tahun_data" class="form-control" value="2026">
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Data Potensi</button>
                    <button type="reset" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('kecamatan').addEventListener('change', function() {
        let idKec = this.value;
        let kelSelect = document.getElementById('kelurahan');
        if (!idKec) {
            kelSelect.innerHTML = '<option value="">-- Pilih Kecamatan Terlebih Dahulu --</option>';
            return;
        }
        fetch('get_kelurahan.php?id_kecamatan=' + idKec)
            .then(res => res.json())
            .then(data => {
                let html = '<option value="">-- Pilih Kelurahan --</option>';
                data.forEach(item => {
                    html += `<option value="${item.id_kelurahan}">${item.nama_kelurahan}</option>`;
                });
                kelSelect.innerHTML = html;
            });
    });
</script>
</body>
</html>