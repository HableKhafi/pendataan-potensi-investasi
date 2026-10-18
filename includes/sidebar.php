<?php
// Deteksi nama file halaman yang sedang dibuka
$current_page = basename($_SERVER['PHP_SELF']);
$user_role    = $_SESSION['role'] ?? '';
$is_superadmin = in_array($user_role, ['superadmin', 'super_admin']);
?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #212529; /* Warna gelap sidebar */
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #1a1d20;
            border-bottom: 1px solid #2c3034;
        }
        #sidebar ul.components {
            padding: 15px 0;
        }
        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: block;
            color: #adb5bd;
            text-decoration: none;
            transition: 0.2s;
        }
        #sidebar ul li a:hover, 
        #sidebar ul li.active > a {
            color: #fff;
            background: #0d6efd; /* Highlight warna biru aktif */
        }
        #sidebar ul li a i {
            margin-right: 10px;
        }
        #content {
            width: 100%;
            padding: 25px;
            min-height: 100vh;
        }
    </style>
<nav id="sidebar">
    <div class="sidebar-header text-center">
        <h5 class="m-0 fw-bold text-white">DPMPTSP</h5>
        <small class="text-white-50">Potensi Investasi</small>
    </div>

    <ul class="list-unstyled components">
        <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </li>
        <li class="<?= $current_page === 'sektor.php' ? 'active' : '' ?>">
            <a href="sektor.php"><i class="bi bi-grid-3x3-gap"></i> Kelola Sektor</a>
        </li>
        <li class="<?= in_array($current_page, ['potensi.php', 'potensi_edit.php']) ? 'active' : '' ?>">
            <a href="potensi.php"><i class="bi bi-file-earmark-text"></i> Data Potensi</a>
        </li>
        <li class="<?= $current_page === 'potensi_tambah.php' ? 'active' : '' ?>">
            <a href="potensi_tambah.php"><i class="bi bi-plus-circle"></i> Tambah Potensi</a>
        </li>
        
        
        <?php if ($is_superadmin): ?>
        <li class="<?= $current_page === 'users.php' ? 'active' : '' ?>">
            <a href="users.php"><i class="bi bi-people"></i> Kelola User</a>
        </li>
        <?php endif; ?>

        <li class="<?= $current_page === 'laporan.php' ? 'active' : '' ?>">
    <a href="laporan.php"><i class="bi bi-bar-chart-line"></i> Laporan & Grafik</a>
</li>

        <li class="mt-4 border-top border-secondary pt-3">
            <a href="../auth/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </li>
        
    </ul>
</nav>

<!-- SweetAlert2 CSS & JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Tangani Form Tambah / Edit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Mencegah perulangan jika form sudah terkonfirmasi
            if (this.dataset.confirmed === 'true') {
                return true;
            }

            e.preventDefault();
            const currentForm = this;
            const submitter = e.submitter; // Mengambil tombol submit yang diklik

            const isEdit = currentForm.querySelector('[name*="edit"]') !== null || window.location.href.includes('edit');
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
                    // Jika tombol submit memiliki 'name', sertakan sebagai input hidden agar terdeteksi PHP
                    if (submitter && submitter.name) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = submitter.name;
                        hiddenInput.value = submitter.value || '1';
                        currentForm.appendChild(hiddenInput);
                    }
                    currentForm.dataset.confirmed = 'true';
                    currentForm.submit();
                }
            });
        });
    });

    // 2. Tangani Tombol Hapus (Hanya untuk link <a> dengan class .btn-danger)
    document.querySelectorAll('a.btn-danger').forEach(button => {
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
});
</script>