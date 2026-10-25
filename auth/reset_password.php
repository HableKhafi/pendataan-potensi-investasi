<?php
session_start();
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['reset_email'])) {
    header("Location: lupa_password.php");
    exit;
}

$email = $_SESSION['reset_email'];
$pesan = '';
$sukses_reset = false;

$host = 'localhost';
$db   = 'db_potensi_investasi';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp           = trim($_POST['otp']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi_password'];

    if ($password_baru !== $konfirmasi) {
        $pesan = "Konfirmasi password baru tidak cocok!";
    } else {
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ? AND reset_otp = ? AND otp_expiry > NOW()");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();

        if ($user) {
            $hashedPassword = password_hash($password_baru, PASSWORD_DEFAULT);

            $update = $pdo->prepare("UPDATE users SET password = ?, reset_otp = NULL, otp_expiry = NULL WHERE email = ?");
            $update->execute([$hashedPassword, $email]);

            unset($_SESSION['reset_email']);
            $sukses_reset = true;
        } else {
            $pesan = "Kode OTP salah atau sudah kedaluwarsa (lebih dari 5 menit)!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DPMPTSP Kota Banjarbaru</title>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-card {
            background: #ffffff;
            width: 100%;
            max-width: 900px;
            min-height: 520px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
            display: flex;
            overflow: hidden;
        }

        .brand-panel {
            flex: 1;
            background: linear-gradient(180deg, #2564ed 0%, #1d52d8 100%);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #ffffff;
        }

        .logo-box {
            background: #ffffff;
            width: 130px;
            height: 130px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .brand-panel h3 {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .brand-panel p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.5;
            max-width: 260px;
        }

        .form-panel {
            flex: 1.1;
            padding: 40px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 20px;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .form-header p {
            font-size: 13px;
            color: #64748b;
        }

        .email-badge {
            color: #2564ed;
            font-weight: 600;
        }

        .alert-warning {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-warning i {
            font-size: 16px;
            color: #dc2626;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-group:focus-within {
            border-color: #2564ed;
            box-shadow: 0 0 0 3px rgba(37, 100, 237, 0.12);
        }

        .input-group-icon {
            padding: 10px 14px;
            background: #f8fafc;
            color: #94a3b8;
            border-right: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 46px;
        }

        .input-group input {
            border: none;
            outline: none;
            padding: 10px 14px;
            width: 100%;
            font-size: 14px;
            color: #1e293b;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #2564ed;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #1d52d8;
        }

        .form-footer {
            margin-top: 16px;
            text-align: right;
        }

        .form-footer a {
            color: #2564ed;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .auth-card {
                flex-direction: column;
            }
            .brand-panel {
                padding: 30px;
            }
            .form-panel {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="brand-panel">
            <div class="logo-box">
                <img src="../assets/img/logo.png" alt="Logo Banjarbaru" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/3/30/Lambang_Kota_Banjarbaru.png/600px-Lambang_Kota_Banjarbaru.png'">
            </div>
            <h3>DPMPTSP KOTA BANJARBARU</h3>
            <p>Sistem Informasi Pendataan Potensi Investasi Daerah</p>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <h2>Verifikasi OTP & Reset</h2>
                <p>Kode OTP telah dikirim ke <span class="email-badge"><?= htmlspecialchars($email); ?></span></p>
            </div>

            <?php if ($pesan): ?>
                <div class="alert-warning">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($pesan); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="otp">Kode OTP (6 Digit)</label>
                    <div class="input-group">
                        <div class="input-group-icon">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <input type="text" id="otp" name="otp" maxlength="6" placeholder="Masukkan 6 Digit Kode OTP" required autocomplete="off">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <div class="input-group">
                        <div class="input-group-icon">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="password_baru" name="password_baru" placeholder="Masukkan Password Baru" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <div class="input-group-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi Password Baru" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Ubah Password</button>

                <div class="form-footer">
                    <a href="lupa_password.php">Kirim Ulang OTP / Ganti Email</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Pop-up SweetAlert2 -->
    <?php if ($sukses_reset): ?>
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: 'Password berhasil diubah! Silakan login kembali.',
            icon: 'success',
            confirmButtonColor: '#2564ed',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>