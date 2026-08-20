<?php
require_once '../config/config.php';
require_super_admin();

$page_title = 'Laporan Bulanan - SayurDesa Super Admin';
$active_menu = 'monthly_reports';

// Get monthly sales data
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') as month,
        COUNT(o.id) as total_orders,
        SUM(o.total_amount) as total_revenue,
        u.name as admin_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.status = 'Selesai'
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month DESC
");
$monthly_reports = $stmt->fetchAll();

// Get top admin by sales
$stmt = $pdo->query("
    SELECT 
        u.name as admin_name,
        COUNT(o.id) as total_orders,
        SUM(o.total_amount) as total_revenue
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status = 'Selesai'
    GROUP BY u.id
    ORDER BY total_revenue DESC
    LIMIT 10
");
$top_admins = $stmt->fetchAll();

require_once 'header.php';
?>
        <h2 class="fw-bold mb-4">Laporan Bulanan</h2>

        <div class="row g-4 mb-5">
            <!-- Monthly Sales Report -->
            <div class="col-md-8">
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
                                        <th>Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthly_reports as $report): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($report['month']); ?></td>
                                            <td><?php echo htmlspecialchars($report['total_orders']); ?></td>
                                            <td><?php echo format_currency($report['total_revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Admins -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="fw-bold mb-0">Admin dengan Penjualan Tertinggi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Admin</th>
                                        <th>Jumlah Pesanan</th>
                                        <th>Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_admins as $admin): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($admin['admin_name'] ?? 'Tidak diketahui'); ?></td>
                                            <td><?php echo htmlspecialchars($admin['total_orders']); ?></td>
                                            <td><?php echo format_currency($admin['total_revenue']); ?></td>
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