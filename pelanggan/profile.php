<?php
require_once '../config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$upload_dir = '../assets/images/profile/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
$max_file_size = 5 * 1024 * 1024; // 5MB

// Pastikan folder upload ada dan bisa ditulis
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $profile_picture = $user['profile_picture']; // Default ke foto lama
    
    // Handle upload foto profile
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];
        
        // Validasi tipe file
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.';
        } 
        // Validasi ukuran file
        elseif ($file_size > $max_file_size) {
            $error = 'Ukuran file terlalu besar. Maksimal 5MB.';
        }
        // Jika validasi berhasil
        else {
            // Buat nama file unik
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_file_name = uniqid('profile_', true) . '.' . $file_ext;
            $target_path = $upload_dir . $new_file_name;
            
            // Upload file
            if (move_uploaded_file($file_tmp, $target_path)) {
                // Hapus foto lama jika ada
                if ($user['profile_picture'] && file_exists($upload_dir . $user['profile_picture'])) {
                    unlink($upload_dir . $user['profile_picture']);
                }
                $profile_picture = $new_file_name;
            } else {
                $error = 'Gagal mengunggah foto profil. Silakan coba lagi.';
            }
        }
    }
    
    if (!$error) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, profile_picture = ? WHERE id = ?");
        
        try {
            $stmt->execute([$name, $phone, $profile_picture, $user_id]);
            $_SESSION['name'] = $name;
            $success = 'Profil berhasil diperbarui';
            
            // Refresh data user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan, silakan coba lagi';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - SayurDesa</title>
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
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-danger"><?php echo get_cart_count(); ?></span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">Profil</a></li>
                            <li><a class="dropdown-item" href="orders.php">Riwayat Pesanan</a></li>
                            <?php if (is_admin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../admin/index.php">Dashboard Admin</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <!-- Foto Profile -->
                            <div class="mb-3 position-relative d-inline-block">
                                <?php 
                                $profile_img = '../assets/images/products/no-image.svg'; // Default
                                if ($user['profile_picture']) {
                                    $img_path = '../assets/images/profile/' . $user['profile_picture'];
                                    if (file_exists($img_path)) {
                                        $profile_img = $img_path;
                                    }
                                }
                                ?>
                                <img id="profilePicturePreview" src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile" class="rounded-circle border-3 border-success shadow" style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <h3 class="fw-bold">Profil Saya</h3>
                        </div>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Upload Foto Profile -->
                            <div class="mb-4">
                                <label for="profile_picture" class="form-label fw-bold">Foto Profil</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewProfilePicture(event)">
                                <div class="form-text">Format: JPG, JPEG, PNG, WEBP | Maksimal 5MB</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="mb-4">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2">Perbarui Profil</button>
                        </form>
                    </div>
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
    <script src="../assets/js/script.js"></script>
    <script>
        function previewProfilePicture(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('profilePicturePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
