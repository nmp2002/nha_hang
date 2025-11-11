<?php
$pageTitle = 'Thông tin cá nhân';
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
    } else {
        $db = getDB();
        
        // Kiểm tra email đã tồn tại chưa (trừ user hiện tại)
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng';
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$full_name, $phone, $email, $user['id']])) {
                $success = 'Cập nhật thông tin thành công';
                // Reload user data
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $user = $stmt->fetch();
                $_SESSION['user_id'] = $user['id'];
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-user"></i> Thông tin cá nhân</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e($success); ?></div>
    <?php endif; ?>
    
    <div class="profile-container">
        <div class="profile-form">
            <h2>Cập nhật thông tin</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" value="<?php echo e($user['username']); ?>" disabled>
                    <small>Tên đăng nhập không thể thay đổi</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Họ tên <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo e($user['full_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo e($user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo e($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Vai trò</label>
                    <input type="text" value="<?php echo getRoleText($user['role']); ?>" disabled>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Cập nhật thông tin</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

