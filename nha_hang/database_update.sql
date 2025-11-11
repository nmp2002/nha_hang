-- Migration: Thêm order_type và delivery_address vào bảng orders
-- Chạy file này để cập nhật database

USE nha_hang;

-- Thêm cột order_type để phân biệt đơn hàng tại quán và mang về
ALTER TABLE orders 
ADD COLUMN order_type ENUM('dine_in', 'takeaway') DEFAULT 'dine_in' AFTER reservation_id;

-- Thêm cột delivery_address cho đơn hàng mang về
ALTER TABLE orders 
ADD COLUMN delivery_address TEXT NULL AFTER customer_email;

-- Cập nhật các đơn hàng hiện có (nếu có table_id thì là dine_in, không thì là takeaway)
UPDATE orders 
SET order_type = CASE 
    WHEN table_id IS NOT NULL THEN 'dine_in'
    ELSE 'takeaway'
END;

-- Migration: Thêm bảng chat_logs để lưu lịch sử chat của khách
CREATE TABLE IF NOT EXISTS chat_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(100) NULL,
    message TEXT NOT NULL,
    reply TEXT NOT NULL,
    source VARCHAR(50) NOT NULL DEFAULT 'fallback',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

