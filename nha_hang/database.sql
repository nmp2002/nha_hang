-- Database: nha_hang
-- Tạo database
CREATE DATABASE IF NOT EXISTS nha_hang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nha_hang;

-- Bảng người dùng
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng bàn ăn
CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(10) UNIQUE NOT NULL,
    capacity INT NOT NULL,
    status ENUM('available', 'occupied', 'reserved', 'maintenance') DEFAULT 'available',
    location VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng đặt bàn
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    table_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    number_of_guests INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng danh mục món ăn
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng món ăn
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    table_id INT,
    reservation_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(100),
    customer_phone VARCHAR(20),
    customer_email VARCHAR(100),
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'partial') DEFAULT 'unpaid',
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng chi tiết đơn hàng
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chèn dữ liệu mẫu
-- Tạo tài khoản admin mặc định (password: admin123)
-- Hash password được tạo bằng: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (username, email, password, full_name, phone, role) VALUES
('admin', 'admin@nhahang.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', '0901234567', 'admin'),
('staff1', 'staff1@nhahang.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nhân viên 1', '0901234568', 'staff');

-- Nếu đăng nhập không được, chạy lệnh sau trong phpMyAdmin để reset password:
-- UPDATE users SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy' WHERE username IN ('admin', 'staff1');
-- Hoặc mở file reset_password.php trong trình duyệt để tự động reset

-- Thêm bàn ăn mẫu
INSERT INTO tables (table_number, capacity, status, location) VALUES
('T01', 2, 'available', 'Tầng 1'),
('T02', 4, 'available', 'Tầng 1'),
('T03', 4, 'available', 'Tầng 1'),
('T04', 6, 'available', 'Tầng 1'),
('T05', 2, 'available', 'Tầng 2'),
('T06', 4, 'available', 'Tầng 2'),
('T07', 8, 'available', 'Tầng 2'),
('T08', 4, 'available', 'Tầng 2'),
('T09', 6, 'available', 'Phòng VIP'),
('T10', 10, 'available', 'Phòng VIP');

-- Thêm danh mục món ăn
INSERT INTO categories (name, description, display_order) VALUES
('Khai vị', 'Các món khai vị hấp dẫn', 1),
('Món chính', 'Các món ăn chính đặc biệt', 2),
('Hải sản', 'Các món hải sản tươi ngon', 3),
('Món chay', 'Các món chay thanh đạm', 4),
('Đồ uống', 'Nước giải khát và đồ uống', 5),
('Tráng miệng', 'Các món tráng miệng ngọt ngào', 6);

-- Thêm món ăn mẫu (với đường dẫn ảnh)
INSERT INTO menu_items (category_id, name, description, price, image, is_available, display_order) VALUES
-- Khai vị (category_id = 1)
(1, 'Gỏi cuốn tôm thịt', 'Gỏi cuốn tươi ngon với tôm và thịt heo', 50000, 'assets/images/gỏi cuốn tôm thịt.jpg', TRUE, 1),
(1, 'Nem nướng Nha Trang', 'Nem nướng đặc sản Nha Trang', 60000, 'assets/images/gỏi cuốn tôm thịt.jpg', TRUE, 2),
(1, 'Chả giò', 'Chả giò giòn rụm', 45000, 'assets/images/chả giò tôm đất.jpg', TRUE, 3),
-- Món chính (category_id = 2)
(2, 'Cơm chiên Dương Châu', 'Cơm chiên với tôm, thịt và rau củ', 80000, 'assets/images/xôi chiên chà bông.jpg', TRUE, 1),
(2, 'Phở bò', 'Phở bò truyền thống', 70000, NULL, TRUE, 2),
(2, 'Bún bò Huế', 'Bún bò Huế đậm đà', 75000, NULL, TRUE, 3),
(2, 'Thịt kho tiêu', 'Thịt ba chỉ kho tiêu đậm đà', 90000, 'assets/images/thịt kho tiêu.jpg', TRUE, 4),
(2, 'Heo quay kho cải chua', 'Heo quay thơm lừng kho cải chua', 120000, 'assets/images/heo quay kho cải chua.jpg', TRUE, 5),
(2, 'Ba rọi chiên mắm tỏi', 'Ba rọi chiên giòn sốt mắm tỏi', 85000, 'assets/images/ba rọi chiên mắm tỏi.jpg', TRUE, 6),
(2, 'Thịt luộc cà pháo', 'Thịt ba chỉ luộc với cà pháo chua ngọt', 75000, 'assets/images/thịt luộc cà pháo.jpg', TRUE, 7),
(2, 'Thịt xá xíu', 'Thịt xá xíu mềm thơm', 95000, 'assets/images/thit xa xiu.jpg', TRUE, 8),
(2, 'Canh chua cá hú', 'Canh chua cá hú đậm đà miền Tây', 80000, 'assets/images/canh chua cá hú.jpg', TRUE, 9),
(2, 'Kho quẹt', 'Kho quẹt đậm đà ăn với cơm nóng', 70000, 'assets/images/kho quẹt.jpg', TRUE, 10),
(2, 'Lẩu mắm', 'Lẩu mắm đặc sản miền Tây', 150000, 'assets/images/lẩu mắm.jpg', TRUE, 11),
(2, 'Ốc bươu nhồi thịt', 'Ốc bươu nhồi thịt thơm ngon', 100000, 'assets/images/ốc bươu nhồi thịt.jpg', TRUE, 12),
(2, 'Ếch xào xả ớt', 'Ếch xào xả ớt cay nồng', 90000, 'assets/images/ếch xào xả ớt.jpg', TRUE, 13),
(2, 'Bánh xèo', 'Bánh xèo giòn rụm miền Tây', 60000, 'assets/images/banh-xeo-20231120153146-rag33.png', TRUE, 14),
(2, 'Bánh khọt', 'Bánh khọt Vũng Tàu', 65000, 'assets/images/banh-khot-20231120152154--n0xw.png', TRUE, 15),
(2, 'Bánh hỏi', 'Bánh hỏi thơm ngon', 55000, 'assets/images/banh-hoi-20231130124006-qyy9n.png', TRUE, 16),
-- Hải sản (category_id = 3)
(3, 'Tôm hùm nướng', 'Tôm hùm nướng sốt bơ tỏi', 350000, NULL, TRUE, 1),
(3, 'Cua rang me', 'Cua biển rang me chua ngọt', 450000, NULL, TRUE, 2),
(3, 'Cá hồi nướng', 'Cá hồi nướng sốt cam', 250000, 'assets/images/canh-chua-ca-hu-20231130124005-ogpoi.png', TRUE, 3),
-- Món chay (category_id = 4)
(4, 'Cơm chay thập cẩm', 'Cơm chay với nhiều loại rau củ', 60000, 'assets/images/đậu hủ nhồi thịt xốt cà.jpg', TRUE, 1),
(4, 'Phở chay', 'Phở chay thanh đạm', 55000, 'assets/images/đậu hủ nhồi thịt xốt cà.jpg', TRUE, 2),
(4, 'Đậu hủ nhồi thịt sốt cà', 'Đậu hủ nhồi thịt chay sốt cà chua', 70000, 'assets/images/đậu hủ nhồi thịt xốt cà.jpg', TRUE, 3),
-- Đồ uống (category_id = 5)
(5, 'Nước ngọt', 'Coca, Pepsi, 7Up', 20000, NULL, TRUE, 1),
(5, 'Nước ép trái cây', 'Nước ép cam, táo, dưa hấu', 35000, NULL, TRUE, 2),
(5, 'Cà phê đá', 'Cà phê đá Việt Nam', 25000, NULL, TRUE, 3),
-- Tráng miệng (category_id = 6)
(6, 'Chè đậu xanh', 'Chè đậu xanh mát lạnh', 30000, NULL, TRUE, 1),
(6, 'Kem dừa', 'Kem dừa tươi', 40000, NULL, TRUE, 2);

