<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

$kec_tahun = !empty($_GET['id_kecamatan']) ? (int)$_GET['id_kecamatan'] : 0;
$sek_tahun = !empty($_GET['id_sektor']) ? (int)$_GET['id_sektor'] : 0;

$conds_t  = ["p.status_data = 'aktif'"];
$params_t = [];

if ($kec_tahun > 0) {
    $conds_t[] = "p.id_kecamatan = :kec_t";
    $params_t[':kec_t'] = $kec_tahun;
}
if ($sek_tahun > 0) {
    $conds_t[] = "p.id_sektor = :sek_t";
    $params_t[':sek_t'] = $sek_tahun;
}
$where_t = implode(" AND ", $conds_t);

// A. Ringkasan per Tahun
$sql_rekap = "SELECT 
                p.tahun_data,
                COUNT(DISTINCT p.id_potensi) AS total_potensi,
                COUNT(DISTINCT p.id_kecamatan) AS jumlah_kecamatan,
                COUNT(DISTINCT p.id_sektor) AS jumlah_sektor,
                COALESCE(GROUP_CONCAT(DISTINCT s.nama_sektor ORDER BY s.nama_sektor SEPARATOR ', '), '-') AS daftar_sektor
              FROM potensi_investasi p
              JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
              JOIN sektor s ON p.id_sektor = s.id_sektor
              WHERE {$where_t}
              GROUP BY p.tahun_data
              ORDER BY p.tahun_data DESC";
$stmt_rk = $pdo->prepare($sql_rekap);
$stmt_rk->execute($params_t);
$rekap_tahun = $stmt_rk->fetchAll();

// B. Rincian Potensi per Tahun
$sql_rincian = "SELECT 
                    p.*,
                    s.nama_sektor,
                    k.nama_kecamatan,
                    l.nama_kelurahan,
                    pg.nama_pegawai
                FROM potensi_investasi p
                JOIN sektor s ON p.id_sektor = s.id_sektor
                JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
                LEFT JOIN kelurahan l ON p.id_kelurahan = l.id_kelurahan
                LEFT JOIN pegawai pg ON p.id_pegawai = pg.id_pegawai
                WHERE {$where_t}
                ORDER BY p.tahun_data DESC, k.nama_kecamatan ASC, s.nama_sektor ASC, p.id_potensi DESC";
$stmt_rc = $pdo->prepare($sql_rincian);
$stmt_rc->execute($params_t);
$rincian_tahun = $stmt_rc->fetchAll();

$bulan_indo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl_cetak  = date('d') . ' ' . $bulan_indo[(int)date('m')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rekapitulasi Potensi per Tahun - DPMPTSP Banjarbaru</title>
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

        <h5 class="text-center fw-bold mb-4 text-uppercase">LAPORAN REKAPITULASI POTENSI INVESTASI PER TAHUN</h5>

        <h6 class="fw-bold mb-2">A. Ringkasan Jumlah Potensi per Tahun</h6>
        <table class="table table-bordered align-middle table-cetak mb-4">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Tahun Data</th>
                    <th width="13%">Jumlah Potensi</th>
                    <th width="13%">Jumlah Kecamatan</th>
                    <th width="12%">Jumlah Sektor</th>
                    <th>Sektor Investasi Terdata</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; $tot_thn = 0;
                foreach ($rekap_tahun as $rt): 
                    $tot_thn += (int)$rt['total_potensi'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($rt['tahun_data'] ?? 'Tidak Ditentukan') ?></td>
                    <td class="text-center fw-bold"><?= $rt['total_potensi'] ?></td>
                    <td class="text-center"><?= $rt['jumlah_kecamatan'] ?></td>
                    <td class="text-center"><?= $rt['jumlah_sektor'] ?></td>
                    <td><?= htmlspecialchars($rt['daftar_sektor']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="fw-bold" style="background-color:#f9f9f9;">
                    <td colspan="2" class="text-end">TOTAL KESELURUHAN POTENSI</td>
                    <td class="text-center"><?= $tot_thn ?></td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>

        <h6 class="fw-bold mb-2">B. Rincian Data Potensi Berdasarkan Tahun</h6>
        <table class="table table-bordered align-middle table-cetak">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="7%">Tahun</th>
                    <th width="11%">Kode</th>
                    <th width="16%">Wilayah</th>
                    <th width="13%">Sektor</th>
                    <th>Deskripsi Potensi</th>
                    <th width="16%">Pegawai Survei</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($rincian_tahun as $dp): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($dp['tahun_data'] ?? '-') ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($dp['kode_potensi']) ?></td>
                    <td>
                        Kec. <?= htmlspecialchars($dp['nama_kecamatan']) ?>
                        <?= !empty($dp['nama_kelurahan']) ? '<br><small>Kel. ' . htmlspecialchars($dp['nama_kelurahan']) . '</small>' : '' ?>
                    </td>
                    <td><?= htmlspecialchars($dp['nama_sektor']) ?></td>
                    <td><?= nl2br(htmlspecialchars($dp['deskripsi'])) ?></td>
                    <td><?= htmlspecialchars($dp['nama_pegawai'] ?? '-') ?></td>
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