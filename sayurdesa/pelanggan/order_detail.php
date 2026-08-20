<?php
require_once '../config/config.php';
require_login();

if (is_admin() || is_super_admin()) {
    header('Location: ../index.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
$stmt->execute([$order_id]);
$payment = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_payment'])) {
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_name = $_FILES['payment_proof']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['payment_proof']['size'];
        
        if (in_array($file_ext, $allowed)) {
            if ($file_size < 5 * 1024 * 1024) { // 5MB
                $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                $upload_dir = '../assets/images/payments/';
                
                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_dir . $new_file_name)) {
                    $stmt = $pdo->prepare("UPDATE payments SET payment_proof = ? WHERE id = ?");
                    $stmt->execute([$new_file_name, $payment['id']]);
                    log_activity('Upload Bukti Pembayaran', 'Mengunggah bukti pembayaran untuk pesanan: ' . $order['invoice_number']);
                    $success = 'Bukti pembayaran berhasil diunggah';
                    
                    // Refresh payment data
                    $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    $payment = $stmt->fetch();
                } else {
                    $error = 'Gagal mengunggah file';
                }
            } else {
                $error = 'Ukuran file terlalu besar (max 5MB)';
            }
        } else {
            $error = 'Format file tidak diizinkan (hanya JPG/PNG)';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - SayurDesa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-success bg-success shadow-sm">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="../index.php">
                <i class="fas fa-leaf me-2"></i>SayurDesa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../products.php">Belanja</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger"><?php echo get_cart_count(); ?></span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                            <li><a class="dropdown-item" href="orders.php">Riwayat Pesanan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="mb-4">
            <a href="orders.php" class="text-success text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if (isset($_GET['new']) && $_GET['new'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> Pesanan Anda berhasil dibuat!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

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

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-file-invoice text-success me-2"></i>
                            Detail Pesanan
                        </h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">No. Invoice</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($order['invoice_number']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Tanggal Pesanan</p>
                                <p class="fw-bold"><?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Status Pesanan</p>
                                <span class="badge <?php 
                                    switch($order['status']) {
                                        case 'Pending': echo 'bg-warning text-dark'; break;
                                        case 'Diproses': echo 'bg-info text-dark'; break;
                                        case 'Dikirim': echo 'bg-primary'; break;
                                        case 'Selesai': echo 'bg-success'; break;
                                        case 'Dibatalkan': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary';
                                    }
                                ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Status Pembayaran</p>
                                <span class="badge <?php 
                                    switch($payment['payment_status']) {
                                        case 'Pending': echo 'bg-warning text-dark'; break;
                                        case 'Dibayar': echo 'bg-success'; break;
                                        case 'Gagal': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary';
                                    }
                                ?>"><?php echo htmlspecialchars($payment['payment_status']); ?></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Alamat Pengiriman</p>
                            <p class="fw-bold"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                        </div>
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Metode Pembayaran</p>
                            <p class="fw-bold"><?php echo htmlspecialchars($payment['payment_method']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="fas fa-shopping-bag text-success me-2"></i>Produk Dipesan</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td><?php echo format_currency($item['price']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo format_currency($item['price'] * $item['quantity']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span class="fw-bold"><?php echo format_currency($order['total_amount']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Biaya Admin</span>
                            <span class="fw-bold"><?php echo format_currency($order['biaya_admin'] ?? 0); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Biaya Pengiriman</span>
                            <span class="fw-bold"><?php echo format_currency($order['biaya_pengiriman'] ?? 0); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5 text-success"><?php echo format_currency($order['grand_total'] ?? $order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($payment['payment_status'] == 'Pending' && !$payment['payment_proof']): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3"><i class="fas fa-upload text-success me-2"></i>Unggah Bukti Pembayaran</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png" required>
                                    <div class="form-text">Format: JPG/PNG, Max 5MB</div>
                                </div>
                                <button type="submit" name="upload_payment" class="btn btn-success">
                                    <i class="fas fa-upload me-2"></i>Unggah Bukti
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif ($payment['payment_proof']): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3"><i class="fas fa-image text-success me-2"></i>Bukti Pembayaran</h5>
                            <img src="../assets/images/payments/<?php echo htmlspecialchars($payment['payment_proof']); ?>" alt="Bukti Pembayaran" class="img-fluid img-thumbnail">
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <a href="https://wa.me/6281392858421?text=Halo, saya ingin menanyakan tentang pesanan <?php echo urlencode($order['invoice_number']); ?>" target="_blank" class="btn btn-success w-100">
                                <i class="fab fa-whatsapp me-2"></i>Hubungi Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
