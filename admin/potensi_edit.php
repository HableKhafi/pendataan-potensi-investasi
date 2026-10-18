<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// Ambil ID dari GET (saat pertama buka) atau POST (saat submit form)
$id_potensi = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

$stmt =$pdo->prepare("SELECT * FROM potensi_investasi WHERE id_potensi = :id AND status_data = 'aktif' LIMIT 1");
$stmt->execute([':id' =>$id_potensi]);
$data =$stmt->fetch();

if (!$data) {
    header("Location: potensi.php");
    exit;
}

$sektor    =$pdo->query("SELECT * FROM sektor WHERE status = 1")->fetchAll();
$kecamatan =$pdo->query("SELECT * FROM kecamatan WHERE status = 1")->fetchAll();

$stmt_kel  =$pdo->prepare("SELECT * FROM kelurahan WHERE id_kecamatan = :id_kec AND status = 1");
$stmt_kel->execute([':id_kec' =>$data['id_kecamatan']]);
$kelurahan =$stmt_kel->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_sektor         = (int)$_POST['id_sektor'];$id_kecamatan      = (int)$_POST['id_kecamatan'];$id_kelurahan      = !empty($_POST['id_kelurahan']) ? (int)$_POST['id_kelurahan'] : null;
        $deskripsi         = trim($_POST['deskripsi']);
        $kondisi_eksisting = trim($_POST['kondisi_eksisting']);
        $peluang_investasi = trim($_POST['peluang_investasi']);
        $kendala           = trim($_POST['kendala']);
        $rekomendasi       = trim($_POST['rekomendasi']);
        $sumber_data       = trim($_POST['sumber_data']);
        $tahun_data        = (int)$_POST['tahun_data'];

        $sql = "UPDATE potensi_investasi SET 
                id_sektor = :id_sektor,
                id_kecamatan = :id_kecamatan,
                id_kelurahan = :id_kelurahan,
                deskripsi = :deskripsi,
                kondisi_eksisting = :kondisi_eksisting,
                peluang_investasi = :peluang_investasi,
                kendala = :kendala,
                rekomendasi = :rekomendasi,
                sumber_data = :sumber_data,
                tahun_data = :tahun_data
                WHERE id_potensi = :id";

        $update =$pdo->prepare($sql);$update->execute([
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
            ':id'                => $id_potensi
        ]);

        header("Location: potensi.php?msg=updated");
        exit;
    } catch (PDOException $e) {$error = "Gagal memperbarui data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Potensi Investasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background-color: #f8f9fa; }
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar { min-width: 250px; max-width: 250px; background: #212529; color: #fff; min-height: 100vh; }
        #sidebar .sidebar-header { padding: 20px; background: #1a1d20; border-bottom: 1px solid #2c3034; }
        #sidebar ul.components { padding: 15px 0; }
        #sidebar ul li a { padding: 12px 20px; font-size: 0.95rem; display: block; color: #adb5bd; text-decoration: none; transition: 0.2s; }
        #sidebar ul li a:hover, #sidebar ul li.active > a { color: #fff; background: #0d6efd; }
        #sidebar ul li a i { margin-right: 10px; }
        #content { width: 100%; padding: 25px; min-height: 100vh; }
    </style>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="card p-4 border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold">Edit Potensi Investasi (<?= htmlspecialchars($data['kode_potensi']) ?>)</h4>
                <a href="potensi.php" class="btn btn-secondary btn-sm">&larr; Batal</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="potensi_edit.php?id=<?= $id_potensi ?>" class="row g-3">
                <input type="hidden" name="id" value="<?= $id_potensi ?>">

                <div class="col-md-4">
                    <label class="form-label">Sektor *</label>
                    <select name="id_sektor" class="form-select" required>
                        <?php foreach ($sektor as$s): ?>
                            <option value="<?= $s['id_sektor'] ?>" <?= $s['id_sektor'] == $data['id_sektor'] ? 'selected' : '' ?>><?= htmlspecialchars($s['nama_sektor']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kecamatan *</label>
                    <select name="id_kecamatan" id="kecamatan" class="form-select" required>
                        <?php foreach ($kecamatan as$k): ?>
                            <option value="<?= $k['id_kecamatan'] ?>" <?= $k['id_kecamatan'] == $data['id_kecamatan'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kecamatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kelurahan</label>
                    <select name="id_kelurahan" id="kelurahan" class="form-select">
                        <option value="">-- Pilih Kelurahan --</option>
                        <?php foreach ($kelurahan as$l): ?>
                            <option value="<?= $l['id_kelurahan'] ?>" <?= $l['id_kelurahan'] == $data['id_kelurahan'] ? 'selected' : '' ?>><?= htmlspecialchars($l['nama_kelurahan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi Potensi *</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kondisi Eksisting</label>
                    <textarea name="kondisi_eksisting" class="form-control" rows="2"><?= htmlspecialchars($data['kondisi_eksisting'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Peluang Investasi</label>
                    <textarea name="peluang_investasi" class="form-control" rows="2"><?= htmlspecialchars($data['peluang_investasi'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kendala</label>
                    <textarea name="kendala" class="form-control" rows="2"><?= htmlspecialchars($data['kendala'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Rekomendasi</label>
                    <textarea name="rekomendasi" class="form-control" rows="2"><?= htmlspecialchars($data['rekomendasi'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sumber Data</label>
                    <input type="text" name="sumber_data" class="form-control" value="<?= htmlspecialchars($data['sumber_data'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tahun Data</label>
                    <input type="number" name="tahun_data" class="form-control" value="<?= htmlspecialchars($data['tahun_data'] ?? '2026') ?>">
                </div>

                <div class="col-12 mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Simpan Perubahan</button>
                    <a href="potensi.php" class="btn btn-secondary">Batal</a>
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
            kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
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
    // Konfirmasi Form Tambah / Edit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Hentikan submit langsung
        
        const isEdit = form.querySelector('[name*="edit"]') !== null || window.location.href.includes('edit');
        const titleText = isEdit ? 'Simpan Perubahan?' : 'Tambah Data Baru?';
        const confirmText = isEdit ? 'Ya, Perbarui!' : 'Ya, Simpan!';

        Swal.fire({
            title: titleText,
            text: "Pastikan data yang diinputkan sudah benar.",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#0d6efd',
            cancelColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Jalankan submit form jika ditekan Ya
            }
        });
    });
});

// Konfirmasi Tombol Hapus
document.querySelectorAll('.btn-danger').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const targetUrl = this.getAttribute('href');

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'error',
            showCancelButton: true,
            confirmColor: '#dc3545',
            cancelColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Data!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = targetUrl;
            }
        });
    });
});
</script>

</body>
</html>