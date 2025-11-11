<?php
$pageTitle = 'Đăng nhập';
require_once 'config/config.php';

// Nếu đã đăng nhập, redirect về trang chủ
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';
$active_tab = 'login'; // Mặc định là tab đăng nhập

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
        $active_tab = 'login';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect dựa trên role và redirect parameter
            $redirect = $_GET['redirect'] ?? '';
            if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                redirect('admin/index.php');
            } else {
                if ($redirect === 'menu') {
                    redirect('pages/menu.php');
                } else {
                    redirect('index.php');
                }
            }
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
            $active_tab = 'login';
        }
    }
}

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc';
        $active_tab = 'register';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
        $active_tab = 'register';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
        $active_tab = 'register';
    } else {
        $db = getDB();
        
        // Kiểm tra username đã tồn tại
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Tên đăng nhập đã được sử dụng';
            $active_tab = 'register';
        } else {
            // Kiểm tra email đã tồn tại
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email đã được sử dụng';
                $active_tab = 'register';
            } else {
                // Tạo tài khoản
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, role) 
                                     VALUES (?, ?, ?, ?, ?, 'customer')");
                if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                    $active_tab = 'login'; // Chuyển về tab đăng nhập sau khi đăng ký thành công
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại';
                    $active_tab = 'register';
                }
            }
        }
    }
}

// Kiểm tra nếu có tham số tab từ URL
if (isset($_GET['tab']) && $_GET['tab'] === 'register') {
    $active_tab = 'register';
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <!-- Tab Navigation -->
        <div class="auth-tabs">
            <button class="auth-tab <?php echo $active_tab === 'login' ? 'active' : ''; ?>" data-tab="login">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
            <button class="auth-tab <?php echo $active_tab === 'register' ? 'active' : ''; ?>" data-tab="register">
                <i class="fas fa-user-plus"></i> Đăng ký
            </button>
        </div>
        
        <!-- Login Form -->
        <div class="auth-form <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="login-form">
            <?php if ($error && $active_tab === 'login'): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="login_username">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="login_username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="login_password">Mật khẩu</label>
                    <input type="password" id="login_password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>
            
            <div class="auth-info">
                <p><strong>Tài khoản demo:</strong></p>
                <p>Admin: username: admin, password: admin123</p>
            </div>
        </div>
        
        <!-- Register Form -->
        <div class="auth-form <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="register-form">
            <?php if ($error && $active_tab === 'register'): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="reg_username">Tên đăng nhập <span class="required">*</span></label>
                    <input type="text" id="reg_username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="reg_email">Email <span class="required">*</span></label>
                    <input type="email" id="reg_email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="reg_full_name">Họ tên <span class="required">*</span></label>
                    <input type="text" id="reg_full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="reg_phone">Số điện thoại</label>
                    <input type="tel" id="reg_phone" name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="reg_password">Mật khẩu <span class="required">*</span></label>
                    <input type="password" id="reg_password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="reg_confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                    <input type="password" id="reg_confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.auth-tab');
    const forms = document.querySelectorAll('.auth-form');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and forms
            tabs.forEach(t => t.classList.remove('active'));
            forms.forEach(f => f.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding form
            this.classList.add('active');
            document.getElementById(targetTab + '-form').classList.add('active');
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
