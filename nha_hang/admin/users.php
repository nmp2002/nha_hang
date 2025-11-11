<?php
$pageTitle = 'Quản lý người dùng';
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$db = getDB();
$error = '';
$success = '';

// Xử lý thêm/cập nhật user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Thêm user mới
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'staff';
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
        } elseif (strlen($password) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự';
        } else {
            // Kiểm tra username đã tồn tại
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Tên đăng nhập đã được sử dụng';
            } else {
                // Kiểm tra email đã tồn tại
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email đã được sử dụng';
                } else {
                    // Tạo tài khoản
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, role) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $role])) {
                        $success = 'Thêm tài khoản thành công';
                    } else {
                        $error = 'Có lỗi xảy ra, vui lòng thử lại';
                    }
                }
            }
        }
    } elseif (isset($_POST['update_user'])) {
        // Cập nhật role
        $user_id = (int)$_POST['user_id'];
        $role = $_POST['role'];
        
        if ($user_id == $_SESSION['user_id']) {
            $error = 'Không thể thay đổi quyền của chính mình';
        } else {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$role, $user_id])) {
                $success = 'Cập nhật thành công';
            } else {
                $error = 'Có lỗi xảy ra';
            }
        }
    }
}

// Lấy danh sách users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Quản lý người dùng</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Form thêm user mới -->
    <div class="admin-section" style="margin-bottom: 30px;">
        <h2><i class="fas fa-user-plus"></i> Thêm tài khoản nhân viên</h2>
        <form method="POST" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Tên đăng nhập <span class="required">*</span></label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Họ tên <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mật khẩu <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="role">Vai trò <span class="required">*</span></label>
                    <select id="role" name="role" required>
                        <option value="staff" selected>Nhân viên</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" name="add_user" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm tài khoản
            </button>
        </form>
    </div>
    
    <div class="admin-section">
        <h2><i class="fas fa-users"></i> Danh sách tài khoản</h2>
        <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Vai trò</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                <td>
                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                        <strong><?php
                            $roles = ['admin' => 'Quản trị viên', 'staff' => 'Nhân viên', 'customer' => 'Khách hàng'];
                            echo $roles[$user['role']] ?? $user['role'];
                        ?></strong>
                    <?php else: ?>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="role" onchange="this.form.submit()">
                                <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Khách hàng</option>
                                <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Nhân viên</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Quản trị</option>
                            </select>
                            <input type="hidden" name="update_user" value="1">
                        </form>
                    <?php endif; ?>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                <td>
                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                        <span class="text-muted">Tài khoản của bạn</span>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

