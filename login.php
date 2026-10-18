<?php
session_start();

// Jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['is_login']) && $_SESSION['is_login'] === true) {
    header('Location: admin/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DPMPTSP Banjarbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title text-center mb-4">Login System</h4>

                        <!-- Handling Pesan Error -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger py-2 small">
                                <?php
                                if ($_GET['error'] === 'empty') echo "Email dan password wajib diisi!";
                                elseif ($_GET['error'] === 'failed') echo "Email atau password salah!";
                                ?>
                            </div>
                        <?php endif; ?>

                        <form action="auth/proses_login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required placeholder="contoh@gmail.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Masuk</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>     