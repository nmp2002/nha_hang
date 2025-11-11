<?php
$pageTitle = 'Đặt bàn';
require_once '../config/config.php';
require_once '../includes/header.php';

$db = getDB();
$error = '';
$success = '';

// Lấy danh sách bàn có sẵn
$available_tables = $db->query("SELECT * FROM tables WHERE status IN ('available', 'reserved') ORDER BY table_number")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_id = (int)($_POST['table_id'] ?? 0);
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $number_of_guests = (int)($_POST['number_of_guests'] ?? 0);
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    if (empty($table_id) || empty($reservation_date) || empty($reservation_time) || 
        empty($number_of_guests) || empty($customer_name) || empty($customer_phone)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        // Kiểm tra ngày đặt không được trong quá khứ
        $reservation_datetime = $reservation_date . ' ' . $reservation_time;
        if (strtotime($reservation_datetime) < time()) {
            $error = 'Ngày và giờ đặt bàn không thể trong quá khứ';
        } else {
            // Kiểm tra bàn đã được đặt chưa
            $stmt = $db->prepare("SELECT id FROM reservations 
                                 WHERE table_id = ? AND reservation_date = ? 
                                 AND reservation_time = ? AND status IN ('pending', 'confirmed')");
            $stmt->execute([$table_id, $reservation_date, $reservation_time]);
            
            if ($stmt->fetch()) {
                $error = 'Bàn này đã được đặt vào thời gian này';
            } else {
                $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
                
                $stmt = $db->prepare("INSERT INTO reservations (user_id, table_id, reservation_date, reservation_time, 
                                    number_of_guests, customer_name, customer_phone, customer_email, special_requests, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                
                if ($stmt->execute([
                    $user_id,
                    $table_id,
                    $reservation_date,
                    $reservation_time,
                    $number_of_guests,
                    $customer_name,
                    $customer_phone,
                    $customer_email ?: null,
                    $special_requests ?: null
                ])) {
                    // Cập nhật trạng thái bàn
                    $stmt = $db->prepare("UPDATE tables SET status = 'reserved' WHERE id = ?");
                    $stmt->execute([$table_id]);
                    
                    $success = 'Đặt bàn thành công! Chúng tôi sẽ liên hệ xác nhận với bạn sớm nhất.';
                    
                    // Reset form
                    $_POST = [];
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại';
                }
            }
        }
    }
}

$user = getCurrentUser();
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-check"></i> Đặt bàn</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="reservation-container">
        <div class="reservation-form">
            <h2>Thông tin đặt bàn</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="table_id">Chọn bàn <span class="required">*</span></label>
                    <select id="table_id" name="table_id" required>
                        <option value="">-- Chọn bàn --</option>
                        <?php foreach ($available_tables as $table): ?>
                            <option value="<?php echo $table['id']; ?>" 
                                    data-capacity="<?php echo $table['capacity']; ?>"
                                    <?php echo isset($_POST['table_id']) && $_POST['table_id'] == $table['id'] ? 'selected' : ''; ?>>
                                Bàn <?php echo htmlspecialchars($table['table_number']); ?> - 
                                <?php echo $table['capacity']; ?> người - 
                                <?php echo htmlspecialchars($table['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="reservation_date">Ngày đặt <span class="required">*</span></label>
                        <input type="date" id="reservation_date" name="reservation_date" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($_POST['reservation_date'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="reservation_time">Giờ đặt <span class="required">*</span></label>
                        <input type="time" id="reservation_time" name="reservation_time" required 
                               value="<?php echo htmlspecialchars($_POST['reservation_time'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="number_of_guests">Số lượng khách <span class="required">*</span></label>
                        <input type="number" id="number_of_guests" name="number_of_guests" required min="1" 
                               value="<?php echo htmlspecialchars($_POST['number_of_guests'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="customer_name">Họ tên <span class="required">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" required 
                           value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ($user ? $user['full_name'] : '')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="customer_phone">Số điện thoại <span class="required">*</span></label>
                    <input type="tel" id="customer_phone" name="customer_phone" required 
                           value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ($user ? ($user['phone'] ?? '') : '')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="customer_email">Email</label>
                    <input type="email" id="customer_email" name="customer_email" 
                           value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ($user ? $user['email'] : '')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="special_requests">Yêu cầu đặc biệt</label>
                    <textarea id="special_requests" name="special_requests" rows="3" 
                              placeholder="Ví dụ: Bàn góc, sinh nhật, yêu cầu về đồ ăn..."><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Đặt bàn
                </button>
            </form>
        </div>
        
        <div class="reservation-info">
            <h2>Thông tin đặt bàn</h2>
            <div class="info-box">
                <h3><i class="fas fa-info-circle"></i> Lưu ý</h3>
                <ul>
                    <li>Vui lòng đặt bàn trước ít nhất 2 giờ</li>
                    <li>Chúng tôi sẽ liên hệ xác nhận với bạn qua điện thoại</li>
                    <li>Nếu bạn muốn hủy đặt bàn, vui lòng liên hệ ít nhất 1 giờ trước</li>
                    <li>Nếu quá 15 phút so với giờ đặt mà không có mặt, đặt bàn sẽ tự động hủy</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

