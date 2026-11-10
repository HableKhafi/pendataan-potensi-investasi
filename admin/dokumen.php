<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

$dokumen_list = $pdo->query("SELECT d.*, u.nama AS uploader 
                             FROM dokumen d 
                             LEFT JOIN users u ON d.created_by = u.id_user 
                             ORDER BY d.id_dokumen DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Dokumen - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
    <h4 class="m-0 fw-bold">Data Dokumen & Berkas</h4>
    <div class="d-flex gap-2">
        <!-- Tombol Cetak Rekapitulasi Dokumen -->
        <a href="dokumen_cetak.php" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Cetak Laporan Dokumen
        </a>
        <?php if ($can_edit_data): ?>
            <a href="dokumen_tambah.php" class="btn btn-primary">
                <i class="bi bi-cloud-upload me-1"></i> Upload Dokumen Baru
            </a>
        <?php endif; ?>
    </div>
</div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show">Dokumen berhasil diunggah!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data dokumen berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Dokumen berhasil dihapus!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card card-modern p-4">
            <div class="table-responsive">
                <table id="tableDokumen" class="table table-modern align-middle w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Judul Dokumen</th>
                            <th>Kategori</th>
                            <th>Info File</th>
                            <th>Tgl Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($dokumen_list as $d): ?>
                        <tr>
                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($d['judul_dokumen']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($d['deskripsi'] ?? '-') ?></small>
                            </td>
                            <td><span class="badge badge-soft-info px-2 py-1"><?= htmlspecialchars($d['kategori']) ?></span></td>
                            <td>
                                <small class="d-block text-uppercase fw-semibold text-primary"><i class="bi bi-file-earmark-code me-1"></i><?= htmlspecialchars($d['tipe_file']) ?></small>
                                <small class="text-muted"><?= round($d['ukuran_file'] / 1024, 1) ?> KB</small>
                            </td>
                            <td>
                                <small class="text-dark d-block"><?= date('d/m/Y', strtotime($d['created_at'])) ?></small>
                                <small class="text-muted"><?= htmlspecialchars($d['uploader'] ?? 'Sistem') ?></small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="../uploads/dokumen/<?= $d['file_dokumen'] ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-action" title="Unduh / Lihat File">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <?php if ($can_edit_data): ?>
                                        <a href="dokumen_edit.php?id=<?= $d['id_dokumen'] ?>" class="btn btn-sm btn-outline-warning btn-action" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <a href="dokumen_hapus.php?id=<?= $d['id_dokumen'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    <?php endif; ?>
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
        $('#tableDokumen').DataTable();
    });
</script>
</body>
</html>