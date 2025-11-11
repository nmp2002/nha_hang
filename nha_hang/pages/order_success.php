<?php
$pageTitle = 'Đặt hàng thành công';
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    redirect('index.php');
}

$db = getDB();
$stmt = $db->prepare("SELECT o.*, t.table_number FROM orders o 
                     LEFT JOIN tables t ON o.table_id = t.id 
                     WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('index.php');
}

$stmt = $db->prepare("SELECT oi.*, mi.name as item_name FROM order_items oi 
                     JOIN menu_items mi ON oi.menu_item_id = mi.id 
                     WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Đặt hàng thành công!</h1>
        <p>Cảm ơn bạn đã đặt hàng tại nhà hàng của chúng tôi</p>
        
        <div class="order-details">
            <h2>Thông tin đơn hàng</h2>
            <table class="order-info-table">
                <tr>
                    <td><strong>Mã đơn hàng:</strong></td>
                    <td><?php echo e($order['order_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Ngày đặt:</strong></td>
                    <td><?php echo formatDateTime($order['created_at']); ?></td>
                </tr>
                <tr>
                    <td><strong>Tên khách hàng:</strong></td>
                    <td><?php echo e($order['customer_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Số điện thoại:</strong></td>
                    <td><?php echo e($order['customer_phone']); ?></td>
                </tr>
                <?php if ($order['table_number']): ?>
                <tr>
                    <td><strong>Bàn:</strong></td>
                    <td><?php echo e($order['table_number']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Trạng thái:</strong></td>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo getOrderStatusText($order['status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Tổng tiền:</strong></td>
                    <td><strong class="total"><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                </tr>
            </table>
            
            <h3>Chi tiết đơn hàng</h3>
            <table class="order-items-table">
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
        
        <div class="success-actions">
            <a href="<?php echo BASE_URL; ?>pages/orders.php" class="btn btn-primary">Xem đơn hàng của tôi</a>
            <a href="<?php echo BASE_URL; ?>pages/menu.php" class="btn btn-secondary">Tiếp tục đặt món</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

