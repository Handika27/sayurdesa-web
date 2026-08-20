<?php
// Temporarily disable session since we might run this via browser or CLI
$originalSessionStatus = session_status();
if ($originalSessionStatus !== PHP_SESSION_NONE) {
    session_abort();
}

require_once 'config/config.php';

try {
    // 1. Update enum role
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'pelanggan') DEFAULT 'pelanggan'");
    
    // 2. Add is_active column
    $pdo->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER role");
    
    // 3. Add profile_picture column if not exists
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL AFTER phone");
    } catch (PDOException $e) {
        // Ignore if column already exists
        if (strpos($e->getMessage(), 'Duplicate column name') === false) {
            throw $e;
        }
    }
    
    // 4. Create activity_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(255) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // 5. Create payment_settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        qr_code_image VARCHAR(255) NULL,
        updated_by INT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 6. Update payments table
    try {
        $pdo->exec("ALTER TABLE payments ADD COLUMN payment_proof VARCHAR(255) NULL AFTER payment_status");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) throw $e;
    }
    try {
        $pdo->exec("ALTER TABLE payments ADD COLUMN verified_by INT NULL AFTER payment_proof");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) throw $e;
    }
    try {
        $pdo->exec("ALTER TABLE payments ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) throw $e;
    }
    
    // 7. Add invoice_number to orders
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN invoice_number VARCHAR(100) UNIQUE NULL AFTER id");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') === false) throw $e;
    }
    
    // 8. Insert Super Admin if not exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'superadmin@sayurdesa.com'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hashedPassword = password_hash('superadmin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, is_active) VALUES ('Super Admin', 'superadmin@sayurdesa.com', ?, '081234567890', 'super_admin', TRUE)");
        $stmt->execute([$hashedPassword]);
    }
    
    // Insert default payment settings if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM payment_settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO payment_settings (qr_code_image) VALUES (NULL)");
    }
    
    echo "Database berhasil di-upgrade!";
    echo "<br><br>";
    echo "Akun Super Admin:";
    echo "<br>Email: superadmin@sayurdesa.com";
    echo "<br>Password: superadmin123";
    echo "<br><br><a href='index.php'>Kembali ke Halaman Utama</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>