<?php
require_once '../config/config.php';
require_admin();

$success = '';
$error = '';
$admin_id = get_current_admin_id();

// Cek apakah kolom admin_id ada di orders dan tabel payments ada
$admin_filter = '1=1';
$has_admin_id = false;
try {
    $pdo->query("SELECT admin_id FROM orders LIMIT 1");
    $has_admin_id = true;
    $admin_filter = get_admin_filter('o');
} catch (PDOException $e) {
    // Kolom admin_id belum ada, tidak filter
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order_status'])) {

    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];

    try {

        // Update status pesanan
        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $order_id
        ]);

        // Cek apakah data pembayaran sudah ada
        $stmt = $pdo->prepare("
            SELECT id
            FROM payments
            WHERE order_id = ?
        ");

        $stmt->execute([$order_id]);

        if ($stmt->fetch()) {

            // Update pembayaran
            $stmt = $pdo->prepare("
                UPDATE payments
                SET payment_status = ?
                WHERE order_id = ?
            ");

            $stmt->execute([
                $payment_status,
                $order_id
            ]);

        } else {

            // Tambah pembayaran baru
            $stmt = $pdo->prepare("
                INSERT INTO payments
                (order_id, payment_method, payment_status)
                VALUES (?, 'Transfer Bank', ?)
            ");

            $stmt->execute([
                $order_id,
                $payment_status
            ]);
        }

        $success = 'Status berhasil diperbarui';

    } catch (PDOException $e) {

        $error = $e->getMessage();
    }
}

// Dapatkan list orders dengan aman
$orders = array();
try {
    $stmt = $pdo->query("SELECT o.*, u.name as customer_name, p.payment_method, p.payment_status, p.payment_proof, p.verified_at, p.verified_by FROM orders o JOIN users u ON o.user_id = u.id LEFT JOIN payments p ON o.id = p.order_id WHERE $admin_filter ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Jika tabel payments atau kolom belum ada, tanpa join payments
    $stmt = $pdo->query("SELECT o.*, u.name as customer_name FROM orders o JOIN users u ON o.user_id = u.id WHERE $admin_filter ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll();
}

$page_title = 'Manajemen Pesanan - SayurDesa Admin';
$active_menu = 'orders';

require_once 'header.php';
?>
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="fw-bold">Manajemen Pesanan</h1>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($order['invoice_number'] ?? '#' . $order['id']); ?></h6>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo format_currency($order['grand_total'] ?? $order['total_amount']); ?></td>
                                    <td><?php echo htmlspecialchars($order['payment_method'] ?? '-'); ?></td>
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
                                   <td>
<?php
$ps = $order['payment_status'] ?? '';
?>

<span class="badge <?php

switch($ps){

    case 'Pending':
        echo 'bg-warning text-dark';
        break;

    case 'Dibayar':
        echo 'bg-success';
        break;

    case 'Gagal':
        echo 'bg-danger';
        break;

    default:
        echo 'bg-secondary';
}
?>">
    <?php echo htmlspecialchars($ps ?: '-'); ?>
</span>
</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                            <i class="fas fa-edit"></i> Ubah Status
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php foreach ($orders as $order): ?>
            <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Ubah Status <?php echo htmlspecialchars($order['invoice_number'] ?? 'Order #' . $order['id']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                
                                <?php if (isset($order['payment_proof']) && $order['payment_proof']): ?>
                                    <div class="mb-4">
                                        <h6 class="fw-bold">Bukti Pembayaran:</h6>
                                        <img src="../assets/images/payments/<?php echo htmlspecialchars($order['payment_proof']); ?>" alt="Bukti Pembayaran" class="img-fluid img-thumbnail">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="status_<?php echo $order['id']; ?>" class="form-label">Status Pesanan</label>
                                    <select class="form-select" id="status_<?php echo $order['id']; ?>" name="status">
                                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Diproses" <?php echo $order['status'] == 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="Dikirim" <?php echo $order['status'] == 'Dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                        <option value="Selesai" <?php echo $order['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="Dibatalkan" <?php echo $order['status'] == 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="payment_status_<?php echo $order['id']; ?>" class="form-label">Status Pembayaran</label>
                                  <?php $ps = $order['payment_status'] ?? ''; ?>

<select class="form-select" name="payment_status">

    <option value="Pending"
        <?php echo $ps == 'Pending' ? 'selected' : ''; ?>>
        Pending
    </option>

    <option value="Dibayar"
        <?php echo $ps == 'Dibayar' ? 'selected' : ''; ?>>
        Dibayar
    </option>

    <option value="Gagal"
        <?php echo $ps == 'Gagal' ? 'selected' : ''; ?>>
        Gagal
    </option>

</select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="update_order_status" class="btn btn-success">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
<?php require_once 'footer.php'; ?>
