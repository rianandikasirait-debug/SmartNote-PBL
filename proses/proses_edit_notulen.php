<?php
session_start(); // Mulai session — memastikan variabel session tersedia
require_once __DIR__ . '/../koneksi.php'; // Sertakan koneksi database (variabel $conn diasumsikan ada)

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Jika tidak login atau bukan admin -> arahkan ke halaman login
    header("Location: ../login.php");
    exit;
}

// Pastikan request method POST, jika bukan -> kembalikan ke dashboard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/dashboard_admin.php");
    exit;
}

// Ambil dan sanitasi input dasar
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0; // cast ke int, jika tidak ada jadi 0
$judul = trim($_POST['judul']); // trim spasi
$tanggal = $_POST['tanggal']; // diharapkan format YYYY-MM-DD dari input date
$isi = $_POST['isi'];
$peserta_arr = isset($_POST['peserta']) ? $_POST['peserta'] : []; // peserta dikirim sebagai array
$peserta_str = implode(', ', $peserta_arr); // gabung jadi string dipisah koma

// Validasi sederhana: cek field wajib
if ($id <= 0 || empty($judul) || empty($tanggal) || empty($isi)) {
    // Jika data tidak lengkap -> kasih alert dan kembali
    echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
    exit;
}

// Cek apakah ada upload lampiran baru
$lampiran_baru = null;
if (!empty($_FILES['lampiran']['name'])) {
    $file = $_FILES['lampiran'];
    // Tipe file yang diizinkan (cek menggunakan MIME dari client — ini tidak 100% aman)
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];

    if (!in_array($file['type'], $allowed_types)) {
        // Jika tipe tidak diizinkan -> tolak
        echo "<script>alert('Format file tidak didukung!'); window.history.back();</script>";
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // batas 5MB
        echo "<script>alert('Ukuran file terlalu besar (Maks 5MB)!'); window.history.back();</script>";
        exit;
    }

    // Buat nama file aman: waktu + nama file yang di-filter karakter aneh
    $namaFile = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($file['name']));
    $target_dir = __DIR__ . '/../file/';

    // Pindahkan file dari temp ke folder tujuan
    if (move_uploaded_file($file['tmp_name'], $target_dir . $namaFile)) {
        $lampiran_baru = $namaFile;
        // CATATAN: File lama TIDAK dihapus, file baru DITAMBAHKAN ke kolom Lampiran
    } else {
        // Jika gagal memindahkan file -> beri peringatan
        echo "<script>alert('Gagal upload file!'); window.history.back();</script>";
        exit;
    }
}

// Update Database — pilih query tergantung ada lampiran baru atau tidak
if ($lampiran_baru) {
    // Jika ada file baru, tambahkan ke kolom Lampiran (concatenate dengan delimiter |)
    $sql = "UPDATE tambah_notulen SET judul_rapat=?, tanggal_rapat=?, isi_rapat=?, peserta=?, Lampiran=CONCAT(IFNULL(Lampiran, ''), IF(Lampiran IS NOT NULL AND Lampiran != '', '|', ''), ?) WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $judul, $tanggal, $isi, $peserta_str, $lampiran_baru, $id);
} else {
    $sql = "UPDATE tambah_notulen SET judul_rapat=?, tanggal_rapat=?, isi_rapat=?, peserta=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $judul, $tanggal, $isi, $peserta_str, $id);
}

// Eksekusi statement dan beri umpan balik
if ($stmt->execute()) {
    echo "<script>alert('Notulen berhasil diperbarui!'); window.location.href='../admin/dashboard_admin.php';</script>";
} else {
    // Tampilkan error dari statement (perlu hati-hati saat menampilkan langsung ke user)
    echo "<script>alert('Gagal memperbarui notulen: " . $stmt->error . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
