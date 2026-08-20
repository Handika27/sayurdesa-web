<?php
require_once '../config/config.php';
require_login();

// Validasi: hanya pelanggan yang bisa akses halaman ini
if (is_admin()) {
    header('Location: ../admin/index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT o.*, p.payment_method, p.payment_status FROM orders o LEFT JOIN payments p ON o.id = p.order_id WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - SayurDesa</title>
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
                        <a class="nav-link text-white" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../about.php">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../products.php">Produk</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <?php if (!is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="../cart.php">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span class="badge bg-danger"><?php echo get_cart_count(); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php
                                // Get user data for profile picture
                                $nav_user_id = $_SESSION['user_id'];
                                $nav_stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                                $nav_stmt->execute([$nav_user_id]);
                                $nav_user = $nav_stmt->fetch();
                                $nav_profile_img = '../assets/images/products/no-image.svg'; // Default
                                if ($nav_user['profile_picture']) {
                                    $nav_img_path = '../assets/images/profile/' . $nav_user['profile_picture'];
                                    if (file_exists($nav_img_path)) {
                                        $nav_profile_img = $nav_img_path;
                                    }
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($nav_profile_img); ?>" alt="Profile" class="rounded-circle border-2 border-white" style="width: 30px; height: 30px; object-fit: cover; margin-right: 8px;">
                                <?php echo $_SESSION['name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (!is_admin()): ?>
                                    <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                    <li><a class="dropdown-item active" href="orders.php">Riwayat Pesanan</a></li>
                                <?php endif; ?>
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="../admin/index.php">Dashboard Admin</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-success ms-2" href="../register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="fw-bold text-success">Riwayat Pesanan</h1>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                Pesanan Anda berhasil dibuat!
            </div>
        <?php endif; ?>

        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-0">Order #<?php echo $order['id']; ?></h5>
                                <small class="text-muted"><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div class="col-md-6 text-end">
                                <?php
                                $status_class = '';
                                switch ($order['status']) {
                                    case 'Pending':
                                        $status_class = 'bg-warning text-dark';
                                        break;
                                    case 'Diproses':
                                        $status_class = 'bg-info text-dark';
                                        break;
                                    case 'Dikirim':
                                        $status_class = 'bg-primary';
                                        break;
                                    case 'Selesai':
                                        $status_class = 'bg-success';
                                        break;
                                    case 'Dibatalkan':
                                        $status_class = 'bg-danger';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?> py-2 px-3"><?php echo $order['status']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                        $stmt->execute([$order['id']]);
                        $order_items = $stmt->fetchAll();
                        ?>
                        
                        <?php foreach ($order_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span class="fw-bold"><?php echo format_currency($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1 text-muted"><i class="fas fa-credit-card me-2"></i>Pembayaran: <?php echo $order['payment_method'] ?? '-'; ?></p>
                                    <p class="mb-0">
                                        <span class="badge <?php 
                                            switch($order['payment_status']) {
                                                case 'Pending': echo 'bg-warning text-dark'; break;
                                                case 'Menunggu Verifikasi': echo 'bg-info text-dark'; break;
                                                case 'Lunas': echo 'bg-success'; break;
                                                case 'Ditolak': echo 'bg-danger'; break;
                                                case 'Refund': echo 'bg-secondary'; break;
                                                default: echo 'bg-secondary';
                                            }
                                        ?>"><?php echo htmlspecialchars($order['payment_status'] ?? '-'); ?></span>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <p class="fw-bold fs-5 text-success mb-0">Total: <?php echo format_currency($order['total_amount']); ?></p>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-success mt-2">
                                        <i class="fas fa-eye me-1"></i>Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-clipboard-list text-muted" style="font-size: 80px;"></i>
                    <h4 class="mt-3 text-muted">Belum Ada Pesanan</h4>
                    <p class="text-muted">Silakan belanja terlebih dahulu</p>
                    <a href="../products.php" class="btn btn-success mt-3">Belanja Sekarang</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h6 class="fw-bold"><i class="fas fa-leaf text-success me-2"></i>SayurDesa</h6>
                    <p>Penyedia sayuran segar berkualitas langsung dari petani lokal.</p>
                    <div class="social-links-footer">
                        <a href="#"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Kontak</h6>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-success"></i> Jl. Sayuran No. 123, Purwokerto, Jawa Tengah</p>
                    <p class="mb-1"><i class="fas fa-phone-alt me-2 text-success"></i> <a href="https://wa.me/6281392858421" target="_blank" class="text-decoration-none hover-text-success">+62 813-9285-8421</a></p>
                    <p><i class="fas fa-envelope me-2 text-success"></i> support@sayurdesa.com</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Ikuti Kami</h6>
                    <a href="#" class="me-2"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-instagram fa-2x"></i></a>
                    <a href="#"><i class="fab fa-whatsapp fa-2x"></i></a>
                </div>
            </div>
            <hr class="bg-secondary mt-4 mb-3">
            <div class="text-center">
                <p>&copy; 2026 SayurDesa. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
