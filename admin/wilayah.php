<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */
$error = '';
$active_tab = $_GET['tab'] ?? 'kecamatan';

// Block akses POST/DELETE jika role tidak memiliki izin edit (kadis & kabid)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    if (!$can_edit_data) {
        header("Location: wilayah.php?msg=unauthorized");
        exit;
    }
}

// PROSES KECAMATAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kecamatan'])) {
    $active_tab = 'kecamatan';
    $nama_kecamatan = trim($_POST['nama_kecamatan'] ?? '');

    if (empty($nama_kecamatan)) {
        $error = "Nama kecamatan wajib diisi!";
    } else {
        $stmt_check = $pdo->prepare("SELECT id_kecamatan FROM kecamatan WHERE nama_kecamatan = :nama LIMIT 1");
        $stmt_check->execute([':nama' => $nama_kecamatan]);

        if ($stmt_check->fetch()) {
            $error = "Kecamatan '$nama_kecamatan' sudah terdaftar!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO kecamatan (nama_kecamatan, status) VALUES (:nama, 1)");
                $stmt->execute([':nama' => $nama_kecamatan]);
                header("Location: wilayah.php?tab=kecamatan&msg=added");
                exit;
            } catch (PDOException $e) {
                $error = "Gagal menyimpan data kecamatan.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kecamatan'])) {
    $active_tab = 'kecamatan';
    $id_kecamatan   = (int)$_POST['id_kecamatan'];
    $nama_kecamatan = trim($_POST['nama_kecamatan'] ?? '');

    $stmt_check = $pdo->prepare("SELECT id_kecamatan FROM kecamatan WHERE nama_kecamatan = :nama AND id_kecamatan != :id LIMIT 1");
    $stmt_check->execute([':nama' => $nama_kecamatan, ':id' => $id_kecamatan]);

    if ($stmt_check->fetch()) {
        $error = "Nama kecamatan '$nama_kecamatan' sudah digunakan!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE kecamatan SET nama_kecamatan = :nama WHERE id_kecamatan = :id");
            $stmt->execute([':nama' => $nama_kecamatan, ':id' => $id_kecamatan]);
            header("Location: wilayah.php?tab=kecamatan&msg=updated");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal memperbarui data kecamatan.";
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_kecamatan') {
    $active_tab = 'kecamatan';
    $id_kecamatan = (int)$_GET['id'];

    $stmt_check_kel = $pdo->prepare("SELECT COUNT(*) FROM kelurahan WHERE id_kecamatan = :id");
    $stmt_check_kel->execute([':id' => $id_kecamatan]);

    $stmt_check_pot = $pdo->prepare("SELECT COUNT(*) FROM potensi_investasi WHERE id_kecamatan = :id");
    $stmt_check_pot->execute([':id' => $id_kecamatan]);

    if ($stmt_check_kel->fetchColumn() > 0 || $stmt_check_pot->fetchColumn() > 0) {
        header("Location: wilayah.php?tab=kecamatan&msg=used");
        exit;
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM kecamatan WHERE id_kecamatan = :id");
            $stmt->execute([':id' => $id_kecamatan]);
            header("Location: wilayah.php?tab=kecamatan&msg=deleted");
            exit;
        } catch (PDOException $e) {
            header("Location: wilayah.php?tab=kecamatan&msg=error");
            exit;
        }
    }
}

// PROSES KELURAHAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kelurahan'])) {
    $active_tab = 'kelurahan';
    $id_kecamatan   = (int)($_POST['id_kecamatan'] ?? 0);
    $nama_kelurahan = trim($_POST['nama_kelurahan'] ?? '');

    if ($id_kecamatan <= 0 || empty($nama_kelurahan)) {
        $error = "Kecamatan dan nama kelurahan wajib diisi!";
    } else {
        $stmt_check = $pdo->prepare("SELECT id_kelurahan FROM kelurahan WHERE id_kecamatan = :id_kec AND nama_kelurahan = :nama LIMIT 1");
        $stmt_check->execute([':id_kec' => $id_kecamatan, ':nama' => $nama_kelurahan]);

        if ($stmt_check->fetch()) {
            $error = "Kelurahan '$nama_kelurahan' sudah ada pada kecamatan tersebut!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO kelurahan (id_kecamatan, nama_kelurahan, status) VALUES (:id_kec, :nama, 1)");
                $stmt->execute([':id_kec' => $id_kecamatan, ':nama' => $nama_kelurahan]);
                header("Location: wilayah.php?tab=kelurahan&msg=added");
                exit;
            } catch (PDOException $e) {
                $error = "Gagal menyimpan data kelurahan.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kelurahan'])) {
    $active_tab = 'kelurahan';
    $id_kelurahan   = (int)$_POST['id_kelurahan'];
    $id_kecamatan   = (int)$_POST['id_kecamatan'];
    $nama_kelurahan = trim($_POST['nama_kelurahan'] ?? '');

    $stmt_check = $pdo->prepare("SELECT id_kelurahan FROM kelurahan WHERE id_kecamatan = :id_kec AND nama_kelurahan = :nama AND id_kelurahan != :id LIMIT 1");
    $stmt_check->execute([':id_kec' => $id_kecamatan, ':nama' => $nama_kelurahan, ':id' => $id_kelurahan]);

    if ($stmt_check->fetch()) {
        $error = "Kelurahan '$nama_kelurahan' sudah ada pada kecamatan tersebut!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE kelurahan SET id_kecamatan = :id_kec, nama_kelurahan = :nama WHERE id_kelurahan = :id");
            $stmt->execute([':id_kec' => $id_kecamatan, ':nama' => $nama_kelurahan, ':id' => $id_kelurahan]);
            header("Location: wilayah.php?tab=kelurahan&msg=updated");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal memperbarui data kelurahan.";
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_kelurahan') {
    $active_tab = 'kelurahan';
    $id_kelurahan = (int)$_GET['id'];

    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM potensi_investasi WHERE id_kelurahan = :id");
    $stmt_check->execute([':id' => $id_kelurahan]);

    if ($stmt_check->fetchColumn() > 0) {
        header("Location: wilayah.php?tab=kelurahan&msg=used");
        exit;
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM kelurahan WHERE id_kelurahan = :id");
            $stmt->execute([':id' => $id_kelurahan]);
            header("Location: wilayah.php?tab=kelurahan&msg=deleted");
            exit;
        } catch (PDOException $e) {
            header("Location: wilayah.php?tab=kelurahan&msg=error");
            exit;
        }
    }
}

// QUERY DATA
$kecamatan_list = $pdo->query("SELECT * FROM kecamatan WHERE status = 1 ORDER BY nama_kecamatan ASC")->fetchAll();
$kecamatan = $pdo->query("SELECT k.*, 
                         (SELECT COUNT(*) FROM kelurahan WHERE id_kecamatan = k.id_kecamatan) as total_kelurahan,
                         (SELECT COUNT(*) FROM potensi_investasi WHERE id_kecamatan = k.id_kecamatan AND status_data = 'aktif') as total_potensi
                         FROM kecamatan k 
                         ORDER BY k.id_kecamatan DESC")->fetchAll();
$kelurahan = $pdo->query("SELECT l.*, k.nama_kecamatan,
                         (SELECT COUNT(*) FROM potensi_investasi WHERE id_kelurahan = l.id_kelurahan AND status_data = 'aktif') as total_potensi
                         FROM kelurahan l
                         JOIN kecamatan k ON l.id_kecamatan = k.id_kecamatan
                         ORDER BY l.id_kelurahan DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Wilayah - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="m-0 fw-bold">Data Wilayah Administrasi</h4>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-shield-exclamation me-2"></i>Anda tidak memiliki hak akses untuk mengubah data wilayah.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'used'): ?>
            <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-circle-fill me-2"></i>Data tidak dapat dihapus karena masih digunakan di relasi lain!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data baru berhasil ditambahkan!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data berhasil dihapus permanen!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded shadow-sm" id="wilayahTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold <?= $active_tab === 'kecamatan' ? 'active' : '' ?>" id="kecamatan-tab" data-bs-toggle="tab" data-bs-target="#kecamatan-pane" type="button" role="tab">
                    <i class="bi bi-geo-alt me-1"></i> Data Kecamatan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold <?= $active_tab === 'kelurahan' ? 'active' : '' ?>" id="kelurahan-tab" data-bs-toggle="tab" data-bs-target="#kelurahan-pane" type="button" role="tab">
                    <i class="bi bi-pin-map me-1"></i> Data Kelurahan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="wilayahTabContent">
            
            <!-- TAB 1: KECAMATAN -->
            <div class="tab-pane fade <?= $active_tab === 'kecamatan' ? 'show active' : '' ?>" id="kecamatan-pane" role="tabpanel">
                <div class="row">
                    <?php if ($can_edit_data): ?>
                    <div class="col-md-4">
                        <div class="card card-modern p-4">
                            <h5 class="fw-bold mb-3">Tambah Kecamatan</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Kecamatan *</label>
                                    <input type="text" name="nama_kecamatan" class="form-control" required placeholder="Contoh: Banjarbaru Selatan">
                                </div>
                                <button type="submit" name="tambah_kecamatan" class="btn btn-primary w-100">Simpan Kecamatan</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="<?= $can_edit_data ? 'col-md-8' : 'col-md-12' ?>">
                        <div class="card card-modern p-4">
                            <h5 class="fw-bold mb-3">Daftar Kecamatan</h5>
                            <div class="table-responsive">
                                <table id="tableKecamatan" class="table table-modern align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th width="8%">No</th>
                                            <th>Nama Kecamatan</th>
                                            <th>Total Kelurahan</th>
                                            <th>Total Potensi</th>
                                            <?php if ($can_edit_data): ?>
                                                <th class="text-center" width="18%">Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no=1; foreach ($kecamatan as $k): ?>
                                        <tr>
                                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($k['nama_kecamatan']) ?></td>
                                            <td><span class="badge badge-soft-info px-2 py-1"><?= $k['total_kelurahan'] ?> Kelurahan</span></td>
                                            <td><span class="badge badge-soft-success px-2 py-1"><?= $k['total_potensi'] ?> Potensi</span></td>
                                            <?php if ($can_edit_data): ?>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button class="btn btn-sm btn-outline-warning btn-action btn-edit-kec" 
                                                            data-id="<?= $k['id_kecamatan'] ?>" 
                                                            data-nama="<?= htmlspecialchars($k['nama_kecamatan']) ?>" 
                                                            data-bs-toggle="modal" data-bs-target="#modalEditKecamatan" title="Edit">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <a href="wilayah.php?action=delete_kecamatan&id=<?= $k['id_kecamatan'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: KELURAHAN -->
            <div class="tab-pane fade <?= $active_tab === 'kelurahan' ? 'show active' : '' ?>" id="kelurahan-pane" role="tabpanel">
                <div class="row">
                    <?php if ($can_edit_data): ?>
                    <div class="col-md-4">
                        <div class="card card-modern p-4">
                            <h5 class="fw-bold mb-3">Tambah Kelurahan</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan *</label>
                                    <select name="id_kecamatan" class="form-select" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                        <?php foreach ($kecamatan_list as $k): ?>
                                            <option value="<?= $k['id_kecamatan'] ?>"><?= htmlspecialchars($k['nama_kecamatan']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Kelurahan *</label>
                                    <input type="text" name="nama_kelurahan" class="form-control" required placeholder="Contoh: Kemuning">
                                </div>
                                <button type="submit" name="tambah_kelurahan" class="btn btn-primary w-100">Simpan Kelurahan</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="<?= $can_edit_data ? 'col-md-8' : 'col-md-12' ?>">
                        <div class="card card-modern p-4">
                            <h5 class="fw-bold mb-3">Daftar Kelurahan</h5>
                            <div class="table-responsive">
                                <table id="tableKelurahan" class="table table-modern align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th width="8%">No</th>
                                            <th>Nama Kelurahan</th>
                                            <th>Kecamatan</th>
                                            <th>Total Potensi</th>
                                            <?php if ($can_edit_data): ?>
                                                <th class="text-center" width="18%">Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no=1; foreach ($kelurahan as $l): ?>
                                        <tr>
                                            <td class="fw-semibold text-muted"><?= $no++ ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($l['nama_kelurahan']) ?></td>
                                            <td><span class="badge badge-soft-primary px-2 py-1">Kec. <?= htmlspecialchars($l['nama_kecamatan']) ?></span></td>
                                            <td><span class="badge badge-soft-success px-2 py-1"><?= $l['total_potensi'] ?> Potensi</span></td>
                                            <?php if ($can_edit_data): ?>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button class="btn btn-sm btn-outline-warning btn-action btn-edit-kel" 
                                                            data-id="<?= $l['id_kelurahan'] ?>" 
                                                            data-idkec="<?= $l['id_kecamatan'] ?>" 
                                                            data-nama="<?= htmlspecialchars($l['nama_kelurahan']) ?>" 
                                                            data-bs-toggle="modal" data-bs-target="#modalEditKelurahan" title="Edit">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <a href="wilayah.php?action=delete_kelurahan&id=<?= $l['id_kelurahan'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <?php endif; ?>
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
    </div>
</div>

<?php if ($can_edit_data): ?>
<!-- Modal Edit Kecamatan -->
<div class="modal fade" id="modalEditKecamatan" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Kecamatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_kecamatan" id="edit_kec_id">
                <div class="mb-3">
                    <label class="form-label">Nama Kecamatan *</label>
                    <input type="text" name="nama_kecamatan" id="edit_kec_nama" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_kecamatan" class="btn btn-warning">Update Kecamatan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kelurahan -->
<div class="modal fade" id="modalEditKelurahan" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Kelurahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_kelurahan" id="edit_kel_id">
                <div class="mb-3">
                    <label class="form-label">Kecamatan *</label>
                    <select name="id_kecamatan" id="edit_kel_idkec" class="form-select" required>
                        <?php foreach ($kecamatan_list as $k): ?>
                            <option value="<?= $k['id_kecamatan'] ?>"><?= htmlspecialchars($k['nama_kecamatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Kelurahan *</label>
                    <input type="text" name="nama_kelurahan" id="edit_kel_nama" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_kelurahan" class="btn btn-warning">Update Kelurahan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tableKecamatan').DataTable();
        $('#tableKelurahan').DataTable();

        $('#tableKecamatan').on('click', '.btn-edit-kec', function() {
            $('#edit_kec_id').val($(this).data('id'));
            $('#edit_kec_nama').val($(this).data('nama'));
        });

        $('#tableKelurahan').on('click', '.btn-edit-kel', function() {
            $('#edit_kel_id').val($(this).data('id'));
            $('#edit_kel_idkec').val($(this).data('idkec'));
            $('#edit_kel_nama').val($(this).data('nama'));
        });
    });
</script>
</body>
</html>