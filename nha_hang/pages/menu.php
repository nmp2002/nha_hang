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

<?php
// Preferred global order for menu items (as requested) - items not in this list will appear after
$preferredOrder = [
    'Bánh xèo', 'Bánh khọt nước dừa', 'Hến xúc bánh đa', 'Mực chiên giòn', 'Cơm cháy mỡ hành chà bông',
    'Sụn gà rang muối', 'Chả mực Hạ Long', 'Ốc bươu nhồi thịt', 'Sụn gà chiên nước mắm', 'Tôm bọc cốm xanh',
    'Chả giò hải sản', 'Cơm cháy chảo kho quẹt', 'Bánh hỏi heo quay', 'Xôi chiên chà bông', 'Bắp giò heo rút xương lên mẹt',
    'Chả cá lên mẹt', 'Gân bò xào cần', 'Nem nướng lên mẹt', 'Bò viên nước lèo', 'Bò viên gân chiên',
    'Chả giò tôm đất', 'Ếch chiên nước mắm', 'Dồi sụn chấm mắm tôm', 'Salad dầu giấm', 'Bò trộn salad',
    'Gỏi mực chua cay', 'Gỏi cuốn tôm thịt', 'Gỏi dưa leo tôm khô', 'Gỏi tôm sốt Thái', 'Gỏi gà bắp chuối',
    'Gỏi xoài tôm khô', 'Gỏi ngó sen tôm thịt', 'Gỏi cuốn bò áp chảo', 'Thịt kho tiêu', 'Thịt kho trứng',
    'Ba rọi cháy cạnh', 'Thịt luộc cà pháo mắm tôm', 'Mắm chưng', 'Ba rọi mắm ruốc', 'Ba rọi rim dừa',
    'Cá lóc kho tộ', 'Cá basa kho tộ', 'Cá thu kho tộ', 'Cá trứng chiên mắm me', 'Cá bống trứng kho tiêu',
    'Cá bớp kho tộ', 'Cá ngừ kho thơm', 'Cá nục kho khô', 'Cá nục kho măng', 'Cá diêu hồng chiên sốt cà',
    'Cá thu sốt cà', 'Mắm kho miền Tây', 'Cá trê chiên mắm xoài', 'Cá diêu hồng chiên xù cuốn bánh tráng', 'Cá sặc trộn xoài',
    'Cá thu chiên mắm xoài', 'Khô cá đù', 'Khô cá dứa', 'Khô cá lóc', 'Mực chiên giòn',
    'Mực xào chua ngọt', 'Mực ống nhồi thịt', 'Mực chiên nước mắm', 'Mực trứng hấp gừng', 'Tôm rim mặn',
    'Tôm rim thịt', 'Tép rong rang khế', 'Tôm rang muối cay', 'Sườn xào chua ngọt', 'Sườn ram mặn',
    'Sườn cọng chiên muối ớt', 'Đậu hủ nhồi thịt sốt cà', 'Đậu hủ chiên giòn chấm mắm tôm', 'Gà tre hấp mắm nhĩ', 'Gà kho gừng',
    'Gà sả ớt', 'Cánh gà chiên nước mắm', 'Vịt kho gừng', 'Trứng chiên thịt', 'Trứng chiên hến',
    'Trứng chiên cà chua', 'Trứng chiên hành', 'Bò xào rau muống', 'Bò xào bí nụ', 'Bò xào cải thìa',
    'Đọt su xào bò', 'Bò xào cải ngồng', 'Bò xào hành cần', 'Cải bó xôi xào bò', 'Bông hẹ xào bò',
    'Cải thìa xào nấm đông cô', 'Cải ngồng xào dầu hào', 'Rau luộc thập cẩm kho quẹt', 'Khổ qua xào trứng', 'Nụ bí xào tỏi',
    'Rau muống xào tỏi', 'Đọt su xào tỏi'
];

// Default image mapping for common dishes (fallback when DB image missing)
$default_images = [
    'Bánh xèo' => 'banh-xeo-20231120153146-rag33.png',
    'Bánh khọt nước dừa' => 'banh-khot-20231120152154--n0xw.png',
    'Gỏi cuốn tôm thịt' => 'gỏi cuốn tôm thịt.jpg',
    'Thịt kho tiêu' => 'thịt kho tiêu.jpg',
    'Thịt luộc cà pháo mắm tôm' => 'thịt luộc cà pháo.jpg',
    'Xôi chiên chà bông' => 'xôi chiên chà bông.jpg',
    'Đậu hủ nhồi thịt sốt cà' => 'đậu hủ nhồi thịt xốt cà.jpg',
    'Ếch chiên nước mắm' => 'ếch xào xả ớt.jpg',
    'Ốc bươu nhồi thịt' => 'ốc bươu nhồi thịt.jpg',
    'Ba rọi cháy cạnh' => 'ba rọi chiên mắm tỏi.jpg',
    'Cơm cháy mỡ hành chà bông' => 'thit-kho-20231130124006-clg9w.png',
    // generic placeholder
    'default' => 'element-01-20231113155532-tetdx.png'
];

// Build an index of available images by scanning known image folders so we can match by dish name
$imageDirs = [
    __DIR__ . '/../assets/images',
    __DIR__ . '/../List đồ ăn',
    __DIR__ . '/../list food 41-60',
    __DIR__ . '/../Món ăn 67-90'
];

$availableImages = [];
foreach ($imageDirs as $dir) {
    if (!is_dir($dir)) continue;
    foreach (scandir($dir) as $f) {
        if (in_array($f, ['.', '..', '.DS_Store'])) continue;
        $path = $dir . '/' . $f;
        if (!is_file($path)) continue;
        $base = pathinfo($f, PATHINFO_FILENAME);
        // normalize filename for matching
        $norm = strtolower(trim($base));
        // remove accents (transliterate) and non-alnum
        $norm = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $norm);
        $norm = preg_replace('/[^a-z0-9]/', '', $norm);
        // store relative web path
        $rel = 'assets/images/' . $f;
        $availableImages[$norm] = $rel;
    }
}

function normalize_name_for_match($s) {
    $s = strtolower(trim($s));
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    $s = preg_replace('/[^a-z0-9]/', '', $s);
    return $s;
}

function find_image_for_dish($dishName, $dbImage, $availableImages, $default_images) {
    // 1) prefer DB-specified image
    if (!empty($dbImage) && file_exists(__DIR__ . '/../' . ltrim($dbImage, '/'))) {
        return BASE_URL . ltrim($dbImage, '/');
    }

    // 2) try direct default_images mapping by exact dish name
    if (isset($default_images[$dishName])) {
        return BASE_URL . 'assets/images/' . $default_images[$dishName];
    }

    // 3) try fuzzy matching by normalized names
    $norm = normalize_name_for_match($dishName);
    if ($norm && isset($availableImages[$norm])) {
        return BASE_URL . $availableImages[$norm];
    }

    // 4) try contains match (filename contains dish key or vice versa)
    foreach ($availableImages as $key => $rel) {
        if (strpos($key, $norm) !== false || strpos($norm, $key) !== false) {
            return BASE_URL . $rel;
        }
    }

    // 5) fallback to generic
    return BASE_URL . 'assets/images/' . $default_images['default'];
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

            // Re-order items according to preferred global order if possible
            usort($items, function($a, $b) use ($preferredOrder) {
                $i = array_search($a['name'], $preferredOrder);
                $j = array_search($b['name'], $preferredOrder);
                if ($i === false) $i = PHP_INT_MAX;
                if ($j === false) $j = PHP_INT_MAX;
                return $i - $j;
            });
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
                            <?php $imgSrc = find_image_for_dish($item['name'], $item['image'] ?? null, $availableImages, $default_images); ?>
                            <img src="<?php echo $imgSrc; ?>" alt="<?php echo e($item['name']); ?>">
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

