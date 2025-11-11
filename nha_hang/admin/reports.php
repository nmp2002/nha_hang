<?php
$pageTitle = 'Báo cáo thống kê';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

require_once '../includes/header.php';

$db = getDB();

// Lọc theo thời gian
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // Đầu tháng
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Hôm nay

// Thống kê tổng quan
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['total_orders'] = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE order_type = 'dine_in' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['total_orders_dine_in'] = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE order_type = 'takeaway' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['total_orders_takeaway'] = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

$stmt = $db->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' AND order_type = 'dine_in' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['revenue_dine_in'] = $stmt->fetchColumn() ?: 0;

$stmt = $db->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' AND order_type = 'takeaway' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['revenue_takeaway'] = $stmt->fetchColumn() ?: 0;

$stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['total_reservations'] = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['completed_orders'] = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE status = 'cancelled' AND DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$date_from, $date_to]);
$stats['cancelled_orders'] = $stmt->fetchColumn();

// Thống kê theo ngày (7 ngày gần nhất)
$daily_stats = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_stats[$date] = [
        'orders' => $db->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = '$date'")->fetchColumn(),
        'revenue' => $db->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' AND DATE(created_at) = '$date'")->fetchColumn() ?: 0,
        'takeaway' => $db->query("SELECT COUNT(*) FROM orders WHERE order_type = 'takeaway' AND DATE(created_at) = '$date'")->fetchColumn(),
        'dine_in' => $db->query("SELECT COUNT(*) FROM orders WHERE order_type = 'dine_in' AND DATE(created_at) = '$date'")->fetchColumn()
    ];
}

// Top món ăn bán chạy
$top_items = $db->query("SELECT mi.name, SUM(oi.quantity) as total_quantity, SUM(oi.subtotal) as total_revenue
                        FROM order_items oi
                        JOIN menu_items mi ON oi.menu_item_id = mi.id
                        JOIN orders o ON oi.order_id = o.id
                        WHERE DATE(o.created_at) BETWEEN '$date_from' AND '$date_to'
                        GROUP BY mi.id, mi.name
                        ORDER BY total_quantity DESC
                        LIMIT 10")->fetchAll();

// Thống kê theo trạng thái
$status_stats = $db->query("SELECT status, COUNT(*) as count FROM orders 
                           WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'
                           GROUP BY status")->fetchAll();
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> Báo cáo thống kê</h1>
        
        <form method="GET" action="" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="date_from">Từ ngày:</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo e($date_from); ?>" required>
                </div>
                <div class="form-group">
                    <label for="date_to">Đến ngày:</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo e($date_to); ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Thống kê tổng quan -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_orders']); ?></h3>
                <p>Tổng đơn hàng</p>
                <small>Ăn tại quán: <?php echo number_format($stats['total_orders_dine_in']); ?> | Mang về: <?php echo number_format($stats['total_orders_takeaway']); ?></small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['completed_orders']); ?></h3>
                <p>Đơn hoàn thành</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['cancelled_orders']); ?></h3>
                <p>Đơn đã hủy</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                <p>Tổng doanh thu</p>
                <small>Tại quán: <?php echo formatCurrency($stats['revenue_dine_in']); ?> | Ship: <?php echo formatCurrency($stats['revenue_takeaway']); ?></small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total_reservations']); ?></h3>
                <p>Đặt bàn</p>
            </div>
        </div>
    </div>
    
    <!-- Thống kê theo ngày -->
    <div class="admin-section">
        <h2>Thống kê 7 ngày gần nhất</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Tổng đơn</th>
                    <th>Ăn tại quán</th>
                    <th>Mang về</th>
                    <th>Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_stats as $date => $day_stats): ?>
                <tr>
                    <td><?php echo formatDate($date); ?></td>
                    <td><?php echo number_format($day_stats['orders']); ?></td>
                    <td><?php echo number_format($day_stats['dine_in']); ?></td>
                    <td><?php echo number_format($day_stats['takeaway']); ?></td>
                    <td><?php echo formatCurrency($day_stats['revenue']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Top món ăn bán chạy -->
    <div class="admin-section">
        <h2>Top 10 món ăn bán chạy</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên món</th>
                    <th>Số lượng</th>
                    <th>Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_items as $index => $item): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo e($item['name']); ?></td>
                    <td><?php echo number_format($item['total_quantity']); ?></td>
                    <td><?php echo formatCurrency($item['total_revenue']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Thống kê theo trạng thái -->
    <div class="admin-section">
        <h2>Thống kê theo trạng thái</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Trạng thái</th>
                    <th>Số lượng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($status_stats as $stat): ?>
                <tr>
                    <td><?php echo getOrderStatusText($stat['status']); ?></td>
                    <td><?php echo number_format($stat['count']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

