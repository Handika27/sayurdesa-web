<?php
require_once 'config/config.php';
require_login();

// Validasi: hanya pelanggan yang bisa akses halaman ini
if (is_admin() || is_super_admin()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT ci.*, p.name, p.price, p.stock FROM cart_items ci JOIN carts c ON ci.cart_id = c.id JOIN products p ON ci.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) == 0) {
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

// Hardcode biaya tetap sementara
$biaya_admin = 500;
$biaya_pengiriman = 2000;
$grand_total = $total + $biaya_admin + $biaya_pengiriman;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $shipping_address = $_POST['shipping_address'];
    $payment_method = $_POST['payment_method'];
    
    $pdo->beginTransaction();
    
    try {
        // Generate invoice number
        $invoice_number = generate_invoice_number();
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM orders WHERE invoice_number = ?");
            $stmt->execute([$invoice_number]);
            if (!$stmt->fetch()) break;
            $invoice_number = generate_invoice_number();
        }
        
        // Assign admin (ambil admin pertama untuk pesanan checkout, atau bisa diatur sesuai kebutuhan)
        $stmt_admin = $pdo->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin') ORDER BY id LIMIT 1");
        $admin_data = $stmt_admin->fetch();
        $admin_id = $admin_data['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, invoice_number, total_amount, biaya_admin, biaya_pengiriman, grand_total, shipping_address, status, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
        $stmt->execute([$user_id, $invoice_number, $total, $biaya_admin, $biaya_pengiriman, $grand_total, $shipping_address, $admin_id]);
        $order_id = $pdo->lastInsertId();
        
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
            
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, payment_status) VALUES (?, ?, 'Pending')");
        $stmt->execute([$order_id, $payment_method]);
        
        $stmt = $pdo->prepare("DELETE ci FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        
        log_activity('Checkout', 'Membuat pesanan baru: ' . $invoice_number);
        
        $pdo->commit();
        
        header('Location: pelanggan/order_detail.php?id=' . $order_id . '&new=1');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - SayurDesa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-success bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="index.php">
                <i class="fas fa-leaf me-2"></i>SayurDesa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="products.php">Produk</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <?php if (!is_admin() && !is_super_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="cart.php">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span class="badge bg-danger"><?php echo get_cart_count(); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php
                                $nav_user_id = $_SESSION['user_id'];
                                $nav_stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                                $nav_stmt->execute([$nav_user_id]);
                                $nav_user = $nav_stmt->fetch();
                                $nav_profile_img = 'assets/images/products/no-image.svg';
                                if ($nav_user['profile_picture']) {
                                    $nav_img_path = 'assets/images/profile/' . $nav_user['profile_picture'];
                                    if (file_exists($nav_img_path)) {
                                        $nav_profile_img = $nav_img_path;
                                    }
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($nav_profile_img); ?>" alt="Profile" class="rounded-circle border-2 border-white" style="width: 30px; height: 30px; object-fit: cover; margin-right: 8px;">
                                <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (!is_admin() && !is_super_admin()): ?>
                                    <li><a class="dropdown-item" href="pelanggan/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="pelanggan/profile.php">Profil</a></li>
                                    <li><a class="dropdown-item" href="pelanggan/orders.php">Riwayat Pesanan</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="fw-bold text-success">Checkout</h2>
            </div>
        </div>

        <?php if (isset($error) && !empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3"><i class="fas fa-map-marker-alt text-success me-2"></i>Alamat Pengiriman</h5>
                            <div class="mb-3">
                                <textarea name="shipping_address" class="form-control" rows="4" placeholder="Masukkan alamat lengkap pengiriman..." required><?php 
                                    if (count($addresses) > 0) {
                                        echo htmlspecialchars($addresses[0]['address']);
                                    }
                                ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3"><i class="fas fa-credit-card text-success me-2"></i>Metode Pembayaran</h5>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                                <label class="form-check-label" for="cod">
                                    <i class="fas fa-hand-holding-usd me-2"></i>Bayar di Tempat (COD)
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="transfer" value="Transfer Bank">
                                <label class="form-check-label" for="transfer">
                                    <i class="fas fa-university me-2"></i>Transfer Bank
                                </label>
                                <div class="mt-2 ms-4 text-muted small">
                                    <p>Bank: BCA</p>
                                    <p>No. Rekening: 1234567890</p>
                                    <p>Atas Nama: SayurDesa</p>
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="qris" value="QRIS">
                                <label class="form-check-label" for="qris">
                                    <i class="fas fa-qrcode me-2"></i>QRIS
                                </label>
                                <?php if ($payment_settings && $payment_settings['qr_code_image']): ?>
                                    <div class="mt-3 ms-4">
                                        <img src="assets/images/payments/<?php echo htmlspecialchars($payment_settings['qr_code_image']); ?>" alt="QRIS" class="img-thumbnail" style="max-width: 300px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-4">Ringkasan Pesanan</h5>
                            
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                                    <span><?php echo format_currency($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span class="fw-bold"><?php echo format_currency($total); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Biaya Admin</span>
                                <span class="fw-bold"><?php echo format_currency($biaya_admin); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Biaya Pengiriman</span>
                                <span class="fw-bold"><?php echo format_currency($biaya_pengiriman); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold fs-5">Total</span>
                                <span class="fw-bold fs-5 text-success"><?php echo format_currency($grand_total); ?></span>
                            </div>
                            
                            <button type="submit" name="checkout" class="btn btn-success w-100 py-3 fw-bold">Konfirmasi Pesanan</button>
                            <a href="cart.php" class="btn btn-outline-success w-100 py-2 mt-2">Kembali ke Keranjang</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h6 class="fw-bold"><i class="fas fa-leaf text-success me-2"></i>SayurDesa</h6>
                    <p>Penyedia sayuran segar berkualitas langsung dari petani lokal.</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Kontak</h6>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-success"></i> Jl. Sayuran No. 123, Purwokerto</p>
                    <p class="mb-1"><i class="fas fa-phone-alt me-2 text-success"></i> +62 813-9285-8421</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Ikuti Kami</h6>
                    <a href="#" class="me-2"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-instagram fa-2x"></i></a>
                </div>
            </div>
            <hr class="bg-secondary mt-4 mb-3">
            <div class="text-center">
                <p>&copy; 2026 SayurDesa. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
