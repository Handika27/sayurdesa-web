USE sayurdesa;

-- Ubah enum role untuk menambahkan super_admin
ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'pelanggan') DEFAULT 'pelanggan';

-- Tambahkan kolom status aktif/nonaktif
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER role;

-- Tambahkan kolom profile_picture jika belum ada
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) NULL AFTER phone;

-- Tabel untuk log aktivitas pengguna
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel untuk pengaturan pembayaran (QR Code)
CREATE TABLE IF NOT EXISTS payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qr_code_image VARCHAR(255) NULL,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tambahkan kolom bukti pembayaran dan validasi di tabel payments
ALTER TABLE payments ADD COLUMN payment_proof VARCHAR(255) NULL AFTER payment_status;
ALTER TABLE payments ADD COLUMN verified_by INT NULL AFTER payment_proof;
ALTER TABLE payments ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by;

-- Tambahkan kolom nomor invoice unik di tabel orders
ALTER TABLE orders ADD COLUMN invoice_number VARCHAR(100) UNIQUE NULL AFTER id;

-- Masukkan Super Admin default (password: superadmin123)
INSERT INTO users (name, email, password, phone, role, is_active) VALUES
('Super Admin', 'superadmin@sayurdesa.com', '$2y$10$7lZ5xQ566d96JQw4h1jJ0e8rY3tU5/I7V6Y9X0Z1W2Q3E4R5T6Y7', '081234567890', 'super_admin', TRUE)
ON DUPLICATE KEY UPDATE email=email;
