<?php
session_start(); // Mulai session — memastikan variabel session tersedia
require_once __DIR__ . '/../koneksi.php'; // Sertakan koneksi database (variabel $conn diasumsikan ada)

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jika tidak login atau bukan admin -> arahkan ke halaman login
    header("Location: ../login.php");
    exit;
}

// Pastikan request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Ambil dan sanitasi input dasar
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$judul = trim($_POST['judul'] ?? '');
$tanggal = $_POST['tanggal'] ?? '';
$isi = $_POST['isi'] ?? '';
$status = $_POST['status'] ?? 'draft'; // Ambil status
$peserta_arr = isset($_POST['peserta']) ? $_POST['peserta'] : [];
// Sanitasi peserta (ensure int)
$clean_peserta = [];
if (is_array($peserta_arr)) {
    foreach ($peserta_arr as $p) {
        $val = (int)$p;
        if ($val > 0) $clean_peserta[] = $val;
    }
}
$peserta_str = implode(',', $clean_peserta);

// Validasi sederhana: cek field wajib
if ($id <= 0 || empty($judul) || empty($tanggal) || empty($isi)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap!']);
    exit;
}

// Ensure data limits (similar to add notulen)
if (strlen($judul) > 50) {
    $judul = substr($judul, 0, 50);
}
if (strlen($peserta_str) > 255) {
    echo json_encode(['success' => false, 'message' => 'Terlalu banyak peserta (max 255 chars).']);
    exit;
}

// --- 1. Ambil data lama untuk mendapatkan list file saat ini ---
$sql_get = "SELECT tindak_lanjut FROM tambah_notulen WHERE id = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$res_get = $stmt_get->get_result();
$row_old = $res_get->fetch_assoc();
$stmt_get->close();

$current_files_str = $row_old['tindak_lanjut'] ?? '';
$current_files = array_filter(array_map('trim', explode('|', $current_files_str)), function($v){ return $v !== ''; });

// --- 2. Proses Penghapusan File ---
$deleted_files = isset($_POST['deleted_files']) ? $_POST['deleted_files'] : [];
if (!empty($deleted_files)) {
    // Hapus dari array current_files
    $current_files = array_diff($current_files, $deleted_files);
    
    // Opsional: Hapus fisik file jika diperlukan (hati-hati jika file dipakai di tempat lain)
    // foreach ($deleted_files as $del) {
    //    $path = __DIR__ . '/../file/' . $del;
    //    if (file_exists($path)) unlink($path);
    // }
}

// --- 3. Proses Upload File Baru (Multiple) ---
$new_uploaded_files = [];
if (!empty($_FILES['lampiran']['name'][0])) {
    $total_files = count($_FILES['lampiran']['name']);
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
    $target_dir = __DIR__ . '/../file/';

    for ($i = 0; $i < $total_files; $i++) {
        $fileName = $_FILES['lampiran']['name'][$i];
        $fileTmp = $_FILES['lampiran']['tmp_name'][$i];
        $fileType = $_FILES['lampiran']['type'][$i];
        $fileSize = $_FILES['lampiran']['size'][$i];
        $fileError = $_FILES['lampiran']['error'][$i];

        if ($fileError === UPLOAD_ERR_OK) {
            if (in_array($fileType, $allowed_types)) {
                if ($fileSize <= 5 * 1024 * 1024) { // 5MB limit
                    $safeName = time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($fileName));
                    if (move_uploaded_file($fileTmp, $target_dir . $safeName)) {
                        $new_uploaded_files[] = $safeName;
                    }
                }
            }
        }
    }
}

// --- 4. Gabungkan File Lama (sisa) + File Baru ---
$final_files = array_merge($current_files, $new_uploaded_files);
// Hapus duplikat jika ada
$final_files = array_unique($final_files);
// Gabungkan jadi string
$final_files_str = implode('|', $final_files);

// --- 5. Update Database ---
$sql = "UPDATE tambah_notulen SET judul=?, tanggal=?, hasil=?, peserta=?, tindak_lanjut=?, status=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $judul, $tanggal, $isi, $peserta_str, $final_files_str, $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notulen berhasil diperbarui!']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
