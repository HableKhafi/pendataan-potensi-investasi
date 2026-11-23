<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

$tahun_wilayah = !empty($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$kec_wilayah   = !empty($_GET['id_kecamatan']) ? (int)$_GET['id_kecamatan'] : 0;

$join_tahun_w = $tahun_wilayah > 0 ? " AND p.tahun_data = :tahun_w " : "";
$where_kec_w  = $kec_wilayah > 0 ? " AND k.id_kecamatan = :kec_w " : "";

// Rekap Kecamatan
$sql_kec = "SELECT 
                k.id_kecamatan,
                k.nama_kecamatan,
                COUNT(DISTINCT l.id_kelurahan) AS jumlah_kelurahan,
                COUNT(DISTINCT p.id_potensi) AS total_potensi,
                COUNT(DISTINCT p.id_sektor) AS jumlah_sektor,
                COALESCE(GROUP_CONCAT(DISTINCT s.nama_sektor ORDER BY s.nama_sektor SEPARATOR ', '), '-') AS daftar_sektor
            FROM kecamatan k
            LEFT JOIN kelurahan l ON k.id_kecamatan = l.id_kecamatan AND l.status = 1
            LEFT JOIN potensi_investasi p ON k.id_kecamatan = p.id_kecamatan AND p.status_data = 'aktif' {$join_tahun_w}
            LEFT JOIN sektor s ON p.id_sektor = s.id_sektor
            WHERE k.status = 1 {$where_kec_w}
            GROUP BY k.id_kecamatan, k.nama_kecamatan
            ORDER BY k.nama_kecamatan ASC";
$stmt_k = $pdo->prepare($sql_kec);
if ($tahun_wilayah > 0) $stmt_k->bindValue(':tahun_w', $tahun_wilayah, PDO::PARAM_INT);
if ($kec_wilayah > 0)   $stmt_k->bindValue(':kec_w', $kec_wilayah, PDO::PARAM_INT);
$stmt_k->execute();
$rekap_kecamatan = $stmt_k->fetchAll();

// Rekap Kelurahan
$sql_kel = "SELECT 
                l.nama_kelurahan,
                k.nama_kecamatan,
                COUNT(DISTINCT p.id_potensi) AS total_potensi,
                COALESCE(GROUP_CONCAT(DISTINCT s.nama_sektor ORDER BY s.nama_sektor SEPARATOR ', '), '-') AS daftar_sektor
            FROM kelurahan l
            JOIN kecamatan k ON l.id_kecamatan = k.id_kecamatan
            LEFT JOIN potensi_investasi p ON l.id_kelurahan = p.id_kelurahan AND p.status_data = 'aktif' {$join_tahun_w}
            LEFT JOIN sektor s ON p.id_sektor = s.id_sektor
            WHERE l.status = 1 {$where_kec_w}
            GROUP BY l.id_kelurahan, l.nama_kelurahan, k.nama_kecamatan
            ORDER BY k.nama_kecamatan ASC, l.nama_kelurahan ASC";
$stmt_kl = $pdo->prepare($sql_kel);
if ($tahun_wilayah > 0) $stmt_kl->bindValue(':tahun_w', $tahun_wilayah, PDO::PARAM_INT);
if ($kec_wilayah > 0)   $stmt_kl->bindValue(':kec_w', $kec_wilayah, PDO::PARAM_INT);
$stmt_kl->execute();
$rekap_kelurahan = $stmt_kl->fetchAll();

$bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl_cetak  = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rekapitulasi Potensi per Wilayah - DPMPTSP Banjarbaru</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: "Times New Roman", Times, serif; color: #000; background: #fff; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .table-cetak th { background-color: #f2f2f2 !important; color: #000 !important; text-align: center; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        @media print {
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 15mm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container-fluid my-3 no-print text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-1">Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Tutup</button>
    </div>

    <div class="container-fluid p-4">
        <div class="row kop-surat align-items-center text-center">
            <div class="col-12">
                <h5 class="fw-bold mb-0">PEMERINTAH KOTA BANJARBARU</h5>
                <h4 class="fw-bold mb-1">DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU</h4>
                <small class="d-block">Jl. Husni Tamrin No.1, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalimantan Selatan 70711, Indonesia</small>
            </div>
        </div>

        <h5 class="text-center fw-bold mb-1 text-uppercase">LAPORAN REKAPITULASI POTENSI INVESTASI PER WILAYAH</h5>
        <p class="text-center mb-4">
            Tahun Data: <strong><?= $tahun_wilayah > 0 ? $tahun_wilayah : 'Semua Tahun' ?></strong>
        </p>

        <h6 class="fw-bold mb-2">A. Rekapitulasi per Kecamatan</h6>
        <table class="table table-bordered align-middle table-cetak mb-4">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Nama Kecamatan</th>
                    <th width="12%">Jumlah Kelurahan</th>
                    <th width="12%">Jumlah Potensi</th>
                    <th width="12%">Jumlah Sektor</th>
                    <th>Sektor Tersedia</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; $tot_kec = 0;
                foreach ($rekap_kecamatan as $rk): 
                    $tot_kec += (int)$rk['total_potensi'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($rk['nama_kecamatan']) ?></td>
                    <td class="text-center"><?= $rk['jumlah_kelurahan'] ?></td>
                    <td class="text-center fw-bold"><?= $rk['total_potensi'] ?></td>
                    <td class="text-center"><?= $rk['jumlah_sektor'] ?></td>
                    <td><?= htmlspecialchars($rk['daftar_sektor']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="fw-bold" style="background-color:#f9f9f9;">
                    <td colspan="3" class="text-end">TOTAL KESELURUHAN POTENSI</td>
                    <td class="text-center"><?= $tot_kec ?></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <h6 class="fw-bold mb-2">B. Rekapitulasi Sebaran per Kelurahan</h6>
        <table class="table table-bordered align-middle table-cetak">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Kecamatan</th>
                    <th width="20%">Kelurahan</th>
                    <th width="12%">Jumlah Potensi</th>
                    <th>Sektor Potensi di Kelurahan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($rekap_kelurahan as $rkel): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($rkel['nama_kecamatan']) ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($rkel['nama_kelurahan']) ?></td>
                    <td class="text-center fw-bold"><?= $rkel['total_potensi'] ?></td>
                    <td><?= htmlspecialchars($rkel['daftar_sektor']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="row mt-5" style="page-break-inside: avoid;">
            <div class="col-8"></div>
            <div class="col-4 text-center">
                <p>Banjarbaru, <?= $tgl_cetak ?></p>
                <br><br><br>
                <p class="fw-bold text-decoration-underline mb-0">(...........................................)</p>
            </div>
        </div>
    </div>

</body>
</html>