# Hướng dẫn cập nhật hệ thống

## Cập nhật Database

Để hệ thống hoạt động đúng với các tính năng mới, bạn cần chạy file migration SQL:

1. Mở phpMyAdmin hoặc MySQL client
2. Chọn database `nha_hang`
3. Chạy file `database_update.sql` để thêm các cột mới:
   - `order_type`: Phân biệt đơn hàng tại quán (dine_in) và mang về (takeaway)
   - `delivery_address`: Địa chỉ giao hàng cho đơn hàng mang về

## Các tính năng mới

### Cho khách hàng:
1. **Đặt món mang về**: 
   - Khách hàng phải đăng nhập để đặt món
   - Có giỏ hàng để quản lý món ăn
   - Thanh toán với địa chỉ giao hàng

2. **Đặt bàn**: 
   - Vẫn giữ nguyên chức năng đặt bàn để ăn tại quán
   - Không cần đăng nhập (nhưng có thể đăng nhập để lưu thông tin)

### Cho Admin/Staff:
1. **Quản lý đơn hàng**:
   - Phân biệt đơn hàng tại quán và đơn hàng ship
   - Xem chi tiết địa chỉ giao hàng cho đơn ship
   - Có tab lọc để xem từng loại đơn

2. **Báo cáo thống kê**:
   - Xem thống kê theo thời gian (từ ngày - đến ngày)
   - Thống kê doanh thu theo loại đơn
   - Top 10 món ăn bán chạy
   - Thống kê theo trạng thái đơn hàng

3. **Dashboard**:
   - Hiển thị thống kê tổng quan cho cả 2 loại đơn
   - Đơn hàng mới nhất với thông tin loại đơn

## Lưu ý

- Nếu database đã có dữ liệu, các đơn hàng cũ sẽ được tự động phân loại:
  - Đơn có `table_id` → `dine_in`
  - Đơn không có `table_id` → `takeaway`

