<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

/** @var PDO $pdo */

$user_role = $_SESSION['role'] ?? '';
$is_superadmin = in_array($user_role, ['superadmin', 'super_admin']);

if (!$is_superadmin) {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

// Proses Tambah User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? 'admin');

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $insert = $pdo->prepare("INSERT INTO users (nama, email, password, role, status) VALUES (:nama, :email, :password, :role, 'aktif')");
    $insert->execute([':nama' => $nama, ':email' => $email, ':password' => $hashedPassword, ':role' => $role]);
    header("Location: users.php?msg=added");
    exit;
}

// Proses Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id_user  = (int)$_POST['id_user'];
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = trim($_POST['role'] ?? 'admin');
    $password = trim($_POST['password'] ?? '');

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

// Proses Hapus User
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_user = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
    $stmt->execute([':id' => $id_user]);
    header("Location: users.php?msg=deleted");
    exit;
}

$users = $pdo->query("SELECT id_user, nama, email, role, status, created_at FROM users ORDER BY id_user DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - DPMPTSP Banjarbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        body { min-height: 100vh; background-color: #f8f9fa; }
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar { min-width: 250px; max-width: 250px; background: #212529; color: #fff; min-height: 100vh; }
        #sidebar .sidebar-header { padding: 20px; background: #1a1d20; border-bottom: 1px solid #2c3034; }
        #sidebar ul.components { padding: 15px 0; }
        #sidebar ul li a { padding: 12px 20px; font-size: 0.95rem; display: block; color: #adb5bd; text-decoration: none; transition: 0.2s; }
        #sidebar ul li a:hover, #sidebar ul li.active > a { color: #fff; background: #0d6efd; }
        #sidebar ul li a i { margin-right: 10px; }
        #content { width: 100%; padding: 25px; min-height: 100vh; }
    </style>
</head>
<body>

<div class="wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <div id="content">
        <h4 class="mb-4 fw-bold">Kelola Akun Pengguna</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="card p-3 border-0 shadow-sm">
                    <h5>Tambah Akun Baru</h5>
                    <hr>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required placeholder="Nama Pegawai">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="pegawai@dpmptsp.go.id">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="admin">Admin (Pegawai)</option>
                                <option value="super_admin">Superadmin</option>
                            </select>
                        </div>
                        <button type="submit" name="tambah_user" class="btn btn-dark w-100">Simpan Akun</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card p-3 border-0 shadow-sm">
                    <h5>Daftar Akun Terdaftar</h5>
                    <hr>
                    <table id="tableUsers" class="table table-hover align-middle w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($users as $u): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($u['nama']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge <?= in_array($u['role'], ['superadmin', 'super_admin']) ? 'bg-danger' : 'bg-primary' ?>">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-warning btn-edit-user" 
                                                data-id="<?= $u['id_user'] ?>" 
                                                data-nama="<?= htmlspecialchars($u['nama']) ?>" 
                                                data-email="<?= htmlspecialchars($u['email']) ?>" 
                                                data-role="<?= htmlspecialchars($u['role']) ?>" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditUser">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="users.php?action=delete&id=<?= $u['id_user'] ?>" class="btn btn-danger"  title="Hapus"><i class="bi bi-trash"></i></a>
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

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_user" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_user_nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit_user_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" id="edit_user_role" class="form-select" required>
                        <option value="admin">Admin (Pegawai)</option>
                        <option value="super_admin">Superadmin</option>
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

        $('.btn-edit-user').click(function() {
            $('#edit_user_id').val($(this).data('id'));
            $('#edit_user_nama').val($(this).data('nama'));
            $('#edit_user_email').val($(this).data('email'));
            $('#edit_user_role').val($(this).data('role'));
        });
    });
    // Konfirmasi Form Tambah / Edit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Hentikan submit langsung
        
        const isEdit = form.querySelector('[name*="edit"]') !== null || window.location.href.includes('edit');
        const titleText = isEdit ? 'Simpan Perubahan?' : 'Tambah Data Baru?';
        const confirmText = isEdit ? 'Ya, Perbarui!' : 'Ya, Simpan!';

        Swal.fire({
            title: titleText,
            text: "Pastikan data yang diinputkan sudah benar.",
            icon: 'warning',
            showCancelButton: true,
            confirmColor: '#0d6efd',
            cancelColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Jalankan submit form jika ditekan Ya
            }
        });
    });
});

// Konfirmasi Tombol Hapus
document.querySelectorAll('.btn-danger').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const targetUrl = this.getAttribute('href');

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'error',
            showCancelButton: true,
            confirmColor: '#dc3545',
            cancelColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Data!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = targetUrl;
            }
        });
    });
});
</script>
</body>
</html>