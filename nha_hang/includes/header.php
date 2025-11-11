<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Cơm Quê Dượng Bầu</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/all.min.css">
</head>
<body<?php echo (isset($isHomePage) && $isHomePage) ? ' class="home-page"' : ''; ?>>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <span><i class="fas fa-clock"></i> T2-CN: 10:00 - 22:00</span>
                    <span><i class="fas fa-phone"></i> Hotline: 076 537 1893</span>
                </div>
                <div class="top-bar-right">
                    <a href="<?php echo BASE_URL; ?>pages/reservation.php" class="btn-reservation">
                        <i class="fas fa-calendar-check"></i> ĐẶT BÀN
                    </a>
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>pages/menu.php" class="btn-reservation">
                        <i class="fas fa-shopping-bag"></i> ĐẶT MÓN
                    </a>
                    <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php?redirect=menu" class="btn-reservation">
                        <i class="fas fa-shopping-bag"></i> ĐẶT MÓN
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?php echo (isLoggedIn() && (isAdmin() || isStaff())) ? BASE_URL . 'admin/index.php' : BASE_URL . 'index.php'; ?>">
                    <img src="<?php echo BASE_URL; ?>assets/images/trang-k-nen-20231110175533-uk948.png" alt="Cơm Quê Dượng Bầu" class="brand-logo">
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="<?php echo (isLoggedIn() && (isAdmin() || isStaff())) ? BASE_URL . 'admin/index.php' : BASE_URL . 'index.php'; ?>">Trang chủ</a></li>
                <?php if (isCustomer() || !isLoggedIn()): ?>
                    <li><a href="<?php echo BASE_URL; ?>pages/menu.php">Thực đơn</a></li>
                    <li><a href="<?php echo BASE_URL; ?>pages/reservation.php">Đặt bàn</a></li>
                <?php endif; ?>
                <?php if (isLoggedIn()): ?>
                    <?php if (isCustomer()): ?>
                        <li><a href="<?php echo BASE_URL; ?>pages/menu.php"><i class="fas fa-shopping-bag"></i> Đặt món</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/cart.php">
                            <i class="fas fa-shopping-cart"></i> Giỏ hàng
                            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                <span class="cart-badge"><?php echo array_sum($_SESSION['cart']); ?></span>
                            <?php endif; ?>
                        </a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/orders.php">Đơn hàng</a></li>
                    <?php endif; ?>
                    <?php if (isCustomer()): ?>
                        <!-- Dropdown menu cho khách hàng -->
                        <li class="nav-dropdown">
                            <a href="#">
                                <i class="fas fa-user"></i> <?php echo e(getCurrentUser()['full_name']); ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo BASE_URL; ?>pages/profile.php"><i class="fas fa-user-circle"></i> Thông tin tài khoản</a></li>
                                <li><a href="<?php echo BASE_URL; ?>pages/orders.php"><i class="fas fa-list-alt"></i> Đơn hàng của tôi</a></li>
                                <li><a href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Menu cho admin/staff -->
                        <li class="nav-dropdown">
                            <a href="#">
                                <i class="fas fa-user-shield"></i> <?php echo e(getCurrentUser()['full_name']); ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo BASE_URL; ?>pages/profile.php"><i class="fas fa-user-circle"></i> Thông tin tài khoản</a></li>
                                <li><a href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

<?php
// Include chatbot widget for logged-in users
if (isLoggedIn()) {
    // include the chatbot markup (loads its own CSS/JS)
    include_once __DIR__ . '/chatbot.php';
}


