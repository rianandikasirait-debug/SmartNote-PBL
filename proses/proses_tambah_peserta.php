<?php
// proses_tambah_peserta.php
session_start(); 
// Memulai session untuk menyimpan pesan sukses/error

require_once __DIR__ . '/../koneksi.php'; 
// Menghubungkan ke database (path menyesuaikan folder)

// Pastikan request berasal dari POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, tolak permintaan dan kembali ke form
    $_SESSION['error_message'] = 'Metode tidak diizinkan.';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

// Ambil dan bersihkan input dari form
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi wajib: semua field harus terisi
if (empty($nama) || empty($email) || empty($nik) || empty($password)) {
    $_SESSION['error_message'] = 'Semua field harus diisi.';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

// Validasi panjang password minimal 8 karakter
if (strlen($password) < 8) {
    $_SESSION['error_message'] = 'Password harus minimal 8 karakter.';
    header('Location: ../admin/tambah_peserta_admin.php');
    exit;
}

// Enkripsi password dengan hash yang aman
$hashed = password_hash($password, PASSWORD_DEFAULT);

try {

    // ---------------------------------------------------
    // CEK APAKAH EMAIL ATAU NIK SUDAH TERDAFTAR SEBELUMNYA
    // ---------------------------------------------------
    $sql_check = "SELECT id FROM users WHERE email = ? OR nik = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) throw new Exception($conn->error);

    // Bind parameter email dan nik
    $stmt_check->bind_param("ss", $email, $nik);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    // Jika sudah ada user dengan email/nik tersebut → tolak
    if ($res->num_rows > 0) {
        $_SESSION['error_message'] = 'Email atau nik sudah terdaftar.';
        $stmt_check->close();
        header('Location: ../admin/tambah_peserta_admin.php');
        exit;
    }
    $stmt_check->close();

    // ---------------------------------------------------
    // INSERT PESERTA BARU KE TABLE USERS
    // ---------------------------------------------------
    $sql_insert = "INSERT INTO users (nama, email, nik, password, role) VALUES (?, ?, ?, ?, 'peserta')";
    $stmt = $conn->prepare($sql_insert);
    if (!$stmt) throw new Exception($conn->error);

    // Bind input user + password hash
    $stmt->bind_param("ssss", $nama, $email, $nik, $hashed);

    // Eksekusi insert
    if ($stmt->execute()) {
        // Jika sukses, simpan pesan sukses ke session
        $_SESSION['success_message'] = "Berhasil menambahkan peserta baru: $nama ($nik)";
    } else {
        // Jika gagal eksekusi SQL insert
        $_SESSION['error_message'] = 'Gagal menyimpan data.';
    }

    $stmt->close();

} catch (Exception $e) {
    // Jika error tak terduga terjadi (prepare atau eksekusi gagal)
    error_log($e->getMessage()); 
    // Mencatat error ke server log

    $_SESSION['error_message'] = 'Terjadi kesalahan server. Coba lagi nanti.';
}

// Tutup koneksi DB
$conn->close();

// Setelah selesai, redirect ke halaman kelola rapat
header('Location: ../admin/kelola_rapat_admin.php');
exit;
