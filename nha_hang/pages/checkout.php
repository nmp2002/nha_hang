<?php
$pageTitle = 'Thanh toán';
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    redirect('pages/cart.php');
}

// Tính tổng tiền
$item_ids = array_keys($cart);
$placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
$stmt = $db->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
$stmt->execute($item_ids);
$items = $stmt->fetchAll();

$total = 0;
$cart_items = [];
foreach ($items as $item) {
    $quantity = $cart[$item['id']];
    $subtotal = $item['price'] * $quantity;
    $total += $subtotal;
    $cart_items[] = [
        'item' => $item,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];
}

$user = getCurrentUser();
$error = '';
$success = '';

// Lấy danh sách bàn có sẵn
$tables = $db->query("SELECT * FROM tables WHERE status = 'available' ORDER BY table_number")->fetchAll();

// Mặc định là đơn hàng mang về
$order_type = 'takeaway';

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $order_type = $_POST['order_type'] ?? 'takeaway'; // Mặc định là mang về
    $table_id = !empty($_POST['table_id']) && $order_type === 'dine_in' ? (int)$_POST['table_id'] : null;
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($customer_name) || empty($customer_phone)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif ($order_type === 'takeaway' && empty($delivery_address)) {
        $error = 'Vui lòng nhập địa chỉ giao hàng';
    } else {
        try {
            $db->beginTransaction();
            
            $order_number = generateOrderNumber();
            $stmt = $db->prepare("INSERT INTO orders (user_id, table_id, order_type, order_number, customer_name, customer_phone, 
                                 customer_email, delivery_address, total_amount, payment_method, notes, status) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $user['id'],
                $table_id,
                $order_type,
                $order_number,
                $customer_name,
                $customer_phone,
                $customer_email ?: null,
                $order_type === 'takeaway' ? $delivery_address : null,
                $total,
                $payment_method,
                $notes ?: null
            ]);
            
            $order_id = $db->lastInsertId();
            
            foreach ($cart_items as $cart_item) {
                $stmt = $db->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price, subtotal) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $order_id,
                    $cart_item['item']['id'],
                    $cart_item['quantity'],
                    $cart_item['item']['price'],
                    $cart_item['subtotal']
                ]);
            }
            
            // Cập nhật trạng thái bàn nếu có
            if ($table_id) {
                $stmt = $db->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?");
                $stmt->execute([$table_id]);
            }
            
            $db->commit();
            
            // Xóa giỏ hàng
            unset($_SESSION['cart']);
            
            redirect('pages/order_success.php?order_id=' . $order_id);
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Thanh toán</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <div class="checkout-container">
        <div class="checkout-form-section">
            <h2>Thông tin đặt hàng</h2>
            <form method="POST" action="" id="checkout-form">
                <input type="hidden" name="order_type" id="order_type" value="takeaway">
                
                <div class="form-group">
                    <label>Loại đơn hàng <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="order_type_radio" value="takeaway" checked onchange="toggleOrderType()">
                            <i class="fas fa-shopping-bag"></i> Mang về
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="order_type_radio" value="dine_in" onchange="toggleOrderType()">
                            <i class="fas fa-utensils"></i> Ăn tại quán
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="customer_name">Họ tên <span class="required">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" required 
                           value="<?php echo e($user['full_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="customer_phone">Số điện thoại <span class="required">*</span></label>
                    <input type="tel" id="customer_phone" name="customer_phone" required 
                           value="<?php echo e($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="customer_email">Email</label>
                    <input type="email" id="customer_email" name="customer_email" 
                           value="<?php echo e($user['email']); ?>">
                </div>
                
                <div class="form-group" id="delivery-address-group">
                    <label for="delivery_address">Địa chỉ giao hàng <span class="required">*</span></label>
                    <textarea id="delivery_address" name="delivery_address" rows="3" required 
                              placeholder="Nhập địa chỉ chi tiết để giao hàng..."><?php echo e($_POST['delivery_address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group" id="table-group" style="display: none;">
                    <label for="table_id">Chọn bàn <span class="required">*</span></label>
                    <select id="table_id" name="table_id">
                        <option value="">-- Chọn bàn --</option>
                        <?php foreach ($tables as $table): ?>
                            <option value="<?php echo $table['id']; ?>">
                                <?php echo htmlspecialchars($table['table_number']); ?> - 
                                <?php echo $table['capacity']; ?> người - 
                                <?php echo htmlspecialchars($table['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Phương thức thanh toán</label>
                    <select id="payment_method" name="payment_method">
                        <option value="cash">Tiền mặt</option>
                        <option value="card">Thẻ</option>
                        <option value="bank_transfer">Chuyển khoản</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Ghi chú</label>
                    <textarea id="notes" name="notes" rows="3" 
                              placeholder="Yêu cầu đặc biệt..."><?php echo e($_POST['notes'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" name="place_order" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Đặt hàng
                </button>
            </form>
        </div>
        
        <div class="checkout-summary">
            <h2>Đơn hàng của bạn</h2>
            <div class="order-summary">
                <?php foreach ($cart_items as $cart_item): ?>
                <div class="summary-item">
                    <span><?php echo e($cart_item['item']['name']); ?> x <?php echo $cart_item['quantity']; ?></span>
                    <span><?php echo formatCurrency($cart_item['subtotal']); ?></span>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-total">
                    <strong>Tổng cộng: <?php echo formatCurrency($total); ?></strong>
                </div>
            </div>
            
            <a href="<?php echo BASE_URL; ?>pages/cart.php" class="btn btn-secondary btn-block">Quay lại giỏ hàng</a>
        </div>
    </div>
</div>

<script>
function toggleOrderType() {
    const orderType = document.querySelector('input[name="order_type_radio"]:checked').value;
    document.getElementById('order_type').value = orderType;
    
    const deliveryGroup = document.getElementById('delivery-address-group');
    const tableGroup = document.getElementById('table-group');
    const deliveryInput = document.getElementById('delivery_address');
    const tableInput = document.getElementById('table_id');
    
    if (orderType === 'takeaway') {
        deliveryGroup.style.display = 'block';
        tableGroup.style.display = 'none';
        deliveryInput.required = true;
        tableInput.required = false;
    } else {
        deliveryGroup.style.display = 'none';
        tableGroup.style.display = 'block';
        deliveryInput.required = false;
        tableInput.required = true;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleOrderType();
});
</script>

<?php require_once '../includes/footer.php'; ?>

