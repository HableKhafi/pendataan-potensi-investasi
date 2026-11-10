<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// 1. Query Tabel Rekapitulasi Per Sektor
$stmt_rekap_sektor = $pdo->query("
    SELECT s.kode_sektor, s.nama_sektor, COUNT(p.id_potensi) as total_potensi
    FROM sektor s
    LEFT JOIN potensi_investasi p ON s.id_sektor = p.id_sektor AND p.status_data = 'aktif'
    WHERE s.status = 1
    GROUP BY s.id_sektor, s.kode_sektor, s.nama_sektor
    ORDER BY total_potensi DESC, s.nama_sektor ASC
");
$rekap_sektor = $stmt_rekap_sektor->fetchAll();

// 2. Query Tabel Rekapitulasi Per Wilayah (Kecamatan & Kelurahan)
$stmt_rekap_wilayah = $pdo->query("
    SELECT k.nama_kecamatan, l.nama_kelurahan, COUNT(p.id_potensi) as total_potensi
    FROM kecamatan k
    LEFT JOIN kelurahan l ON k.id_kecamatan = l.id_kecamatan AND l.status = 1
    LEFT JOIN potensi_investasi p ON (p.id_kecamatan = k.id_kecamatan AND (p.id_kelurahan = l.id_kelurahan OR (p.id_kelurahan IS NULL AND l.id_kelurahan IS NULL))) AND p.status_data = 'aktif'
    WHERE k.status = 1
    GROUP BY k.id_kecamatan, k.nama_kecamatan, l.id_kelurahan, l.nama_kelurahan
    ORDER BY k.nama_kecamatan ASC, l.nama_kelurahan ASC
");
$rekap_wilayah = $stmt_rekap_wilayah->fetchAll();

// 3. Query Data Grafik Statistik
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

// Prepare JSON for Chart.js
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
    <title>Laporan Statistik & Rekapitulasi - DPMPTSP Banjarbaru</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="m-0 fw-bold"><i class="bi bi-bar-chart-line text-primary me-2"></i>Laporan Statistik & Rekapitulasi Potensi</h4>
            <a href="cetak_laporan_rekap.php" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Laporan Rekapitulasi
            </a>
        </div>

        <!-- TABEL REKAPITULASI 1: PER SEKTOR (DI ATAS) -->
        <div class="card card-modern p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-grid-3x3-gap me-2"></i>Laporan Rekapitulasi Potensi Investasi Per Sektor</h5>
            </div>
            <div class="table-responsive">
                <table id="tableRekapSektor" class="table table-modern align-middle w-100">
                    <thead>
                        <tr>
                            <th width="8%">No</th>
                            <th width="20%">Kode Sektor</th>
                            <th>Nama Sektor Investasi</th>
                            <th class="text-center" width="20%">Jumlah Potensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        $grand_total_sektor = 0;
                        foreach ($rekap_sektor as $rs): 
                            $grand_total_sektor += $rs['total_potensi'];
                        ?>
                        <tr>
                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                            <td><span class="badge badge-soft-primary px-2 py-1"><?= htmlspecialchars($rs['kode_sektor']) ?></span></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($rs['nama_sektor']) ?></td>
                            <td class="text-center">
                                <span class="badge badge-soft-success px-3 py-1 fs-6"><?= $rs['total_potensi'] ?> Potensi</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="3" class="text-end">TOTAL KESELURUHAN SEKTOR:</td>
                            <td class="text-center"><span class="badge bg-success px-3 py-2 fs-6"><?= $grand_total_sektor ?> Potensi</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- TABEL REKAPITULASI 2: PER WILAYAH (DI ATAS) -->
        <div class="card card-modern p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-geo-alt-fill me-2"></i>Laporan Rekapitulasi Potensi Investasi Per Wilayah</h5>
            </div>
            <div class="table-responsive">
                <table id="tableRekapWilayah" class="table table-modern align-middle w-100">
                    <thead>
                        <tr>
                            <th width="8%">No</th>
                            <th width="40%">Kecamatan</th>
                            <th width="35%">Kelurahan</th>
                            <th class="text-center" width="17%">Jumlah Potensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1; 
                        $grand_total_wilayah = 0;
                        foreach ($rekap_wilayah as $rw): 
                            $grand_total_wilayah += $rw['total_potensi'];
                        ?>
                        <tr>
                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                            <td class="fw-bold text-dark">Kec. <?= htmlspecialchars($rw['nama_kecamatan']) ?></td>
                            <td><?= !empty($rw['nama_kelurahan']) ? 'Kel. ' . htmlspecialchars($rw['nama_kelurahan']) : '-' ?></td>
                            <td class="text-center">
                                <span class="badge badge-soft-info px-3 py-1 fs-6"><?= $rw['total_potensi'] ?> Potensi</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold bg-light">
                            <td colspan="3" class="text-end">TOTAL KESELURUHAN WILAYAH:</td>
                            <td class="text-center"><span class="badge bg-info text-dark px-3 py-2 fs-6"><?= $grand_total_wilayah ?> Potensi</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- GRAFIK STATISTIK ROW 1 (DI BAWAH) -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2"></i>Sebaran Potensi Berdasarkan Sektor</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartSektor"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Sebaran Potensi Per Kecamatan</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartKecamatan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFIK STATISTIK ROW 2 (DI BAWAH) -->
        <div class="row g-4 mb-4">
            <div class="col-md-7">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-pin-map me-2"></i>Sebaran Potensi Per Kelurahan</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartKelurahan"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card card-modern p-3 h-100">
                    <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event me-2"></i>Tren Potensi Per Tahun Data</h6>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartTahun"></canvas>
                    </div>
                </div>
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
        $('#tableRekapSektor').DataTable();
        $('#tableRekapWilayah').DataTable();
    });

    const colors = ['#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#dc3545', '#6f42c1', '#fd7e14', '#20c997'];

    new Chart(document.getElementById('chartSektor'), {
        type: 'bar',
        data: {
            labels: <?php echo $sektor_labels; ?>,
            datasets: [{
                label: 'Jumlah Potensi',
                data: <?php echo $sektor_totals; ?>,
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
            labels: <?php echo $kec_labels; ?>,
            datasets: [{
                data: <?php echo $kec_totals; ?>,
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
            labels: <?php echo $kel_labels; ?>,
            datasets: [{
                label: 'Jumlah Potensi',
                data: <?php echo $kel_totals; ?>,
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
            labels: <?php echo $tahun_labels; ?>,
            datasets: [{
                label: 'Jumlah Data Masuk',
                data: <?php echo $tahun_totals; ?>,
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