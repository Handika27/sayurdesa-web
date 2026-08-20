<?php
require_once 'config/config.php';
require_login();

// Validasi: hanya pelanggan yang bisa akses halaman ini
if (is_admin()) {
    header('Location: admin/index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        $item_id = $_POST['item_id'];
        $quantity = (int)$_POST['quantity'];
        
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $item_id]);
    }
    
    if (isset($_POST['remove'])) {
        $item_id = $_POST['item_id'];
        
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
        $stmt->execute([$item_id]);
    }
    
    header('Location: cart.php');
    exit;
}

$stmt = $pdo->prepare("SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.price, p.stock FROM cart_items ci JOIN carts c ON ci.cart_id = c.id JOIN products p ON ci.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - SayurDesa</title>
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
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger"><?php echo get_cart_count(); ?></span>
                        </a>
                    </li>
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
                            <li><a class="dropdown-item" href="pelanggan/profile.php">Profil</a></li>
                            <li><a class="dropdown-item" href="pelanggan/orders.php">Riwayat Pesanan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h1 class="fw-bold text-success">Keranjang Belanja</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?php if (count($cart_items) > 0): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="row align-items-center py-3 border-bottom">
                                    <div class="col-md-2">
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 80px;">
                                            <i class="fas fa-leaf text-success" style="font-size: 40px;"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="text-success fw-bold"><?php echo format_currency($item['price']); ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="POST" class="d-flex">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="update" class="btn btn-outline-secondary" onclick="this.form.quantity.value = Math.max(1, this.form.quantity.value - 1);">-</button>
                                            <input type="number" name="quantity" class="form-control text-center mx-2" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" style="width: 80px;" onchange="this.form.submit();">
                                            <button type="submit" name="update" class="btn btn-outline-secondary" onclick="this.form.quantity.value = Math.min(<?php echo $item['stock']; ?>, parseInt(this.form.quantity.value) + 1);">+</button>
                                        </form>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <h5 class="fw-bold"><?php echo format_currency($item['price'] * $item['quantity']); ?></h5>
                                    </div>
                                    <div class="col-md-1 text-end">
                                        <form method="POST">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" name="remove" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-shopping-cart text-muted" style="font-size: 80px;"></i>
                            <h4 class="mt-3 text-muted">Keranjang Kosong</h4>
                            <p class="text-muted">Silakan belanja terlebih dahulu</p>
                            <a href="products.php" class="btn btn-success mt-3">Belanja Sekarang</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($cart_items) > 0): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-4">Ringkasan Pesanan</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span class="fw-bold"><?php echo format_currency($total); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Pengiriman</span>
                                <span class="fw-bold text-success">Gratis</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold fs-5">Total</span>
                                <span class="fw-bold fs-5 text-success"><?php echo format_currency($total); ?></span>
                            </div>
                            <a href="checkout.php" class="btn btn-success w-100 py-3 fw-bold">Lanjutkan ke Pembayaran</a>
                            <a href="products.php" class="btn btn-outline-success w-100 py-2 mt-2 fw-bold"><i class="fas fa-arrow-left me-2"></i>Tambah Produk</a>
                            
                            <button type="button" class="btn btn-outline-success w-100 py-3 mt-2 fw-bold" data-bs-toggle="modal" data-bs-target="#whatsappOrderModal"><i class="fab fa-whatsapp me-2"></i>Pesan via WhatsApp</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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

    <!-- Modal Pesan via WhatsApp -->
    <div class="modal fade" id="whatsappOrderModal" tabindex="-1" aria-labelledby="whatsappOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="whatsappOrderModalLabel"><i class="fab fa-whatsapp text-success me-2"></i>Pesan via WhatsApp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="whatsappOrderForm">
                        <div class="mb-3">
                            <label for="whatsappName" class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="whatsappName" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="whatsappAddress" class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" id="whatsappAddress" rows="4" required placeholder="Masukkan alamat lengkap pengiriman Anda..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="submitWhatsappOrder"><i class="fab fa-whatsapp me-2"></i>Pesan Sekarang</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('submitWhatsappOrder').addEventListener('click', function() {
            const name = document.getElementById('whatsappName').value.trim();
            const address = document.getElementById('whatsappAddress').value.trim();
            
            if (!name || !address) {
                alert('Nama dan alamat harus diisi!');
                return;
            }
            
            // Redirect ke whatsapp_order.php dengan data nama dan alamat
            window.location.href = 'whatsapp_order.php?name=' + encodeURIComponent(name) + '&address=' + encodeURIComponent(address);
        });
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>
