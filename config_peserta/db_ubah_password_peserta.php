<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data pengguna yang sedang login
$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nama, foto, is_first_login FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRes = $stmt->get_result();
$userData = $userRes->fetch_assoc();
$stmt->close();

if (!$userData) {
    header("Location: ../login.php");
    exit;
}

$userName = $userData['nama'] ?? 'Peserta';
$userPhoto = $userData['foto'] ?? null;
$isFirstLogin = $userData['is_first_login'] ?? false;

// Ambil pesan dari sesi
$success_msg = $_SESSION['success_message'] ?? '';
$error_msg = $_SESSION['error_message'] ?? '';

if ($success_msg) unset($_SESSION['success_message']);
if ($error_msg) unset($_SESSION['error_message']);
?>
