<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */
$user_role = $_SESSION['role'] ?? '';

// Proteksi halaman: Hanya role 'admin' yang bisa mengelola akun pengguna
if ($user_role !== 'admin') {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

$error = '';

// 1. Tambah User Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? 'pegawai');

    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Semua bidang wajib diisi!";
    } else {
        $stmt_check = $pdo->prepare("SELECT id_user FROM users WHERE email = :email LIMIT 1");
        $stmt_check->execute([':email' => $email]);

        if ($stmt_check->fetch()) {
            $error = "Email '$email' sudah terdaftar!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO users (nama, email, password, role, status) VALUES (:nama, :email, :password, :role, 'aktif')");
            $insert->execute([':nama' => $nama, ':email' => $email, ':password' => $hashedPassword, ':role' => $role]);
            header("Location: users.php?msg=added");
            exit;
        }
    }
}

// 2. Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id_user  = (int)$_POST['id_user'];
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = trim($_POST['role'] ?? 'pegawai');
    $password = trim($_POST['password'] ?? '');

    $stmt_check = $pdo->prepare("SELECT id_user FROM users WHERE email = :email AND id_user != :id LIMIT 1");
    $stmt_check->execute([':email' => $email, ':id' => $id_user]);

    if ($stmt_check->fetch()) {
        $error = "Email '$email' sudah digunakan oleh akun lain!";
    } else {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET nama = :nama, email = :email, role = :role, password = :pass WHERE id_user = :id");
            $stmt->execute([':nama' => $nama, ':email' => $email, ':role' => $role, ':pass' => $hashedPassword, ':id' => $id_user]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama = :nama, email = :email, role = :role WHERE id_user = :id");
            $stmt->execute([':nama' => $nama, ':email' => $email, ':role' => $role, ':id' => $id_user]);
        }
        header("Location: users.php?msg=updated");
        exit;
    }
}

// 3. Hapus User (Hard Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_user = (int)$_GET['id'];

    // Mencegah admin menghapus akunnya sendiri yang sedang dipakai
    if ($id_user === (int)$_SESSION['id_user']) {
        header("Location: users.php?msg=self_delete");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
        $stmt->execute([':id' => $id_user]);
        header("Location: users.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        header("Location: users.php?msg=error");
        exit;
    }
}

$users = $pdo->query("SELECT id_user, nama, email, role, status, created_at FROM users ORDER BY id_user DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - DPMPTSP Banjarbaru</title>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <h4 class="mb-4 fw-bold">Kelola Akun Pengguna</h4>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show">Akun pengguna baru berhasil ditambahkan!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Data akun berhasil diperbarui!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">Akun pengguna berhasil dihapus!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'self_delete'): ?>
            <div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-circle-fill me-2"></i>Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-modern p-4">
                    <h5 class="fw-bold mb-3">Tambah Akun Baru</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama" class="form-control" required placeholder="Nama Pegawai">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required placeholder="pegawai@dpmptsp.go.id">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required placeholder="Password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role Pengguna *</label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Admin (Full Access & Kelola User)</option>
                                <option value="pegawai" selected>Pegawai (Input & Edit Data)</option>
                                <option value="kadis">Kepala Dinas (View Only)</option>
                                <option value="kabid">Kepala Bidang (View Only)</option>
                            </select>
                        </div>
                        <button type="submit" name="tambah_user" class="btn btn-primary w-100">Simpan Akun</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-modern p-4">
                    <h5 class="fw-bold mb-3">Daftar Akun Terdaftar</h5>
                    <div class="table-responsive">
                        <table id="tableUsers" class="table table-modern align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($users as $u): ?>
                                <tr>
                                    <td class="fw-semibold text-muted"><?= $no++ ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($u['nama']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge badge-soft-danger px-2 py-1"><i class="bi bi-shield-lock me-1"></i> Admin</span>
                                        <?php elseif ($u['role'] === 'pegawai'): ?>
                                            <span class="badge badge-soft-primary px-2 py-1"><i class="bi bi-person me-1"></i> Pegawai</span>
                                        <?php elseif ($u['role'] === 'kadis'): ?>
                                            <span class="badge badge-soft-info px-2 py-1"><i class="bi bi-eye me-1"></i> Kepala Dinas</span>
                                        <?php elseif ($u['role'] === 'kabid'): ?>
                                            <span class="badge badge-soft-secondary px-2 py-1"><i class="bi bi-eye me-1"></i> Kepala Bidang</span>
                                        <?php else: ?>
                                            <span class="badge badge-soft-secondary px-2 py-1"><?= strtoupper($u['role']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-outline-warning btn-action btn-edit-user" 
                                                    data-id="<?= $u['id_user'] ?>" 
                                                    data-nama="<?= htmlspecialchars($u['nama']) ?>" 
                                                    data-email="<?= htmlspecialchars($u['email']) ?>" 
                                                    data-role="<?= htmlspecialchars($u['role']) ?>" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditUser" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <a href="users.php?action=delete&id=<?= $u['id_user'] ?>" class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
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

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_user" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" id="edit_user_nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="edit_user_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role Pengguna *</label>
                    <select name="role" id="edit_user_role" class="form-select" required>
                        <option value="admin">Admin (Full Access & Kelola User)</option>
                        <option value="pegawai">Pegawai (Input & Edit Data)</option>
                        <option value="kadis">Kepala Dinas (View Only)</option>
                        <option value="kabid">Kepala Bidang (View Only)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak diganti)</small></label>
                    <input type="password" name="password" class="form-control" placeholder="Password baru">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_user" class="btn btn-warning">Update User</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tableUsers').DataTable();

        // Event delegation agar tombol edit di page 2 DataTables tetap aktif
        $('#tableUsers').on('click', '.btn-edit-user', function() {
            $('#edit_user_id').val($(this).data('id'));
            $('#edit_user_nama').val($(this).data('nama'));
            $('#edit_user_email').val($(this).data('email'));
            $('#edit_user_role').val($(this).data('role'));
        });
    });
</script>
</body>
</html>