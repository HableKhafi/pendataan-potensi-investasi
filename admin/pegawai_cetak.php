<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

$filter_status = isset($_GET['status_pegawai']) && $_GET['status_pegawai'] !== '' ? $_GET['status_pegawai'] : null;

$where = ["status = 1"];
$params = [];

if ($filter_status) {
    $where[] = "status_pegawai = :status_pegawai";
    $params[':status_pegawai'] = $filter_status;
}

$where_sql = implode(" AND ", $where);
$sql = "SELECT * FROM pegawai WHERE $where_sql ORDER BY id_pegawai DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pegawai_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Pegawai - DPMPTSP Kota Banjarbaru</title>
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

        <h5 class="text-center fw-bold mb-4 text-uppercase">REKAPITULASI DATA PEGAWAI DPMPTSP KOTA BANJARBARU</h5>

        <!-- Tabel Cetak Data Pegawai -->
        <table class="table table-bordered align-middle table-cetak">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="18%">NIP / NIPPPK</th>
                    <th width="25%">Nama Pegawai</th>
                    <th width="12%">Status</th>
                    <th>Jabatan</th>
                    <th width="15%">Gol. / Tipe PPPK</th>
                    <th width="10%">Reg. Absen</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($pegawai_list as $p): 
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($p['nip'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($p['nama_pegawai']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($p['status_pegawai']); ?></td>
                    <td><?= htmlspecialchars($p['jabatan'] ?? '-'); ?></td>
                    <td class="text-center">
                        <?php 
                            if ($p['status_pegawai'] === 'ASN') {
                                echo htmlspecialchars($p['golongan'] ?? '-');
                            } elseif ($p['status_pegawai'] === 'PPPK') {
                                echo htmlspecialchars($p['tipe_pppk'] ?? '-');
                            } else {
                                echo '-';
                            }
                        ?>
                    </td>
                    <td class="text-center"><?= htmlspecialchars($p['no_reg_absen'] ?? '-'); ?></td>
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