<?php
require_once '../config/config.php';
require_login();

// Validasi role hanya pelanggan
if (is_admin() || is_super_admin()) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get summary data
$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE user_id = ? AND status = 'Pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetch()['pending_orders'];

// Get recent orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll();

// Get recommended products
$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY RAND() LIMIT 4");
$recommended_products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SayurDesa</title>
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
                        <a class="nav-link text-white active" href="dashboard.php">Dashboard</a>
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
                            <?php
                            $nav_user_id = $_SESSION['user_id'];
                            $nav_stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                            $nav_stmt->execute([$nav_user_id]);
                            $nav_user = $nav_stmt->fetch();
                            $nav_profile_img = '../assets/images/products/no-image.svg';
                            if ($nav_user['profile_picture']) {
                                $nav_img_path = '../assets/images/profile/' . $nav_user['profile_picture'];
                                if (file_exists($nav_img_path)) {
                                    $nav_profile_img = $nav_img_path;
                                }
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($nav_profile_img); ?>" alt="Profile" class="rounded-circle border-2 border-white" style="width: 30px; height: 30px; object-fit: cover; margin-right: 8px;">
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
        <h2 class="fw-bold mb-4">Selamat Datang, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>

        <!-- Summary Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card shadow-sm border-start border-5 border-success">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-shopping-bag text-success me-2"></i>Total Pesanan</h5>
                        <h2 class="fw-bold text-success"><?php echo $total_orders; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-start border-5 border-warning">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-clock text-warning me-2"></i>Pesanan Pending</h5>
                        <h2 class="fw-bold text-warning"><?php echo $pending_orders; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-list text-success me-2"></i>Pesanan Terbaru</h5>
                    <a href="orders.php" class="text-success">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_orders) > 0): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['invoice_number'] ?? '-'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo format_currency($order['total_amount']); ?></td>
                                        <td>
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
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada pesanan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recommended Products -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="fas fa-star text-success me-2"></i>Produk Rekomendasi</h5>
                <div class="row g-4">
                    <?php foreach ($recommended_products as $product): ?>
                        <div class="col-md-3 col-6">
                            <div class="card h-100">
                                <img src="<?php 
                                    $img_src = '../assets/images/products/no-image.svg';
                                    if ($product['image']) {
                                        $img_path = '../assets/images/products/' . $product['image'];
                                        if (file_exists($img_path)) {
                                            $img_src = $img_path;
                                        }
                                    }
                                    echo htmlspecialchars($img_src);
                                ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                                    <p class="text-success fw-bold"><?php echo format_currency($product['price']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
