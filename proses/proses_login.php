<?php
session_start();

// sesuaikan path ke koneksi.php
require_once __DIR__ . '/../koneksi.php'; // <-- ubah ke '/../koneksi.php' kalau login.php ada di subfolder

// Pastikan lewat POST
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
    $_SESSION['login_error'] = 'Email dan password harus diisi.';
    header('Location: ../login.php');
    exit;
}

// Siapkan query (pastikan nama tabelmu 'users' atau sesuaikan)
$sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    $_SESSION['login_error'] = 'Kesalahan server.';
    header('Location: ../login.php');
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['login_error'] = 'Email tidak ditemukan.';
    header('Location: ../login.php');
    exit;
}

$authenticated = false;

if (!empty($user['password_hash'])) {
    // normal case (aman)
    if (password_verify($password, $user['password_hash'])) {
        $authenticated = true;
        // optionally rehash if algorithm changed
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $u_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($u_stmt) {
                $u_stmt->bind_param('si', $newHash, $user['id']);
                $u_stmt->execute();
                $u_stmt->close();
            }
        }
    }
} elseif (isset($user['password'])) {
    // fallback (insecure) -> migrate to hashed password
    if ($password === $user['password']) {
        $authenticated = true;
        // migrasi: buat hash dan simpan
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $u_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        if ($u_stmt) {
            $u_stmt->bind_param('si', $newHash, $user['id']);
            $u_stmt->execute();
            $u_stmt->close();
        }
        // (opsional) kamu bisa menghapus kolom password plain nanti
    }
}

if (!$authenticated) {
    $_SESSION['login_error'] = 'Password salah.';
    header('Location: ../login.php');
    exit;
}

// Login berhasil
session_regenerate_id(true); // penting
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'] ?? $user['username'] ?? '';
$_SESSION['user_role'] = $user['role'] ?? 'peserta';

// Redirect berdasarkan role
if (trim(strtolower($_SESSION['user_role'])) === 'admin') {
    header('Location: ../admin/dashboard_admin.php'); // sesuaikan path
    exit;
} else {
    header('Location: ../peserta/dashboard_peserta.php'); // sesuaikan path / halaman peserta
    exit;
}