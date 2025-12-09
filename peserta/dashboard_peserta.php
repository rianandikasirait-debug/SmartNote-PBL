<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

// Initialize viewed notulen session if not exists
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

// Ambil semua notulen dari database
$user_id = $_SESSION['user_id'];

// Query untuk mengambil semua notulen (untuk sementara tidak filter, agar kita bisa debug)
$sql = "SELECT id, judul_rapat, tanggal_rapat, created_by, Lampiran, peserta, created_at 
        FROM tambah_notulen 
        ORDER BY tanggal_rapat DESC";

$result = $conn->query($sql);

$notulens = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notulens[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Peserta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/admin.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #faf8f5;
            font-family: "Poppins", sans-serif;
        }
        .sidebar {
            height: 100vh !important;
            display: flex;
            flex-direction: column;
        }
        .sidebar-content {
            background: #fff;
            height: 100%;
            border-right: 1px solid #eee;
            padding: 1.5rem 1rem;
        }

        /* Apply min-width only on larger screens */
        @media (min-width: 992px) {
            .sidebar-content { min-width: 250px; }
        }

        /* Make offcanvas (mobile sidebar) wider and more usable on small screens */
        @media (max-width: 991.98px) {
            .offcanvas.offcanvas-start {
                width: 320px !important;
                max-width: 90% !important;
            }
            .offcanvas.offcanvas-start .sidebar-content {
                min-width: 0 !important;
                padding: 1.25rem !important;
            }
        }

        .sidebar-content .nav-link {
            color: #555;
            font-weight: 500;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
        }

        .sidebar-content .nav-link.active,
        .sidebar-content .nav-link:hover {
            background-color: #00c853;
            color: #fff;
        }

        .logout-btn {
            border: 1px solid #f8d7da;
            color: #dc3545;
            border-radius: 0.5rem;
        }

        .main-content {
            margin-left: 260px;
            padding: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
            }
        }

        .highlight-card {
            background-color: #fff;
            border-radius: 1rem;
            border: 1px solid #00b050;
            padding: 1rem;
            box-shadow: 8px 8px 0px 0px #00c853;
            transition: 0.2s;
        }

        .highlight-card:hover {
            box-shadow: 14px 14px 0px 0px #00c853;
        }

        .highlight-card h6 {
            font-weight: 600;
        }

        .highlight-card p {
            color: #777;
            font-size: 0.9rem;
        }

        .table-wrapper {
            background: #fff;
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 1rem;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }

        .btn-view {
            color: #0d6efd;
        }

        td a.text-success i.bi-download {
            color: #198754 !important;
        }

        .search-table {
            width: 250px;
        }

        .table-responsive table {
            table-layout: fixed;
        }

        .table th:nth-child(1),
        .table td:nth-child(1) {
            width: 5%;
            min-width: 50px;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 40%;
            min-width: 180px;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 20%;
            min-width: 100px;
            white-space: nowrap;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: 20%;
            min-width: 100px;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 15%;
            min-width: 100px;
        }

        .table-responsive .text-center a[title="Download"] i {
            color: #198754 !important;
        }

        /* Custom controls styling to match admin mobile look (rounded green inputs) */
        .table-header .controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-header .controls > * {
            min-width: 0;
        }

        .table-header .controls select.form-select,
        .table-header .controls input.form-control {
            border: 2px solid var(--primary);
            border-radius: 12px;
            padding: 10px 12px;
            background: #fff;
            height: 46px;
            box-shadow: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23333' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center; /* Posisi panah */
            background-size: 16px 12px;
            padding-right: 2.5rem !important; /
        }

        .table-header .controls .search-table input.form-control {
            height: 46px;
        }

        /* Stack vertically on small screens to match the screenshot */
        @media (max-width: 575.98px) {
            .table-header .controls {
                flex-direction: column;
                align-items: stretch;
            }

            .table-header .controls > * {
                width: 100% !important;
            }
        }

        .mobile-card {
            border: 1px solid #198754; /* Bootstrap success color */
            transition: all 0.2s ease-in-out;
            background-color: white; /* Ensure background is white */
        }
        .mobile-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
            /* Green border on hover to match */
            border-color: #198754!important; 
        }
        @media (min-width: 768px) {
            .border-start-md {
                border-left: 1px solid #dee2e6 !important;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>
<!-- Mobile -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                        <li>
                            <a class="nav-link active" href="dashboard_peserta.php"><i
                                    class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                    </ul>
                </div>
                
                <div class="mt-auto px-3">
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link" href="profile_peserta.php"><i
                                    class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
                        <li>
                            <a id="logoutBtnMobile" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<!-- Desktop -->
    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
                <li>
                    <a class="nav-link active" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
<<<<<<< HEAD
            </ul>
        </div>

        <div>
            <ul class="nav flex-column mb-3">
                <li>
                    <a class="nav-link" href="profile_peserta.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
            </ul>
            <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
        </div>
=======
            </ul>
            <ul class="nav flex-column mt-auto">
                <li>
                    <a class="nav-link" href="profile_peserta.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
                  <a id="logoutBtn" class="nav-link text-danger" href="#"><i class="bi bi-box-arrow-right me-2 text-danger"></i>Logout</a>
                </li>
            </ul>
        </div>
>>>>>>> 113bf630acfd33def2c4aef1b930991629a41cf3
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4><b>Dashboard Peserta</b></h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-medium">Halo, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Peserta') ?> 👋</span>
            </div>
        </div>

        <!-- Highlight Cards -->
        <div class="row g-3 mb-4">
            <?php
            // Ambil 3 notulen terbaru untuk highlight
            $top3 = array_slice($notulens, 0, 3);
            foreach ($top3 as $highlight):
                $id = (int) ($highlight['id'] ?? 0);
                $isViewed = in_array($id, $_SESSION['viewed_notulen']);
                ?>
                <div class="col-md-4">
                    <a href="detail_rapat_peserta.php?id=<?php echo $id ?>" class="text-decoration-none text-reset">
                        <div class="highlight-card h-100 position-relative">
                            <?php if (!$isViewed): ?>
                                <span class="badge bg-success position-absolute top-0 end-0 m-2">Baru</span>
                            <?php endif; ?>
                            <span class="text-muted"><?= date('d/m/Y', strtotime($highlight['tanggal_rapat'])) ?><?php if (!empty($highlight['created_at'])) echo ' • ' . date('H:i', strtotime($highlight['created_at'])); ?></span>
                            <h6 class="mt-1 mb-1"><?= htmlspecialchars($highlight['judul_rapat']) ?></h6>
                            <p class="text-truncate">Dibuat oleh: <?= htmlspecialchars($highlight['created_by'] ?? 'Admin') ?>
                            </p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

            <div class="table-wrapper">
            <div class="table-header d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h5 class="fw-semibold mb-2 mb-sm-0">Daftar Notulen</h5>

                <div class="d-flex gap-2 flex-wrap controls align-items-center">
                    <select id="filterPembuat" class="form-select form-select-sm border-success" style="width: 180px;">
                        <option value="">Semua Pembuat</option>
                    </select>

                    <select id="rowsPerPage" class="form-select form-select-sm border-success" style="width: 140px;">
                        <option value="5">5 data</option>
                        <option value="10" selected>10 data</option>
                        <option value="20">20 data</option>
                        <option value="all">Semua</option>
                    </select>

                    <div class="search-table">
                        <input type="text" id="searchInput" class="form-control form-control-sm border-success"
                            placeholder="Cari notulen..." />
                    </div>
                </div>
            </div>

                <!-- List Container -->
                <div id="notulenList" class="row g-3"></div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                <small class="text-muted" id="dataInfo"></small>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const tableBody = document.getElementById("tableBody");
            const searchInput = document.getElementById("searchInput");
            const filterPembuat = document.getElementById("filterPembuat");
            const pagination = document.getElementById("pagination");
            const dataInfo = document.getElementById("dataInfo");
            const rowsPerPageSelect = document.getElementById("rowsPerPage");

            // Data dari PHP
            const notulenData = <?= json_encode($notulens, JSON_UNESCAPED_UNICODE) ?>;

            let currentPage = 1;
            let rowsPerPage = 10;

            function escapeHtml(text) {
                return String(text || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function renderTable(data, startIndex = 0) {
                const notulenList = document.getElementById("notulenList");
                notulenList.innerHTML = "";

                if (data.length === 0) {
                    notulenList.innerHTML = `<div class="text-center text-muted py-4">Tidak ada data notulen.</div>`;
                    return;
                }

                data.forEach((item, index) => {
                    const judul = escapeHtml(item.judul_rapat || '');
                    const tanggal = escapeHtml(item.tanggal_rapat || '');
                    const pembuat = escapeHtml(item.created_by || 'Admin');
                    const pesertaCount = item.peserta ? item.peserta.split(',').length : 0;

                    const card = document.createElement('div');
                    card.className = 'col-md-6'; // Grid column
                    
                    // Match Admin Style (Grid Layout)
                    card.innerHTML = `
                        <div class="mobile-card h-100 p-3 rounded-3 position-relative shadow-sm" style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button')) window.location.href='detail_rapat_peserta.php?id=${encodeURIComponent(item.id)}'">
                            
                            <!-- Header: Actions (Badge removed) -->
                            <div class="d-flex justify-content-end align-items-center mb-2">
                                <div class="d-flex gap-2">
                                    ${item.Lampiran ? `<a href="../file/${encodeURIComponent(item.Lampiran)}" class="btn btn-sm text-secondary p-0" title="Download" download><i class="bi bi-download fs-5"></i></a>` : ''}
                                </div>
                            </div>

                            <!-- Body: Title & Metadata -->
                            <div>
                                <h5 class="fw-bold text-dark mb-3 text-truncate" title="${judul}">${judul}</h5>
                                
                                <div class="d-flex flex-column gap-2 text-secondary small">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>${tanggal}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person"></i>
                                        <span class="text-truncate" style="max-width: 200px;">PIC: ${pembuat}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-people"></i>
                                        <span>${pesertaCount} Peserta</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    notulenList.appendChild(card);
                });
            }

            // Isi filter pembuat
            const pembuatUnik = [...new Set(notulenData.map((d) => d.created_by || 'Admin'))];
            pembuatUnik.forEach((nama) => {
                const opt = document.createElement("option");
                opt.value = nama;
                opt.textContent = nama;
                filterPembuat.appendChild(opt);
            });

            function getFilteredData() {
                const keyword = searchInput.value.toLowerCase();
                const selectedPembuat = filterPembuat.value;

                return notulenData.filter((item) => {
                    const judul = (item.judul_rapat || '').toLowerCase();
                    const tanggal = (item.tanggal_rapat || '').toLowerCase();
                    const pembuat = (item.created_by || 'Admin').toLowerCase();

                    const cocokKeyword =
                        judul.includes(keyword) ||
                        tanggal.includes(keyword) ||
                        pembuat.includes(keyword);

                    const cocokPembuat =
                        selectedPembuat === "" || (item.created_by || 'Admin') === selectedPembuat;

                    return cocokKeyword && cocokPembuat;
                });
            }

            function paginate(data) {
                if (rowsPerPage === "all") return data;
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                return data.slice(start, end);
            }

            function renderPagination(totalRows) {
                pagination.innerHTML = "";
                if (rowsPerPage === "all") return;

                const totalPages = Math.ceil(totalRows / rowsPerPage);

                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement("li");
                    li.className = `page-item ${i === currentPage ? "active" : ""}`;
                    li.innerHTML = `<a class="page-link border-success text-success" href="#">${i}</a>`;
                    li.addEventListener("click", (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updateTable();
                        const notulenList = document.getElementById("notulenList");
                        window.scrollTo({
                            top: notulenList.getBoundingClientRect().top + window.scrollY - 100,
                            behavior: "smooth"
                        });
                    });
                    pagination.appendChild(li);
                }
            }

            function updateTable() {
                const filteredData = getFilteredData();
                const totalRows = filteredData.length;
                const startIndex = (rowsPerPage === "all" || totalRows === 0) ? 0 : (currentPage - 1) * rowsPerPage;
                const paginatedData = paginate(filteredData);

                renderTable(paginatedData, startIndex);
                renderPagination(totalRows);

                const start = totalRows === 0 ? 0 : startIndex + 1;
                const end = start + paginatedData.length - 1;
                dataInfo.textContent = `Menampilkan ${start}-${end} dari ${totalRows} data`;
            }

            searchInput.addEventListener("input", () => {
                currentPage = 1;
                updateTable();
            });
            filterPembuat.addEventListener("change", () => {
                currentPage = 1;
                updateTable();
            });
            rowsPerPageSelect.addEventListener("change", () => {
                rowsPerPage = rowsPerPageSelect.value === "all" ? "all" : parseInt(rowsPerPageSelect.value);
                currentPage = 1;
                updateTable();
            });

            // Logout function
            function confirmLogout() {
                if (confirm("Apakah kamu yakin ingin logout?")) {
                    window.location.href = "../proses/proses_logout.php";
                }
            }

            document.getElementById("logoutBtn").addEventListener("click", confirmLogout);

            const logoutBtnMobile = document.getElementById("logoutBtnMobile");
            if (logoutBtnMobile) {
                logoutBtnMobile.addEventListener("click", confirmLogout);
            }

            // Handle highlight card clicks to mark as viewed
            document.querySelectorAll('.highlight-card').forEach(card => {
                const link = card.closest('a');
                if (link) {
                    link.addEventListener('click', function(e) {
                        const href = this.getAttribute('href');
                        const urlParams = new URLSearchParams(new URL(href, window.location.origin).search);
                        const id = urlParams.get('id');
                        
                        if (id) {
                            fetch('../proses/proses_mark_viewed.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: id })
                            }).catch(err => console.error('Error marking as viewed:', err));
                        }
                    });
                }
            });

            updateTable();

            // Re-render table when viewport changes (debounced)
            window.addEventListener('resize', function () {
                if (window._dashResizeTimer) clearTimeout(window._dashResizeTimer);
                window._dashResizeTimer = setTimeout(() => {
                    updateTable();
                }, 120);
            });
        });
    </script>
</body>
</html>