<?php
$current_page     = basename($_SERVER['PHP_SELF']);
$user_role        = $_SESSION['role'] ?? '';
$can_manage_users = ($user_role === 'admin');
$can_edit_data    = in_array($user_role, ['admin', 'pegawai']);
?>

<!-- CSS Global & Modern Table Styling -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    body { min-height: 100vh; background-color: #f8f9fa; }
    
    /* Ubah align-items menjadi flex-start agar sticky bekerja optimal */
    .wrapper { display: flex; width: 100%; align-items: flex-start; }
    
    /* Konfigurasi Sidebar Sticky */
    #sidebar { 
        min-width: 250px; 
        max-width: 250px; 
        background: #212529; 
        color: #fff; 
        height: 100vh;        /* Kunci tinggi sidebar tepat setinggi layar */
        position: sticky;     /* Buat posisi sidebar menempel */
        top: 0;               /* Posisi selalu di paling atas viewport */
        overflow-y: auto;     /* Tambahkan scroll internal jika menu sidebar melebihi layar */
        z-index: 100;
    }
    
    #sidebar .sidebar-header { padding: 20px; background: #1a1d20; border-bottom: 1px solid #2c3034; }
    #sidebar ul.components { padding: 15px 0; }
    #sidebar ul li a { padding: 12px 20px; font-size: 0.95rem; display: block; color: #adb5bd; text-decoration: none; transition: 0.2s; }
    #sidebar ul li a:hover, #sidebar ul li.active > a { color: #fff; background: #0d6efd; }
    #sidebar ul li a i { margin-right: 10px; }
    
    #content { width: 100%; padding: 25px; min-height: 100vh; }

    /* Card & Table Modern UI */
    .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); background: #ffffff; }
    .table-modern { border-collapse: separate; border-spacing: 0 6px; margin-top: -6px; }
    .table-modern thead th { background-color: #f8f9fa !important; color: #6c757d; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; border: none; padding: 14px 16px; }
    .table-modern tbody tr { background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s ease-in-out; }
    .table-modern tbody tr:hover { background-color: #f1f5f9 !important; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .table-modern tbody td { border: none; padding: 14px 16px; color: #334155; font-size: 0.9rem; }
    .table-modern tbody tr td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .table-modern tbody tr td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

    /* Badges & Buttons */
    .badge-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; font-weight: 600; }
    .badge-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; font-weight: 600; }
    .badge-soft-danger  { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; font-weight: 600; }
    .badge-soft-secondary { background-color: rgba(108, 117, 125, 0.1); color: #495057; font-weight: 600; }
    .badge-soft-info    { background-color: rgba(13, 202, 240, 0.15); color: #087990; font-weight: 600; }
    .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
</style>

<nav id="sidebar">
    <div class="sidebar-header text-center">
        <h5 class="m-0 fw-bold text-white">DPMPTSP</h5>
        <small class="text-white-50">Pendataan Potensi Investasi</small>
    </div>

    <ul class="list-unstyled components">
        <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </li>
        <li class="<?= $current_page === 'sektor.php' ? 'active' : '' ?>">
            <a href="sektor.php"><i class="bi bi-grid-3x3-gap"></i> Data Sektor</a>
        </li>
        <li class="<?= $current_page === 'wilayah.php' ? 'active' : '' ?>">
            <a href="wilayah.php"><i class="bi bi-geo-alt"></i> Data Wilayah</a>
        </li>
        <li class="<?= in_array($current_page, ['potensi.php', 'potensi_edit.php']) ? 'active' : '' ?>">
            <a href="potensi.php"><i class="bi bi-file-earmark-text"></i> Data Potensi</a>
        </li>




        
        <?php if ($can_manage_users): ?>
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

<!-- SweetAlert2 Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handling Form Submit (Tambah & Edit saja, abaikan form Filter/GET)
    document.querySelectorAll('form').forEach(form => {
        // Abaikan form jika menggunakan method GET atau memiliki class 'no-alert'
        if (form.method.toLowerCase() === 'get' || form.classList.contains('no-alert')) return;

        form.addEventListener('submit', function(e) {
            if (this.dataset.confirmed === 'true') return true;

            e.preventDefault();
            const currentForm = this;
            const submitter = e.submitter;

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
    // Handling Delete Buttons
    document.querySelectorAll('a.btn-outline-danger, a.btn-danger').forEach(button => {
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