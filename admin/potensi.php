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
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="m-0 fw-bold">Daftar Potensi Investasi</h4>
            <a href="potensi_tambah.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Tambah Data Baru</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data berhasil dihapus permanen!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card card-modern p-4">
            <div class="table-responsive">
                <table id="dataTable" class="table table-modern align-middle w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Sektor</th>
                            <th>Wilayah</th>
                            <th>Deskripsi Potensi</th>
                            <th>Peluang Investasi</th>
                            <th>Tahun</th>
                            <th class="text-center" width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($potensi_list as $p): ?>
                        <tr>
                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                            <td><span class="badge badge-soft-secondary px-2 py-1"><?= htmlspecialchars($p['kode_potensi']) ?></span></td>
                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($p['nama_sektor']) ?></span></td>
                            <td>
                                <span class="fw-medium">Kec. <?= htmlspecialchars($p['nama_kecamatan']) ?></span>
                                <?php if (!empty($p['nama_kelurahan'])): ?>
                                    <br><small class="text-muted"><i class="bi bi-geo-alt"></i> Kel. <?= htmlspecialchars($p['nama_kelurahan']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($p['peluang_investasi'] ?? '-')) ?></td>
                            <td><span class="badge badge-soft-info px-2 py-1"><?= htmlspecialchars($p['tahun_data'] ?? '-') ?></span></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="potensi_edit.php?id=<?= $p['id_potensi'] ?>" class="btn btn-sm btn-outline-warning btn-action" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="potensi_hapus.php?id=<?= $p['id_potensi'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
</body>
</html>