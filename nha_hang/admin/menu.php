<?php
$pageTitle = 'Quản lý thực đơn';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

$db = getDB();
$error = '';
$success = '';

// Xử lý thêm/sửa món ăn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $category_id = (int)$_POST['category_id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        if (empty($name) || $price <= 0) {
            $error = 'Vui lòng nhập đầy đủ thông tin';
        } else {
            $stmt = $db->prepare("INSERT INTO menu_items (category_id, name, description, price, is_available) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$category_id, $name, $description, $price, $is_available])) {
                $success = 'Thêm món ăn thành công';
            } else {
                $error = 'Có lỗi xảy ra';
            }
        }
    }
    
    if (isset($_POST['update_item'])) {
        $item_id = (int)$_POST['item_id'];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, is_available = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $price, $is_available, $item_id])) {
            $success = 'Cập nhật món ăn thành công';
        } else {
            $error = 'Có lỗi xảy ra';
        }
    }
    
    if (isset($_POST['delete_item'])) {
        $item_id = (int)$_POST['item_id'];
        $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ?");
        if ($stmt->execute([$item_id])) {
            $success = 'Xóa món ăn thành công';
        } else {
            $error = 'Không thể xóa món ăn đang được sử dụng';
        }
    }
}

// Lấy danh mục
$categories = $db->query("SELECT * FROM categories ORDER BY display_order")->fetchAll();

// Lấy món ăn
$menu_items = $db->query("SELECT mi.*, c.name as category_name FROM menu_items mi 
                         JOIN categories c ON mi.category_id = c.id 
                         ORDER BY c.display_order, mi.display_order")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-utensils"></i> Quản lý thực đơn</h1>
        <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="../import_full_menu.php" class="btn btn-primary" style="background: #28a745;">
                <i class="fas fa-upload"></i> Import Toàn Bộ Menu
            </a>
            <a href="../import_menu.php" class="btn btn-primary">
                <i class="fas fa-file-import"></i> Import Menu từ Google Sites
            </a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="admin-section">
        <h2>Thêm món ăn mới</h2>
        <form method="POST" class="add-item-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tên món <span class="required">*</span></label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Giá (VNĐ) <span class="required">*</span></label>
                    <input type="number" name="price" step="1000" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_available" checked> Có sẵn
                </label>
            </div>
            <button type="submit" name="add_item" class="btn btn-primary">Thêm món</button>
        </form>
    </div>
    
    <div class="admin-section">
        <h2>Danh sách món ăn</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Danh mục</th>
                    <th>Tên món</th>
                    <th>Mô tả</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menu_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                    <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo formatCurrency($item['price']); ?></td>
                    <td>
                        <?php if ($item['is_available']): ?>
                            <span class="status-badge status-available">Có sẵn</span>
                        <?php else: ?>
                            <span class="status-badge status-cancelled">Hết</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                                class="btn btn-small btn-info">Sửa</button>
                        <form method="POST" style="display: inline-block;" 
                              onsubmit="return confirm('Bạn có chắc muốn xóa món này?')">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="delete_item" class="btn btn-small btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal sửa món ăn -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Sửa món ăn</h2>
        <form method="POST" id="editForm">
            <input type="hidden" name="item_id" id="edit_item_id">
            <div class="form-group">
                <label>Danh mục</label>
                <select name="category_id" id="edit_category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tên món</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" id="edit_description" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>Giá (VNĐ)</label>
                <input type="number" name="price" id="edit_price" step="1000" min="0" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_available" id="edit_is_available"> Có sẵn
                </label>
            </div>
            <button type="submit" name="update_item" class="btn btn-primary">Cập nhật</button>
            <button type="button" onclick="closeModal()" class="btn btn-secondary">Hủy</button>
        </form>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('edit_item_id').value = item.id;
    document.getElementById('edit_category_id').value = item.category_id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_is_available').checked = item.is_available == 1;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>

