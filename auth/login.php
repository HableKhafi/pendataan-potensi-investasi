<?php
session_start();
require_once '../config/database.php';

/** @var PDO $pdo */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    if (empty($identifier) || empty($password)) {
        $error = 'Username/Email dan password wajib diisi!';
    } else {
        // Cek pengguna berdasarkan EMAIL atau USERNAME
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR username = :id LIMIT 1");
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Update waktu Last Login ke Database
            $stmt_last_login = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id_user = :id");
            $stmt_last_login->execute([':id' => $user['id_user']]);

            // Simpan Session User
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['username'] = $user['username'] ?? '';
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];

            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = 'Username/Email atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DPMPTSP Kota Banjarbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    body {
        background-color: #f1f5f9;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 20px;
    }
    .login-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        overflow: hidden;
        width: 100%;
        max-width: 900px;
        background: #ffffff;
    }
    .left-banner {
        /* Gradasi Biru Cerah & Elegan */
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        color: #ffffff;
        text-align: center;
    }
    .logo-box {
        background: #ffffff;
        padding: 20px;
        border-radius: 24px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 160px;
        height: 160px;
        margin-bottom: 20px;
    }
    .logo-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .form-control, .input-group-text {
        border-color: #e2e8f0;
        padding: 12px 14px;
    }
    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .btn-primary-custom {
        background-color: #2563eb;
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.2s ease-in-out;
    }
    .btn-primary-custom:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
    }
</style>
</head>
<body>

<div class="card login-card">
    <div class="row g-0">
        <!-- Kolom Kiri: Banner & Logo Banjarbaru -->
        <div class="col-md-6 left-banner">
            <div class="logo-box">
                <!-- Pastikan file logo berada di assets/img/logo-banjarbaru.png atau ganti path sesuai lokasi gambar -->
                <img src="../assets/img/Lambang_Kota_Banjarbaru.svg" alt="Logo Kota Banjarbaru" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/f/f6/Lambang_Kota_Banjarbaru.svg';">
            </div>
            <h5 class="fw-bold m-0 mt-2">DPMPTSP KOTA BANJARBARU</h5>
            <small class="text-white-50">Sistem Informasi Pendataan Potensi Investasi Daerah</small>
        </div>

        <!-- Kolom Kanan: Form Login -->
        <div class="col-md-6 p-4 p-md-5 bg-white d-flex flex-column justify-content-center">
            <div class="mb-4">
                <h3 class="fw-bold text-dark m-0">Login Sistem</h3>
                <p class="text-muted small">Masuk untuk melanjutkan</p>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset_success'): ?>
                <div class="alert alert-success alert-dismissible fade show py-2 small">
                    <i class="bi bi-check-circle-fill me-1"></i>Password berhasil diperbarui! Silakan login.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show py-2 small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Username atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="text" name="identifier" class="form-control" required placeholder="Masukkan Email atau Username">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="inputPassword" class="form-control" required placeholder="Masukkan Password">
                        <button class="btn btn-outline-secondary bg-white text-muted" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash" id="iconToggle"></i>
                        </button>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <a href="lupa_password.php" class="text-decoration-none small fw-semibold" style="color: #2563eb;">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-primary-custom w-100 text-white">Masuk</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('inputPassword');
        const icon = document.getElementById('iconToggle');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    });
</script>
</body>
</html>