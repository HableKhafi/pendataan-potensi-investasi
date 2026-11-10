<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// Query Rekap Sektor
$stmt_rekap_sektor = $pdo->query("
    SELECT s.kode_sektor, s.nama_sektor, COUNT(p.id_potensi) as total_potensi
    FROM sektor s
    LEFT JOIN potensi_investasi p ON s.id_sektor = p.id_sektor AND p.status_data = 'aktif'
    WHERE s.status = 1
    GROUP BY s.id_sektor, s.kode_sektor, s.nama_sektor
    ORDER BY total_potensi DESC, s.nama_sektor ASC
");
$rekap_sektor = $stmt_rekap_sektor->fetchAll();

// Query Rekap Wilayah
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Potensi Investasi - DPMPTSP Kota Banjarbaru</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            color: #000;
            background: #fff;
        }
        
        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .table-cetak th {
            background-color: #f2f2f2 !important;
            color: #000 !important;
            text-align: center;
        }

        table {
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        thead {
            display: table-header-group;
        }

        @media print {
            .no-print { 
                display: none !important; 
            }
            @page { 
                size: A4 portrait; 
                margin: 15mm; 
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Bar Aksi Layar -->
    <div class="container-fluid my-3 no-print text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-1">Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Tutup</button>
    </div>

    <div class="container-fluid p-4">
        <!-- Kop Surat -->
        <div class="row kop-surat align-items-center text-center">
            <div class="col-12">
                <h5 class="fw-bold mb-0">PEMERINTAH KOTA BANJARBARU</h5>
                <h4 class="fw-bold mb-1">DINAS PENANAMAN MODAL DAN PELAYANAN TERPADU SATU PINTU</h4>
                <small class="d-block">Jl. Husni Tamrin No.1, Kel. Komet, Kec. Banjarbaru Utara, Kota Banjarbaru, Kalimantan Selatan 70711, Indonesia</small>
            </div>
        </div>

        <h5 class="text-center fw-bold mb-4 text-uppercase">REKAPITULASI DATA POTENSI INVESTASI DAERAH</h5>

        <!-- TABLE 1: REKAP PER SEKTOR -->
        <h6 class="fw-bold mt-4 mb-2 text-uppercase">I. Rekapitulasi Potensi Investasi Per Sektor</h6>
        <table class="table table-bordered align-middle table-cetak mb-4">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th width="20%">Kode Sektor</th>
                    <th>Nama Sektor Investasi</th>
                    <th width="20%">Jumlah Potensi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                $total_sektor = 0;
                foreach ($rekap_sektor as $rs): 
                    $total_sektor += $rs['total_potensi'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($rs['kode_sektor']); ?></td>
                    <td><?= htmlspecialchars($rs['nama_sektor']); ?></td>
                    <td class="text-center fw-bold"><?= $rs['total_potensi']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">TOTAL:</td>
                    <td class="text-center"><?= $total_sektor; ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- TABLE 2: REKAP PER WILAYAH -->
        <h6 class="fw-bold mt-4 mb-2 text-uppercase">II. Rekapitulasi Potensi Investasi Per Wilayah</h6>
        <table class="table table-bordered align-middle table-cetak">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th width="40%">Kecamatan</th>
                    <th width="35%">Kelurahan</th>
                    <th width="17%">Jumlah Potensi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                $total_wilayah = 0;
                foreach ($rekap_wilayah as $rw): 
                    $total_wilayah += $rw['total_potensi'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="fw-bold">Kec. <?= htmlspecialchars($rw['nama_kecamatan']); ?></td>
                    <td><?= !empty($rw['nama_kelurahan']) ? 'Kel. ' . htmlspecialchars($rw['nama_kelurahan']) : '-'; ?></td>
                    <td class="text-center fw-bold"><?= $rw['total_potensi']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">TOTAL:</td>
                    <td class="text-center"><?= $total_wilayah; ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Area Tanda Tangan -->
        <div class="row mt-5" style="page-break-inside: avoid;">
            <div class="col-8"></div>
            <div class="col-4 text-center">
                <p>Banjarbaru, <?= date('d F Y'); ?></p>
                <br><br>
                <p class="fw-bold text-decoration-underline mb-0">(...........................................)</p>
            </div>
        </div>
    </div>

</body>
</html>