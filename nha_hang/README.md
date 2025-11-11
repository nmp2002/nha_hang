# Hệ Thống Quản Lý Nhà Hàng - PHP

Website quản lý nhà hàng được xây dựng bằng PHP, hỗ trợ đặt bàn, đặt món và quản trị.

## Tính năng

### Cho Khách Hàng
- **Đăng ký/Đăng nhập**: Tạo tài khoản và đăng nhập
- **Xem thực đơn**: Duyệt danh sách món ăn theo danh mục
- **Đặt món**: Thêm món vào giỏ hàng và đặt hàng
- **Đặt bàn**: Đặt bàn trước với thông tin chi tiết
- **Xem đơn hàng**: Theo dõi trạng thái đơn hàng của mình
- **Quản lý tài khoản**: Cập nhật thông tin cá nhân

### Cho Quản Trị/Staff
- **Quản lý đơn hàng**: Xem, cập nhật trạng thái đơn hàng
- **Quản lý đặt bàn**: Xem và xác nhận đặt bàn
- **Quản lý bàn ăn**: Thêm, sửa, xóa và cập nhật trạng thái bàn
- **Quản lý thực đơn**: Thêm, sửa, xóa món ăn và danh mục
- **Thống kê**: Xem tổng quan về đơn hàng, doanh thu, đặt bàn

## Cài đặt

### Yêu cầu
- XAMPP (hoặc PHP 7.4+, MySQL, Apache)
- MySQL/MariaDB

### Các bước cài đặt

1. **Copy project vào thư mục htdocs**
   ```
   D:\xampp\htdocs\nha_hang\
   ```

2. **Tạo database**
   - Mở phpMyAdmin (http://localhost/phpmyadmin)
   - Import file `database.sql` để tạo database và dữ liệu mẫu

3. **Cấu hình kết nối database** (nếu cần)
   - Mở file `config/database.php`
   - Kiểm tra thông tin kết nối:
     ```php
     DB_HOST: localhost
     DB_USER: root
     DB_PASS: (để trống nếu không có password)
     DB_NAME: nha_hang
     ```

4. **Truy cập website**
   - Mở trình duyệt và vào: `http://localhost/nha_hang/`

## Tài khoản mặc định

### Admin
- **Username**: admin
- **Password**: admin123

### Staff
- **Username**: staff1
- **Password**: admin123

## Cấu trúc thư mục

```
nha_hang/
├── admin/              # Trang quản trị
│   ├── index.php       # Dashboard
│   ├── orders.php      # Quản lý đơn hàng
│   ├── reservations.php # Quản lý đặt bàn
│   ├── tables.php      # Quản lý bàn ăn
│   ├── menu.php        # Quản lý thực đơn
│   └── users.php       # Quản lý người dùng
├── assets/
│   ├── css/
│   │   └── style.css   # File CSS chính
│   └── js/
│       └── main.js     # File JavaScript
├── config/
│   ├── config.php      # Cấu hình chung
│   └── database.php    # Kết nối database
├── includes/
│   ├── header.php      # Header chung
│   └── footer.php      # Footer chung
├── index.php           # Trang chủ
├── menu.php            # Trang thực đơn
├── reservation.php     # Trang đặt bàn
├── cart.php            # Giỏ hàng
├── checkout.php        # Thanh toán
├── orders.php          # Đơn hàng của khách
├── profile.php         # Thông tin cá nhân
├── login.php           # Đăng nhập
├── register.php        # Đăng ký
├── logout.php          # Đăng xuất
├── order_success.php   # Thành công đặt hàng
├── database.sql        # File SQL
└── README.md           # File hướng dẫn
```

## Tính năng chi tiết

### Hệ thống đặt bàn
- Chọn bàn, ngày, giờ đặt
- Nhập thông tin khách hàng
- Yêu cầu đặc biệt
- Quản trị viên xác nhận/hủy đặt bàn

### Hệ thống đặt hàng
- Thêm món vào giỏ hàng
- Quản lý số lượng
- Thanh toán với thông tin khách hàng
- Theo dõi trạng thái đơn hàng

### Quản trị
- Dashboard với thống kê tổng quan
- Quản lý đầy đủ các module
- Cập nhật trạng thái real-time
- Phân quyền admin/staff/customer

## Công nghệ sử dụng

- **Backend**: PHP (PDO)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome
- **Design**: Responsive, Modern UI

## Lưu ý

- File này được thiết kế để chạy trên XAMPP localhost
- Database mặc định không có password
- Password được hash bằng `password_hash()` (bcrypt)
- Tất cả input được sanitize để tránh SQL injection
- Session được sử dụng cho authentication và cart

## Phát triển thêm

Có thể mở rộng thêm các tính năng:
- Thanh toán online
- Gửi email xác nhận
- Upload hình ảnh món ăn
- Đánh giá và bình luận
- Mã giảm giá
- Báo cáo chi tiết

## Hỗ trợ

Nếu có vấn đề, vui lòng kiểm tra:
1. XAMPP đã chạy (Apache và MySQL)
2. Database đã được import
3. Cấu hình database trong `config/database.php`
4. Quyền ghi file trong thư mục (nếu có upload)

---

**Tác giả**: Hệ thống quản lý nhà hàng PHP
**Phiên bản**: 1.0
**Ngày**: 2024

