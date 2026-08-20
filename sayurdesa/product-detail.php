<?php
require_once 'config/config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && is_logged_in()) {
    // Validasi: hanya pelanggan yang bisa menambah ke keranjang
    if (is_admin()) {
        header('Location: admin/index.php');
        exit;
    }
    
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();
    
    if (!$cart) {
        $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    } else {
        $cart_id = $cart['id'];
    }
    
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $cart_item = $stmt->fetch();
    
    if ($cart_item) {
        $new_quantity = $cart_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $cart_item['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$cart_id, $product_id, $quantity]);
    }
    
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - SayurDesa</title>
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
                        <a class="nav-link text-white" href="about.php">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="products.php">Produk</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <?php if (!is_admin()): ?>
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
                                // Get user data for profile picture
                                $nav_user_id = $_SESSION['user_id'];
                                $nav_stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                                $nav_stmt->execute([$nav_user_id]);
                                $nav_user = $nav_stmt->fetch();
                                $nav_profile_img = 'assets/images/products/no-image.svg'; // Default
                                if ($nav_user['profile_picture']) {
                                    $nav_img_path = 'assets/images/profile/' . $nav_user['profile_picture'];
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
                                    <li><a class="dropdown-item" href="pelanggan/profile.php">Profil</a></li>
                                    <li><a class="dropdown-item" href="pelanggan/orders.php">Riwayat Pesanan</a></li>
                                <?php endif; ?>
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="admin/index.php">Dashboard Admin</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-success ms-2" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <?php 
                    $img_src = 'assets/images/products/no-image.svg';
                    if ($product['image']) {
                        $img_path = 'assets/images/products/' . $product['image'];
                        if (file_exists($img_path)) {
                            $img_src = $img_path;
                        }
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-img-top" style="height: 400px; object-fit: cover;">
                </div>
            </div>
            <div class="col-md-7">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-success">Home</a></li>
                        <li class="breadcrumb-item"><a href="products.php" class="text-success">Produk</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                    </ol>
                </nav>
                
                <h1 class="fw-bold text-success mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <span class="badge bg-success mb-3">Fresh Harvest</span>
                <?php if ($product['category_name']): ?>
                    <span class="badge bg-outline-success border-success text-success mb-3 ms-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>
                
                <p class="text-muted mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                
                <h2 class="fw-bold text-success mb-4"><?php echo format_currency($product['price']); ?></h2>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <p class="text-muted mb-1"><i class="fas fa-calendar-alt text-success me-2"></i>Tanggal Panen</p>
                        <p class="fw-bold"><?php echo $product['harvest_date'] ? date('d-m-Y', strtotime($product['harvest_date'])) : '-'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1"><i class="fas fa-box-open text-success me-2"></i>Stok</p>
                        <p class="fw-bold"><?php echo $product['stock']; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1"><i class="fas fa-seedling text-success me-2"></i>Penghematan Karbon</p>
                        <p class="fw-bold"><?php echo $product['carbon_saving']; ?> kg CO2</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fas fa-nutritionix text-success me-2"></i>Informasi Gizi</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><i class="fas fa-vial text-success me-2"></i>Vitamin</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($product['vitamin'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><i class="fas fa-mineral text-success me-2"></i>Zat Besi</p>
                                <p class="fw-bold"><?php echo $product['iron']; ?> mg</p>
                            </div>
                        </div>
                        <?php if ($product['health_benefits']): ?>
                            <hr>
                            <h6 class="fw-bold mb-2"><i class="fas fa-heart text-success me-2"></i>Manfaat Kesehatan</h6>
                            <p class="text-muted"><?php echo htmlspecialchars($product['health_benefits']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (is_logged_in()): ?>
                    <?php if (!is_admin()): ?>
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Jumlah</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                            </div>
                            <div class="col-md-8 align-self-end">
                                <button type="submit" class="btn btn-success w-100 py-2"><i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang</button>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-success w-100 py-2"><i class="fas fa-sign-in-alt me-2"></i>Login untuk Membeli</a>
                <?php endif; ?>
            </div>
        </div>
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
    <script src="assets/js/script.js"></script>
</body>
</html>