<?php
session_start(); // Mulai session — memastikan variabel session tersedia
require_once __DIR__ . '/../koneksi.php'; // Sertakan koneksi database ($conn diasumsikan tersedia)

// Pastikan user sudah login; jika belum arahkan ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_SESSION['user_id']; // Ambil ID user dari session (digunakan untuk update)
$role = $_SESSION['user_role'] ?? 'peserta'; // Ambil role; default 'peserta' jika tidak ada

// Tentukan halaman redirect sesuai role (profil & edit)
$redirect_profile = ($role === 'admin') ? '../admin/profile.php' : '../peserta/profile_peserta.php';
$redirect_edit = ($role === 'admin') ? '../admin/edit_profile_admin.php' : '../peserta/edit_profile_peserta.php';

$nama = trim($_POST['nama']); // Ambil nama baru (trim spasi)
$password_baru = $_POST['password_baru'] ?? ''; // Ambil password baru jika ada
$konfirmasi = $_POST['password_konfirmasi'] ?? ''; // Ambil konfirmasi password
$foto_baru = null; // Placeholder untuk nama file foto baru

// Validasi password baru: jika diisi, harus cocok dengan konfirmasi
if (!empty($password_baru)) {
    if ($password_baru !== $konfirmasi) {
        $_SESSION['error_message'] = "Konfirmasi password tidak cocok.";
        header("Location: $redirect_edit");
        exit;
    }
}

// Upload Foto (jika ada file di input 'foto')
if (!empty($_FILES['foto']['name'])) {
    $file = $_FILES['foto'];

    // Validasi tipe file berdasarkan nilai client-provided MIME type (TIDAK AMAN sendirian)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Format file tidak didukung (hanya JPG, PNG, GIF).";
        header("Location: $redirect_edit");
        exit;
    }

    // Validasi ukuran (maks 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar (maks 2MB).";
        header("Location: $redirect_edit");
        exit;
    }

    // Buat nama file aman: timestamp + filter karakter tidak valid
    $namaFile = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($file['name']));
    $target_dir = __DIR__ . '/../file/';

    // Pindahkan file dari temporary ke folder tujuan
    if (move_uploaded_file($file['tmp_name'], $target_dir . $namaFile)) {
        $foto_baru = $namaFile;

        // Ambil foto lama dari database agar dapat dihapus (opsional)
        $stmt_old = $conn->prepare("SELECT foto FROM users WHERE id=?");
        $stmt_old->bind_param("i", $id);
        $stmt_old->execute();
        $res_old = $stmt_old->get_result();
        if ($row_old = $res_old->fetch_assoc()) {
            $old_photo = $row_old['foto'];
            // Jika foto lama bukan default 'user.jpg' dan file ada -> hapus file lama
            if ($old_photo && $old_photo !== 'user.jpg' && file_exists($target_dir . $old_photo)) {
                unlink($target_dir . $old_photo);
            }
        }
        $stmt_old->close();

        // Update nama file foto di database
        $stmt = $conn->prepare("UPDATE users SET foto=? WHERE id=?");
        $stmt->bind_param("si", $foto_baru, $id);
        $stmt->execute();

        // Sinkron session agar tampilan profil reflektif
        $_SESSION['user_foto'] = $foto_baru;
    } else {
        // Jika gagal memindahkan file -> set error dan redirect ke form edit
        $_SESSION['error_message'] = "Gagal mengupload foto.";
        header("Location: $redirect_edit");
        exit;
    }
}

// Update nama (jika ada)
if (!empty($nama)) {
    $stmt = $conn->prepare("UPDATE users SET nama=? WHERE id=?");
    $stmt->bind_param("si", $nama, $id);
    $stmt->execute();
    $_SESSION['user_name'] = $nama; // sinkron session
}

// Update password baru (jika ada)
if (!empty($password_baru)) {
    $hash = password_hash($password_baru, PASSWORD_DEFAULT); // hash password aman
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
}

// Sukses — set pesan sukses dan redirect ke profil
$_SESSION['success_message'] = "Profil berhasil diperbarui!";
header("Location: $redirect_profile");
exit;
?>
