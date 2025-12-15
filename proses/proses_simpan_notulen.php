<?php
session_start();
// Mulai session untuk akses data session (mis. user_name)

// Sertakan file koneksi database ($conn diasumsikan tersedia)
require_once __DIR__ . '/../koneksi.php';
error_reporting(0); // Suppress errors to allow JSON response

// Pastikan request menggunakan metode POST — endpoint ini hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Ambil field form dan beri nilai default jika tidak ada
$judul = trim($_POST['judul'] ?? '');
$tanggal = $_POST['tanggal'] ?? '';
$isi = $_POST['isi'] ?? '';
// Peserta bisa dikirim sebagai array (peserta[]) atau satu nilai
$peserta_ids = $_POST['peserta'] ?? [];

// Validasi wajib: judul, tanggal, dan isi harus diisi
if ($judul === '' || $tanggal === '' || $isi === '') {
    echo json_encode(['success' => false, 'message' => 'Judul, tanggal, isi wajib diisi']);
    exit;
}

// File upload handled after insertion into tb_lampiran

// ---------- Prepare peserta CSV ----------
// Jika peserta dikirim sebagai array, sanitasi menjadi list ID integer
$peserta_csv = '';
if (is_array($peserta_ids) && count($peserta_ids) > 0) {
    // Konversi setiap value ke integer untuk mencegah injeksi
    $clean = array_map('intval', $peserta_ids);
    // Buang nilai yang bukan positif
    $clean = array_filter($clean, function($v) { return $v > 0; });
    // Gabungkan menjadi string CSV (mis. "1,2,3")
    $peserta_csv = implode(',', $clean);
} else {
    // Jika tidak ada peserta yang dipilih, fallback ambil semua user dengan role 'peserta'
    $stmtAll = $conn->prepare("SELECT id FROM users WHERE role = 'peserta'");
    $stmtAll->execute();
    $resAll = $stmtAll->get_result();
    $allIds = [];
    while ($row = $resAll->fetch_assoc()) {
        $allIds[] = $row['id'];
    }
    // Gabungkan semua ID peserta menjadi CSV
    $peserta_csv = implode(',', $allIds);
}

// Ensure data limits match database schema to prevent errors
// title varchar(50), peserta varchar(255)
if (strlen($judul) > 50) {
    $judul = substr($judul, 0, 50);
}
if (strlen($peserta_csv) > 255) {
    // If we have too many participants, we cannot save properly with current schema.
    // Return error instead of crashing or truncating corrupt data.
    echo json_encode(['success' => false, 'message' => 'Terlalu banyak peserta (max karakter 255). Hubungi admin untuk upgrade sistem.']);
    exit;
}

// ---------- Insert notulen ----------
// Siapa yang membuat notulen — ambil dari session (jika tersedia), fallback 'Admin'
$created_by = $_SESSION['user_name'] ?? 'Admin';

// Siapkan statement INSERT
$userId = (int) $_SESSION['user_id'];
$status = $_POST['status'] ?? 'draft'; 
$tempat = ''; 
// Legacy: 'tindak_lanjut' column was used for single file. We leave it empty now.
$legacyFileCol = ''; 

$stmt = $conn->prepare("INSERT INTO tambah_notulen (id_user, judul, tanggal, tempat, peserta, hasil, tindak_lanjut, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('isssssss', $userId, $judul, $tanggal, $tempat, $peserta_csv, $isi, $legacyFileCol, $status);

// Eksekusi dan berikan respons JSON sesuai hasil
if ($stmt->execute()) {
    $notulenId = $stmt->insert_id;
    
    // ---------- Handle Multiple Attachments (tb_lampiran) ----------
    $uploadErrors = [];
    
    if (isset($_FILES['file_lampiran']) && isset($_POST['judul_lampiran'])) {
        $files = $_FILES['file_lampiran'];
        $titles = $_POST['judul_lampiran'];
        $count = count($files['name']);
        
        // Prepare insert statement for lampiran
        $stmtLampiran = $conn->prepare("INSERT INTO tb_lampiran (id_notulen, judul_lampiran, file_lampiran) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $files['tmp_name'][$i];
                $originalName = basename($files['name'][$i]);
                $title = trim($titles[$i]);
                if (empty($title)) $title = $originalName; // Fallback title
                
                $safeName = time() . '_' . $i . '_' . preg_replace('/[^a-z0-9\-_.]/i', '_', $originalName);
                $dest = __DIR__ . '/../file/' . $safeName;
                
                if (move_uploaded_file($tmp, $dest)) {
                    $stmtLampiran->bind_param('iss', $notulenId, $title, $safeName);
                    $stmtLampiran->execute();
                } else {
                    $uploadErrors[] = "Gagal upload: $originalName";
                }
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Notulen berhasil disimpan', 'upload_errors' => $uploadErrors]);
} else {
    // Jika gagal, kembalikan pesan error (berisi $stmt->error)
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan notulen: ' . $stmt->error]);
}
exit;
?>
