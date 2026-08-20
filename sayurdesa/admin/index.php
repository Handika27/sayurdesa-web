<?php
require_once '../config/config.php';
require_admin();

$page_title = 'Dashboard Admin - SayurDesa';
$active_menu = 'dashboard';

// Cek apakah kolom admin_id ada di orders
$order_admin_filter = '1=1';
try {
    $pdo->query("SELECT admin_id FROM orders LIMIT 1");
    $order_admin_filter = get_admin_filter('o');
} catch (PDOException $e) {
    // Kolom admin_id belum ada
}

// Cek apakah kolom admin_id ada di products
$product_admin_filter = '1=1';
try {
    $pdo->query("SELECT admin_id FROM products LIMIT 1");
    $product_admin_filter = get_admin_filter('products');
} catch (PDOException $e) {
    // Kolom admin_id belum ada
}

// Get statistics (filtered by admin_id if available)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE $product_admin_filter");
$total_products = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'pelanggan'");
$total_customers = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders o WHERE $order_admin_filter");
$total_orders = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COALESCE(SUM(o.total_amount), 0) as total FROM orders o WHERE $order_admin_filter AND o.status = 'Selesai'");
$total_revenue = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, SUM(o.total_amount) as total FROM orders o WHERE $order_admin_filter AND o.status = 'Selesai' GROUP BY DATE_FORMAT(o.created_at, '%Y-%m') ORDER BY month DESC LIMIT 6");
$monthly_sales = $stmt->fetchAll();

require_once 'header.php';
?>
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="fw-bold">Dashboard</h1>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted mb-0">Total Produk</p>
                                <h3 class="fw-bold mb-0"><?php echo $total_products; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted mb-0">Total Pelanggan</p>
                                <h3 class="fw-bold mb-0"><?php echo $total_customers; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted mb-0">Total Pesanan</p>
                                <h3 class="fw-bold mb-0"><?php echo $total_orders; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-info text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                            <div class="ms-3">
                                <p class="text-muted mb-0">Total Pendapatan</p>
                                <h3 class="fw-bold mb-0"><?php echo format_currency($total_revenue); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="fw-bold mb-0">Penjualan Bulanan</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const months = <?php echo json_encode(array_reverse(array_column($monthly_sales, 'month'))); ?>;
        const totals = <?php echo json_encode(array_reverse(array_column($monthly_sales, 'total'))); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Penjualan',
                    data: totals,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
<?php require_once 'footer.php'; ?>