<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// 1. Query Ringkasan Kartu Statistik (KPI)
$total_potensi   = $pdo->query("SELECT COUNT(*) FROM potensi_investasi WHERE status_data = 'aktif'")->fetchColumn();
$total_sektor    = $pdo->query("SELECT COUNT(*) FROM sektor WHERE status = 1")->fetchColumn();
$total_kecamatan = $pdo->query("SELECT COUNT(*) FROM kecamatan WHERE status = 1")->fetchColumn();
$total_kelurahan = $pdo->query("SELECT COUNT(*) FROM kelurahan WHERE status = 1")->fetchColumn();
$total_dokumen   = $pdo->query("SELECT COUNT(*) FROM dokumen")->fetchColumn();
$total_pegawai   = $pdo->query("SELECT COUNT(*) FROM pegawai WHERE status = 1")->fetchColumn();

// 2. Query Data Grafik Statistik (Dipindahkan dari laporan.php)
$data_sektor = $pdo->query("
    SELECT s.nama_sektor, COUNT(p.id_potensi) as total 
    FROM sektor s
    LEFT JOIN potensi_investasi p ON s.id_sektor = p.id_sektor AND p.status_data = 'aktif'
    WHERE s.status = 1
    GROUP BY s.id_sektor, s.nama_sektor
    ORDER BY total DESC
")->fetchAll();

$data_kec = $pdo->query("
    SELECT k.nama_kecamatan, COUNT(p.id_potensi) as total 
    FROM kecamatan k
    LEFT JOIN potensi_investasi p ON k.id_kecamatan = p.id_kecamatan AND p.status_data = 'aktif'
    WHERE k.status = 1
    GROUP BY k.id_kecamatan, k.nama_kecamatan
")->fetchAll();

$data_kel = $pdo->query("
    SELECT l.nama_kelurahan, COUNT(p.id_potensi) as total 
    FROM kelurahan l
    INNER JOIN potensi_investasi p ON l.id_kelurahan = p.id_kelurahan AND p.status_data = 'aktif'
    WHERE l.status = 1
    GROUP BY l.id_kelurahan, l.nama_kelurahan
    ORDER BY total DESC
")->fetchAll();

$data_tahun = $pdo->query("
    SELECT p.tahun_data, COUNT(p.id_potensi) as total 
    FROM potensi_investasi p
    WHERE p.status_data = 'aktif' AND p.tahun_data IS NOT NULL 
    GROUP BY p.tahun_data 
    ORDER BY p.tahun_data ASC
")->fetchAll();

// Konversi data ke JSON untuk Chart.js
$sektor_labels = json_encode(array_column($data_sektor, 'nama_sektor'));
$sektor_totals = json_encode(array_column($data_sektor, 'total'));

$kec_labels    = json_encode(array_column($data_kec, 'nama_kecamatan'));
$kec_totals    = json_encode(array_column($data_kec, 'total'));

$kel_labels    = json_encode(array_column($data_kel, 'nama_kelurahan'));
$kel_totals    = json_encode(array_column($data_kel, 'total'));

$tahun_labels  = json_encode(array_column($data_tahun, 'tahun_data'));
$tahun_totals  = json_encode(array_column($data_tahun, 'total'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DPMPTSP Kota Banjarbaru</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <!-- Header Selamat Datang -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="m-0 fw-bold">Dashboard Sistem Informasi Potensi Investasi</h4>
                <small class="text-muted">Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?></strong> (<?= strtoupper(htmlspecialchars($_SESSION['role'] ?? '-')) ?>)</small>
            </div>
            <a href="laporan.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Lihat Tabel Rekapitulasi
            </a>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-shield-exclamation me-2"></i>Anda tidak memiliki hak akses ke halaman tersebut.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Kartu Ringkasan Data -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card card-modern p-3 h-100 border-start border-primary border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-semibold text-uppercase">Potensi Investasi</small>
                            <h3 class="fw-bold m-0 mt-1 text-dark"><?= $total_potensi ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card card-modern p-3 h-100 border-start border-success border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-semibold text-uppercase">Sektor Investasi</small>
                            <h3 class="fw-bold m-0 mt-1 text-dark"><?= $total_sektor ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                            <i class="bi bi-grid-3x3-gap fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card card-modern p-3 h-100 border-start border-info border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-semibold text-uppercase">Wilayah Administrasi</small>
                            <h3 class="fw-bold m-0 mt-1 text-dark"><?= $total_kecamatan ?> <small class="fs-6 fw-normal text-muted">Kec / <?= $total_kelurahan ?> Kel</small></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                            <i class="bi bi-geo-alt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="card card-modern p-3 h-100 border-start border-warning border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-semibold text-uppercase">Pegawai & Dokumen</small>
                            <h3 class="fw-bold m-0 mt-1 text-dark"><?= $total_pegawai ?> <small class="fs-6 fw-normal text-muted">Staf / <?= $total_dokumen ?> Dok</small></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFIK STATISTIK ROW 1 -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Sebaran Potensi Berdasarkan Sektor</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartSektor"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt text-primary me-2"></i>Sebaran Potensi Per Kecamatan</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartKecamatan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFIK STATISTIK ROW 2 -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-pin-map text-success me-2"></i>Sebaran Potensi Per Kelurahan</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartKelurahan"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event text-info me-2"></i>Tren Potensi Per Tahun Data</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartTahun"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const colors = ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'];

    new Chart(document.getElementById('chartSektor'), {
        type: 'bar',
        data: {
            labels: <?= $sektor_labels ?>,
            datasets: [{
                label: 'Jumlah Potensi',
                data: <?= $sektor_totals ?>,
                backgroundColor: '#0d6efd',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    new Chart(document.getElementById('chartKecamatan'), {
        type: 'doughnut',
        data: {
            labels: <?= $kec_labels ?>,
            datasets: [{
                data: <?= $kec_totals ?>,
                backgroundColor: colors
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } } 
        }
    });

    new Chart(document.getElementById('chartKelurahan'), {
        type: 'bar',
        data: {
            labels: <?= $kel_labels ?>,
            datasets: [{
                label: 'Jumlah Potensi',
                data: <?= $kel_totals ?>,
                backgroundColor: '#198754',
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    new Chart(document.getElementById('chartTahun'), {
        type: 'line',
        data: {
            labels: <?= $tahun_labels ?>,
            datasets: [{
                label: 'Jumlah Data Masuk',
                data: <?= $tahun_totals ?>,
                borderColor: '#0dcaf0',
                backgroundColor: 'rgba(13, 202, 240, 0.2)',
                fill: true,
                tension: 0.3,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
</body>
</html>