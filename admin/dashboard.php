<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */
$total_sektor    = $pdo->query("SELECT COUNT(*) FROM sektor WHERE status = 1")->fetchColumn();
$total_potensi   = $pdo->query("SELECT COUNT(*) FROM potensi_investasi WHERE status_data = 'aktif'")->fetchColumn();
$total_kecamatan = $pdo->query("SELECT COUNT(*) FROM kecamatan WHERE status = 1")->fetchColumn();
$total_kelurahan = $pdo->query("SELECT COUNT(*) FROM kelurahan WHERE status = 1")->fetchColumn();

$user_role = $_SESSION['role'] ?? '';

// Format Label Nama Role
$role_label = [
    'admin'   => 'Admin',
    'pegawai' => 'Pegawai',
    'kadis'   => 'Kepala Dinas',
    'kabid'   => 'Kepala Bidang'
];
$display_role = $role_label[$user_role] ?? strtoupper($user_role);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="m-0 fw-bold">Dashboard Sistem</h4>
            <span class="badge badge-soft-primary p-2 fs-6">
                <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?> (<?= htmlspecialchars($display_role) ?>)
            </span>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-shield-exclamation me-2"></i>Anda tidak memiliki hak akses untuk membuka halaman tersebut.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white p-3 shadow-sm border-0 rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Sektor</h6>
                            <h2 class="m-0 fw-bold"><?= $total_sektor ?></h2>
                        </div>
                        <i class="bi bi-grid-3x3-gap fs-1 text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white p-3 shadow-sm border-0 rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Total Potensi</h6>
                            <h2 class="m-0 fw-bold"><?= $total_potensi ?></h2>
                        </div>
                        <i class="bi bi-file-earmark-text fs-1 text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white p-3 shadow-sm border-0 rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Kecamatan</h6>
                            <h2 class="m-0 fw-bold"><?= $total_kecamatan ?></h2>
                        </div>
                        <i class="bi bi-geo-alt fs-1 text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark p-3 shadow-sm border-0 rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-dark-50">Kelurahan</h6>
                            <h2 class="m-0 fw-bold"><?= $total_kelurahan ?></h2>
                        </div>
                        <i class="bi bi-pin-map fs-1 text-dark-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-modern p-4">
            <h5 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>!</h5>
            <p class="text-muted m-0">
                <?php if (in_array($user_role, ['kadis', 'kabid'])): ?>
                    Anda masuk sebagai <strong><?= htmlspecialchars($display_role) ?></strong> dengan hak akses <em>Lihat Data (Read-Only)</em>. Silakan gunakan menu di sebelah kiri untuk meninjau data sektor, wilayah, potensi investasi, serta laporan grafik statistik.
                <?php elseif ($user_role === 'pegawai'): ?>
                    Anda masuk sebagai <strong>Pegawai</strong>. Anda memiliki hak akses penuh untuk mengolah data sektor, wilayah, serta menginput dan memperbarui data potensi investasi daerah.
                <?php else: ?>
                    Anda masuk sebagai <strong>Admin System</strong> dengan hak akses penuh, termasuk pengelolaan akun pengguna sistem.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>