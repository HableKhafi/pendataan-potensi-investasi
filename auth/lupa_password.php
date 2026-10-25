<?php
session_start();
date_default_timezone_set('Asia/Makassar');

$host = 'localhost';
$db   = 'db_potensi_investasi';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id_user, nama FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userData = $stmt->fetch();

    if ($userData) {
        $otp = sprintf("%06d", mt_rand(1, 999999));

        $update = $pdo->prepare("UPDATE users SET reset_otp = ?, otp_expiry = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE email = ?");
        $update->execute([$otp, $email]);

        $to      = $email;
        $subject = "Kode OTP Reset Password - DPMPTSP";
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
          <h3>Halo, {$userData['nama']}</h3>
          <p>Kode OTP reset password Anda adalah: <b style='font-size: 22px; color: #2564ed;'>$otp</b></p>
          <p>Kode ini berlaku selama <b>5 menit</b>.</p>
        </body>
        </html>
        ";

        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: DPMPTSP Banjarbaru <admin@dpmtsp.local>" . "\r\n";

        if (mail($to, $subject, $message, $headers)) {
            $_SESSION['reset_email'] = $email;
            header("Location: reset_password.php");
            exit;
        } else {
            $pesan = "Gagal mengirim email OTP. Pastikan server mail/Mailpit aktif.";
        }
    } else {
        $pesan = "Email tidak terdaftar dalam sistem!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - DPMPTSP Kota Banjarbaru</title>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* Panel Kiri (Branding) */
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

        /* Panel Kanan (Form) */
        .form-panel {
            flex: 1.1;
            padding: 50px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 24px;
        }

        .form-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .form-header p {
            font-size: 13px;
            color: #64748b;
        }

        /* Alert Warning Konsisten */
        .alert-warning {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-warning i {
            font-size: 16px;
            color: #dc2626;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        /* Input Group Presisi Seperti Login */
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
            padding: 12px 14px;
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
            padding: 12px 14px;
            width: 100%;
            font-size: 14px;
            color: #1e293b;
        }

        .input-group input::placeholder {
            color: #94a3b8;
        }

        .btn-primary {
            width: 100%;
            padding: 13px;
            background-color: #2564ed;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 8px;
        }

        .btn-primary:hover {
            background-color: #1d52d8;
        }

        .form-footer {
            margin-top: 20px;
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
        <!-- Panel Kiri Branding -->
        <div class="brand-panel">
            <div class="logo-box">
                <!-- Sesuaikan path logo jika berbeda -->
                <img src="../assets/img/Lambang_Kota_Banjarbaru.svg" alt="Logo Banjarbaru" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/3/30/Lambang_Kota_Banjarbaru.png/600px-Lambang_Kota_Banjarbaru.png'">
            </div>
            <h3>DPMPTSP KOTA BANJARBARU</h3>
            <p>Sistem Informasi Pendataan Potensi Investasi Daerah</p>
        </div>

        <!-- Panel Kanan Form -->
        <div class="form-panel">
            <div class="form-header">
                <h2>Lupa Password</h2>
                <p>Masukkan Email terdaftar untuk menerima OTP</p>
            </div>

            <?php if ($pesan): ?>
                <div class="alert-warning">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($pesan); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <div class="input-group-icon">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" placeholder="Masukkan Email Anda" required autocomplete="off">
                    </div>
                </div>

                <button type="submit" class="btn-primary">Kirim Kode OTP</button>

                <div class="form-footer">
                    <a href="login.php">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>