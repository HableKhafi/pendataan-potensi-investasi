<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

$sql = "SELECT p.*, s.nama_sektor, k.nama_kecamatan, l.nama_kelurahan 
        FROM potensi_investasi p
        JOIN sektor s ON p.id_sektor = s.id_sektor
        JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
        LEFT JOIN kelurahan l ON p.id_kelurahan = l.id_kelurahan
        ORDER BY p.id_potensi DESC";

$potensi_list = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Potensi Investasi - DPMPTSP Banjarbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
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
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="m-0 fw-bold">Daftar Potensi Investasi</h4>
            <a href="potensi_tambah.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Data Baru</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-danger alert-dismissible fade show">Data berhasil dihapus!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm p-3">
            <table id="dataTable" class="table table-hover align-middle w-100">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Sektor</th>
                        <th>Wilayah</th>
                        <th>Deskripsi Potensi</th>
                        <th>Peluang Investasi</th>
                        <th>Tahun</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($potensi_list as $p): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['kode_potensi']) ?></span></td>
                        <td><strong><?= htmlspecialchars($p['nama_sektor']) ?></strong></td>
                        <td>
                            Kec. <?= htmlspecialchars($p['nama_kecamatan']) ?>
                            <?php if (!empty($p['nama_kelurahan'])): ?>
                                <br><small class="text-muted">Kel. <?= htmlspecialchars($p['nama_kelurahan']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($p['peluang_investasi'] ?? '-')) ?></td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($p['tahun_data'] ?? '-') ?></span></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="potensi_edit.php?id=<?= $p['id_potensi'] ?>" class="btn btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="potensi_hapus.php?id=<?= $p['id_potensi'] ?>" class="btn btn-danger" title="Hapus"><i class="bi bi-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JS DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            }
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