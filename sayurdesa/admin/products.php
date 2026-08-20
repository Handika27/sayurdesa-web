<?php
require_once '../config/config.php';
require_admin();

$upload_dir = '../assets/images/products/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
$max_file_size = 5 * 1024 * 1024; // 5MB
$admin_id = get_current_admin_id();
// Cek apakah kolom admin_id ada di tabel products
$admin_filter = '1=1';
try {
    $pdo->query("SELECT admin_id FROM products LIMIT 1");
    $admin_filter = get_admin_filter('products');
} catch (PDOException $e) {
    // Kolom admin_id belum ada, tidak filter
}

if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Get product image before deleting (and ensure it's the admin's product)
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ? AND $admin_filter");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if ($product && $product['image'] && file_exists($upload_dir . $product['image'])) {
        unlink($upload_dir . $product['image']);
    }
    
    $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ? AND $admin_filter");
    $stmt->execute([$product_id]);
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'] ?: null;
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $harvest_date = $_POST['harvest_date'] ?: null;
    $vitamin = $_POST['vitamin'];
    $iron = (float)$_POST['iron'];
    $health_benefits = $_POST['health_benefits'];
    $carbon_saving = (float)$_POST['carbon_saving'];
    $image_name = null;
    $error = null;
    
    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_type = $_FILES['image']['type'];
        
        // Validate file size
        if ($file_size > $max_file_size) {
            $error = "Ukuran file terlalu besar. Maksimal 5MB.";
        }
        
        // Validate file type
        if (!$error && !in_array($file_type, $allowed_types)) {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.";
        }
        
        if (!$error) {
            // Generate unique filename
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $image_name = uniqid('product_', true) . '.' . $file_ext;
            
            // Move uploaded file
            if (!move_uploaded_file($file_tmp, $upload_dir . $image_name)) {
                $error = "Gagal mengunggah file. Silakan coba lagi.";
                $image_name = null;
            }
        }
    }
    
    if (!$error) {
        if (isset($_POST['id'])) {
            $product_id = $_POST['id'];
            
            // Get old image
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ? AND $admin_filter");
            $stmt->execute([$product_id]);
            $old_product = $stmt->fetch();
            
            if (!$old_product) {
                header('Location: products.php');
                exit;
            }
            
            $old_image = $old_product['image'];
            
            // If new image uploaded, delete old one
            if ($image_name && $old_image && file_exists($upload_dir . $old_image)) {
                unlink($upload_dir . $old_image);
            }
            
            // Update product
            if ($image_name) {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, description = ?, price = ?, stock = ?, harvest_date = ?, vitamin = ?, iron = ?, health_benefits = ?, carbon_saving = ?, image = ? WHERE id = ? AND $admin_filter");
                $stmt->execute([$name, $category_id, $description, $price, $stock, $harvest_date, $vitamin, $iron, $health_benefits, $carbon_saving, $image_name, $product_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, description = ?, price = ?, stock = ?, harvest_date = ?, vitamin = ?, iron = ?, health_benefits = ?, carbon_saving = ? WHERE id = ? AND $admin_filter");
                $stmt->execute([$name, $category_id, $description, $price, $stock, $harvest_date, $vitamin, $iron, $health_benefits, $carbon_saving, $product_id]);
            }
        } else {
            // Cek apakah kolom admin_id ada
            $has_admin_id = false;
            try {
                $pdo->query("SELECT admin_id FROM products LIMIT 1");
                $has_admin_id = true;
            } catch (PDOException $e) {
                // Kolom admin_id belum ada
            }
            
            if ($has_admin_id && $admin_id) {
                $stmt = $pdo->prepare("INSERT INTO products (name, category_id, description, price, stock, harvest_date, vitamin, iron, health_benefits, carbon_saving, image, is_active, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
                $stmt->execute([$name, $category_id, $description, $price, $stock, $harvest_date, $vitamin, $iron, $health_benefits, $carbon_saving, $image_name, $admin_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (name, category_id, description, price, stock, harvest_date, vitamin, iron, health_benefits, carbon_saving, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$name, $category_id, $description, $price, $stock, $harvest_date, $vitamin, $iron, $health_benefits, $carbon_saving, $image_name]);
            }
        }
        
        header('Location: products.php');
        exit;
    }
}

// Get products with filter (admin only see their products if admin_id exists)
$product_list_admin_filter = '1=1';
try {
    $pdo->query("SELECT admin_id FROM products LIMIT 1");
    $product_list_admin_filter = get_admin_filter('p');
} catch (PDOException $e) {
    // Kolom admin_id belum ada, tidak filter
}
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND $product_list_admin_filter ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

$product_to_edit = null;
if (isset($_GET['edit'])) {
    $edit_admin_filter = '1=1';
    try {
        $pdo->query("SELECT admin_id FROM products LIMIT 1");
        $edit_admin_filter = get_admin_filter('products');
    } catch (PDOException $e) {
        // Kolom admin_id belum ada, tidak filter
    }
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND $edit_admin_filter");
    $stmt->execute([$_GET['edit']]);
    $product_to_edit = $stmt->fetch();
}

$page_title = 'Manajemen Produk - SayurDesa Admin';
$active_menu = 'products';

require_once 'header.php';
?>
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="fw-bold">Manajemen Produk</h1>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="fas fa-plus me-2"></i>Tambah Produk
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Tanggal Panen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $img_src = '../assets/images/products/no-image.svg';
                                        if ($product['image'] && file_exists($upload_dir . $product['image'])) {
                                            $img_src = '../assets/images/products/' . htmlspecialchars($product['image']);
                                        }
                                        ?>
                                        <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                                    <td><?php echo format_currency($product['price']); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo $product['harvest_date'] ? date('d-m-Y', strtotime($product['harvest_date'])) : '-'; ?></td>
                                    <td>
                                        <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus produk ini?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="productModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo $product_to_edit ? 'Edit Produk' : 'Tambah Produk'; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <?php if ($product_to_edit): ?>
                                <input type="hidden" name="id" value="<?php echo $product_to_edit['id']; ?>">
                            <?php endif; ?>
                            
                            <!-- Foto Produk -->
                            <div class="mb-3">
                                <label for="image" class="form-label">Foto Produk</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/webp">
                                <div class="form-text">Format: JPG, JPEG, PNG, WEBP | Maksimal 5MB</div>
                                <div class="mt-3" id="image-preview-container">
                                    <?php 
                                    $preview_src = '../assets/images/products/no-image.svg';
                                    if ($product_to_edit && $product_to_edit['image'] && file_exists($upload_dir . $product_to_edit['image'])) {
                                        $preview_src = '../assets/images/products/' . htmlspecialchars($product_to_edit['image']);
                                    }
                                    ?>
                                    <img id="image-preview" src="<?php echo $preview_src; ?>" alt="Preview" class="rounded border" style="width: 200px; height: 200px; object-fit: cover;">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $product_to_edit ? htmlspecialchars($product_to_edit['name']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $product_to_edit && $product_to_edit['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo $product_to_edit ? htmlspecialchars($product_to_edit['description']) : ''; ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Harga</label>
                                    <input type="number" class="form-control" id="price" name="price" value="<?php echo $product_to_edit ? $product_to_edit['price'] : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Stok</label>
                                    <input type="number" class="form-control" id="stock" name="stock" value="<?php echo $product_to_edit ? $product_to_edit['stock'] : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="harvest_date" class="form-label">Tanggal Panen</label>
                                <input type="date" class="form-control" id="harvest_date" name="harvest_date" value="<?php echo $product_to_edit ? $product_to_edit['harvest_date'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="vitamin" class="form-label">Vitamin</label>
                                <input type="text" class="form-control" id="vitamin" name="vitamin" value="<?php echo $product_to_edit ? htmlspecialchars($product_to_edit['vitamin']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="iron" class="form-label">Zat Besi (mg)</label>
                                <input type="number" step="0.01" class="form-control" id="iron" name="iron" value="<?php echo $product_to_edit ? $product_to_edit['iron'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="carbon_saving" class="form-label">Penghematan Karbon (kg CO2)</label>
                                <input type="number" step="0.01" class="form-control" id="carbon_saving" name="carbon_saving" value="<?php echo $product_to_edit ? $product_to_edit['carbon_saving'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="health_benefits" class="form-label">Manfaat Kesehatan</label>
                                <textarea class="form-control" id="health_benefits" name="health_benefits" rows="3"><?php echo $product_to_edit ? htmlspecialchars($product_to_edit['health_benefits']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // Image preview
            document.getElementById('image').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('image-preview').src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        </script>
        <?php if ($product_to_edit): ?>
            <script>
                const productModal = new bootstrap.Modal(document.getElementById('productModal'));
                productModal.show();
            </script>
        <?php endif; ?>
<?php require_once 'footer.php'; ?>