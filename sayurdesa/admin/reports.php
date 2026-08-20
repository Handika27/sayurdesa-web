<?php
require_once '../config/config.php';
require_admin();

// Cek apakah kolom admin_id ada di orders
$admin_filter = '1=1';
try {
    $pdo->query("SELECT admin_id FROM orders LIMIT 1");
    $admin_filter = get_admin_filter('o');
} catch (PDOException $e) {
    // Kolom admin_id belum ada
}

$stmt = $pdo->query("SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, COUNT(o.id) as order_count, SUM(o.total_amount) as total FROM orders o WHERE $admin_filter AND o.status = 'Selesai' GROUP BY DATE_FORMAT(o.created_at, '%Y-%m') ORDER BY month DESC");
$monthly_reports = $stmt->fetchAll();

$stmt = $pdo->query("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE $admin_filter GROUP BY p.id ORDER BY total_sold DESC LIMIT 10");
$top_products = $stmt->fetchAll();

$page_title = 'Laporan Penjualan - SayurDesa Admin';
$active_menu = 'reports';

require_once 'header.php';
?>
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="fw-bold">Laporan Penjualan</h1>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="fw-bold mb-0">Penjualan Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th>Jumlah Pesanan</th>
                                        <th>Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_reports as $report): ?>
                                        <tr>
                                            <td><?php echo $report['month']; ?></td>
                                            <td><?php echo $report['order_count']; ?></td>
                                            <td><?php echo format_currency($report['total']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="fw-bold mb-0">Produk Terlaris</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Terjual</th>
                                        <th>Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td><?php echo $product['total_sold']; ?></td>
                                            <td><?php echo format_currency($product['revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php require_once 'footer.php'; ?>