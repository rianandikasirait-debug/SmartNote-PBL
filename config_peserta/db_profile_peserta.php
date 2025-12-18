<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
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
    // Jika pengguna tidak ditemukan (misal dihapus admin saat sedang login)
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Atur foto default jika kosong
$foto_profile = (!empty($user['foto']) ? '../uploads/' . $user['foto'] : '../uploads/user.jpg') . '?v=' . time();
?>
