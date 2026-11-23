<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

// Tab aktif saat reload filter (urutan: sektor, wilayah, tahun)
$allowed_tabs = ['sektor', 'wilayah', 'tahun'];
$active_tab   = in_array($_GET['tab'] ?? '', $allowed_tabs, true) ? $_GET['tab'] : 'sektor';

// Master data untuk dropdown filter
$tahun_list     = $pdo->query("SELECT DISTINCT tahun_data FROM potensi_investasi WHERE status_data = 'aktif' AND tahun_data IS NOT NULL ORDER BY tahun_data DESC")->fetchAll(PDO::FETCH_COLUMN);
$kecamatan_list = $pdo->query("SELECT id_kecamatan, nama_kecamatan FROM kecamatan WHERE status = 1 ORDER BY nama_kecamatan ASC")->fetchAll();
$sektor_list    = $pdo->query("SELECT id_sektor, kode_sektor, nama_sektor FROM sektor WHERE status = 1 ORDER BY kode_sektor ASC")->fetchAll();

// =========================================================================
// TAB 1: QUERY REKAPITULASI PER SEKTOR INVESTASI
// =========================================================================
$tahun_sektor = !empty($_GET['tahun_sektor']) ? (int)$_GET['tahun_sektor'] : 0;
$join_tahun_s = $tahun_sektor > 0 ? " AND p.tahun_data = :tahun_s " : "";

$sql_rekap_sektor = "SELECT 
                        s.id_sektor,
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
$stmt_rs = $pdo->prepare($sql_rekap_sektor);
if ($tahun_sektor > 0) $stmt_rs->bindValue(':tahun_s', $tahun_sektor, PDO::PARAM_INT);
$stmt_rs->execute();
$rekap_sektor = $stmt_rs->fetchAll();

// =========================================================================
// TAB 2: QUERY REKAPITULASI PER WILAYAH (KECAMATAN & KELURAHAN)
// =========================================================================
$tahun_wilayah = !empty($_GET['tahun_wilayah']) ? (int)$_GET['tahun_wilayah'] : 0;
$kec_wilayah   = !empty($_GET['kec_wilayah']) ? (int)$_GET['kec_wilayah'] : 0;

$join_tahun_w = $tahun_wilayah > 0 ? " AND p.tahun_data = :tahun_w " : "";
$where_kec_w  = $kec_wilayah > 0 ? " AND k.id_kecamatan = :kec_w " : "";

// A. Rekap per Kecamatan
$sql_rekap_kec = "SELECT 
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
$stmt_rk = $pdo->prepare($sql_rekap_kec);
if ($tahun_wilayah > 0) $stmt_rk->bindValue(':tahun_w', $tahun_wilayah, PDO::PARAM_INT);
if ($kec_wilayah > 0)   $stmt_rk->bindValue(':kec_w', $kec_wilayah, PDO::PARAM_INT);
$stmt_rk->execute();
$rekap_kecamatan = $stmt_rk->fetchAll();

// B. Rekap per Kelurahan
$sql_rekap_kel = "SELECT 
                    l.id_kelurahan,
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
$stmt_rkel = $pdo->prepare($sql_rekap_kel);
if ($tahun_wilayah > 0) $stmt_rkel->bindValue(':tahun_w', $tahun_wilayah, PDO::PARAM_INT);
if ($kec_wilayah > 0)   $stmt_rkel->bindValue(':kec_w', $kec_wilayah, PDO::PARAM_INT);
$stmt_rkel->execute();
$rekap_kelurahan = $stmt_rkel->fetchAll();

// =========================================================================
// TAB 3: QUERY REKAPITULASI PER TAHUN
// =========================================================================
$kec_tahun = !empty($_GET['kec_tahun']) ? (int)$_GET['kec_tahun'] : 0;
$sek_tahun = !empty($_GET['sek_tahun']) ? (int)$_GET['sek_tahun'] : 0;

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
$sql_rekap_tahun = "SELECT 
                        p.tahun_data,
                        COUNT(DISTINCT p.id_potensi) AS total_potensi,
                        COUNT(DISTINCT p.id_kecamatan) AS jumlah_kecamatan,
                        COUNT(DISTINCT p.id_sektor) AS jumlah_sektor,
                        COALESCE(GROUP_CONCAT(DISTINCT k.nama_kecamatan ORDER BY k.nama_kecamatan SEPARATOR ', '), '-') AS daftar_kecamatan,
                        COALESCE(GROUP_CONCAT(DISTINCT s.nama_sektor ORDER BY s.nama_sektor SEPARATOR ', '), '-') AS daftar_sektor
                    FROM potensi_investasi p
                    JOIN kecamatan k ON p.id_kecamatan = k.id_kecamatan
                    JOIN sektor s ON p.id_sektor = s.id_sektor
                    WHERE {$where_t}
                    GROUP BY p.tahun_data
                    ORDER BY p.tahun_data DESC";
$stmt_rt = $pdo->prepare($sql_rekap_tahun);
$stmt_rt->execute($params_t);
$rekap_tahun = $stmt_rt->fetchAll();

// B. Rincian Potensi per Tahun
$sql_rincian_tahun = "SELECT 
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
$stmt_rincian_t = $pdo->prepare($sql_rincian_tahun);
$stmt_rincian_t->execute($params_t);
$rincian_tahun =$stmt_rincian_t->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="m-0 fw-bold">Laporan Rekapitulasi Potensi Investasi</h4>
                <small class="text-muted">Pilih kategori laporan per sektor, per wilayah, atau per tahun untuk melihat rekap dan mencetak secara terpisah</small>
            </div>
        </div>

        <!-- Navigasi Tab (Model sama seperti Form Data Wilayah) -->
        <ul class="nav nav-tabs mb-3" id="laporanTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold <?= $active_tab === 'sektor' ? 'active' : '' ?>" id="sektor-tab" data-bs-toggle="tab" data-bs-target="#tab-sektor" type="button" role="tab">
                    <i class="bi bi-grid-fill me-1"></i> Rekap per Sektor
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold <?= $active_tab === 'wilayah' ? 'active' : '' ?>" id="wilayah-tab" data-bs-toggle="tab" data-bs-target="#tab-wilayah" type="button" role="tab">
                    <i class="bi bi-geo-alt-fill me-1"></i> Rekap per Wilayah
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold <?= $active_tab === 'tahun' ? 'active' : '' ?>" id="tahun-tab" data-bs-toggle="tab" data-bs-target="#tab-tahun" type="button" role="tab">
                    <i class="bi bi-calendar-check-fill me-1"></i> Rekap per Tahun
                </button>
            </li>
        </ul>

        <!-- Isi Tab -->
        <div class="tab-content" id="laporanTabsContent">

            <!-- ========================================================= -->
            <!-- TAB 1: REKAPITULASI PER SEKTOR INVESTASI                  -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?= $active_tab === 'sektor' ? 'show active' : '' ?>" id="tab-sektor" role="tabpanel">
                <div class="card card-modern p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="fw-bold mb-0">Rekapitulasi Potensi per Sektor Investasi</h5>
                        <a href="cetak_laporan_sektor.php?tahun=<?= $tahun_sektor ?>" target="_blank" class="btn btn-primary btn-sm">
                            <i class="bi bi-printer me-1"></i> Cetak Rekap Sektor
                        </a>
                    </div>

                    <!-- Filter Sektor -->
                    <form method="GET" class="row g-2 align-items-end bg-light p-3 rounded mb-4">
                        <input type="hidden" name="tab" value="sektor">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Tahun Data</label>
                            <select name="tahun_sektor" class="form-select form-select-sm">
                                <option value="">-- Semua Tahun --</option>
                                <?php foreach ($tahun_list as$th): ?>
                                    <option value="<?= $th ?>" <?= $tahun_sektor == $th ? 'selected' : '' ?>><?= $th ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5 d-flex gap-2">
                            <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-funnel me-1"></i> Terapkan Filter</button>
                            <a href="laporan.php?tab=sektor" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="tblRekapSektor" class="table table-modern align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">Kode</th>
                                    <th width="25%">Nama Sektor Investasi</th>
                                    <th class="text-center" width="14%">Jumlah Potensi</th>
                                    <th class="text-center" width="14%">Jml Kecamatan</th>
                                    <th>Sebaran Wilayah Kecamatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $grand_potensi_sek = 0;
                                foreach ($rekap_sektor as$rs): 
                                    $grand_potensi_sek += (int)$rs['total_potensi'];
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><span class="badge badge-soft-primary px-2 py-1"><?= htmlspecialchars($rs['kode_sektor']) ?></span></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($rs['nama_sektor']) ?></td>
                                    <td class="text-center"><span class="badge badge-soft-success px-3 py-1 fs-6"><?= $rs['total_potensi'] ?></span></td>
                                    <td class="text-center"><?= $rs['jumlah_kecamatan'] ?></td>
                                    <td class="small"><?= htmlspecialchars($rs['sebaran_kecamatan']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Total Keseluruhan Potensi:</td>
                                    <td class="text-center text-primary"><?= $grand_potensi_sek ?></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 2: REKAPITULASI PER WILAYAH (KECAMATAN & KELURAHAN)   -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?= $active_tab === 'wilayah' ? 'show active' : '' ?>" id="tab-wilayah" role="tabpanel">
                <div class="card card-modern p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="fw-bold mb-0">Rekapitulasi Potensi Investasi per Wilayah</h5>
                        <a href="cetak_laporan_wilayah.php?tahun=<?= $tahun_wilayah ?>&id_kecamatan=<?= $kec_wilayah ?>" target="_blank" class="btn btn-primary btn-sm">
                            <i class="bi bi-printer me-1"></i> Cetak Rekap Wilayah
                        </a>
                    </div>

                    <!-- Filter Wilayah -->
                    <form method="GET" class="row g-2 align-items-end bg-light p-3 rounded mb-4">
                        <input type="hidden" name="tab" value="wilayah">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Filter Kecamatan</label>
                            <select name="kec_wilayah" class="form-select form-select-sm">
                                <option value="">-- Semua Kecamatan --</option>
                                <?php foreach ($kecamatan_list as$k): ?>
                                    <option value="<?= $k['id_kecamatan'] ?>" <?= $kec_wilayah ==$k['id_kecamatan'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kecamatan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Tahun Data</label>
                            <select name="tahun_wilayah" class="form-select form-select-sm">
                                <option value="">-- Semua Tahun --</option>
                                <?php foreach ($tahun_list as$th): ?>
                                    <option value="<?= $th ?>" <?= $tahun_wilayah == $th ? 'selected' : '' ?>><?= $th ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5 d-flex gap-2">
                            <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-funnel me-1"></i> Terapkan Filter</button>
                            <a href="laporan.php?tab=wilayah" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>

                    <!-- Tabel Rekap Kecamatan -->
                    <h6 class="fw-bold text-primary mb-2">A. Ringkasan per Kecamatan</h6>
                    <div class="table-responsive mb-4">
                        <table id="tblRekapKecamatan" class="table table-modern align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Kecamatan</th>
                                    <th class="text-center" width="13%">Jml Kelurahan</th>
                                    <th class="text-center" width="13%">Jml Potensi</th>
                                    <th class="text-center" width="13%">Jml Sektor</th>
                                    <th>Sektor Unggulan / Tersedia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                $grand_potensi_kec = 0;
                                foreach ($rekap_kecamatan as$rk): 
                                    $grand_potensi_kec += (int)$rk['total_potensi'];
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($rk['nama_kecamatan']) ?></td>
                                    <td class="text-center"><?= $rk['jumlah_kelurahan'] ?></td>
                                    <td class="text-center"><span class="badge badge-soft-primary px-3 py-1 fs-6"><?= $rk['total_potensi'] ?></span></td>
                                    <td class="text-center"><?= $rk['jumlah_sektor'] ?></td>
                                    <td class="small"><?= htmlspecialchars($rk['daftar_sektor']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">Total Keseluruhan Potensi:</td>
                                    <td class="text-center text-primary"><?= $grand_potensi_kec ?></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Tabel Rekap Kelurahan -->
                    <h6 class="fw-bold text-primary mb-2">B. Sebaran per Kelurahan</h6>
                    <div class="table-responsive">
                        <table id="tblRekapKelurahan" class="table table-modern align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Kecamatan</th>
                                    <th width="20%">Kelurahan</th>
                                    <th class="text-center" width="14%">Jumlah Potensi</th>
                                    <th>Sektor Potensi di Kelurahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($rekap_kelurahan as$rkel): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($rkel['nama_kecamatan']) ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($rkel['nama_kelurahan']) ?></td>
                                    <td class="text-center"><span class="badge badge-soft-success px-3 py-1"><?= $rkel['total_potensi'] ?></span></td>
                                    <td class="small"><?= htmlspecialchars($rkel['daftar_sektor']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 3: REKAPITULASI PER TAHUN                             -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?= $active_tab === 'tahun' ? 'show active' : '' ?>" id="tab-tahun" role="tabpanel">
                <div class="card card-modern p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="fw-bold mb-0">Rekapitulasi Potensi Investasi per Tahun</h5>
                        <a href="cetak_laporan_tahun.php?id_kecamatan=<?= $kec_tahun ?>&id_sektor=<?= $sek_tahun ?>" target="_blank" class="btn btn-primary btn-sm">
                            <i class="bi bi-printer me-1"></i> Cetak Rekap Tahun
                        </a>
                    </div>

                    <!-- Filter Tahun -->
                    <form method="GET" class="row g-2 align-items-end bg-light p-3 rounded mb-4">
                        <input type="hidden" name="tab" value="tahun">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Filter Kecamatan</label>
                            <select name="kec_tahun" class="form-select form-select-sm">
                                <option value="">-- Semua Kecamatan --</option>
                                <?php foreach ($kecamatan_list as$k): ?>
                                    <option value="<?= $k['id_kecamatan'] ?>" <?= $kec_tahun ==$k['id_kecamatan'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kecamatan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Filter Sektor</label>
                            <select name="sek_tahun" class="form-select form-select-sm">
                                <option value="">-- Semua Sektor --</option>
                                <?php foreach ($sektor_list as$s): ?>
                                    <option value="<?= $s['id_sektor'] ?>" <?= $sek_tahun ==$s['id_sektor'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['kode_sektor'] . ' - ' .$s['nama_sektor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-funnel me-1"></i> Terapkan Filter</button>
                            <a href="laporan.php?tab=tahun" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>

                    <!-- A. Tabel Ringkasan per Tahun -->
                    <h6 class="fw-bold text-primary mb-2">A. Ringkasan Jumlah Potensi per Tahun</h6>
                    <div class="table-responsive mb-4">
                        <table id="tblRekapTahun" class="table table-modern align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th class="text-center" width="12%">Tahun Data</th>
                                    <th class="text-center" width="13%">Jumlah Potensi</th>
                                    <th class="text-center" width="13%">Jml Kecamatan</th>
                                    <th class="text-center" width="12%">Jml Sektor</th>
                                    <th>Sektor Investasi Terdata</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $grand_potensi_thn = 0;
                                foreach ($rekap_tahun as$rt): 
                                    $grand_potensi_thn += (int)$rt['total_potensi'];
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="text-center fw-bold text-dark fs-6"><?= htmlspecialchars($rt['tahun_data'] ?? 'Tidak Ditentukan') ?></td>
                                    <td class="text-center"><span class="badge badge-soft-primary px-3 py-1 fs-6"><?= $rt['total_potensi'] ?></span></td>
                                    <td class="text-center"><?= $rt['jumlah_kecamatan'] ?></td>
                                    <td class="text-center"><?= $rt['jumlah_sektor'] ?></td>
                                    <td class="small"><?= htmlspecialchars($rt['daftar_sektor']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="2" class="text-end">Total Keseluruhan Potensi:</td>
                                    <td class="text-center text-primary"><?= $grand_potensi_thn ?></td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- B. Tabel Rincian Data Berdasarkan Tahun -->
                    <h6 class="fw-bold text-primary mb-2">B. Rincian Data Potensi Berdasarkan Tahun</h6>
                    <div class="table-responsive">
                        <table id="tblRincianTahun" class="table table-modern align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="4%">No</th>
                                    <th class="text-center" width="8%">Tahun</th>
                                    <th width="11%">Kode</th>
                                    <th width="16%">Wilayah</th>
                                    <th width="13%">Sektor</th>
                                    <th>Deskripsi Potensi</th>
                                    <th width="16%">Pegawai Survei</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($rincian_tahun as$dp): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="text-center fw-bold"><?= htmlspecialchars($dp['tahun_data'] ?? '-') ?></td>
                                    <td><span class="badge badge-soft-primary px-2 py-1"><?= htmlspecialchars($dp['kode_potensi']) ?></span></td>
                                    <td>
                                        <div class="fw-semibold text-dark">Kec. <?= htmlspecialchars($dp['nama_kecamatan']) ?></div>
                                        <?php if (!empty($dp['nama_kelurahan'])): ?>
                                            <small class="text-muted">Kel. <?= htmlspecialchars($dp['nama_kelurahan']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($dp['nama_sektor']) ?></td>
                                    <td class="small"><?= htmlspecialchars($dp['deskripsi']) ?></td>
                                    <td class="small text-primary fw-semibold"><?= htmlspecialchars($dp['nama_pegawai'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
    $(document).ready(function() {$('#tblRekapSektor').DataTable();
        $('#tblRekapKecamatan').DataTable({ paging: false, info: false });
        $('#tblRekapKelurahan').DataTable();
        $('#tblRekapTahun').DataTable({ paging: false, info: false });
        $('#tblRincianTahun').DataTable();

        // Penyesuaian lebar kolom DataTables saat berpindah tab
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
</script>
</body>
</html>