<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */

// Filter Status Pegawai
$filter_status = isset($_GET['status_pegawai']) && $_GET['status_pegawai'] !== '' ? $_GET['status_pegawai'] : null;

$where = ["status = 1"];
$params = [];

if ($filter_status) {
    $where[] = "status_pegawai = :status_pegawai";
    $params[':status_pegawai'] = $filter_status;
}

$where_sql = implode(" AND ", $where);
$sql = "SELECT * FROM pegawai WHERE $where_sql ORDER BY id_pegawai DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pegawai_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pegawai - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="m-0 fw-bold">Data Pegawai & Staf</h4>
            <div class="d-flex gap-2">
                <a href="pegawai_cetak.php<?= $filter_status ? '?status_pegawai=' . urlencode($filter_status) : '' ?>" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-printer me-1"></i> Cetak Laporan Pegawai
                </a>
                <?php if ($can_edit_data): ?>
                    <a href="pegawai_tambah.php" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Tambah Pegawai Baru
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data pegawai baru berhasil ditambahkan!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data pegawai berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data pegawai berhasil dihapus!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Panel Filter Status -->
        <div class="card card-modern p-3 mb-4">
            <form method="GET" action="pegawai.php" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="status_pegawai" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Status Pegawai --</option>
                        <option value="ASN" <?= $filter_status === 'ASN' ? 'selected' : '' ?>>ASN / PNS</option>
                        <option value="PPPK" <?= $filter_status === 'PPPK' ? 'selected' : '' ?>>PPPK</option>
                        <option value="Tenaga Kontrak" <?= $filter_status === 'Tenaga Kontrak' ? 'selected' : '' ?>>Tenaga Kontrak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <?php if ($filter_status): ?>
                        <a href="pegawai.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card card-modern p-4">
            <div class="table-responsive">
                <table id="tablePegawai" class="table table-modern align-middle w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>NIP / NIPPPK</th>
                            <th>Nama Pegawai</th>
                            <th>Status Pegawai</th>
                            <th>Jabatan</th>
                            <th>Gol. / Tipe PPPK</th>
                            <th>No. Reg Absen</th>
                            <?php if ($can_edit_data): ?>
                                <th class="text-center" width="12%">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($pegawai_list as $p): ?>
                        <tr>
                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($p['nip'] ?? '-') ?></td>
                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($p['nama_pegawai']) ?></span></td>
                            <td>
                                <?php if ($p['status_pegawai'] === 'ASN'): ?>
                                    <span class="badge badge-soft-primary px-2 py-1">ASN / PNS</span>
                                <?php elseif ($p['status_pegawai'] === 'PPPK'): ?>
                                    <span class="badge badge-soft-success px-2 py-1">PPPK</span>
                                <?php else: ?>
                                    <span class="badge badge-soft-secondary px-2 py-1">Tenaga Kontrak</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['jabatan'] ?? '-') ?></td>
                            <td>
                                <?php 
                                    if ($p['status_pegawai'] === 'ASN') {
                                        echo htmlspecialchars($p['golongan'] ?? '-');
                                    } elseif ($p['status_pegawai'] === 'PPPK') {
                                        echo htmlspecialchars($p['tipe_pppk'] ?? '-');
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td><span class="badge badge-soft-info px-2 py-1"><?= htmlspecialchars($p['no_reg_absen'] ?? '-') ?></span></td>
                            <?php if ($can_edit_data): ?>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="pegawai_edit.php?id=<?= $p['id_pegawai'] ?>" class="btn btn-sm btn-outline-warning btn-action" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="pegawai_hapus.php?id=<?= $p['id_pegawai'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
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
        $('#tablePegawai').DataTable();
    });
</script>
</body>
</html>