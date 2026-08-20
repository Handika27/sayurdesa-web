<?php
require_once 'config/config.php';
require_login();

// Validasi: hanya pelanggan yang bisa akses
if (is_admin() || is_super_admin()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data nama dan alamat dari parameter GET (dari modal)
$customer_name = isset($_GET['name']) ? trim($_GET['name']) : $_SESSION['name'];
$shipping_address = isset($_GET['address']) ? trim($_GET['address']) : '';

// Jika alamat tidak ada di parameter, coba ambil dari database
if (empty($shipping_address)) {
    $stmt_address = $pdo->prepare("SELECT address FROM addresses WHERE user_id = ? AND is_primary = 1 LIMIT 1");
    $stmt_address->execute([$user_id]);
    $address = $stmt_address->fetch();
    $shipping_address = $address ? $address['address'] : 'Belum ditentukan';
}

// Ambil data user
$stmt_user = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Ambil item keranjang
$stmt = $pdo->prepare("SELECT ci.*, p.name, p.price, p.stock FROM cart_items ci JOIN carts c ON ci.cart_id = c.id JOIN products p ON ci.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) == 0) {
    header('Location: cart.php');
    exit;
}

// Hitung total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Hardcode biaya tetap sementara
$biaya_admin = 500;
$biaya_pengiriman = 2000;
$grand_total = $total + $biaya_admin + $biaya_pengiriman;

$pdo->beginTransaction();

try {
    // Generate invoice number
    $invoice_number = generate_invoice_number();
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE invoice_number = ?");
        $stmt->execute([$invoice_number]);
        if (!$stmt->fetch()) break;
        $invoice_number = generate_invoice_number();
    }

    // Assign admin (ambil admin pertama untuk pesanan WhatsApp, atau bisa diatur sesuai kebutuhan)
    $stmt_admin = $pdo->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin') ORDER BY id LIMIT 1");
    $admin_data = $stmt_admin->fetch();
    $admin_id = $admin_data['id'] ?? null;

    // Buat pesanan
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, invoice_number, total_amount, biaya_admin, biaya_pengiriman, grand_total, shipping_address, status, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->execute([$user_id, $invoice_number, $total, $biaya_admin, $biaya_pengiriman, $grand_total, $shipping_address, $admin_id]);
    $order_id = $pdo->lastInsertId();

    // Tambahkan item pesanan
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity']]);
        
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Buat pembayaran
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, payment_status) VALUES (?, 'Transfer Bank / QRIS', 'Pending')");
    $stmt->execute([$order_id]);

    // Kosongkan keranjang
    $stmt = $pdo->prepare("DELETE ci FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);

    log_activity('Pesanan WhatsApp', 'Membuat pesanan via WhatsApp: ' . $invoice_number);

    $pdo->commit();

    // Dapatkan nomor WhatsApp admin (default jika tidak ada)
    $admin_whatsapp = '6281392858421'; // Nomor default
    if ($payment_settings && !empty($payment_settings['whatsapp_number'])) {
        $admin_whatsapp = $payment_settings['whatsapp_number'];
    }

    // Buat pesan WhatsApp dengan format rapi (tanpa encoding manual, kita gunakan PHP_EOL lalu urlencode)
    $whatsapp_message = "Halo Admin, saya mau pesan:\n\n";
    $whatsapp_message .= "Nama Customer: " . $customer_name . "\n\n";
    $whatsapp_message .= "Daftar Belanja:\n\n";
    foreach ($cart_items as $item) {
        $whatsapp_message .= $item['name'] . " - " . format_currency($item['price'] * $item['quantity']) . "\n";
    }
    $whatsapp_message .= "\n--------------------------\n\n";
    $whatsapp_message .= "Subtotal Produk: " . format_currency($total) . "\n";
    $whatsapp_message .= "Biaya Admin: " . format_currency($biaya_admin) . "\n";
    $whatsapp_message .= "Biaya Pengiriman: " . format_currency($biaya_pengiriman) . "\n";
    $whatsapp_message .= "\n--------------------------\n\n";
    $whatsapp_message .= "Total Pembayaran: " . format_currency($grand_total) . "\n\n";
    $whatsapp_message .= "Alamat: " . $shipping_address . "\n";

    header("Location: https://wa.me/" . $admin_whatsapp . "?text=" . urlencode($whatsapp_message));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Terjadi kesalahan: " . $e->getMessage());
}
?>