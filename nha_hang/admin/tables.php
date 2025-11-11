<?php
$pageTitle = 'Quản lý bàn ăn';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

$db = getDB();
$error = '';
$success = '';

// Xử lý thêm/sửa/xóa bàn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_table'])) {
        $table_number = trim($_POST['table_number'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        
        if (empty($table_number) || $capacity <= 0) {
            $error = 'Vui lòng nhập đầy đủ thông tin';
        } else {
            $stmt = $db->prepare("INSERT INTO tables (table_number, capacity, location, status) VALUES (?, ?, ?, 'available')");
            if ($stmt->execute([$table_number, $capacity, $location])) {
                $success = 'Thêm bàn thành công';
            } else {
                $error = 'Bàn số này đã tồn tại';
            }
        }
    }
    
    if (isset($_POST['update_table'])) {
        $table_id = (int)$_POST['table_id'];
        $status = $_POST['status'];
        
        $stmt = $db->prepare("UPDATE tables SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $table_id])) {
            $success = 'Cập nhật trạng thái thành công';
        } else {
            $error = 'Có lỗi xảy ra';
        }
    }
    
    if (isset($_POST['delete_table'])) {
        $table_id = (int)$_POST['table_id'];
        $stmt = $db->prepare("DELETE FROM tables WHERE id = ?");
        if ($stmt->execute([$table_id])) {
            $success = 'Xóa bàn thành công';
        } else {
            $error = 'Không thể xóa bàn đang được sử dụng';
        }
    }
}

// Lấy danh sách bàn
$tables = $db->query("SELECT * FROM tables ORDER BY table_number")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-table"></i> Quản lý bàn ăn</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="admin-section">
        <h2>Thêm bàn mới</h2>
        <form method="POST" class="form-inline">
            <div class="form-group">
                <input type="text" name="table_number" placeholder="Số bàn (VD: T11)" required>
            </div>
            <div class="form-group">
                <input type="number" name="capacity" placeholder="Sức chứa" min="1" required>
            </div>
            <div class="form-group">
                <input type="text" name="location" placeholder="Vị trí (VD: Tầng 1)">
            </div>
            <button type="submit" name="add_table" class="btn btn-primary">Thêm bàn</button>
        </form>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Số bàn</th>
                <th>Sức chứa</th>
                <th>Vị trí</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $table): ?>
            <tr>
                <td><?php echo htmlspecialchars($table['table_number']); ?></td>
                <td><?php echo $table['capacity']; ?> người</td>
                <td><?php echo htmlspecialchars($table['location']); ?></td>
                <td>
                    <span class="status-badge status-<?php echo $table['status']; ?>">
                        <?php
                        $statuses = [
                            'available' => 'Trống',
                            'occupied' => 'Đang dùng',
                            'reserved' => 'Đã đặt',
                            'maintenance' => 'Bảo trì'
                        ];
                        echo $statuses[$table['status']] ?? $table['status'];
                        ?>
                    </span>
                </td>
                <td>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="table_id" value="<?php echo $table['id']; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="available" <?php echo $table['status'] === 'available' ? 'selected' : ''; ?>>Trống</option>
                            <option value="occupied" <?php echo $table['status'] === 'occupied' ? 'selected' : ''; ?>>Đang dùng</option>
                            <option value="reserved" <?php echo $table['status'] === 'reserved' ? 'selected' : ''; ?>>Đã đặt</option>
                            <option value="maintenance" <?php echo $table['status'] === 'maintenance' ? 'selected' : ''; ?>>Bảo trì</option>
                        </select>
                        <input type="hidden" name="update_table" value="1">
                    </form>
                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa bàn này?')">
                        <input type="hidden" name="table_id" value="<?php echo $table['id']; ?>">
                        <button type="submit" name="delete_table" class="btn btn-small btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

