<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// 1. Ambil Parameter Filter dari GET
$filter_sektor    = isset($_GET['id_sektor']) && $_GET['id_sektor'] !== '' ? (int)$_GET['id_sektor'] : null;
$filter_kecamatan = isset($_GET['id_kecamatan']) && $_GET['id_kecamatan'] !== '' ? (int)$_GET['id_kecamatan'] : null;
$filter_tahun     = isset($_GET['tahun_data']) && $_GET['tahun_data'] !== '' ? (int)$_GET['tahun_data'] : null;

// 2. Susun Dynamic Query Condition
$where = ["p.status_data = 'aktif'"];
$params = [];

if ($filter_sektor) {
    $where[] = "p.id_sektor = :id_sektor";
    $params[':id_sektor'] = $filter_sektor;
}
if ($filter_kecamatan) {
    $where[] = "p.id_kecamatan = :id_kecamatan";
    $params[':id_kecamatan'] = $filter_kecamatan;
}
if ($filter_tahun) {
    $where[] = "p.tahun_data = :tahun_data";
    $params[':tahun_data'] = $filter_tahun;
}

$where_sql = implode(" AND ", $where);

// 3. Query Option Filter Dropdown
$opt_sektor    = $pdo->query("SELECT * FROM sektor WHERE status = 1 ORDER BY nama_sektor ASC")->fetchAll();
$opt_kecamatan = $pdo->query("SELECT * FROM kecamatan WHERE status = 1 ORDER BY nama_kecamatan ASC")->fetchAll();
$opt_tahun     = $pdo->query("SELECT DISTINCT tahun_data FROM potensi_investasi WHERE status_data = 'aktif' AND tahun_data IS NOT NULL ORDER BY tahun_data DESC")->fetchAll();

// 4. Query Data Tabel Laporan (Filtered)
$sql_tabel = "SELECT p.*, s.nama_sektor, k.nama_kecamatan, l.nama_kelurahan 
              FROM potensi_investasi p
              JOIN sektor s ON p.id_sektor = s.id_sektor
              JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
              LEFT JOIN kelurahan l ON p.id_kelurahan = l.id_kelurahan
              WHERE $where_sql
              ORDER BY p.id_potensi DESC";
$stmt_tabel = $pdo->prepare($sql_tabel);
$stmt_tabel->execute($params);
$laporan_list = $stmt_tabel->fetchAll();

// 5. Query Data Grafik Statistik (Filtered)
$stmt_sektor = $pdo->prepare("
    SELECT s.nama_sektor, COUNT(p.id_potensi) as total 
    FROM sektor s
    LEFT JOIN potensi_investasi p ON s.id_sektor = p.id_sektor AND $where_sql
    WHERE s.status = 1
    GROUP BY s.id_sektor, s.nama_sektor
    ORDER BY total DESC
");
$stmt_sektor->execute($params);
$data_sektor = $stmt_sektor->fetchAll();

$stmt_kec = $pdo->prepare("
    SELECT k.nama_kecamatan, COUNT(p.id_potensi) as total 
    FROM kecamatan k
    LEFT JOIN potensi_investasi p ON k.id_kecamatan = p.id_kecamatan AND $where_sql
    WHERE k.status = 1
    GROUP BY k.id_kecamatan, k.nama_kecamatan
");
$stmt_kec->execute($params);
$data_kec = $stmt_kec->fetchAll();

$stmt_kel = $pdo->prepare("
    SELECT l.nama_kelurahan, COUNT(p.id_potensi) as total 
    FROM kelurahan l
    INNER JOIN potensi_investasi p ON l.id_kelurahan = p.id_kelurahan AND $where_sql
    WHERE l.status = 1
    GROUP BY l.id_kelurahan, l.nama_kelurahan
    ORDER BY total DESC
");
$stmt_kel->execute($params);
$data_kel = $stmt_kel->fetchAll();

$stmt_tahun = $pdo->prepare("
    SELECT p.tahun_data, COUNT(p.id_potensi) as total 
    FROM potensi_investasi p
    WHERE $where_sql AND p.tahun_data IS NOT NULL 
    GROUP BY p.tahun_data 
    ORDER BY p.tahun_data ASC
");
$stmt_tahun->execute($params);
$data_tahun = $stmt_tahun->fetchAll();

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
    <title>Laporan & Grafik Potensi Investasi</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="m-0 fw-bold"><i class="bi bi-bar-chart-line text-primary me-2"></i>Laporan Statistik Potensi Investasi</h4>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i> Cetak Laporan</button>
        </div>

        <!-- Panel Filter Kategori -->
        <div class="card card-modern p-4 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2"></i>Filter Data Laporan</h6>
            <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Kategori Sektor</label>
                    <select name="id_sektor" class="form-select">
                        <option value="">-- Semua Sektor --</option>
                        <?php foreach ($opt_sektor as $s): ?>
                            <option value="<?= $s['id_sektor'] ?>" <?= $filter_sektor == $s['id_sektor'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nama_sektor']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Kategori Wilayah (Kecamatan)</label>
                    <select name="id_kecamatan" class="form-select">
                        <option value="">-- Semua Kecamatan --</option>
                        <?php foreach ($opt_kecamatan as $k): ?>
                            <option value="<?= $k['id_kecamatan'] ?>" <?= $filter_kecamatan == $k['id_kecamatan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kecamatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tahun Data</label>
                    <select name="tahun_data" class="form-select">
                        <option value="">-- Semua Tahun --</option>
                        <?php foreach ($opt_tahun as $t): ?>
                            <option value="<?= $t['tahun_data'] ?>" <?= $filter_tahun == $t['tahun_data'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['tahun_data']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
                    <a href="laporan.php" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>

        <!-- Grafik Statistik -->
      <!-- Grafik Statistik Row 1 -->
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

<!-- Grafik Statistik Row 2 -->
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

        <!-- Tabel Rekapitulasi Data Terfilter -->
        <div class="card card-modern p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-table me-2"></i>Tabel Rekapitulasi Data Potensi Investasi</h5>
                <span class="badge badge-soft-primary px-3 py-2 fs-6">Total Data: <?= count($laporan_list) ?> Record</span>
            </div>
            <div class="table-responsive">
                <table id="tableLaporan" class="table table-modern align-middle w-100">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Sektor</th>
                            <th>Wilayah</th>
                            <th>Deskripsi Potensi</th>
                            <th>Peluang Investasi</th>
                            <th>Tahun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($laporan_list as $row): ?>
                        <tr>
                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                            <td><span class="badge badge-soft-secondary px-2 py-1"><?= htmlspecialchars($row['kode_potensi']) ?></span></td>
                            <td><span class="fw-bold text-dark"><?= htmlspecialchars($row['nama_sektor']) ?></span></td>
                            <td>
                                <span class="fw-medium">Kec. <?= htmlspecialchars($row['nama_kecamatan']) ?></span>
                                <?php if (!empty($row['nama_kelurahan'])): ?>
                                    <br><small class="text-muted"><i class="bi bi-geo-alt"></i> Kel. <?= htmlspecialchars($row['nama_kelurahan']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['peluang_investasi'] ?? '-')) ?></td>
                            <td><span class="badge badge-soft-info px-2 py-1"><?= htmlspecialchars($row['tahun_data'] ?? '-') ?></span></td>
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
        $('#tableLaporan').DataTable();
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
            maintainAspectRatio: false, // <-- Menghilangkan area kosong melayang
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
            maintainAspectRatio: false, // <-- Menghilangkan area kosong melayang
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
            maintainAspectRatio: false, // <-- Menghilangkan area kosong melayang
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
            maintainAspectRatio: false, // <-- Menghilangkan area kosong melayang
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
</body>
</html>