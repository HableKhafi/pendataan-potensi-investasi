<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

$tahun_sektor = !empty($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$join_tahun_s = $tahun_sektor > 0 ? " AND p.tahun_data = :tahun_s " : "";

$sql = "SELECT 
            s.kode_sektor,
            s.nama_sektor,
            COUNT(DISTINCT p.id_potensi) AS total_potensi,
            COUNT(DISTINCT p.id_kecamatan) AS jumlah_kecamatan,
            COALESCE(GROUP_CONCAT(DISTINCT k.nama_kecamatan ORDER BY k.nama_kecamatan SEPARATOR ', '), '-') AS sebaran_kecamatan
        FROM sektor s
        LEFT JOIN potensi_investasi p ON s.id_sektor = p.id_sektor AND p.status_data = 'aktif' {$join_tahun_s}
        LEFT JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
        WHERE s.status = 1
        GROUP BY s.id_sektor, s.kode_sektor, s.nama_sektor
        ORDER BY total_potensi DESC, s.kode_sektor ASC";
$stmt = $pdo->prepare($sql);
if ($tahun_sektor > 0) $stmt->bindValue(':tahun_s', $tahun_sektor, PDO::PARAM_INT);
$stmt->execute();
$rekap_sektor = $stmt->fetchAll();

$bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl_cetak  = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rekapitulasi Potensi per Sektor - DPMPTSP Banjarbaru</title>
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

        <h5 class="text-center fw-bold mb-1 text-uppercase">LAPORAN REKAPITULASI POTENSI PER SEKTOR INVESTASI</h5>
        <p class="text-center mb-4">
            Tahun Data: <strong><?= $tahun_sektor > 0 ? $tahun_sektor : 'Semua Tahun' ?></strong>
        </p>

        <table class="table table-bordered align-middle table-cetak">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="10%">Kode</th>
                    <th width="25%">Nama Sektor Investasi</th>
                    <th width="13%">Jumlah Potensi</th>
                    <th width="13%">Jumlah Kecamatan</th>
                    <th>Sebaran Wilayah Kecamatan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; $tot_sek = 0;
                foreach ($rekap_sektor as $rs): 
                    $tot_sek += (int)$rs['total_potensi'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($rs['kode_sektor']) ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($rs['nama_sektor']) ?></td>
                    <td class="text-center fw-bold"><?= $rs['total_potensi'] ?></td>
                    <td class="text-center"><?= $rs['jumlah_kecamatan'] ?></td>
                    <td><?= htmlspecialchars($rs['sebaran_kecamatan']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="fw-bold" style="background-color:#f9f9f9;">
                    <td colspan="3" class="text-end">TOTAL KESELURUHAN POTENSI</td>
                    <td class="text-center"><?= $tot_sek ?></td>
                    <td colspan="2"></td>
                </tr>
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