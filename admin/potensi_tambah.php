<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

if (!$can_edit_data) {
    header("Location: potensi.php?msg=unauthorized");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_potensi      = 'POT-' . time();
    $id_kecamatan      = (int)($_POST['id_kecamatan'] ?? 0);
    $id_kelurahan      = !empty($_POST['id_kelurahan']) ? (int)$_POST['id_kelurahan'] : null;
    $id_sektor         = (int)($_POST['id_sektor'] ?? 0);
    $id_pegawai        = !empty($_POST['id_pegawai']) ? (int)$_POST['id_pegawai'] : null;
    $deskripsi         = trim($_POST['deskripsi'] ?? '');
    $kondisi_eksisting = trim($_POST['kondisi_eksisting'] ?? '');
    $peluang_investasi = trim($_POST['peluang_investasi'] ?? '');
    $kendala           = trim($_POST['kendala'] ?? '');
    $rekomendasi       = trim($_POST['rekomendasi'] ?? '');
    $sumber_data       = trim($_POST['sumber_data'] ?? '');
    $tahun_data        = !empty($_POST['tahun_data']) ? (int)$_POST['tahun_data'] : date('Y');
    $created_by        = $_SESSION['id_user'] ?? 1;

    if ($id_kecamatan <= 0 || $id_sektor <= 0 || empty($deskripsi)) {
        $error = "Kecamatan, Sektor Investasi, dan Deskripsi Potensi wajib diisi!";
    } else {
        try {
            $sql = "INSERT INTO potensi_investasi 
                    (kode_potensi, id_kecamatan, id_kelurahan, id_sektor, id_pegawai, deskripsi, kondisi_eksisting, peluang_investasi, kendala, rekomendasi, sumber_data, tahun_data, status_data, created_by) 
                    VALUES 
                    (:kode, :id_kecamatan, :id_kelurahan, :id_sektor, :id_pegawai, :deskripsi, :kondisi, :peluang, :kendala, :rekomendasi, :sumber, :tahun, 'aktif', :created_by)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':kode'         => $kode_potensi,
                ':id_kecamatan' => $id_kecamatan,
                ':id_kelurahan' => $id_kelurahan,
                ':id_sektor'    => $id_sektor,
                ':id_pegawai'   => $id_pegawai,
                ':deskripsi'    => $deskripsi,
                ':kondisi'      => $kondisi_eksisting,
                ':peluang'      => $peluang_investasi,
                ':kendala'      => $kendala,
                ':rekomendasi'  => $rekomendasi,
                ':sumber'       => $sumber_data,
                ':tahun'        => $tahun_data,
                ':created_by'   => $created_by
            ]);

            header("Location: potensi.php?msg=added");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal menyimpan data potensi investasi.";
        }
    }
}

$sektor_list    = $pdo->query("SELECT * FROM sektor WHERE status = 1 ORDER BY nama_sektor ASC")->fetchAll();
$kecamatan_list = $pdo->query("SELECT * FROM kecamatan WHERE status = 1 ORDER BY nama_kecamatan ASC")->fetchAll();
$kelurahan_list = $pdo->query("SELECT * FROM kelurahan WHERE status = 1 ORDER BY nama_kelurahan ASC")->fetchAll();
$pegawai_list   = $pdo->query("SELECT id_pegawai, nama_pegawai, status_pegawai, jabatan FROM pegawai WHERE status = 1 ORDER BY nama_pegawai ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Potensi Investasi - DPMPTSP Banjarbaru</title>
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
                <h4 class="mb-0 fw-bold">Tambah Potensi Investasi Baru</h4>
                <a href="potensi.php" class="btn btn-secondary btn-sm">← Batal</a>
            </div>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Sektor Investasi *</label>
                    <select name="id_sektor" class="form-select" required>
                        <option value="">-- Pilih Sektor --</option>
                        <?php foreach ($sektor_list as $s): ?>
                            <option value="<?= $s['id_sektor'] ?>" <?= (isset($_POST['id_sektor']) && $_POST['id_sektor'] == $s['id_sektor']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['kode_sektor'] . ' - ' . $s['nama_sektor']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Pegawai yang Melakukan Survei</label>
                    <select name="id_pegawai" class="form-select">
                        <option value="">-- Pilih Pegawai Survei --</option>
                        <?php foreach ($pegawai_list as $pg): ?>
                            <option value="<?= $pg['id_pegawai'] ?>" <?= (isset($_POST['id_pegawai']) && $_POST['id_pegawai'] == $pg['id_pegawai']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pg['nama_pegawai']) ?> (<?= htmlspecialchars($pg['status_pegawai']) ?><?= !empty($pg['jabatan']) ? ' - ' . htmlspecialchars($pg['jabatan']) : '' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kecamatan *</label>
                    <select name="id_kecamatan" id="id_kecamatan" class="form-select" required>
                        <option value="">-- Pilih Kecamatan --</option>
                        <?php foreach ($kecamatan_list as $k): ?>
                            <option value="<?= $k['id_kecamatan'] ?>" <?= (isset($_POST['id_kecamatan']) && $_POST['id_kecamatan'] == $k['id_kecamatan']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kecamatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kelurahan <small class="text-muted">(Opsional)</small></label>
                    <select name="id_kelurahan" id="id_kelurahan" class="form-select">
                        <option value="">-- Pilih Kelurahan --</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi Potensi Investasi *</label>
                    <textarea name="deskripsi" class="form-control" rows="3" required placeholder="Jelaskan potensi investasi yang ditemukan..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kondisi Eksisting</label>
                    <textarea name="kondisi_eksisting" class="form-control" rows="2" placeholder="Kondisi lapangan saat ini..."><?= htmlspecialchars($_POST['kondisi_eksisting'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Peluang Investasi</label>
                    <textarea name="peluang_investasi" class="form-control" rows="2" placeholder="Jenis usaha/investasi yang berpeluang dikembangkan..."><?= htmlspecialchars($_POST['peluang_investasi'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kendala</label>
                    <textarea name="kendala" class="form-control" rows="2" placeholder="Hambatan atau kendala di lapangan..."><?= htmlspecialchars($_POST['kendala'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Rekomendasi</label>
                    <textarea name="rekomendasi" class="form-control" rows="2" placeholder="Saran tindak lanjut atau dukungan kebijakan..."><?= htmlspecialchars($_POST['rekomendasi'] ?? '') ?></textarea>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Sumber Data</label>
                    <input type="text" name="sumber_data" class="form-control" placeholder="Contoh: Survei Lapangan / Notulen Rapat Koordinasi 2026" value="<?= htmlspecialchars($_POST['sumber_data'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tahun Data</label>
                    <input type="number" name="tahun_data" class="form-control" min="2000" max="2099" value="<?= htmlspecialchars($_POST['tahun_data'] ?? date('Y')) ?>">
                </div>

                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Potensi</button>
                    <button type="reset" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const allKelurahan = <?= json_encode($kelurahan_list) ?>;
    const selectedKelurahan = "<?= htmlspecialchars($_POST['id_kelurahan'] ?? '') ?>";

    function filterKelurahan(idKec, preselect = '') {
        let html = '<option value="">-- Pilih Kelurahan --</option>';
        allKelurahan.forEach(function(item) {
            if (String(item.id_kecamatan) === String(idKec)) {
                let sel = (String(item.id_kelurahan) === String(preselect)) ? 'selected' : '';
                html += `<option value="${item.id_kelurahan}" ${sel}>${item.nama_kelurahan}</option>`;
            }
        });
        $('#id_kelurahan').html(html);
    }

    $(document).ready(function() {
        let initKec = $('#id_kecamatan').val();
        if (initKec) {
            filterKelurahan(initKec, selectedKelurahan);
        }

        $('#id_kecamatan').on('change', function() {
            filterKelurahan($(this).val());
        });
    });
</script>
</body>
</html>