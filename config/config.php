<?php
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration (Aman untuk Railway & Localhost menggunakan PDO)
$db_url = getenv('DATABASE_URL');

try {
    if ($db_url) {
        // Jika berjalan di Railway
        $db_parts = parse_url($db_url);
        $host = $db_parts['host'];
        $username = $db_parts['user'];
        $password = $db_parts['pass'];
        $dbname = ltrim($db_parts['path'], '/');
        $port = isset($db_parts['port']) ? $db_parts['port'] : 3306;

        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    } else {
        // Jika berjalan di Laptop (XAMPP)
        $host = 'localhost';
        $dbname = 'sayurdesa';
        $username = 'root';
        $password = '';

        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
}

// Check if user is super admin
function is_super_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

// Require login
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Require admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

// Require super admin
function require_super_admin() {
    require_login();
    if (!is_super_admin()) {
        header('Location: index.php');
        exit;
    }
}

// Format currency
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Get cart count
function get_cart_count() {
    global $pdo;
    if (!is_logged_in() || is_admin()) {
        return 0;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $cart = $stmt->fetch();
    
    if (!$cart) {
        return 0;
    }
    
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cart['id']]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

// Log activity
function log_activity($action, $description = '') {
    global $pdo;
    if (!is_logged_in()) {
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $description, $ip_address]);
}

// Generate invoice number
function generate_invoice_number() {
    return 'INV' . date('YmdHis');
}

// Get payment settings
function get_payment_settings() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM payment_settings ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

function get_current_admin_id() {
    if (is_logged_in() && (is_admin() || is_super_admin())) {
        return $_SESSION['user_id'];
    }
    return null;
}

function get_admin_filter($table, $alias = '') {
    if (is_super_admin()) {
        return '1=1';
    }
    
    $admin_id = get_current_admin_id();
    if ($alias) {
        return "$alias.admin_id = $admin_id";
    }
    return "$table.admin_id = $admin_id";
}
?>