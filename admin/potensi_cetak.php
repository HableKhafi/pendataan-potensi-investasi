<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// Query mengambil SELURUH data tanpa limit / pagination DataTables
$sql = "SELECT p.*, s.nama_sektor, k.nama_kecamatan, l.nama_kelurahan 
        FROM potensi_investasi p
        JOIN sektor s ON p.id_sektor = s.id_sektor
        JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
        LEFT JOIN kelurahan l ON p.id_kelurahan = l.id_kelurahan
        ORDER BY p.id_potensi DESC";

$query = $pdo->query($sql);
$potensi_list = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Potensi Investasi - DPMPTSP Kota Banjarbaru</title>
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

        /* Aturan agar tabel yang panjangnya multi-halaman tidak berantakan saat diprint */
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
                size: A4 landscape; 
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

        <!-- Tabel Cetak Seluruh Data -->
        <table class="table table-bordered align-middle table-cetak">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="12%">Kode</th>
                    <th width="15%">Sektor</th>
                    <th width="20%">Wilayah</th>
                    <th>Deskripsi Potensi</th>
                    <th>Peluang Investasi</th>
                    <th width="8%">Tahun</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($potensi_list as $d): 
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($d['kode_potensi']); ?></td>
                    <td><?= htmlspecialchars($d['nama_sektor']); ?></td>
                    <td>
                        Kec. <?= htmlspecialchars($d['nama_kecamatan']); ?>
                        <?= !empty($d['nama_kelurahan']) ? '<br><small class="text-muted">Kel. ' . htmlspecialchars($d['nama_kelurahan']) . '</small>' : ''; ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($d['deskripsi'])); ?></td>
                    <td><?= nl2br(htmlspecialchars($d['peluang_investasi'] ?? '-')); ?></td>
                    <td class="text-center"><?= htmlspecialchars($d['tahun_data'] ?? '-'); ?></td>
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