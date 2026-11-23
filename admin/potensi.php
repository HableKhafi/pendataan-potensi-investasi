<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

// Query Data Utama Potensi Investasi beserta Pegawai Survei
$sql = "SELECT p.*, s.nama_sektor, k.nama_kecamatan, l.nama_kelurahan, pg.nama_pegawai, pg.jabatan AS jabatan_pegawai
        FROM potensi_investasi p
        JOIN sektor s ON p.id_sektor = s.id_sektor
        JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
        LEFT JOIN kelurahan l ON p.id_kelurahan = l.id_kelurahan
        LEFT JOIN pegawai pg ON p.id_pegawai = pg.id_pegawai
        WHERE p.status_data = 'aktif'
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
            <h4 class="m-0 fw-bold">Data Potensi Investasi Daerah</h4>
            <div class="d-flex gap-2">
                <a href="potensi_cetak.php" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan Potensi
                </a>
                <?php if ($can_edit_data): ?>
                    <a href="potensi_tambah.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Potensi Baru
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-shield-exclamation me-2"></i>Anda tidak memiliki hak akses untuk mengubah data.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data potensi investasi berhasil ditambahkan!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data potensi investasi berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data potensi investasi berhasil dihapus!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card card-modern p-4">
            <div class="table-responsive">
                <table id="tablePotensi" class="table table-modern align-middle w-100">
                    <thead>
                        <tr>
                            <th width="4%">No</th>
                            <th width="12%">Kode</th>
                            <th width="14%">Sektor</th>
                            <th width="16%">Wilayah</th>
                            <th>Deskripsi Potensi</th>
                            <th width="16%">Pegawai Survei</th>
                            <th width="7%">Tahun</th>
                            <th class="text-center" width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($potensi_list as $d): ?>
                        <tr>
                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                            <td><span class="badge badge-soft-primary px-2 py-1"><?= htmlspecialchars($d['kode_potensi']) ?></span></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($d['nama_sektor']) ?></td>
                            <td>
                                <div class="fw-semibold text-dark">Kec. <?= htmlspecialchars($d['nama_kecamatan']) ?></div>
                                <?php if (!empty($d['nama_kelurahan'])): ?>
                                    <small class="text-muted">Kel. <?= htmlspecialchars($d['nama_kelurahan']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark"><?= htmlspecialchars(mb_strimwidth($d['deskripsi'], 0, 110, '...')) ?></div>
                                <small class="text-muted">Peluang: <?= htmlspecialchars(mb_strimwidth($d['peluang_investasi'] ?? '-', 0, 75, '...')) ?></small>
                            </td>
                            <td>
                                <?php if (!empty($d['nama_pegawai'])): ?>
                                    <div class="fw-semibold text-dark"><i class="bi bi-person-badge text-primary me-1"></i><?= htmlspecialchars($d['nama_pegawai']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($d['jabatan_pegawai'] ?? '') ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($d['tahun_data'] ?? '-') ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <!-- Tombol Detail -->
                                    <button type="button" class="btn btn-sm btn-outline-info btn-action btn-detail"
                                            data-kode="<?= htmlspecialchars($d['kode_potensi']) ?>"
                                            data-sektor="<?= htmlspecialchars($d['nama_sektor']) ?>"
                                            data-wilayah="Kec. <?= htmlspecialchars($d['nama_kecamatan']) ?><?= !empty($d['nama_kelurahan']) ? ' - Kel. ' . htmlspecialchars($d['nama_kelurahan']) : '' ?>"
                                            data-pegawai="<?= htmlspecialchars($d['nama_pegawai'] ?? '-') ?>"
                                            data-deskripsi="<?= htmlspecialchars($d['deskripsi']) ?>"
                                            data-kondisi="<?= htmlspecialchars($d['kondisi_eksisting'] ?? '-') ?>"
                                            data-peluang="<?= htmlspecialchars($d['peluang_investasi'] ?? '-') ?>"
                                            data-kendala="<?= htmlspecialchars($d['kendala'] ?? '-') ?>"
                                            data-rekomendasi="<?= htmlspecialchars($d['rekomendasi'] ?? '-') ?>"
                                            data-sumber="<?= htmlspecialchars($d['sumber_data'] ?? '-') ?>"
                                            data-tahun="<?= htmlspecialchars($d['tahun_data'] ?? '-') ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalDetailPotensi" title="Detail">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>

                                    <?php if ($can_edit_data): ?>
                                        <!-- Tombol Edit (Halaman Terpisah) -->
                                        <a href="potensi_edit.php?id=<?= $d['id_potensi'] ?>" class="btn btn-sm btn-outline-warning btn-action" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <!-- Tombol Hapus -->
                                        <a href="potensi_hapus.php?id=<?= $d['id_potensi'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
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

<!-- Modal Detail Potensi -->
<div class="modal fade" id="modalDetailPotensi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detail Potensi Investasi (<span id="det_kode"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered mb-0">
                    <tr><th width="28%">Sektor Investasi</th><td id="det_sektor"></td></tr>
                    <tr><th>Wilayah</th><td id="det_wilayah"></td></tr>
                    <tr><th>Pegawai Survei</th><td class="fw-bold text-primary" id="det_pegawai"></td></tr>
                    <tr><th>Deskripsi Potensi</th><td id="det_deskripsi"></td></tr>
                    <tr><th>Kondisi Eksisting</th><td id="det_kondisi"></td></tr>
                    <tr><th>Peluang Investasi</th><td id="det_peluang"></td></tr>
                    <tr><th>Kendala</th><td id="det_kendala"></td></tr>
                    <tr><th>Rekomendasi</th><td id="det_rekomendasi"></td></tr>
                    <tr><th>Sumber & Tahun Data</th><td><span id="det_sumber"></span> (<span id="det_tahun"></span>)</td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
        $('#tablePotensi').DataTable();

        // Pass data ke Modal Detail
        $('#tablePotensi').on('click', '.btn-detail', function() {
            $('#det_kode').text($(this).data('kode'));
            $('#det_sektor').text($(this).data('sektor'));
            $('#det_wilayah').text($(this).data('wilayah'));
            $('#det_pegawai').text($(this).data('pegawai'));
            $('#det_deskripsi').text($(this).data('deskripsi'));
            $('#det_kondisi').text($(this).data('kondisi'));
            $('#det_peluang').text($(this).data('peluang'));
            $('#det_kendala').text($(this).data('kendala'));
            $('#det_rekomendasi').text($(this).data('rekomendasi'));
            $('#det_sumber').text($(this).data('sumber'));
            $('#det_tahun').text($(this).data('tahun'));
        });
    });
</script>
</body>
</html>