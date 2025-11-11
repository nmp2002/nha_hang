<?php
$pageTitle = 'Đơn hàng của tôi';
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng
$stmt = $db->prepare("SELECT o.*, t.table_number FROM orders o 
                     LEFT JOIN tables t ON o.table_id = t.id 
                     WHERE o.user_id = ? 
                     ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-receipt"></i> Đơn hàng của tôi</h1>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <h2>Bạn chưa có đơn hàng nào</h2>
            <a href="<?php echo BASE_URL; ?>pages/menu.php" class="btn btn-primary">Đặt món ngay</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <?php
                // Lấy chi tiết đơn hàng
                $stmt = $db->prepare("SELECT oi.*, mi.name as item_name FROM order_items oi 
                                     JOIN menu_items mi ON oi.menu_item_id = mi.id 
                                     WHERE oi.order_id = ?");
                $stmt->execute([$order['id']]);
                $order_items = $stmt->fetchAll();
                
                ?>
                
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Đơn hàng: <?php echo e($order['order_number']); ?></h3>
                            <p class="order-date">
                                <i class="fas fa-calendar"></i> 
                                <?php echo formatDateTime($order['created_at']); ?>
                            </p>
                            <p class="order-type">
                                <?php if ($order['order_type'] === 'takeaway'): ?>
                                    <i class="fas fa-shopping-bag"></i> Đơn hàng mang về
                                <?php else: ?>
                                    <i class="fas fa-utensils"></i> Đơn hàng tại quán
                                    <?php if ($order['table_number']): ?>
                                        - Bàn <?php echo e($order['table_number']); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                            <?php if ($order['order_type'] === 'takeaway' && !empty($order['delivery_address'])): ?>
                                <p class="order-delivery">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo e($order['delivery_address']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo getOrderStatusText($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h4>Chi tiết đơn hàng:</h4>
                        <ul>
                            <?php foreach ($order_items as $item): ?>
                            <li>
                                <?php echo e($item['item_name']); ?> 
                                x <?php echo $item['quantity']; ?> 
                                = <?php echo formatCurrency($item['subtotal']); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-total">
                            <strong>Tổng cộng: <?php echo formatCurrency($order['total_amount']); ?></strong>
                        </div>
                        <?php if ($order['notes']): ?>
                            <div class="order-notes">
                                <strong>Ghi chú:</strong> <?php echo e($order['notes']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

