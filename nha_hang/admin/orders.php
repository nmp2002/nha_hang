<?php
$pageTitle = 'Quản lý đơn hàng';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

$db = getDB();
$error = '';
$success = '';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $order_id])) {
        $success = 'Cập nhật trạng thái thành công';
    } else {
        $error = 'Có lỗi xảy ra';
    }
}

// Lọc theo loại đơn hàng
$order_type_filter = $_GET['type'] ?? '';
$where_clause = '';
if ($order_type_filter === 'takeaway') {
    $where_clause = "WHERE o.order_type = 'takeaway'";
} elseif ($order_type_filter === 'dine_in') {
    $where_clause = "WHERE o.order_type = 'dine_in'";
}

// Lấy danh sách đơn hàng
$orders = $db->query("SELECT o.*, t.table_number, u.full_name as user_name 
                     FROM orders o 
                     LEFT JOIN tables t ON o.table_id = t.id 
                     LEFT JOIN users u ON o.user_id = u.id 
                     $where_clause
                     ORDER BY o.created_at DESC")->fetchAll();

// Xem chi tiết đơn hàng
$order_detail = null;
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT o.*, t.table_number, u.full_name as user_name 
                          FROM orders o 
                          LEFT JOIN tables t ON o.table_id = t.id 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order_detail = $stmt->fetch();
    
    if ($order_detail) {
        $stmt = $db->prepare("SELECT oi.*, mi.name as item_name FROM order_items oi 
                             JOIN menu_items mi ON oi.menu_item_id = mi.id 
                             WHERE oi.order_id = ?");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>
            <i class="fas <?php echo $order_type_filter === 'takeaway' ? 'fa-shopping-bag' : 'fa-shopping-cart'; ?>"></i> 
            Quản lý đơn hàng <?php echo $order_type_filter === 'takeaway' ? 'ship (mang về)' : ($order_type_filter === 'dine_in' ? 'ăn tại quán' : ''); ?>
        </h1>
        <div class="filter-tabs">
            <a href="orders.php" class="filter-tab <?php echo empty($order_type_filter) ? 'active' : ''; ?>">
                Tất cả
            </a>
            <a href="orders.php?type=dine_in" class="filter-tab <?php echo $order_type_filter === 'dine_in' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> Ăn tại quán
            </a>
            <a href="orders.php?type=takeaway" class="filter-tab <?php echo $order_type_filter === 'takeaway' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> Mang về
            </a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <?php if ($order_detail): ?>
        <div class="order-detail-view">
            <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            <h2>Chi tiết đơn hàng #<?php echo e($order_detail['order_number']); ?></h2>
            
            <div class="detail-section">
                <h3>Thông tin đơn hàng</h3>
                <table class="detail-table">
                    <tr>
                        <td>Mã đơn hàng:</td>
                        <td><?php echo e($order_detail['order_number']); ?></td>
                    </tr>
                    <tr>
                        <td>Ngày đặt:</td>
                        <td><?php echo formatDateTime($order_detail['created_at']); ?></td>
                    </tr>
                    <tr>
                        <td>Khách hàng:</td>
                        <td><?php echo e($order_detail['customer_name']); ?></td>
                    </tr>
                    <tr>
                        <td>Điện thoại:</td>
                        <td><?php echo e($order_detail['customer_phone']); ?></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><?php echo e($order_detail['customer_email'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td>Loại đơn:</td>
                        <td>
                            <span class="badge badge-<?php echo $order_detail['order_type'] === 'takeaway' ? 'info' : 'success'; ?>">
                                <?php echo $order_detail['order_type'] === 'takeaway' ? '<i class="fas fa-shopping-bag"></i> Mang về' : '<i class="fas fa-utensils"></i> Ăn tại quán'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php if ($order_detail['order_type'] === 'takeaway'): ?>
                    <tr>
                        <td>Địa chỉ giao hàng:</td>
                        <td><?php echo e($order_detail['delivery_address'] ?? '-'); ?></td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td>Bàn:</td>
                        <td><?php echo $order_detail['table_number'] ? e($order_detail['table_number']) : '-'; ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Trạng thái:</td>
                        <td>
                            <span class="status-badge status-<?php echo $order_detail['status']; ?>">
                                <?php echo getOrderStatusText($order_detail['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Tổng tiền:</td>
                        <td><strong><?php echo formatCurrency($order_detail['total_amount']); ?></strong></td>
                    </tr>
                </table>
                
                <form method="POST" class="status-form">
                    <input type="hidden" name="order_id" value="<?php echo $order_detail['id']; ?>">
                    <div class="form-group">
                        <label for="status">Cập nhật trạng thái:</label>
                        <select name="status" id="status">
                            <option value="pending" <?php echo $order_detail['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                            <option value="confirmed" <?php echo $order_detail['status'] === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                            <option value="preparing" <?php echo $order_detail['status'] === 'preparing' ? 'selected' : ''; ?>>Đang chuẩn bị</option>
                            <option value="ready" <?php echo $order_detail['status'] === 'ready' ? 'selected' : ''; ?>>Sẵn sàng</option>
                            <option value="completed" <?php echo $order_detail['status'] === 'completed' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo $order_detail['status'] === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
            
            <div class="detail-section">
                <h3>Chi tiết món ăn</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Món ăn</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo e($item['item_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatCurrency($item['price']); ?></td>
                            <td><?php echo formatCurrency($item['subtotal']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th><?php echo $order_type_filter === 'takeaway' ? 'Địa chỉ giao hàng' : 'Bàn'; ?></th>
                        <th>Tổng tiền</th>
                        <th>Loại</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo e($order['order_number']); ?></td>
                    <td><?php echo e($order['customer_name']); ?></td>
                    <td>
                        <?php if ($order['order_type'] === 'takeaway'): ?>
                            <?php echo e($order['delivery_address'] ?? '-'); ?>
                        <?php else: ?>
                            <?php echo $order['table_number'] ? e($order['table_number']) : '-'; ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $order['order_type'] === 'takeaway' ? 'info' : 'success'; ?>">
                            <?php echo $order['order_type'] === 'takeaway' ? '<i class="fas fa-shopping-bag"></i> Mang về' : '<i class="fas fa-utensils"></i> Tại quán'; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo getOrderStatusText($order['status']); ?>
                        </span>
                    </td>
                    <td><?php echo formatDateTime($order['created_at']); ?></td>
                    <td>
                        <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-small btn-info">Xem</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

