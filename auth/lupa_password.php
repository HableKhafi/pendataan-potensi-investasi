<?php
session_start();
require_once '../config/database.php';

/** @var PDO $pdo */
$error = '';
$step  = 1;
$email_verified = $_SESSION['reset_email'] ?? '';

// Reset sesi permohonan lupa password
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    unset($_SESSION['reset_email'], $_SESSION['reset_nama']);
    header("Location: lupa_password.php");
    exit;
}

// Tahap 1: Verifikasi Email Pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verifikasi_email'])) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Email wajib diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT id_user, email, nama FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['reset_email'] = $user['email'];
            $_SESSION['reset_nama']  = $user['nama'];
            $email_verified = $user['email'];
            $step = 2;
        } else {
            $error = "Email '$email' tidak terdaftar dalam sistem!";
        }
    }
}

// Tahap 2: Simpan Password Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_password'])) {
    $step = 2;
    $password_baru = trim($_POST['password_baru'] ?? '');
    $konfirmasi    = trim($_POST['konfirmasi_password'] ?? '');

    if (empty($password_baru) || empty($konfirmasi)) {
        $error = "Semua kolom password wajib diisi!";
    } elseif ($password_baru !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $hashed = password_hash($password_baru, PASSWORD_BCRYPT);
        $stmt   = $pdo->prepare("UPDATE users SET password = :pass WHERE email = :email");
        $stmt->execute([':pass' => $hashed, ':email' => $email_verified]);

        // Bersihkan session reset
        unset($_SESSION['reset_email'], $_SESSION['reset_nama']);
        
        header("Location: login.php?msg=reset_success");
        exit;
    }
}

if (!empty($_SESSION['reset_email'])) {
    $step = 2;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DPMPTSP Kota Banjarbaru</title>
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
        .reset-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            background: #ffffff;
        }
        .left-banner {
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

<div class="card reset-card">
    <div class="row g-0">
        <!-- Kolom Kiri: Banner & Logo -->
        <div class="col-md-6 left-banner">
            <div class="logo-box">
                <img src="../assets/img/Lambang_Kota_Banjarbaru.svg" alt="Logo Kota Banjarbaru" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/f/f6/Lambang_Kota_Banjarbaru.svg';">
            </div>
            <h5 class="fw-bold m-0 mt-2">DPMPTSP KOTA BANJARBARU</h5>
            <small class="text-white-50">Sistem Informasi Potensi Investasi Daerah</small>
        </div>

        <!-- Kolom Kanan: Form Reset Password -->
        <div class="col-md-6 p-4 p-md-5 bg-white d-flex flex-column justify-content-center">
            <div class="mb-4">
                <h3 class="fw-bold text-dark m-0">Reset Password</h3>
                <p class="text-muted small">Pulihkan akses akun Anda</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show py-2 small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- FORM TAHAP 1: INPUT EMAIL -->
                <p class="text-muted small mb-3">Masukkan email akun Anda yang terdaftar untuk verifikasi pemulihan password.</p>
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Email Terdaftar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required placeholder="Masukkan Email Anda">
                        </div>
                    </div>
                    <button type="submit" name="verifikasi_email" class="btn btn-primary btn-primary-custom w-100 text-white mb-2">Verifikasi Email</button>
                    <a href="login.php" class="btn btn-link w-100 text-decoration-none text-secondary small">&larr; Kembali ke Login</a>
                </form>

            <?php else: ?>
                <!-- FORM TAHAP 2: INPUT PASSWORD BARU -->
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-person-check-fill me-1"></i> Email terverifikasi untuk: <br>
                    <strong><?= htmlspecialchars($_SESSION['reset_nama'] ?? '') ?></strong> (<?= htmlspecialchars($email_verified) ?>)
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password_baru" class="form-control" required placeholder="Masukkan Password Baru">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="konfirmasi_password" class="form-control" required placeholder="Ulangi Password Baru">
                        </div>
                    </div>
                    <button type="submit" name="simpan_password" class="btn btn-success btn-primary-custom w-100 text-white mb-2" style="background-color: #198754;">Simpan Password Baru</button>
                    <a href="lupa_password.php?action=reset" class="btn btn-link w-100 text-decoration-none text-secondary small">Ganti Email Verifikasi</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>