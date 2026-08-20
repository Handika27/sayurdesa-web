<?php
require_once 'config/config.php';

try {
    echo "<h2 style='text-align: center;'>Memulai Migrasi Database Final...</h2>";
    echo "<div style='max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>";

    // ------------------------------
    // 1. Buat tabel categories (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel categories siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel categories sudah ada</p>";
    }

    // ------------------------------
    // 2. Buat tabel products (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                category_id INT,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                image VARCHAR(255),
                harvest_date DATE,
                vitamin VARCHAR(255),
                iron DECIMAL(10,2),
                carbon_saving DECIMAL(10,2),
                health_benefits TEXT,
                is_active TINYINT(1) DEFAULT 1,
                admin_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel products siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel products sudah ada</p>";
    }

    // ------------------------------
    // 3. Tambahkan kolom admin_id di products (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("ALTER TABLE products ADD COLUMN admin_id INT NULL AFTER is_active");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom admin_id ditambahkan di products</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom admin_id di products sudah ada</p>";
    }

    // ------------------------------
    // 4. Buat tabel banners (jika belum ada)
    // ------------------------------
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
        echo "<p style='color: green;'>✅ Tabel banners siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel banners sudah ada</p>";
    }

    // ------------------------------
    // 5. Buat tabel carts (jika belum ada)
    // ------------------------------
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
        echo "<p style='color: green;'>✅ Tabel carts siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel carts sudah ada</p>";
    }

    // ------------------------------
    // 6. Buat tabel cart_items (jika belum ada)
    // ------------------------------
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
        echo "<p style='color: green;'>✅ Tabel cart_items siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel cart_items sudah ada</p>";
    }

    // ------------------------------
    // 7. Buat tabel orders (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                invoice_number VARCHAR(50),
                user_id INT NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                shipping_address TEXT,
                status VARCHAR(50) DEFAULT 'Pending',
                admin_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel orders siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel orders sudah ada</p>";
    }

    // ------------------------------
    // 8. Tambahkan kolom admin_id di orders (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("ALTER TABLE orders ADD COLUMN admin_id INT NULL AFTER user_id");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom admin_id ditambahkan di orders</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom admin_id di orders sudah ada</p>";
    }

    // ------------------------------
    // 9. Buat tabel order_items (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(255),
                price DECIMAL(10,2) NOT NULL,
                quantity INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel order_items siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel order_items sudah ada</p>";
    }

    // ------------------------------
    // 10. Buat tabel payments (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("
            CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                payment_method VARCHAR(255),
                payment_status VARCHAR(50) DEFAULT 'Pending',
                payment_proof VARCHAR(255),
                verified_by INT,
                verified_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Tabel payments siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel payments sudah ada</p>";
    }

    // ------------------------------
    // 11. Buat tabel addresses (jika belum ada)
    // ------------------------------
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
        echo "<p style='color: green;'>✅ Tabel addresses siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel addresses sudah ada</p>";
    }

    // ------------------------------
    // 12. Buat tabel activity_logs (jika belum ada)
    // ------------------------------
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
        echo "<p style='color: green;'>✅ Tabel activity_logs siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel activity_logs sudah ada</p>";
    }

    // ------------------------------
    // 13. Buat tabel payment_settings (jika belum ada)
    // ------------------------------
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
        echo "<p style='color: green;'>✅ Tabel payment_settings siap</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Tabel payment_settings sudah ada</p>";
    }

    // ------------------------------
    // 14. Tambahkan kolom is_active di users (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER role");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom is_active ditambahkan di users</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom is_active di users sudah ada</p>";
    }

    // ------------------------------
    // 15. Tambahkan kolom profile_picture di users (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->prepare("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL AFTER phone");
        $stmt->execute();
        echo "<p style='color: green;'>✅ Kolom profile_picture ditambahkan di users</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Kolom profile_picture di users sudah ada</p>";
    }

    // ------------------------------
    // 16. Assign admin_id ke data lama (jika belum ada)
    // ------------------------------
    try {
        $stmt = $pdo->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin') ORDER BY id LIMIT 1");
        $first_admin = $stmt->fetch();
        if ($first_admin) {
            $admin_id = $first_admin['id'];

            $stmt = $pdo->prepare("UPDATE orders SET admin_id = ? WHERE admin_id IS NULL");
            $stmt->execute([$admin_id]);

            $stmt = $pdo->prepare("UPDATE products SET admin_id = ? WHERE admin_id IS NULL");
            $stmt->execute([$admin_id]);

            echo "<p style='color: green;'>✅ Data lama di-assign ke admin dengan ID: $admin_id</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Data lama sudah di-assign</p>";
    }

    echo "<br><h3 style='color: green; text-align: center;'>🎉 Migrasi Database Selesai! Proyek Siap Digunakan!</h3>";
    echo "<p style='text-align: center;'><a href='index.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Halaman Utama</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>";
    echo "<h3 style='color: red;'>❌ Error: " . $e->getMessage() . "</h3>";
    echo "</div>";
}
?>