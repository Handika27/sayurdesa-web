<?php
require_once '../config/config.php';
require_super_admin();

$page_title = 'Dashboard Super Admin - SayurDesa';
$active_menu = 'dashboard';

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
$total_products = $stmt->fetch()['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total_sales FROM orders WHERE status = 'Selesai'");
$total_sales = $stmt->fetch()['total_sales'] ?? 0;

// Get recent orders
$stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// Get recent activity logs (jika tabel ada)
$recent_activity = [];
try {
    $stmt = $pdo->query("SELECT a.*, u.name as user_name FROM activity_logs a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10");
    $recent_activity = $stmt->fetchAll();
} catch (PDOException $e) {
    // Tabel activity_logs belum ada
}

require_once 'header.php';
?>
        <h2 class="fw-bold mb-4">Dashboard Super Admin</h2>

        <!-- Statistics -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-5 border-primary">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-users text-primary me-2"></i>Total Pengguna</h6>
                        <h3 class="fw-bold text-primary"><?php echo $total_users; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-5 border-success">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-box text-success me-2"></i>Total Produk</h6>
                        <h3 class="fw-bold text-success"><?php echo $total_products; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-5 border-info">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-shopping-cart text-info me-2"></i>Total Pesanan</h6>
                        <h3 class="fw-bold text-info"><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-5 border-warning">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-money-bill text-warning me-2"></i>Total Penjualan</h6>
                        <h3 class="fw-bold text-warning"><?php echo format_currency($total_sales); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4"><i class="fas fa-list text-success me-2"></i>Pesanan Terbaru</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>No. Invoice</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['invoice_number'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                            <td><?php echo format_currency($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge <?php
                                                    switch($order['status']) {
                                                        case 'Pending': echo 'bg-warning text-dark'; break;
                                                        case 'Diproses': echo 'bg-info text-dark'; break;
                                                        case 'Dikirim': echo 'bg-primary'; break;
                                                        case 'Selesai': echo 'bg-success'; break;
                                                        case 'Dibatalkan': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4"><i class="fas fa-history text-success me-2"></i>Log Aktivitas Terbaru</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Pengguna</th>
                                        <th>Aksi</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activity as $log): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
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