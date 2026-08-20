<?php
require_once 'config/config.php';

try {
    echo "<h2>Memulai Update Database...</h2>";
    
    // 0. Buat tabel banners (jika belum ada)
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS banners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                image VARCHAR(255),
                title VARCHAR(255),
                description TEXT,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel banners dibuat</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel banners sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0a. Buat tabel carts (jika belum ada)
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS carts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel carts dibuat</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel carts sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0b. Buat tabel cart_items (jika belum ada)
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS cart_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cart_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel cart_items dibuat</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel cart_items sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0c. Buat tabel addresses (jika belum ada)
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS addresses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                address TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel addresses dibuat</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel addresses sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0d. Buat tabel activity_logs (jika belum ada)
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(255) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel activity_logs dibuat</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel activity_logs sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0b. Buat tabel payment_settings (jika belum ada)
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS payment_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                qr_image VARCHAR(255),
                whatsapp_number VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel payment_settings dibuat</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel payment_settings sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0c. Tambahkan kolom invoice_number di tabel orders (jika belum ada)
    try {
        $stmt = $pdo->prepare("ALTER TABLE orders ADD COLUMN invoice_number VARCHAR(50) NULL AFTER id");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom invoice_number ditambahkan ke tabel orders</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom invoice_number di tabel orders sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0d. Tambahkan kolom payment_proof, verified_by, verified_at di tabel payments (jika belum ada)
    try {
        $stmt = $pdo->prepare("ALTER TABLE payments ADD COLUMN payment_proof VARCHAR(255) NULL AFTER payment_method");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom payment_proof ditambahkan ke tabel payments</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom payment_proof sudah ada: " . $e->getMessage() . "</p>";
    }
    
    try {
        $stmt = $pdo->prepare("ALTER TABLE payments ADD COLUMN verified_by INT NULL AFTER payment_proof");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom verified_by ditambahkan ke tabel payments</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom verified_by sudah ada: " . $e->getMessage() . "</p>";
    }
    
    try {
        $stmt = $pdo->prepare("ALTER TABLE payments ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom verified_at ditambahkan ke tabel payments</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom verified_at sudah ada: " . $e->getMessage() . "</p>";
    }
    
    // 0e. Tambahkan kolom is_active di tabel users (jika belum ada)
    try {
        $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER role");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom is_active ditambahkan ke tabel users</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom is_active sudah ada: " . $e->getMessage() . "</p>";
    }

    // 1. Tambahkan kolom admin_id ke tabel orders
    try {
        $stmt = $pdo->prepare("ALTER TABLE orders ADD COLUMN admin_id INT NULL AFTER user_id");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom admin_id ditambahkan ke tabel orders</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom admin_id di tabel orders sudah ada atau tidak dapat ditambahkan: " . $e->getMessage() . "</p>";
    }

    // 2. Tambahkan kolom admin_id ke tabel products
    try {
        $stmt = $pdo->prepare("ALTER TABLE products ADD COLUMN admin_id INT NULL AFTER category_id");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom admin_id ditambahkan ke tabel products</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom admin_id di tabel products sudah ada atau tidak dapat ditambahkan: " . $e->getMessage() . "</p>";
    }

    // 3. Set admin_id default untuk data yang sudah ada (assign ke admin pertama)
    $stmt = $pdo->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin') ORDER BY id LIMIT 1");
    $first_admin = $stmt->fetch();
    if ($first_admin) {
        $admin_id = $first_admin['id'];
        
        $stmt = $pdo->prepare("UPDATE orders SET admin_id = ? WHERE admin_id IS NULL");
        $stmt->execute([$admin_id]);
        
        $stmt = $pdo->prepare("UPDATE products SET admin_id = ? WHERE admin_id IS NULL");
        $stmt->execute([$admin_id]);
        
        echo "<p style='color: green;'>✅ Data lama di-assign ke admin dengan ID: " . $admin_id . "</p>";
    }

    echo "<br><h3 style='color: green;'>🎉 Database berhasil diperbarui!</h3>";
    echo "<p><a href='index.php'>Klik di sini untuk kembali ke halaman utama</a></p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
}
?>