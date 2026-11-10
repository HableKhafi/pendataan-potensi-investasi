<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// Query gabungan Kecamatan dan Kelurahan beserta total potensi per kelurahan
$sql = "SELECT k.nama_kecamatan, l.nama_kelurahan,
               (SELECT COUNT(*) FROM potensi_investasi p WHERE p.id_kelurahan = l.id_kelurahan AND p.status_data = 'aktif') as total_potensi
        FROM kecamatan k
        LEFT JOIN kelurahan l ON k.id_kecamatan = l.id_kecamatan AND l.status = 1
        WHERE k.status = 1
        ORDER BY k.nama_kecamatan ASC, l.nama_kelurahan ASC";

$stmt = $pdo->query($sql);
$wilayah_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Wilayah Administrasi - DPMPTSP Kota Banjarbaru</title>
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

        /* Aturan cetak multi-halaman */
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

        <h5 class="text-center fw-bold mb-4 text-uppercase">REKAPITULASI DATA WILAYAH ADMINISTRASI</h5>

        <!-- Tabel Cetak Gabungan Kecamatan & Kelurahan -->
        <table class="table table-bordered align-middle table-cetak">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th width="40%">Nama Kecamatan</th>
                    <th width="40%">Nama Kelurahan</th>
                    <th width="12%">Total Potensi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($wilayah_list as $w): 
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($w['nama_kecamatan']); ?></td>
                    <td><?= htmlspecialchars($w['nama_kelurahan'] ?? '-'); ?></td>
                    <td class="text-center"><?= htmlspecialchars($w['total_potensi'] ?? '0'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
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