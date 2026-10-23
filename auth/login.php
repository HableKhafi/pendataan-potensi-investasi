<?php
session_start();
require_once '../config/database.php';

/** @var PDO $pdo */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // Bisa Email atau Username
    $password   = trim($_POST['password'] ?? '');

    if (empty($identifier) || empty($password)) {
        $error = 'Username/Email dan password wajib diisi!';
    } else {
        // Cek pengguna berdasarkan EMAIL atau USERNAME
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR username = :id LIMIT 1");
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch();


        if ($user && password_verify($password, $user['password'])) {
            $stmt_last_login = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id_user = :id");
            $stmt_last_login->execute([':id' => $user['id_user']]);
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
    <title>Login - DPMPTSP Banjarbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 420px;
            background: #ffffff;
        }
    </style>
</head>
<body>

<div class="card card-login p-4">
    <div class="text-center mb-4">
        <h4 class="fw-bold m-0">Login Sistem</h4>
        <small class="text-muted">Potensi Investasi DPMPTSP Banjarbaru</small>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset_success'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>Password berhasil diperbarui! Silakan login dengan password baru.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Username atau Email *</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                <input type="text" name="identifier" class="form-control" required placeholder="Username atau email">
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0">Password *</label>
                <a href="lupa_password.php" class="text-decoration-none small text-primary">Lupa Password?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="inputPassword" class="form-control" required placeholder="••••••••">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Tampilkan/Sembunyikan Password">
                    <i class="bi bi-eye-slash" id="iconToggle"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">Masuk ke Sistem</button>
    </form>
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