    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-bowl-rice"></i> Cơm Quê Dượng Bầu</h3>
                    <p class="footer-slogan">Chuẩn vị cơm nhà</p>
                    <p>Cơm nhà là điều xa xỉ giữa cuộc sống đầy bận rộn. Nếu thèm một bữa cơm quê với thịt kho tiêu, canh chua cá hú, đừng ngại ngần mà đến ngay Cơm Quê Dượng Bầu.</p>
                </div>
                <div class="footer-section">
                    <h3>Thông tin liên hệ</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Lầu 3, Chung cư 40E Ngô Đức Kế, Quận 1, TP. HCM</p>
                    <p><i class="fas fa-phone"></i> Hotline: <a href="tel:0765371893">076 537 1893</a></p>
                    <p><i class="fas fa-clock"></i> Giờ mở cửa: T2-CN 10:00 - 22:00</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-zalo"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/menu.php">Thực đơn</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/reservation.php">Đặt bàn</a></li>
                        <?php if (!isLoggedIn()): ?>
                        <li><a href="<?php echo BASE_URL; ?>login.php">Đăng nhập</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Cơm Quê Dượng Bầu. All rights reserved. | Chuẩn vị cơm nhà</p>
            </div>
        </div>
    </footer>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>

