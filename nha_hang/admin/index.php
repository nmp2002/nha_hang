<?php
$pageTitle = 'Trang quản trị';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

require_once '../includes/header.php';

$db = getDB();

// Thống kê
$stats = [
    'total_orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_orders_dine_in' => $db->query("SELECT COUNT(*) FROM orders WHERE order_type = 'dine_in'")->fetchColumn(),
    'total_orders_takeaway' => $db->query("SELECT COUNT(*) FROM orders WHERE order_type = 'takeaway'")->fetchColumn(),
    'pending_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'pending_takeaway' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending' AND order_type = 'takeaway'")->fetchColumn(),
    'total_reservations' => $db->query("SELECT COUNT(*) FROM reservations WHERE status IN ('pending', 'confirmed')")->fetchColumn(),
    'total_revenue' => $db->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn() ?: 0,
    'revenue_takeaway' => $db->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' AND order_type = 'takeaway'")->fetchColumn() ?: 0
];

// Đơn hàng mới nhất
$recent_orders = $db->query("SELECT o.*, t.table_number, 
                            COALESCE(u.full_name, o.customer_name) as customer_name,
                            u.full_name as user_name 
                            FROM orders o 
                            LEFT JOIN tables t ON o.table_id = t.id 
                            LEFT JOIN users u ON o.user_id = u.id 
                            ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

// Đặt bàn mới nhất
$recent_reservations = $db->query("SELECT r.*, t.table_number 
                                  FROM reservations r 
                                  JOIN tables t ON r.table_id = t.id 
                                  ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>

<div class="admin-container">
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <h1><i class="fas fa-tachometer-alt"></i> Bảng điều khiển</h1>
                <p class="admin-subtitle">Quản lý nhà hàng hiệu quả</p>
            </div>
            <div class="admin-header-right">
                <div class="admin-welcome">
                    <span>Xin chào, <strong><?php echo e(getCurrentUser()['full_name']); ?></strong></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card stat-card-blue">
            <div class="stat-card-header">
                <div class="stat-icon blue">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_orders']); ?></h3>
                <p>Tổng đơn hàng</p>
                <div class="stat-details">
                    <span class="stat-detail-item"><i class="fas fa-utensils"></i> Tại quán: <strong><?php echo number_format($stats['total_orders_dine_in']); ?></strong></span>
                    <span class="stat-detail-item"><i class="fas fa-shopping-bag"></i> Mang về: <strong><?php echo number_format($stats['total_orders_takeaway']); ?></strong></span>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-orange">
            <div class="stat-card-header">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                <p>Đơn chờ xử lý</p>
                <div class="stat-details">
                    <span class="stat-detail-item"><i class="fas fa-shopping-bag"></i> Đơn ship: <strong><?php echo number_format($stats['pending_takeaway']); ?></strong></span>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-green">
            <div class="stat-card-header">
                <div class="stat-icon green">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_reservations']); ?></h3>
                <p>Đặt bàn đang chờ</p>
                <div class="stat-details">
                    <span class="stat-detail-item"><i class="fas fa-table"></i> Cần xác nhận</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-purple">
            <div class="stat-card-header">
                <div class="stat-icon purple">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="stat-info">
                <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                <p>Tổng doanh thu</p>
                <div class="stat-details">
                    <span class="stat-detail-item"><i class="fas fa-shipping-fast"></i> Doanh thu ship: <strong><?php echo formatCurrency($stats['revenue_takeaway']); ?></strong></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-grid">
        <div class="admin-section admin-section-enhanced">
            <div class="admin-section-header">
                <h2><i class="fas fa-shopping-cart"></i> Đơn hàng mới nhất</h2>
                <a href="orders.php" class="btn-view-all">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Loại</th>
                        <th>Bàn/Địa chỉ</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><?php echo e($order['order_number']); ?></td>
                        <td><?php echo e($order['customer_name']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo ($order['order_type'] ?? 'dine_in') === 'takeaway' ? 'info' : 'success'; ?>">
                                <?php echo ($order['order_type'] ?? 'dine_in') === 'takeaway' ? '<i class="fas fa-shopping-bag"></i> Ship' : '<i class="fas fa-utensils"></i> Tại quán'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (($order['order_type'] ?? 'dine_in') === 'takeaway'): ?>
                                <?php echo e($order['delivery_address'] ?? '-'); ?>
                            <?php else: ?>
                                <?php echo $order['table_number'] ? e($order['table_number']) : '-'; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo getOrderStatusText($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-small btn-info">
                                Xem
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="7" class="empty-table-message">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có đơn hàng nào</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
        
        <div class="admin-section admin-section-enhanced">
            <div class="admin-section-header">
                <h2><i class="fas fa-calendar-check"></i> Đặt bàn mới nhất</h2>
                <a href="reservations.php" class="btn-view-all">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Bàn</th>
                        <th>Ngày/Giờ</th>
                        <th>Số khách</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_reservations as $reservation): ?>
                    <tr>
                        <td><?php echo e($reservation['customer_name']); ?></td>
                        <td><?php echo e($reservation['table_number']); ?></td>
                        <td>
                            <?php echo formatDate($reservation['reservation_date']); ?><br>
                            <?php echo date('H:i', strtotime($reservation['reservation_time'])); ?>
                        </td>
                        <td><?php echo $reservation['number_of_guests']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                <?php echo getReservationStatusText($reservation['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="reservations.php?id=<?php echo $reservation['id']; ?>" class="btn btn-small btn-info">
                                Xem
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_reservations)): ?>
                    <tr>
                        <td colspan="6" class="empty-table-message">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có đặt bàn nào</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    
    <div class="admin-menu">
        <div class="admin-menu-header">
            <h2><i class="fas fa-th-large"></i> Quản lý</h2>
            <p class="admin-menu-subtitle">Truy cập nhanh các chức năng quản lý</p>
        </div>
        <div class="admin-menu-grid">
            <a href="orders.php" class="admin-menu-item admin-menu-item-blue">
                <div class="admin-menu-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Đơn hàng tại quán</h3>
                    <p>Quản lý đơn hàng ăn tại quán</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <a href="orders.php?type=takeaway" class="admin-menu-item admin-menu-item-orange">
                <div class="admin-menu-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Đơn hàng ship</h3>
                    <p>Quản lý đơn hàng mang về</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <a href="reservations.php" class="admin-menu-item admin-menu-item-green">
                <div class="admin-menu-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Đặt bàn</h3>
                    <p>Quản lý đặt bàn</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <a href="reports.php" class="admin-menu-item admin-menu-item-purple">
                <div class="admin-menu-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Báo cáo thống kê</h3>
                    <p>Xem báo cáo và thống kê</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <a href="tables.php" class="admin-menu-item admin-menu-item-brown">
                <div class="admin-menu-icon">
                    <i class="fas fa-table"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Bàn ăn</h3>
                    <p>Quản lý bàn ăn</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <a href="menu.php" class="admin-menu-item admin-menu-item-primary">
                <div class="admin-menu-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Thực đơn</h3>
                    <p>Quản lý món ăn</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <a href="../import_menu.php" class="admin-menu-item admin-menu-item-info">
                <div class="admin-menu-icon">
                    <i class="fas fa-file-import"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Import Menu</h3>
                    <p>Import từ Google Sites</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <?php if (isAdmin()): ?>
            <a href="users.php" class="admin-menu-item admin-menu-item-dark">
                <div class="admin-menu-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-menu-content">
                    <h3>Người dùng</h3>
                    <p>Quản lý tài khoản</p>
                </div>
                <div class="admin-menu-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

