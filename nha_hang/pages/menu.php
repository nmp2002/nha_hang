<?php
$pageTitle = 'Thực đơn';
require_once '../config/config.php';
require_once '../includes/header.php';

$db = getDB();
// Only show three main categories and in this order
$desired = ['Khai vị', 'Món chính', 'Tráng miệng'];
$placeholders = implode(',', array_fill(0, count($desired), '?'));
$orderList = "'" . implode("','", $desired) . "'";
$stmt = $db->prepare("SELECT * FROM categories WHERE name IN ($placeholders) ORDER BY FIELD(name, $orderList)");
$stmt->execute($desired);
$categories = $stmt->fetchAll();

// Xử lý thêm vào giỏ hàng (session)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }
    
    $success_message = 'Đã thêm vào giỏ hàng!';
}
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-bowl-rice"></i> Thực đơn Cơm Quê</h1>
        <p class="section-subtitle">Chuẩn vị cơm nhà - Hương vị miền Tây đậm đà</p>
    </div>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <?php if (isLoggedIn()): ?>
        <div class="cart-info">
            <a href="<?php echo BASE_URL; ?>pages/cart.php" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Giỏ hàng 
                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="cart-count"><?php echo array_sum($_SESSION['cart']); ?></span>
                <?php endif; ?>
            </a>
        </div>
    <?php endif; ?>
    
    <?php foreach ($categories as $category): ?>
        <?php
            // Fetch items for this category
            $stmt = $db->prepare("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1 ORDER BY display_order");
            $stmt->execute([$category['id']]);
            $items = $stmt->fetchAll();
            $countItems = count($items);
        ?>
        <section class="menu-section" id="category-<?php echo $category['id']; ?>">
            <div class="menu-section-header">
                <h2><?php echo e($category['name']); ?></h2>
                <div class="menu-section-actions">
                    <span class="item-count"><?php echo $countItems; ?> món</span>
                    <button class="btn btn-outline toggle-menu-btn" data-target="category-<?php echo $category['id']; ?>-grid" aria-expanded="false">Xem món</button>
                </div>
            </div>

            <?php if ($category['description']): ?>
                <p class="category-description"><?php echo e($category['description']); ?></p>
            <?php endif; ?>

            <div class="menu-grid collapsed" id="category-<?php echo $category['id']; ?>-grid">
                <?php foreach ($items as $item): ?>
                <div class="menu-item" id="dish-<?php echo $item['id']; ?>">
                    <div class="menu-item-image">
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?php echo BASE_URL . $item['image']; ?>" alt="<?php echo e($item['name']); ?>">
                        <?php else: ?>
                            <i class="fas fa-utensils"></i>
                        <?php endif; ?>
                    </div>
                    <div class="menu-item-content">
                        <h3><?php echo e($item['name']); ?></h3>
                        <?php if ($item['description']): ?>
                            <p><?php echo e($item['description']); ?></p>
                        <?php endif; ?>
                        <div class="menu-item-footer">
                            <span class="price"><?php echo formatCurrency($item['price']); ?></span>
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <div class="quantity-input">
                                        <button type="button" class="qty-btn" onclick="changeQty(this, -1)">-</button>
                                        <input type="number" name="quantity" value="1" min="1" class="qty-input">
                                        <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                                    </div>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-small">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="../login.php?redirect=menu" class="btn btn-secondary btn-small">
                                    <i class="fas fa-lock"></i> Đăng nhập để đặt món
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<script>
function changeQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    const currentVal = parseInt(input.value) || 1;
    const newVal = Math.max(1, currentVal + delta);
    input.value = newVal;
}

document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.toggle-menu-btn');
    toggles.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const grid = document.getElementById(targetId);
            if (!grid) return;
            const expanded = this.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                grid.classList.add('collapsed');
                this.setAttribute('aria-expanded', 'false');
                this.textContent = 'Xem món';
            } else {
                grid.classList.remove('collapsed');
                this.setAttribute('aria-expanded', 'true');
                this.textContent = 'Ẩn';
                // scroll into view a bit
                grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

