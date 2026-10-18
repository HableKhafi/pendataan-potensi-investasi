<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// 1. Data Potensi Berdasarkan Sektor
$stmt_sektor = $pdo->query("
    SELECT s.nama_sektor, COUNT(p.id_potensi) as total 
    FROM sektor s
    LEFT JOIN potensi_investasi p ON s.id_sektor = p.id_sektor AND p.status_data = 'aktif'
    WHERE s.status = 1
    GROUP BY s.id_sektor, s.nama_sektor
    ORDER BY total DESC
");
$data_sektor = $stmt_sektor->fetchAll();

// 2. Data Potensi Berdasarkan Kecamatan
$stmt_kec = $pdo->query("
    SELECT k.nama_kecamatan, COUNT(p.id_potensi) as total 
    FROM kecamatan k
    LEFT JOIN potensi_investasi p ON k.id_kecamatan = p.id_kecamatan AND p.status_data = 'aktif'
    WHERE k.status = 1
    GROUP BY k.id_kecamatan, k.nama_kecamatan
");
$data_kec = $stmt_kec->fetchAll();

// 3. Data Potensi Berdasarkan Kelurahan
$stmt_kel = $pdo->query("
    SELECT l.nama_kelurahan, COUNT(p.id_potensi) as total 
    FROM kelurahan l
    INNER JOIN potensi_investasi p ON l.id_kelurahan = p.id_kelurahan AND p.status_data = 'aktif'
    WHERE l.status = 1
    GROUP BY l.id_kelurahan, l.nama_kelurahan
    ORDER BY total DESC
");
$data_kel = $stmt_kel->fetchAll();

// 4. Data Potensi Berdasarkan Tahun
$stmt_tahun = $pdo->query("
    SELECT tahun_data, COUNT(id_potensi) as total 
    FROM potensi_investasi 
    WHERE status_data = 'aktif' AND tahun_data IS NOT NULL 
    GROUP BY tahun_data 
    ORDER BY tahun_data ASC
");
$data_tahun = $stmt_tahun->fetchAll();

// Format data ke Array JSON untuk Chart.js
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
    <title>Laporan & Grafik Potensi Investasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h4 class="m-0 fw-bold"><i class="bi bi-bar-chart-line text-primary me-2"></i>Laporan Statistik Potensi Investasi</h4>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i> Cetak Laporan</button>
        </div>

        <!-- Row 1: Grafik Sektor & Kecamatan -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2"></i>Sebaran Potensi Berdasarkan Sektor</h5>
                    <canvas id="chartSektor" height="180"></canvas>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Sebaran Potensi Per Kecamatan</h5>
                    <canvas id="chartKecamatan" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: Grafik Kelurahan & Perkembangan Tahun -->
        <div class="row g-4">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-pin-map me-2"></i>Sebaran Potensi Per Kelurahan</h5>
                    <canvas id="chartKelurahan" height="180"></canvas>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-calendar-event me-2"></i>Tren Potensi Per Tahun Data</h5>
                    <canvas id="chartTahun" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Palette Warna
    const colors = ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'];

    // 1. Chart Sektor (Bar Chart)
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
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // 2. Chart Kecamatan (Doughnut Chart)
    new Chart(document.getElementById('chartKecamatan'), {
        type: 'doughnut',
        data: {
            labels: <?= $kec_labels ?>,
            datasets: [{
                data: <?= $kec_totals ?>,
                backgroundColor: colors
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // 3. Chart Kelurahan (Horizontal Bar)
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
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // 4. Chart Tahun (Line Chart)
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
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>