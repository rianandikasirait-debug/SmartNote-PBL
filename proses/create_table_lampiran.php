<?php
require_once '../koneksi.php';

$sql = "CREATE TABLE IF NOT EXISTS tb_lampiran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_notulen INT NOT NULL,
    judul_lampiran VARCHAR(255) NOT NULL,
    file_lampiran VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_notulen) REFERENCES tambah_notulen(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table tb_lampiran created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>
