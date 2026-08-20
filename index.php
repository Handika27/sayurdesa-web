<?php
require_once 'config/config.php';

$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 LIMIT 8");
$products = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM banners WHERE is_active = 1 LIMIT 3");
$banners = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SayurDesa - Marketplace sayur segar langsung dari petani lokal. Belanja sayur organik, sehat, dan berkualitas dengan pengiriman cepat.">
    <title>SayurDesa - Marketplace Sayur Segar Langsung dari Petani</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=1.1">

    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6B78T44H1L"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-6B78T44H1L');
</script>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent fixed-top" id="navbar">
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
                        <a class="nav-link text-white" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#keunggulan">Keunggulan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#produk">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#edukasi">Edukasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#testimoni">Testimoni</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#faq">FAQ</a>
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
                            <a class="nav-link navbar-btn ms-2" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link navbar-btn ms-2" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="floating-veggies">
            <i class="fas fa-carrot veggie veggie-1"></i>
            <i class="fas fa-leaf veggie veggie-2"></i>
            <i class="fas fa-seedling veggie veggie-3"></i>
            <i class="fas fa-apple-alt veggie veggie-4"></i>
            <i class="fas fa-pepper-hot veggie veggie-5"></i>
        </div>
        
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-md-6 text-white" data-aos="fade-right" data-aos-duration="1200">
                    <h1 class="display-3 fw-bold mb-4 hero-headline">
                        Sayur Segar, <span class="text-yellow-300">Hidup Sehat</span>
                    </h1>
                    <p class="lead mb-5 hero-subheadline">
                        Belanja sayuran segar langsung dari petani lokal. 100% fresh, organik, dan dikirim ke rumah Anda dalam hitungan jam!
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#produk" class="btn btn-yellow btn-lg px-5 py-3 rounded-pill fw-bold">
                            <i class="fas fa-shopping-cart me-2"></i>Belanja Sekarang
                        </a>
                        <a href="#produk" class="btn btn-outline-light btn-lg px-5 py-3 rounded-pill fw-bold">
                            Lihat Produk
                        </a>
                    </div>
                </div>
                <div class="col-md-6 text-center" data-aos="fade-left" data-aos-duration="1200">
                    <div class="hero-image-wrapper">
                        <i class="fas fa-shopping-basket hero-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Section -->
        <div class="stats-container" data-aos="fade-up" data-aos-delay="400">
            <div class="stats-scroll-wrapper">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3 class="fw-bold text-white"><span class="counter" data-target="10000">0</span>+</h3>
                        <p class="text-light mb-0">Pelanggan Puas</p>
                    </div>
                    <div class="stat-card">
                        <h3 class="fw-bold text-white"><span class="counter" data-target="50000">0</span>+</h3>
                        <p class="text-light mb-0">Pesanan Berhasil</p>
                    </div>
                    <div class="stat-card">
                        <h3 class="fw-bold text-white"><span class="counter" data-target="500">0</span>+</h3>
                        <p class="text-light mb-0">Produk Tersedia</p>
                    </div>
                    <div class="stat-card">
                        <h3 class="fw-bold text-white"><span class="counter" data-target="100000">0</span>+</h3>
                        <p class="text-light mb-0">Kg CO₂ Hemat</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Keunggulan Section -->
    <section id="keunggulan" class="section-padding">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="fw-bold text-success mb-3">Mengapa Pilih SayurDesa?</h2>
                <p class="text-muted max-w-2xl mx-auto">Kami hadir untuk memberikan pengalaman belanja sayuran yang mudah, aman, dan memuaskan</p>
            </div>
            
            <div class="row g-4 mt-5">
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="0">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon bg-success-100 text-success">
                            <i class="fas fa-truck fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Pengiriman Cepat</h4>
                        <p class="text-muted">Dikirim ke rumah Anda dalam waktu 2 jam setelah pemesanan</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon bg-success-100 text-success">
                            <i class="fas fa-leaf fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Segar & Organik</h4>
                        <p class="text-muted">100% hasil panen hari ini langsung dari petani lokal terpercaya</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon bg-success-100 text-success">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Kualitas Terjamin</h4>
                        <p class="text-muted">Setiap produk melewati proses quality control ketat</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon bg-success-100 text-success">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Harga Terbaik</h4>
                        <p class="text-muted">Harga langsung dari petani tanpa perantara</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="400">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon bg-success-100 text-success">
                            <i class="fas fa-lock fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Transaksi Aman</h4>
                        <p class="text-muted">Sistem pembayaran yang aman dan terlindungi</p>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="500">
                    <div class="keunggulan-card">
                        <div class="keunggulan-icon bg-success-100 text-success">
                            <i class="fas fa-headset fa-2x"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Support 24/7</h4>
                        <p class="text-muted">Tim customer service siap membantu kapan saja</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Produk Unggulan Section -->
    <section id="produk" class="section-padding bg-gradient">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="fw-bold text-success mb-3">Produk Unggulan Kami</h2>
                <p class="text-muted max-w-2xl mx-auto">Pilih sayuran segar pilihan terbaik untuk keluarga Anda</p>
            </div>
            
            <div class="row g-4 mt-5">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="<?php echo array_search($product, $products) * 50; ?>">
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <?php 
                                $img_src = 'assets/images/products/no-image.svg';
                                if ($product['image']) {
                                    $img_path = 'assets/images/products/' . $product['image'];
                                    if (file_exists($img_path)) {
                                        $img_src = $img_path;
                                    }
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                <div class="product-badges">
                                    <span class="badge bg-success">Fresh Harvest</span>
                                    <span class="badge bg-yellow text-dark">Organik</span>
                                </div>
                            </div>
                            <div class="product-body">
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="rating mb-2">
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <i class="fas fa-star-half-alt text-yellow-400"></i>
                                    <span class="text-muted small ms-1">(4.8)</span>
                                </div>
                                <p class="text-success fw-bold fs-5 mb-2"><?php echo format_currency($product['price']); ?></p>
                                <p class="text-muted small mb-3">Stok: <span class="fw-bold"><?php echo $product['stock']; ?></span></p>
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-success w-100">
                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="products.php" class="btn btn-success btn-lg px-5 py-3 rounded-pill fw-bold">
                    Lihat Semua Produk <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Edukasi & Sustainability Section -->
    <section id="edukasi" class="section-padding">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="fw-bold text-success mb-3">Edukasi Gizi & Sustainability</h2>
                <p class="text-muted max-w-2xl mx-auto">Kenali manfaat sayuran dan dampak positifnya terhadap lingkungan</p>
            </div>
            
            <div class="row align-items-center mt-5">
                <div class="col-md-6" data-aos="fade-right">
                    <div class="edukasi-illustration">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                </div>
                
                <div class="col-md-6" data-aos="fade-left">
                    <h3 class="fw-bold mb-4">Manfaat Konsumsi Sayur</h3>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Kaya Vitamin</span>
                            <span class="text-success fw-bold">95%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 95%;" data-aos="width" data-aos-delay="200"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Tinggi Serat</span>
                            <span class="text-success fw-bold">88%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 88%;" data-aos="width" data-aos-delay="400"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Antioksidan</span>
                            <span class="text-success fw-bold">92%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 92%;" data-aos="width" data-aos-delay="600"></div>
                        </div>
                    </div>
                    
                    <div class="row mt-5">
                        <div class="col-6">
                            <div class="counter-box">
                                <h3 class="fw-bold text-success"><span class="counter" data-target="50">0</span>+</h3>
                                <p class="text-muted mb-0">Jenis Sayuran</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="counter-box">
                                <h3 class="fw-bold text-success"><span class="counter" data-target="100">0</span>%</h3>
                                <p class="text-muted mb-0">Alami & Segar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimoni Section -->
    <section id="testimoni" class="section-padding bg-gradient">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="fw-bold text-success mb-3">Apa Kata Mereka?</h2>
                <p class="text-muted max-w-2xl mx-auto">Testimoni nyata dari pelanggan SayurDesa</p>
            </div>
            
            <div class="row mt-5" data-aos="fade-up">
                <div class="col-md-12">
                    <div id="testimoniCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-bs-touch="true" data-bs-pause="hover">
                        <div class="carousel-inner">
                            <!-- Item 1 -->
                            <div class="carousel-item active">
                                <div class="testimoni-card">
                                    <div class="testimoni-avatar bg-success-100">
                                        <i class="fas fa-user text-success"></i>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    </div>
                                    <p class="text-muted mb-3">"Sayurannya benar-benar segar! Pengiriman cepat dan packaging rapi. Sudah jadi langganan!"</p>
                                    <h6 class="fw-bold">- Siti Aisyah</h6>
                                </div>
                            </div>
                            <!-- Item 2 -->
                            <div class="carousel-item">
                                <div class="testimoni-card">
                                    <div class="testimoni-avatar bg-success-100">
                                        <i class="fas fa-user text-success"></i>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    </div>
                                    <p class="text-muted mb-3">"Harga terjangkau dan kualitas top! Customer service juga sangat membantu."</p>
                                    <h6 class="fw-bold">- Budi Santoso</h6>
                                </div>
                            </div>
                            <!-- Item 3 -->
                            <div class="carousel-item">
                                <div class="testimoni-card">
                                    <div class="testimoni-avatar bg-success-100">
                                        <i class="fas fa-user text-success"></i>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star-half-alt text-yellow-400"></i>
                                    </div>
                                    <p class="text-muted mb-3">"Sangat membantu untuk yang suka masak tapi males ke pasar. Recommended!"</p>
                                    <h6 class="fw-bold">- Dewi Lestari</h6>
                                </div>
                            </div>
                            <!-- Item 4 -->
                            <div class="carousel-item">
                                <div class="testimoni-card">
                                    <div class="testimoni-avatar bg-success-100">
                                        <i class="fas fa-user text-success"></i>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    </div>
                                    <p class="text-muted mb-3">"Produk organiknya benar-benar berkualitas. Cocok untuk keluarga yang peduli kesehatan!"</p>
                                    <h6 class="fw-bold">- Andi Pratama</h6>
                                </div>
                            </div>
                            <!-- Item 5 -->
                            <div class="carousel-item">
                                <div class="testimoni-card">
                                    <div class="testimoni-avatar bg-success-100">
                                        <i class="fas fa-user text-success"></i>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    </div>
                                    <p class="text-muted mb-3">"Pengiriman super cepat! Baru pesan, datang langsung. Sayurannya fresh banget!"</p>
                                    <h6 class="fw-bold">- Rina Putri</h6>
                                </div>
                            </div>
                            <!-- Item 6 -->
                            <div class="carousel-item">
                                <div class="testimoni-card">
                                    <div class="testimoni-avatar bg-success-100">
                                        <i class="fas fa-user text-success"></i>
                                    </div>
                                    <div class="rating mb-3">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star-half-alt text-yellow-400"></i>
                                    </div>
                                    <p class="text-muted mb-3">"Harga bersaing, kualitas oke. Aplikasi dan website juga mudah digunakan!"</p>
                                    <h6 class="fw-bold">- Hendra Wijaya</h6>
                                </div>
                            </div>
                        </div>
                        
                        <button class="carousel-control-prev" type="button" data-bs-target="#testimoniCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-success rounded-circle p-2"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#testimoniCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-success rounded-circle p-2"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="section-padding">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="fw-bold text-success mb-3">Pertanyaan yang Sering Diajukan</h2>
                <p class="text-muted max-w-2xl mx-auto">Kami siap menjawab pertanyaan Anda tentang SayurDesa</p>
            </div>
            
            <div class="row justify-content-center mt-5">
                <div class="col-md-8" data-aos="fade-up">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Bagaimana cara memesan di SayurDesa?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Cukup daftar, pilih produk yang Anda inginkan, masukkan ke keranjang, lalu checkout dan selesaikan pembayaran. Produk akan dikirim ke alamat Anda!
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Berapa lama waktu pengiriman?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Produk akan dikirim dalam waktu 1-2 jam setelah pemesanan untuk area tertentu, dan maksimal 4 jam untuk area lainnya.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Apakah sayuran benar-benar segar?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya! Semua sayuran kami dipetik hari ini langsung dari petani lokal terpercaya dan melalui quality control ketat.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Bagaimana jika saya tidak puas dengan produk?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Kami memberikan garansi kepuasan 100%. Jika tidak puas, Anda bisa mengembalikan produk dalam waktu 24 jam dan uang Anda kembali.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Apa saja metode pembayaran yang tersedia?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Kami menerima transfer bank, e-wallet (GoPay, OVO, DANA), dan pembayaran di tempat (COD).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row" data-aos="zoom-in">
                <div class="col-md-12 text-center text-white">
                    <h2 class="display-4 fw-bold mb-4">Mulai Hidup Sehat Hari Ini!</h2>
                    <p class="lead mb-5 max-w-2xl mx-auto">Gabung bersama ribuan pelanggan lainnya yang sudah merasakan manfaat sayuran segar dari SayurDesa</p>
                    <a href="#produk" class="btn btn-yellow btn-lg px-6 py-4 rounded-pill fw-bold">
                        <i class="fas fa-shopping-cart me-2"></i>Belanja Sekarang Juga
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust & Credibility Section -->
    <section class="trust-section section-padding bg-gradient">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="fw-bold text-success mb-3">Kepercayaan Anda Adalah Prioritas Kami</h2>
                <p class="text-muted max-w-2xl mx-auto">Kami berkomitmen untuk memberikan pelayanan terbaik dan keamanan dalam setiap transaksi</p>
            </div>
            
            <div class="row mt-5 g-4">
                <div class="col-md-6" data-aos="fade-right">
                    <div class="trust-card">
                        <h4 class="fw-bold mb-4 text-success"><i class="fas fa-map-marker-alt me-2"></i>Hubungi Kami</h4>
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-building me-2 text-success"></i>Alamat</h6>
                            <p class="text-muted mb-0">Jl. Sayuran No. 123, Jakarta Selatan, Indonesia 12345</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-phone-alt me-2 text-success"></i>WhatsApp</h6>
                            <a href="https://wa.me/6281392858421" target="_blank" class="text-decoration-none">
                                <p class="text-success mb-0 fw-bold">+62 813-9285-8421</p>
                            </a>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold"><i class="fas fa-envelope me-2 text-success"></i>Email</h6>
                            <a href="mailto:support@sayurdesa.com" class="text-decoration-none">
                                <p class="text-muted mb-0">support@sayurdesa.com</p>
                            </a>
                        </div>
                        <div class="mb-4">
                            <h6 class="fw-bold"><i class="fas fa-clock me-2 text-success"></i>Jam Operasional</h6>
                            <p class="text-muted mb-0">Setiap Hari: 06.00 - 22.00 WIB</p>
                        </div>
                        
                        <!-- Google Maps Purwokerto -->
                        <div class="map-placeholder">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126693.4942277861!2d109.22557168955078!3d-7.419736999999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655ea4836dd317%3A0x401573623f40330!2sPurwokerto%2C%20Kabupaten%20Banyumas%2C%20Jawa%20Tengah!5e0!3m2!1sid!2sid!4v1718000000000!5m2!1sid!2sid" 
                                width="100%" 
                                height="250" 
                                style="border:0; border-radius: 15px;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6" data-aos="fade-left">
                    <div class="trust-card">
                        <h4 class="fw-bold mb-4 text-success"><i class="fas fa-shield-alt me-2"></i>Keamanan & Kebijakan</h4>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-lock text-success fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Transaksi Aman</h6>
                                <p class="text-muted small mb-0">Sistem pembayaran terenkripsi dan terlindungi</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-file-contract text-success fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Syarat & Ketentuan</h6>
                                <p class="text-muted small mb-0">Ketentuan penggunaan layanan yang jelas</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-user-shield text-success fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Kebijakan Privasi</h6>
                                <p class="text-muted small mb-0">Data Anda aman bersama kami</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mb-4">
                            <i class="fas fa-truck-loading text-success fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Kebijakan Pengiriman</h6>
                                <p class="text-muted small mb-0">Pengiriman cepat dan terjamin</p>
                            </div>
                        </div>
                        
                        <div class="security-badges">
                            <span class="security-badge"><i class="fas fa-shield-check text-success"></i> Aman</span>
                            <span class="security-badge"><i class="fas fa-leaf text-success"></i> Organik</span>
                            <span class="security-badge"><i class="fas fa-star text-yellow-400"></i> Terpercaya</span>
                            <span class="security-badge"><i class="fas fa-truck-fast text-success"></i> Cepat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Media Sosial Section -->
    <section class="social-section section-padding bg-gradient">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <h2 class="fw-bold text-success mb-3">Ikuti Kami di Media Sosial</h2>
                <p class="text-muted max-w-2xl mx-auto">Dapatkan informasi terbaru dan promo menarik!</p>
            </div>
            
            <div class="row justify-content-center mt-5 g-4">
                <div class="col-md-2 col-4 text-center" data-aos="zoom-in" data-aos-delay="0">
                    <a href="#" class="social-link">
                        <div class="social-icon instagram">
                            <i class="fab fa-instagram fa-2x"></i>
                        </div>
                        <p class="fw-bold mt-3 mb-1">Instagram</p>
                        <p class="text-muted small"><span class="counter" data-target="50000">0</span>+ Followers</p>
                    </a>
                </div>
                
                <div class="col-md-2 col-4 text-center" data-aos="zoom-in" data-aos-delay="100">
                    <a href="#" class="social-link">
                        <div class="social-icon tiktok">
                            <i class="fab fa-tiktok fa-2x"></i>
                        </div>
                        <p class="fw-bold mt-3 mb-1">TikTok</p>
                        <p class="text-muted small"><span class="counter" data-target="30000">0</span>+ Followers</p>
                    </a>
                </div>
                
                <div class="col-md-2 col-4 text-center" data-aos="zoom-in" data-aos-delay="200">
                    <a href="#" class="social-link">
                        <div class="social-icon facebook">
                            <i class="fab fa-facebook-f fa-2x"></i>
                        </div>
                        <p class="fw-bold mt-3 mb-1">Facebook</p>
                        <p class="text-muted small"><span class="counter" data-target="40000">0</span>+ Followers</p>
                    </a>
                </div>
                
                <div class="col-md-2 col-4 text-center" data-aos="zoom-in" data-aos-delay="300">
                    <a href="#" class="social-link">
                        <div class="social-icon youtube">
                            <i class="fab fa-youtube fa-2x"></i>
                        </div>
                        <p class="fw-bold mt-3 mb-1">YouTube</p>
                        <p class="text-muted small"><span class="counter" data-target="15000">0</span>+ Subscribers</p>
                    </a>
                </div>
                
                <div class="col-md-2 col-4 text-center" data-aos="zoom-in" data-aos-delay="400">
                    <a href="#" class="social-link">
                        <div class="social-icon linkedin">
                            <i class="fab fa-linkedin-in fa-2x"></i>
                        </div>
                        <p class="fw-bold mt-3 mb-1">LinkedIn</p>
                        <p class="text-muted small"><span class="counter" data-target="10000">0</span>+ Followers</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/6281392858421" class="whatsapp-float" target="_blank" data-aos="zoom-in" data-aos-delay="1000">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-tooltip">Hubungi Kami</span>
    </a>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h4 class="fw-bold mb-3"><i class="fas fa-leaf text-success me-2"></i>SayurDesa</h4>
                    <p class="mb-4">Marketplace sayuran segar langsung dari petani lokal. Kami berkomitmen untuk memberikan produk berkualitas tinggi dan layanan terbaik.</p>
                    
                    <div class="social-links-footer">
                        <a href="#"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#"><i class="fab fa-tiktok fa-lg"></i></a>
                        <a href="#"><i class="fab fa-youtube fa-lg"></i></a>
                        <a href="#"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <h5 class="fw-bold mb-3">Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home" class="text-decoration-none hover-text-success">Home</a></li>
                        <li class="mb-2"><a href="#keunggulan" class="text-decoration-none hover-text-success">Keunggulan</a></li>
                        <li class="mb-2"><a href="#produk" class="text-decoration-none hover-text-success">Produk</a></li>
                        <li class="mb-2"><a href="#edukasi" class="text-decoration-none hover-text-success">Edukasi</a></li>
                        <li class="mb-2"><a href="#testimoni" class="text-decoration-none hover-text-success">Testimoni</a></li>
                        <li class="mb-2"><a href="#faq" class="text-decoration-none hover-text-success">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h5 class="fw-bold mb-3">Kontak</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-success"></i> Jl. Sayuran No. 123, Purwokerto, Jawa Tengah</li>
                        <li class="mb-2"><i class="fas fa-phone-alt me-2 text-success"></i> 
                            <a href="https://wa.me/6281392858421" target="_blank" class="text-decoration-none hover-text-success">+62 813-9285-8421</a>
                        </li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-success"></i> support@sayurdesa.com</li>
                        <li class="mb-2"><i class="fas fa-clock me-2 text-success"></i> Setiap Hari: 06.00 - 22.00 WIB</li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h5 class="fw-bold mb-3">Kebijakan</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none hover-text-success">Kebijakan Privasi</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none hover-text-success">Syarat & Ketentuan</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none hover-text-success">Kebijakan Pengiriman</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none hover-text-success">FAQ</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="bg-secondary my-4">
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> SayurDesa. All rights reserved. SayurDesa adalah platform agritech untuk memudahkan akses sayuran segar.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
