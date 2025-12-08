<?php
session_start();
// Mulai session untuk akses data session (mis. user_name)

// Sertakan file koneksi database ($conn diasumsikan tersedia)
require_once __DIR__ . '/../koneksi.php';

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

// ---------- Handle file upload (optional) ----------
// Variabel untuk menyimpan nama file yang diunggah (jika ada)
$uploadedFileName = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Ambil temporary file path dan nama file asli
    $tmp = $_FILES['file']['tmp_name'];
    $originalName = basename($_FILES['file']['name']);
    // Sanitasi nama file: ganti karakter tidak diizinkan agar aman
    $safeName = time() . '_' . preg_replace('/[^a-z0-9\-_.]/i', '_', $originalName);
    // Tentukan path tujuan penyimpanan file (folder ../file/)
    $dest = __DIR__ . '/../file/' . $safeName;
    // Pindahkan file dari temp ke tujuan
    if (move_uploaded_file($tmp, $dest)) {
        $uploadedFileName = $safeName; // simpan nama file untuk disimpan di DB
    } else {
        // Jika gagal memindahkan file, kembalikan error ke client
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file']);
        exit;
    }
}

// ---------- Prepare peserta CSV ----------
// Jika peserta dikirim sebagai array, sanitasi menjadi list ID integer
$peserta_csv = '';
if (is_array($peserta_ids) && count($peserta_ids) > 0) {
    // Konversi setiap value ke integer untuk mencegah injeksi
    $clean = array_map('intval', $peserta_ids);
    // Buang nilai yang bukan positif
    $clean = array_filter($clean, fn($v) => $v > 0);
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

// ---------- Insert notulen ----------
// Siapa yang membuat notulen — ambil dari session (jika tersedia), fallback 'Admin'
$created_by = $_SESSION['user_name'] ?? 'Admin';

// Siapkan statement INSERT dengan prepared statement untuk menghindari SQL injection
$stmt = $conn->prepare("INSERT INTO tambah_notulen (judul_rapat, tanggal_rapat, isi_rapat, Lampiran, peserta, created_by) VALUES (?, ?, ?, ?, ?, ?)");
// Bind parameter (semua string di sini)
$stmt->bind_param('ssssss', $judul, $tanggal, $isi, $uploadedFileName, $peserta_csv, $created_by);

// Eksekusi dan berikan respons JSON sesuai hasil
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notulen berhasil disimpan']);
} else {
    // Jika gagal, kembalikan pesan error (berisi $stmt->error)
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
}
exit;
?>
