<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - SayurDesa</title>
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
                        <a class="nav-link text-white active" href="about.php">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="products.php">Produk</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="badge bg-danger"><?php echo get_cart_count(); ?></span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="pelanggan/profile.php">Profil</a></li>
                                <li><a class="dropdown-item" href="pelanggan/orders.php">Riwayat Pesanan</a></li>
                                <?php if (is_admin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/index.php">Dashboard Admin</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
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
            <div class="col-md-12 text-center mb-5">
                <h1 class="fw-bold text-success">Tentang SayurDesa</h1>
                <p class="text-muted">Menyediakan sayuran segar berkualitas untuk keluarga Anda</p>
            </div>
        </div>
        
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <div class="bg-success text-white p-5 rounded-4 text-center">
                    <i class="fas fa-leaf" style="font-size: 150px;"></i>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="fw-bold mb-4">Misi Kami</h2>
                <p class="lead mb-4">
                    SayurDesa berkomitmen untuk menyediakan sayuran segar berkualitas tinggi langsung dari petani lokal ke meja makan Anda.
                </p>
                <p class="mb-4">
                    Kami bekerja sama dengan petani terpilih untuk memastikan setiap produk yang kami jual selalu segar, berkualitas, dan terjangkau.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                            <span>100% Segar</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                            <span>Hasil Panen Hari Ini</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                            <span>Kualitas Terjamin</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                            <span>Pengiriman Cepat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <h2 class="fw-bold">Tim Kami</h2>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-light p-4 rounded-3">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-user fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Petani Lokal</h5>
                    <p class="text-muted">Mitra petani terpilih</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-light p-4 rounded-3">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-truck fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Tim Pengiriman</h5>
                    <p class="text-muted">Pengiriman cepat & aman</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="bg-light p-4 rounded-3">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-headset fs-2"></i>
                    </div>
                    <h5 class="fw-bold">Customer Service</h5>
                    <p class="text-muted">Siap membantu 24/7</p>
                </div>
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
