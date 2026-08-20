<?php
require_once '../config/config.php';
require_admin();

if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    header('Location: categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    
    if (isset($_POST['id'])) {
        $category_id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $category_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
    }
    
    header('Location: categories.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$category_to_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $category_to_edit = $stmt->fetch();
}

$page_title = 'Manajemen Kategori - SayurDesa Admin';
$active_menu = 'categories';

require_once 'header.php';
?>
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="fw-bold">Manajemen Kategori</h1>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    <i class="fas fa-plus me-2"></i>Tambah Kategori
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                                    </td>
                                    <td><?php echo htmlspecialchars($category['description'] ?? '-'); ?></td>
                                    <td>
                                        <a href="categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?');">
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
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $category_to_edit ? 'Edit Kategori' : 'Tambah Kategori'; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($category_to_edit): ?>
                            <input type="hidden" name="id" value="<?php echo $category_to_edit['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $category_to_edit ? htmlspecialchars($category_to_edit['name']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $category_to_edit ? htmlspecialchars($category_to_edit['description']) : ''; ?></textarea>
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

    <?php if ($category_to_edit): ?>
        <script>
            const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            categoryModal.show();
        </script>
    <?php endif; ?>
<?php
require_once 'footer.php';
?>
