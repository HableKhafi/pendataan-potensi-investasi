<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var bool $can_edit_data */
/** @var PDO $pdo */
$error = '';

// Block akses POST/DELETE jika role tidak memiliki izin edit (kadis & kabid)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    if (!$can_edit_data) {
        header("Location: sektor.php?msg=unauthorized");
        exit;
    }
}

// Tambah Sektor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_sektor'])) {
    $kode_sektor = trim($_POST['kode_sektor'] ?? '');
    $nama_sektor = trim($_POST['nama_sektor'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');

    if (empty($kode_sektor) || empty($nama_sektor)) {
        $error = "Kode Sektor dan Nama Sektor wajib diisi!";
    } else {
        $stmt_check = $pdo->prepare("SELECT id_sektor FROM sektor WHERE kode_sektor = :kode LIMIT 1");
        $stmt_check->execute([':kode' => $kode_sektor]);
        
        if ($stmt_check->fetch()) {
            $error = "Kode sektor '$kode_sektor' sudah digunakan!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO sektor (kode_sektor, nama_sektor, deskripsi, status) VALUES (:kode, :nama, :deskripsi, 1)");
                $stmt->execute([':kode' => $kode_sektor, ':nama' => $nama_sektor, ':deskripsi' => $deskripsi]);
                header("Location: sektor.php?msg=added");
                exit;
            } catch (PDOException $e) {
                $error = "Gagal menyimpan data sektor.";
            }
        }
    }
}

// Edit Sektor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_sektor'])) {
    $id_sektor   = (int)$_POST['id_sektor'];
    $kode_sektor = trim($_POST['kode_sektor'] ?? '');
    $nama_sektor = trim($_POST['nama_sektor'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');

    $stmt_check = $pdo->prepare("SELECT id_sektor FROM sektor WHERE kode_sektor = :kode AND id_sektor != :id LIMIT 1");
    $stmt_check->execute([':kode' => $kode_sektor, ':id' => $id_sektor]);
    
    if ($stmt_check->fetch()) {
        $error = "Kode sektor '$kode_sektor' sudah digunakan oleh sektor lain!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE sektor SET kode_sektor = :kode, nama_sektor = :nama, deskripsi = :deskripsi WHERE id_sektor = :id");
            $stmt->execute([':kode' => $kode_sektor, ':nama' => $nama_sektor, ':deskripsi' => $deskripsi, ':id' => $id_sektor]);
            header("Location: sektor.php?msg=updated");
            exit;
        } catch (PDOException $e) {
            $error = "Gagal memperbarui data sektor.";
        }
    }
}

// Hard Delete Sektor
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_sektor = (int)$_GET['id'];

    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM potensi_investasi WHERE id_sektor = :id");
    $stmt_check->execute([':id' => $id_sektor]);
    
    if ($stmt_check->fetchColumn() > 0) {
        header("Location: sektor.php?msg=used");
        exit;
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM sektor WHERE id_sektor = :id");
            $stmt->execute([':id' => $id_sektor]);
            header("Location: sektor.php?msg=deleted");
            exit;
        } catch (PDOException $e) {
            header("Location: sektor.php?msg=error");
            exit;
        }
    }
}

$sektor = $pdo->query("SELECT * FROM sektor ORDER BY id_sektor DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Sektor - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
       <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
         <h4 class="m-0 fw-bold">Data Sektor Investasi</h4>
       </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-shield-exclamation me-2"></i>Anda tidak memiliki hak akses untuk mengubah data sektor.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'used'): ?>
            <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-circle-fill me-2"></i>Sektor tidak dapat dihapus karena masih digunakan oleh data Potensi Investasi!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show">Sektor baru berhasil ditambahkan!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data sektor berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Sektor berhasil dihapus permanen!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row">
            <?php if ($can_edit_data): ?>
            <div class="col-md-4">
                <div class="card card-modern p-4">
                    <h5 class="fw-bold mb-3">Tambah Sektor Baru</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kode Sektor *</label>
                            <input type="text" name="kode_sektor" class="form-control" required placeholder="Contoh: S11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Sektor *</label>
                            <input type="text" name="nama_sektor" class="form-control" required placeholder="Contoh: Energi Terbarukan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" name="tambah_sektor" class="btn btn-primary w-100">Simpan Sektor</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="<?= $can_edit_data ? 'col-md-8' : 'col-md-12' ?>">
                <div class="card card-modern p-4">
                    <h5 class="fw-bold mb-3">Daftar Sektor</h5>
                    <div class="table-responsive">
                        <table id="tableSektor" class="table table-modern align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Kode</th>
                                    <th>Nama Sektor</th>
                                    <th>Deskripsi</th>
                                    <?php if ($can_edit_data): ?>
                                        <th class="text-center" width="15%">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no=1; foreach ($sektor as $s): ?>
                                <tr>
                                    <td class="fw-semibold text-muted"><?= $no++ ?></td>
                                    <td><span class="badge badge-soft-primary px-2 py-1"><?= htmlspecialchars($s['kode_sektor']) ?></span></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($s['nama_sektor']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($s['deskripsi'] ?? '-') ?></td>
                                    <?php if ($can_edit_data): ?>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning btn-action btn-edit" 
                                                    data-id="<?= $s['id_sektor'] ?>" 
                                                    data-kode="<?= htmlspecialchars($s['kode_sektor']) ?>" 
                                                    data-nama="<?= htmlspecialchars($s['nama_sektor']) ?>" 
                                                    data-deskripsi="<?= htmlspecialchars($s['deskripsi'] ?? '') ?>" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditSektor" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <a href="sektor.php?action=delete&id=<?= $s['id_sektor'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
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

<?php if ($can_edit_data): ?>
<!-- Modal Edit Sektor -->
<div class="modal fade" id="modalEditSektor" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Sektor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_sektor" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Kode Sektor *</label>
                    <input type="text" name="kode_sektor" id="edit_kode" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Sektor *</label>
                    <input type="text" name="nama_sektor" id="edit_nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_sektor" class="btn btn-warning">Update Sektor</button>
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
        $('#tableSektor').DataTable();
        $('#tableSektor').on('click', '.btn-edit', function() {
            $('#edit_id').val($(this).data('id'));
            $('#edit_kode').val($(this).data('kode'));
            $('#edit_nama').val($(this).data('nama'));
            $('#edit_deskripsi').val($(this).data('deskripsi'));
        });
    });
</script>
</body>
</html>