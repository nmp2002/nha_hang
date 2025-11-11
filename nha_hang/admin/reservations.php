<?php
$pageTitle = 'Quản lý đặt bàn';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

$db = getDB();
$error = '';
$success = '';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $reservation_id = (int)$_POST['reservation_id'];
    $status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $reservation_id])) {
        // Cập nhật trạng thái bàn
        $stmt = $db->prepare("SELECT table_id FROM reservations WHERE id = ?");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch();
        
        if ($status === 'confirmed') {
            $stmt = $db->prepare("UPDATE tables SET status = 'reserved' WHERE id = ?");
            $stmt->execute([$reservation['table_id']]);
        } elseif ($status === 'completed' || $status === 'cancelled') {
            $stmt = $db->prepare("UPDATE tables SET status = 'available' WHERE id = ?");
            $stmt->execute([$reservation['table_id']]);
        }
        
        $success = 'Cập nhật trạng thái thành công';
    } else {
        $error = 'Có lỗi xảy ra';
    }
}

// Lấy danh sách đặt bàn
$reservations = $db->query("SELECT r.*, t.table_number, t.capacity 
                           FROM reservations r 
                           JOIN tables t ON r.table_id = t.id 
                           ORDER BY r.created_at DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-check"></i> Quản lý đặt bàn</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>Điện thoại</th>
                <th>Bàn</th>
                <th>Ngày/Giờ</th>
                <th>Số khách</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?php echo htmlspecialchars($reservation['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($reservation['customer_phone']); ?></td>
                <td>
                    <?php echo htmlspecialchars($reservation['table_number']); ?>
                    (<?php echo $reservation['capacity']; ?> người)
                </td>
                <td>
                    <?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?><br>
                    <?php echo date('H:i', strtotime($reservation['reservation_time'])); ?>
                </td>
                <td><?php echo $reservation['number_of_guests']; ?></td>
                <td>
                    <span class="status-badge status-<?php echo $reservation['status']; ?>">
                        <?php
                        $statuses = [
                            'pending' => 'Chờ',
                            'confirmed' => 'Xác nhận',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Hủy'
                        ];
                        echo $statuses[$reservation['status']] ?? $reservation['status'];
                        ?>
                    </span>
                </td>
                <td>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>Chờ</option>
                            <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Xác nhận</option>
                            <option value="completed" <?php echo $reservation['status'] === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Hủy</option>
                        </select>
                        <input type="hidden" name="update_status" value="1">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

