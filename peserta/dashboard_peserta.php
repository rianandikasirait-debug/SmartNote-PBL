<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

// Ambil semua notulen dari database
$user_id = $_SESSION['user_id'];

// Query untuk mengambil semua notulen (untuk sementara tidak filter, agar kita bisa debug)
$sql = "SELECT id, judul_rapat, tanggal_rapat, created_by, Lampiran, peserta 
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #faf8f5;
            font-family: "Poppins", sans-serif;
        }

        .sidebar-content {
            min-width: 250px;
            background: #fff;
            height: 100%;
            border-right: 1px solid #eee;
            padding: 1.5rem 1rem;
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
    </style>
</head>

<body>
    <nav class="navbar navbar-light bg-white sticky-top px-3">
        <button class="btn btn-outline-success d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
        </button>
    </nav>
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
        aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-body p-0">
            <div class="sidebar-content d-flex flex-column justify-content-between h-100">
                <div>
                    <h5 class="fw-bold mb-4 ms-3">Menu</h5>
                    <ul class="nav flex-column">
                        <li>
                            <a class="nav-link active" href="dashboard_peserta.php"><i
                                    class="bi bi-grid me-2"></i>Dashboard</a>
                        </li>
                        <li>
                            <a class="nav-link" href="profile_peserta.php"><i
                                    class="bi bi-person-circle me-2"></i>Profile</a>
                        </li>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <button id="logoutBtnMobile" class="btn logout-btn px-4 py-2">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-content d-none d-lg-flex flex-column justify-content-between position-fixed">
        <div>
            <h5 class="fw-bold mb-4 ms-3">Menu</h5>
            <ul class="nav flex-column">
                <li>
                    <a class="nav-link active" href="dashboard_peserta.php"><i class="bi bi-grid me-2"></i>Dashboard</a>
                </li>
                <li>
                    <a class="nav-link" href="profile_peserta.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                </li>
            </ul>
        </div>

        <div class="text-center">
            <button id="logoutBtn" class="btn logout-btn px-4 py-2">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
        </div>
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
                ?>
                <div class="col-md-4">
                    <div class="highlight-card h-100">
                        <span class="text-muted"><?= date('d/m/Y', strtotime($highlight['tanggal_rapat'])) ?></span>
                        <h6 class="mt-1 mb-1"><?= htmlspecialchars($highlight['judul_rapat']) ?></h6>
                        <p class="text-truncate">Dibuat oleh: <?= htmlspecialchars($highlight['created_by'] ?? 'Admin') ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="table-wrapper">
            <div class="table-header d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h5 class="fw-semibold mb-2 mb-sm-0">Daftar Notulen</h5>

                <div class="d-flex gap-2 flex-wrap">
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

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light border-0" style="background-color: #e8f6ee;">
                        <tr class="text-success">
                            <th>No</th>
                            <th>Judul Rapat</th>
                            <th>Tanggal</th>
                            <th>Pembuat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>

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
            const notulenData = <?= json_encode($notulens) ?>;

            let currentPage = 1;
            let rowsPerPage = 10;

            function renderTable(data, startIndex = 0) {
                tableBody.innerHTML = "";
                if (data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data notulen.</td></tr>`;
                    return;
                }

                data.forEach((item, index) => {
                    const nomorUrut = startIndex + index + 1;
                    const tanggal = new Date(item.tanggal_rapat).toLocaleDateString('id-ID');
                    const pembuat = item.created_by || 'Admin';

                    let downloadBtn = '';
                    if (item.Lampiran) {
                        downloadBtn = `<a href="../file/${item.Lampiran}" class="text-success" title="Download" download> <i class="bi bi-download"></i></a>`;
                    } else {
                        downloadBtn = `<span class="text-muted" title="Tidak ada lampiran"><i class="bi bi-download"></i></span>`;
                    }

                    const row = `
                <tr>
                  <td>${nomorUrut}</td>
                  <td>${item.judul_rapat}</td>
                  <td>${tanggal}</td>
                  <td>${pembuat}</td>
                  <td class="text-center"> 
                    <a href="detail_rapat_peserta.php?id=${item.id}" class="text-primary me-2 px-2" title="Lihat"><i class="bi bi-eye"></i></a>
                    ${downloadBtn}
                  </td>
                </tr>
              `;
                    tableBody.insertAdjacentHTML("beforeend", row);
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

            updateTable();
        });
    </script>
</body>

</html>