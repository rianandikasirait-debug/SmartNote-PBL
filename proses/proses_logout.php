<?php
session_start(); 
// Mulai session agar bisa menghapusnya

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Hancurkan session di server
session_destroy();

// (Opsional) Kirim pesan agar login.php bisa menampilkan notifikasi
session_start();
$_SESSION['success_message'] = 'Anda berhasil logout.';

// Redirect ke halaman login
header("Location: ../login.php");
exit;
