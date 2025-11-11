<?php
$pageTitle = 'Giỏ hàng';
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total = 0;

if (!empty($cart)) {
    $item_ids = array_keys($cart);
    $placeholders = str_repeat('?,', count($item_ids) - 1) . '?';
    $stmt = $db->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders)");
    $stmt->execute($item_ids);
    $items = $stmt->fetchAll();
    
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
}

// Xử lý cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $item_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$item_id]);
            } else {
                $_SESSION['cart'][$item_id] = $quantity;
            }
        }
        redirect('pages/cart.php');
    }
    
    if (isset($_POST['remove_item'])) {
        unset($_SESSION['cart'][$_POST['item_id']]);
        redirect('pages/cart.php');
    }
    
    if (isset($_POST['checkout'])) {
        redirect('pages/checkout.php');
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-shopping-cart"></i> Giỏ hàng</h1>
    </div>
    
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Giỏ hàng trống</h2>
            <p>Bạn chưa có món nào trong giỏ hàng</p>
            <a href="<?php echo BASE_URL; ?>pages/menu.php" class="btn btn-primary">Xem thực đơn</a>
        </div>
    <?php else: ?>
        <form method="POST" action="">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Món ăn</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $cart_item): ?>
                    <tr>
                        <td>
                            <strong><?php echo e($cart_item['item']['name']); ?></strong>
                        </td>
                        <td><?php echo formatCurrency($cart_item['item']['price']); ?></td>
                        <td>
                            <input type="number" name="quantity[<?php echo $cart_item['item']['id']; ?>]" 
                                   value="<?php echo $cart_item['quantity']; ?>" min="1" class="qty-input">
                        </td>
                        <td><strong><?php echo formatCurrency($cart_item['subtotal']); ?></strong></td>
                        <td>
                            <form method="POST" style="display: inline-block;" 
                                  onsubmit="return confirm('Bạn có chắc muốn xóa món này?')">
                                <input type="hidden" name="item_id" value="<?php echo $cart_item['item']['id']; ?>">
                                <button type="submit" name="remove_item" value="1" 
                                        class="btn btn-danger btn-small">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Tổng cộng:</strong></td>
                        <td><strong class="total-amount"><?php echo formatCurrency($total); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="cart-actions">
                <a href="<?php echo BASE_URL; ?>pages/menu.php" class="btn btn-secondary">Tiếp tục mua sắm</a>
                <button type="submit" name="update_cart" class="btn btn-info">Cập nhật giỏ hàng</button>
                <button type="submit" name="checkout" class="btn btn-primary">Thanh toán</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

