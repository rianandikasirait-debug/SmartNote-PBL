<?php
session_start();

// cek file koneksi dulu (debug)
$koneksi_path = __DIR__ . '/../koneksi.php';
if (!file_exists($koneksi_path)) {
    die('File koneksi tidak ditemukan: ' . $koneksi_path);
}

require_once $koneksi_path;

// Pastikan variabel $conn ada dan valid
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Variabel koneksi ($conn) tidak tersedia atau bukan mysqli. Cek koneksi.php');
}

// Pastikan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_error'] = 'Metode tidak diperbolehkan.';
    header('Location: ../login.php');
    exit;
}

// Ambil input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi sederhana
if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Email dan password wajib diisi.';
    header('Location: ../login.php');
    exit;
}

// validasi format email (opsional tapi disarankan)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = 'Format email tidak valid.';
    header('Location: ../login.php');
    exit;
}

// Siapkan dan jalankan prepared statement (procedural)
$sql = "SELECT id, nama AS name, email, password, role FROM users WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    // debug: tampilkan pesan error MySQL (sementara)
    $_SESSION['login_error'] = 'Kesalahan server (prepare): ' . mysqli_error($conn);
    header('Location: ../login.php');
    exit;
}

// bind param, execute
mysqli_stmt_bind_param($stmt, 's', $email);
if (!mysqli_stmt_execute($stmt)) {
    $_SESSION['login_error'] = 'Kesalahan server (execute): ' . mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    header('Location: ../login.php');
    exit;
}

// Bind hasil (fallback bila get_result() tidak tersedia)
mysqli_stmt_bind_result($stmt, $id, $name, $email_db, $password_hash, $role);
$found = mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$found) {
    $_SESSION['login_error'] = 'Email atau password salah.';
    header('Location: ../login.php');
    exit;
}

// Verifikasi password
if (!password_verify($password, $password_hash)) {
    $_SESSION['login_error'] = 'Email atau password salah.';
    header('Location: ../login.php');
    exit;
}

// Login sukses
session_regenerate_id(true);
$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email_db;
$_SESSION['user_role'] = $role;

// Redirect berdasarkan role
if (strtolower(trim($role)) === 'admin') {
    header('Location: ../admin/dashboard_admin.php');
    exit;
} else {
    header('Location: ../peserta/dashboard_peserta.php');
    exit;
}