<?php
require_once 'config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if (!$user['is_active']) {
            $error = 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            log_activity('Login', 'Pengguna berhasil login');
            
            if ($user['role'] == 'super_admin') {
                header('Location: super_admin/index.php');
            } elseif ($user['role'] == 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: pelanggan/dashboard.php');
            }
            exit;
        }
    } else {
        $error = 'Email atau password salah';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SayurDesa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-leaf text-success" style="font-size: 50px;"></i>
                            <h3 class="fw-bold text-success mt-2">SayurDesa</h3>
                            <p class="text-muted">Silakan masuk ke akun Anda</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2">Masuk</button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted mb-2">Belum punya akun?</p>
                            <a href="register.php" class="btn btn-success w-100">Daftar Sekarang</a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-muted"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
