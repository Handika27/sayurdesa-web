<?php
require_once '../config/config.php';
require_admin();

$payment_settings = get_payment_settings();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        // Handle text settings
        $whatsapp_number = $_POST['whatsapp_number'] ?? '';
        $biaya_admin = floatval($_POST['biaya_admin'] ?? 0);
        $biaya_pengiriman = floatval($_POST['biaya_pengiriman'] ?? 0);
        
        if ($payment_settings) {
            $stmt = $pdo->prepare("UPDATE payment_settings SET whatsapp_number = ?, biaya_admin = ?, biaya_pengiriman = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$whatsapp_number, $biaya_admin, $biaya_pengiriman, $_SESSION['user_id'], $payment_settings['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO payment_settings (whatsapp_number, biaya_admin, biaya_pengiriman, updated_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$whatsapp_number, $biaya_admin, $biaya_pengiriman, $_SESSION['user_id']]);
        }
        
        log_activity('Update Pengaturan Pembayaran', 'Memperbarui pengaturan biaya dan nomor WhatsApp');
        $success = 'Pengaturan berhasil diupdate';
        
        // Refresh settings
        $payment_settings = get_payment_settings();
    }
    
    if (isset($_POST['update_qr'])) {
        if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $file_name = $_FILES['qr_code']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_size = $_FILES['qr_code']['size'];
            
            if (in_array($file_ext, $allowed)) {
                if ($file_size < 5 * 1024 * 1024) { // 5MB
                    $new_file_name = 'qr_code_' . time() . '.' . $file_ext;
                    $upload_dir = '../assets/images/payments/';
                    
                    // Delete old QR if exists
                    if ($payment_settings && $payment_settings['qr_code_image']) {
                        $old_file = $upload_dir . $payment_settings['qr_code_image'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $upload_dir . $new_file_name)) {
                        if ($payment_settings) {
                            $stmt = $pdo->prepare("UPDATE payment_settings SET qr_code_image = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
                            $stmt->execute([$new_file_name, $_SESSION['user_id'], $payment_settings['id']]);
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO payment_settings (qr_code_image, updated_by) VALUES (?, ?)");
                            $stmt->execute([$new_file_name, $_SESSION['user_id']]);
                        }
                        
                        log_activity('Update QR Code', 'Memperbarui QR code pembayaran');
                        $success = 'QR Code berhasil diupdate';
                        
                        // Refresh settings
                        $payment_settings = get_payment_settings();
                    } else {
                        $error = 'Gagal mengunggah file';
                    }
                } else {
                    $error = 'Ukuran file terlalu besar (max 5MB)';
                }
            } else {
                $error = 'Format file tidak diizinkan';
            }
        }
    }
}

$page_title = 'Pengaturan Pembayaran - SayurDesa Admin';
$active_menu = 'payments';

require_once 'header.php';
?>
        <h2 class="fw-bold mb-4">Pengaturan Pembayaran</h2>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4">Pengaturan Biaya</h5>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp Admin</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="<?php echo htmlspecialchars($payment_settings['whatsapp_number'] ?? ''); ?>" placeholder="Contoh: 6281392858421">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Biaya Admin</label>
                        <input type="number" name="biaya_admin" class="form-control" value="<?php echo htmlspecialchars($payment_settings['biaya_admin'] ?? 0); ?>" min="0" step="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Biaya Pengiriman</label>
                        <input type="number" name="biaya_pengiriman" class="form-control" value="<?php echo htmlspecialchars($payment_settings['biaya_pengiriman'] ?? 0); ?>" min="0" step="100">
                    </div>
                    <button type="submit" name="update_settings" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-4">QR Code QRIS</h5>
                
                <?php if ($payment_settings && $payment_settings['qr_code_image']): ?>
                    <div class="mb-4">
                        <img src="../assets/images/payments/<?php echo htmlspecialchars($payment_settings['qr_code_image']); ?>" alt="QR Code" class="img-thumbnail" style="max-width: 300px;">
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Upload QR Code Baru</label>
                        <input type="file" name="qr_code" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text">Format: JPG, PNG, WEBP | Max 5MB</div>
                    </div>
                    <button type="submit" name="update_qr" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Simpan QR Code
                    </button>
                </form>
            </div>
        </div>
<?php require_once 'footer.php'; ?>
