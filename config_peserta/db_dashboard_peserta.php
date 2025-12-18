<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login & Peran
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: ../login.php");
    exit;
}

// Inisialisasi sesi notulen yang dilihat jika belum ada
if (!isset($_SESSION['viewed_notulen'])) {
    $_SESSION['viewed_notulen'] = [];
}

// Ambil semua notulen dari database
// Ambil data untuk kartu sorotan - HANYA untuk peserta ini
$currentUserId = $_SESSION['user_id'];

// 1. Total Notulen untuk peserta ini
$sqlNotulen = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0";
$stmtNotulen = $conn->prepare($sqlNotulen);
$stmtNotulen->bind_param("i", $currentUserId);
$stmtNotulen->execute();
$resNotulen = $stmtNotulen->get_result();
$totalNotulen = $resNotulen ? $resNotulen->fetch_assoc()['total'] : 0;
$stmtNotulen->close();

// 2. Total Notulen berdasarkan Status (Draft dan Final) untuk peserta ini
$sqlDraft = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0 AND (status = 'draft' OR status IS NULL)";
$stmtDraft = $conn->prepare($sqlDraft);
$stmtDraft->bind_param("i", $currentUserId);
$stmtDraft->execute();
$resDraft = $stmtDraft->get_result();
$totalDraft = $resDraft ? $resDraft->fetch_assoc()['total'] : 0;
$stmtDraft->close();

$sqlFinal = "SELECT COUNT(*) as total FROM tambah_notulen WHERE FIND_IN_SET(?, peserta) > 0 AND status = 'final'";
$stmtFinal = $conn->prepare($sqlFinal);
$stmtFinal->bind_param("i", $currentUserId);
$stmtFinal->execute();
$resFinal = $stmtFinal->get_result();
$totalFinal = $resFinal ? $resFinal->fetch_assoc()['total'] : 0;
$stmtFinal->close();

// Ambil data pengguna (nama & foto)
$stmt = $conn->prepare("SELECT nama, foto FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$resUser = $stmt->get_result();
$userData = $resUser->fetch_assoc();
$userName = $userData['nama'] ?? 'Peserta';
$userPhoto = $userData['foto'] ?? null;
$stmt->close();

// Query untuk mengambil notulen yang peserta ini terdaftar di dalamnya
$currentUserId = $_SESSION['user_id'];
$sql = "SELECT id, judul, tanggal, tempat, peserta, tindak_lanjut as Lampiran, created_at,
                COALESCE(status, 'draft') as status
        FROM tambah_notulen 
        WHERE FIND_IN_SET(?, peserta) > 0
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

// Konversi ke format array dan tambahkan status is_viewed
$dataNotulen = [];
if ($result) {
    $viewedIdsSession = $_SESSION['viewed_notulen'] ?? [];
    while ($row = $result->fetch_assoc()) {
        $row['is_viewed'] = in_array((int)$row['id'], $viewedIdsSession);
        // Tambahkan alias untuk kompatibilitas dengan JavaScript
        $row['judul_rapat'] = $row['judul'];
        $row['tanggal_rapat'] = $row['tanggal'];
        $row['created_by'] = $row['tempat'];
        $dataNotulen[] = $row;
    }
}

$stmt->close();
?>
