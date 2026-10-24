<?php
$current_page     = basename($_SERVER['PHP_SELF']);
$user_role        = $_SESSION['role'] ?? '';
$can_manage_users = ($user_role === 'admin');
$can_edit_data    = in_array($user_role, ['admin', 'pegawai']);
?>

<!-- CSS Global & Compact UI Styling -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<style>
    body { 
        min-height: 100vh; 
        background-color: #f8fafc; 
        font-size: 0.85rem; 
    }
    
    .wrapper { display: flex; width: 100%; align-items: flex-start; }

    /* Sidebar Clean White Theme */
    #sidebar { 
        min-width: 220px; 
        max-width: 220px; 
        background: #ffffff; 
        color: #334155; 
        height: 100vh; 
        position: sticky; 
        top: 0; 
        overflow-y: auto; 
        z-index: 100;
        border-right: 1px solid #e2e8f0; /* Garis pembatas samping */
    }
    
    #sidebar .sidebar-header { 
        padding: 16px 12px; 
        background: #ffffff; 
        border-bottom: 1px solid #f1f5f9; 
    }

    #sidebar .sidebar-logo {
        width: 46px;
        height: auto;
        object-fit: contain;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.08));
    }
    
    #sidebar ul.components { padding: 12px 8px; }
    #sidebar ul li { margin-bottom: 2px; }
    
    #sidebar ul li a { 
        padding: 9px 12px; 
        font-size: 0.85rem; 
        display: block; 
        color: #475569; 
        text-decoration: none; 
        transition: all 0.2s ease-in-out; 
        font-weight: 500;
        border-radius: 6px;
    }
    
    /* Hover & Active State warna biru */
    #sidebar ul li a:hover, #sidebar ul li.active > a { 
        color: #ffffff !important; 
        background-color: #2563eb; 
    }
    
    #sidebar ul li a i { margin-right: 8px; font-size: 0.95rem; }

    /* Area Konten Utama */
    #content { 
        width: 100%; 
        padding: 16px 20px; 
        min-height: 100vh; 
    }

    /* Card & Tabel Padat (Compact UI) */
    .card-modern { 
        border: none; 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.03); 
        background: #ffffff; 
    }
    
    .table-modern { 
        border-collapse: separate; 
        border-spacing: 0 3px; 
        margin-top: -3px; 
        font-size: 0.825rem; 
    }
    
    .table-modern thead th { 
        background-color: #f1f5f9 !important; 
        color: #475569; 
        font-size: 0.725rem; 
        font-weight: 700; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        border: none; 
        padding: 8px 10px; 
    }
    
    .table-modern tbody tr { 
        background-color: #ffffff; 
        box-shadow: 0 1px 2px rgba(0,0,0,0.02); 
        transition: all 0.15s ease-in-out; 
    }
    
    .table-modern tbody tr:hover { 
        background-color: #f8fafc !important; 
        transform: translateY(-1px); 
    }
    
    .table-modern tbody td { 
        border: none; 
        padding: 8px 10px; 
        color: #334155; 
    }
    
    .table-modern tbody tr td:first-child { border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
    .table-modern tbody tr td:last-child { border-top-right-radius: 6px; border-bottom-right-radius: 6px; }

    /* Form & Button Compact */
    .form-control, .form-select { 
        padding: 6px 10px; 
        font-size: 0.825rem; 
        border-radius: 6px; 
    }
    
    .form-label { 
        font-size: 0.8rem; 
        margin-bottom: 4px; 
        font-weight: 600;
    }
    
    .btn-sm { 
        padding: 4px 10px; 
        font-size: 0.8rem; 
    }

    /* Badges & Action Buttons */
    .badge-soft-primary { background-color: rgba(37, 99, 235, 0.1); color: #2563eb; font-weight: 600; font-size: 0.75rem; }
    .badge-soft-success { background-color: rgba(22, 163, 74, 0.1); color: #16a34a; font-weight: 600; font-size: 0.75rem; }
    .badge-soft-danger  { background-color: rgba(220, 38, 38, 0.1); color: #dc2626; font-weight: 600; font-size: 0.75rem; }
    .badge-soft-secondary { background-color: rgba(100, 116, 139, 0.1); color: #475569; font-weight: 600; font-size: 0.75rem; }
    .badge-soft-info    { background-color: rgba(14, 165, 233, 0.15); color: #0284c7; font-weight: 600; font-size: 0.75rem; }
    
    .btn-action { 
        width: 28px; 
        height: 28px; 
        padding: 0; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 6px; 
        font-size: 0.8rem; 
    }

    /* Fix Dropdown Show Entries DataTables */
    div.dataTables_length select {
        width: auto !important;
        display: inline-block !important;
        padding-right: 2.2rem !important;
        padding-left: 0.75rem !important;
        min-width: 70px !important;
        background-position: right 0.5rem center !important;
    }
</style>

<nav id="sidebar">
    <div class="sidebar-header text-center">
        <!-- Logo Kota Banjarbaru -->
        <img src="../assets/img/Lambang_Kota_Banjarbaru.svg" 
             alt="Logo Kota Banjarbaru" 
             class="sidebar-logo mb-2" 
             onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/f/f6/Lambang_Kota_Banjarbaru.svg';">
        <h6 class="m-0 fw-bold text-dark" style="font-size: 0.825rem; letter-spacing: 0.5px;">DPMPTSP BANJARBARU</h6>
        <small class="text-muted" style="font-size: 0.7rem;">Potensi Investasi</small>
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


        <li class="mt-4 border-top border-slate-200 pt-2">
            <a href="../auth/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </li>
    </ul>
</nav>

<!-- SweetAlert2 Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handling Form Submit (Abaikan form dengan method GET/Filter)
    document.querySelectorAll('form').forEach(form => {
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
                confirmColor: '#2563eb',
                cancelColor: '#64748b',
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
                confirmColor: '#dc2626',
                cancelColor: '#64748b',
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