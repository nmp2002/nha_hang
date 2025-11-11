<?php
$pageTitle = 'Trang chủ';
$isHomePage = true;
require_once 'config/config.php';

// Redirect admin/staff to admin dashboard
if (isLoggedIn() && (isAdmin() || isStaff())) {
    redirect('admin/index.php');
}

require_once 'includes/header.php';
?>

<div class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">Cơm Quê Dượng Bầu</h1>
            <p class="hero-slogan">Chuẩn vị cơm nhà</p>
            <p class="hero-description">
                Cơm nhà là điều xa xỉ giữa cuộc sống đầy bận rộn. Nếu thèm một bữa cơm quê với thịt kho tiêu, canh chua cá hú, đừng ngại ngần mà đến ngay Cơm Quê Dượng Bầu.
            </p>
            <p class="hero-subdescription">
                Tại đây, bạn sẽ thưởng thức vị cơm quê nhà ngay tại trung tâm thành phố. Khơi dậy hương vị tuổi thơ và cảm giác ấm cúng trong không gian của chúng tôi.
            </p>
        </div>
        <div class="hero-buttons">
            <a href="pages/menu.php" class="btn btn-primary">
                <i class="fas fa-book-open"></i> Xem thực đơn
            </a>
            <a href="pages/reservation.php" class="btn btn-secondary">
                <i class="fas fa-calendar-check"></i> Đặt bàn ngay
            </a>
            <?php if (isLoggedIn()): ?>
            <a href="pages/menu.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Đặt món mang về
            </a>
            <?php else: ?>
            <a href="login.php?redirect=menu" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Đặt món mang về
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="intro-section">
    <div class="container">
        <div class="intro-wrapper">
            <div class="intro-art-left">
                <img src="<?php echo BASE_URL; ?>assets/images/tranh-dong-ho-4-20231123112227-fiqqe.png" alt="Tranh Đông Hồ" class="intro-art-img">
            </div>
            <div class="intro-image">
                <img src="<?php echo BASE_URL; ?>assets/images/quan-com-duong-bau7077cr3-copy-20231110174138-bzrrb.png" alt="Món ăn Cơm Quê Dượng Bầu" class="intro-food-img">
            </div>
            <div class="intro-content">
                <h2 class="intro-main-title">CƠM QUÊ DƯỢNG BẦU</h2>
                <div class="intro-divider"></div>
                <p class="intro-text">
                    Cơm nhà là điều xa xỉ giữa cuộc sống đầy bận rộn. Nếu thèm một bữa cơm quê với thịt kho tiêu, canh chua cá hú, đừng ngại ngần mà đến ngay Cơm Quê Dượng Bầu.
                </p>
                <p class="intro-text">
                    Tại đây, bạn sẽ thưởng thức vị cơm quê nhà ngay tại trung tâm thành phố. Khơi dậy hương vị tuổi thơ và cảm giác ấm cúng trong không gian của chúng tôi. Hãy đến ngay để thưởng thức món ăn "Chuẩn vị cơm nhà" nhé!
                </p>
                <div class="intro-buttons">
                    <a href="<?php echo BASE_URL; ?>pages/reservation.php" class="btn btn-primary intro-btn">
                        ĐẶT BÀN NGAY
                    </a>
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>pages/menu.php" class="btn btn-secondary intro-btn">
                        <i class="fas fa-shopping-bag"></i> ĐẶT MÓN MANG VỀ
                    </a>
                    <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php?redirect=menu" class="btn btn-secondary intro-btn">
                        <i class="fas fa-shopping-bag"></i> ĐẶT MÓN MANG VỀ
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="intro-illustration">
                <img src="<?php echo BASE_URL; ?>assets/images/element-05-20231113170549-z_wiv.png" alt="Tranh dân gian" class="intro-art-img">
            </div>
            <div class="intro-art-right">
                <img src="<?php echo BASE_URL; ?>assets/images/tranh-dong-ho-4-20231123112227-fiqqe.png" alt="Tranh Đông Hồ" class="intro-art-img">
            </div>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <div class="section-header">
            <h2>Vì sao chọn Cơm Quê Dượng Bầu?</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bowl-rice"></i>
                </div>
                <h3>Chuẩn vị cơm quê</h3>
                <p>Hương vị đậm đà, truyền thống miền Tây</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3>Nguyên liệu tươi ngon</h3>
                <p>Chọn lọc từ nguồn cung uy tín, đảm bảo chất lượng</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Nấu nướng tận tâm</h3>
                <p>Mỗi món ăn được chế biến với tình yêu và sự cẩn thận</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3>Không gian ấm cúng</h3>
                <p>Mang đến cảm giác như về nhà, thân thiện và gần gũi</p>
            </div>
        </div>
    </div>
</section>

<section class="popular-dishes">
    <div class="container">
        <div class="section-header">
            <h2>Món ngon NỔI BẬT</h2>
            <p class="section-subtitle">Những món ăn được yêu thích nhất</p>
        </div>
        <div class="dishes-carousel-wrapper">
            <div class="dishes-carousel" id="dishesCarousel">
                <?php
                $db = getDB();
                $stmt = $db->query("SELECT mi.*, c.name as category_name FROM menu_items mi 
                                   JOIN categories c ON mi.category_id = c.id 
                                   WHERE mi.is_available = 1 
                                   ORDER BY mi.display_order LIMIT 10");
                $dishes = $stmt->fetchAll();
                
                foreach ($dishes as $dish):
                ?>
                <div class="dish-card">
                    <div class="dish-image">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="dish-info">
                        <h3><?php echo e($dish['name']); ?></h3>
                        <p class="dish-category"><?php echo e($dish['category_name']); ?></p>
                        <p class="dish-description"><?php echo e($dish['description']); ?></p>
                        <div class="dish-footer">
                            <span class="dish-price"><?php echo formatCurrency($dish['price']); ?></span>
                            <a href="pages/menu.php#dish-<?php echo $dish['id']; ?>" class="btn btn-small">Xem thêm</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="carousel-indicators">
                <?php for ($i = 0; $i < count($dishes); $i++): ?>
                    <span class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

