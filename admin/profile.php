<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil data terbaru dari database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Buat variabel foto
$foto = $user['foto'] ?? null;
$filePath = "../file/" . $foto;
$hasPhoto = ($foto && file_exists($filePath));

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.min.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>

    <!-- Sidebar Mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" id="sidebarOffcanvas">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
                    <ul class="nav flex-column">
                        <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                        <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
                    </ul>
                </div>
                <div class="mt-auto px-3">
                    <ul class="nav flex-column mb-3">
                        <li><a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
                        <li><a id="logoutBtnMobile" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Desktop -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h4 class="fw-bold mb-4 ms-3">SmartNote</h4>
            <ul class="nav flex-column">
                <li><a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="kelola_rapat_admin.php"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
            </ul>
        </div>
        <div>
            <ul class="nav flex-column mb-3">
                <li><a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
                <li><a id="logoutBtn" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Keluar</a></li>
            </ul>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h4 class="fw-bold fs-4 mb-0 text-dark">Profile Admin</h4>
            </div>
        </div>

        <div class="profile-container">
            <div class="profile-card-modern">

                <!-- FOTO PROFIL + NAMA + ROLE -->
                <div class="profile-header-modern text-center">

                    <?php if ($hasPhoto): ?>
                        <img src="<?= htmlspecialchars($filePath) . '?v=' . time(); ?>"
                             alt="Profile"
                             class="profile-avatar-modern"
                             style="object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-person-circle"
                           style="font-size: 95px; color: #495057;"></i>
                    <?php endif; ?>

                    <h3 class="profile-name-modern mt-3"><?= htmlspecialchars($user['nama']); ?></h3>
                    <span class="profile-role-badge"><?= ucfirst($user['role']); ?></span>
                </div>

                <!-- DETAIL INFORMASI -->
                <div class="profile-info-grid mt-4">
                    <div class="info-item">
                        <span class="info-label">Nama Lengkap</span>
                        <span class="info-value"><?= htmlspecialchars($user['nama']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']); ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Nomor Induk (NIK/NIP)</span>
                        <span class="info-value"><?= !empty($user['nik']) ? htmlspecialchars($user['nik']) : '-'; ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Role Akses</span>
                        <span class="info-value"><?= ucfirst($user['role']); ?></span>
                    </div>
                </div>

                <!-- ACTION BUTTON -->
                <div class="profile-actions-modern mt-4">
                    <a href="edit_profile_admin.php" class="btn-edit-modern">
                        <i class="bi bi-pencil-square"></i> Edit Profil
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Logout Confirm -->
    <script>
        async function confirmLogout(e) {
            e.preventDefault();
            const confirmed = await showConfirm("Apakah kamu yakin ingin logout?");
            if (confirmed) window.location.href = "../proses/proses_logout.php";
        }

        document.getElementById("logoutBtn").addEventListener("click", confirmLogout);
        document.getElementById("logoutBtnMobile").addEventListener("click", confirmLogout);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>

</body>
</html>
