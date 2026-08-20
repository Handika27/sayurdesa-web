<?php
require_once 'config/config.php';

try {
    // Cek apakah kolom profile_picture sudah ada
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        // Tambahkan kolom profile_picture
        $sql = "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL DEFAULT NULL AFTER phone";
        $pdo->exec($sql);
        echo "Kolom profile_picture berhasil ditambahkan ke tabel users!";
    } else {
        echo "Kolom profile_picture sudah ada di tabel users!";
    }
} catch (PDOException $e) {
    echo "Gagal menambahkan kolom: " . $e->getMessage();
}
?>
