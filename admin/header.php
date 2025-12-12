<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../koneksi.php';

$userId = $_SESSION['user_id'] ?? 0;
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

$userName = $res['nama'] ?? 'Admin';
$userPhoto = $res['foto'] ?? null;
$photoPath = "../file/" . $userPhoto;
$hasPhoto = $userPhoto && file_exists($photoPath);

// --- Ambil judul halaman otomatis
$pageTitle = $GLOBALS['page_title'] ?? "Dashboard Admin";
?>

<!-- BOOTSTRAP ICONS (JIKA BELUM ADA) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>

<style>
/* ===== SIDEBAR DESKTOP ===== */
.sidebar-admin {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: #ffffff;
    border-right: 1px solid #e6e6e6;
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    z-index: 999;
}

.sidebar-admin .title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 25px;
}

.sidebar-admin a {
    display: block;
    padding: 10px 5px;
    border-radius: 8px;
    margin-bottom: 8px;
    color: #222;
    font-weight: 500;
    text-decoration: none !important;
}

.sidebar-admin a.active,
.sidebar-admin a:hover {
    background: #00C853;
    color: #fff !important;
}

/* ===== HEADER (TOP BAR) ===== */
.header-admin {
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    height: 70px;
    background: white;
    border-bottom: 1px solid #e6e6e6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    z-index: 998;
}

.header-admin .page-title {
    font-size: 22px;
    font-weight: 700;
}

.header-admin .right-section {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* ===== MOBILE ONLY ===== */
@media (max-width: 991px){
    .sidebar-admin { display: none; }

    .header-admin {
        left: 0 !important;
    }
}
</style>

<!-- ====================================================== -->
<!-- ================ SIDEBAR DESKTOP ===================== -->
<!-- ====================================================== -->
<div class="sidebar-admin d-none d-lg-flex">

    <div class="sidebar-top">
        <div class="title">SmartNote</div>

        <a href="dashboard_admin.php"
           class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'active' : '' ?>">
            <i class="bi bi-grid me-2"></i> Dashboard
        </a>

        <a href="kelola_rapat_admin.php"
           class="<?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'active' : '' ?>">
            <i class="bi bi-people me-2"></i> Kelola Pengguna
        </a>
    </div>

    <div class="sidebar-bottom">
        <a href="profile.php"
           class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
            <i class="bi bi-person-circle me-2"></i> Profile
        </a>

        <a href="#" id="logoutBtn" class="text-danger">
            <i class="bi bi-box-arrow-right me-2"></i> Keluar
        </a>
    </div>
</div>

<!-- ====================================================== -->
<!-- ================ HEADER / TOP BAR ==================== -->
<!-- ====================================================== -->
<div class="header-admin">
    
    <!-- Tombol sidebar mobile -->
    <button class="btn btn-outline-success d-lg-none" type="button" 
            data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile"
            aria-controls="sidebarMobile">
        <i class="bi bi-list"></i>
    </button>

    <div class="page-title"><?= htmlspecialchars($pageTitle) ?></div>

    <div class="right-section">
        <span class="fw-semibold">Halo, <?= htmlspecialchars($userName) ?> 👋</span>

        <?php if ($hasPhoto): ?>
            <img src="<?= htmlspecialchars($photoPath) ?>" 
                class="rounded-circle"
                style="width:45px;height:45px;object-fit:cover;border:2px solid #ddd;">
        <?php else: ?>
            <i class="bi bi-person-circle" style="font-size:45px;color:#555;"></i>
        <?php endif; ?>
    </div>
</div>

<!-- ====================================================== -->
<!-- ================ SIDEBAR MOBILE ====================== -->
<!-- ====================================================== -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarMobile">
    <div class="offcanvas-body p-0 d-flex flex-column justify-content-between">

        <div>
            <h4 class="fw-bold mb-4 ms-3 mt-3">SmartNote</h4>

            <a class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'active' : '' ?>" 
               href="dashboard_admin.php">
               <i class="bi bi-grid me-2"></i> Dashboard
            </a>

            <a class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) === 'kelola_rapat_admin.php' ? 'active' : '' ?>" 
               href="kelola_rapat_admin.php">
               <i class="bi bi-people me-2"></i> Kelola Pengguna
            </a>
        </div>

        <div class="mb-3 px-3">
            <a class="nav-link mb-2" href="profile.php">
                <i class="bi bi-person-circle me-2"></i> Profile
            </a>
            <a id="logoutBtnMobile" class="nav-link text-danger" href="#">
                <i class="bi bi-box-arrow-right me-2 text-danger"></i> Keluar
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById("logoutBtn")?.addEventListener("click", function(e){
    e.preventDefault();
    if(confirm("Apakah yakin ingin logout?"))
        window.location.href = "../proses/proses_logout.php";
});

document.getElementById("logoutBtnMobile")?.addEventListener("click", function(e){
    e.preventDefault();
    if(confirm("Apakah yakin ingin logout?"))
        window.location.href = "../proses/proses_logout.php";
});
</script>
