<?php
// proses_edit_peserta.php
session_start();
require_once __DIR__ . '/../koneksi.php';

header('Content-Type: application/json');

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
    exit;
}

// Cek sesi login admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil input
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$nik = trim($_POST['nik'] ?? '');
$whatsapp = trim($_POST['nomor_whatsapp'] ?? '');
$reset_password = isset($_POST['reset_password']) && $_POST['reset_password'] == '1';

if ($id <= 0 || empty($nama) || empty($email) || empty($nik)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Validasi Unik (Email/NIK tidak boleh sama dengan user LAIN)
$sqlCheck = "SELECT id FROM users WHERE (email = ? OR nik = ?) AND id != ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ssi", $email, $nik, $id);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();

if ($resCheck->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email atau NIK sudah digunakan pengguna lain.']);
    exit;
}
$stmtCheck->close();

try {
    // Siapkan query update dasar
    $query = "UPDATE users SET nama = ?, email = ?, nik = ?, nomor_whatsapp = ? WHERE id = ?";
    
    // Jika reset password dicentang, update juga passwordnya
    if ($reset_password) {
        $newPassword = password_hash($nik, PASSWORD_DEFAULT); // Reset ke NIK
        $query = "UPDATE users SET nama = ?, email = ?, nik = ?, nomor_whatsapp = ?, password = ? WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $nama, $email, $nik, $whatsapp, $newPassword, $id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $nama, $email, $nik, $whatsapp, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data pengguna berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server.']);
}

$conn->close();
