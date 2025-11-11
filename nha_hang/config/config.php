<?php
// Bắt đầu session
session_start();

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nha_hang');
define('DB_CHARSET', 'utf8mb4');

// Đường dẫn cơ sở
define('BASE_URL', 'http://localhost/nha_hang/');
define('BASE_PATH', __DIR__ . '/../');

// Cài đặt timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kết nối database
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Kết nối database thất bại: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// Lấy kết nối database
function getDB() {
    return Database::getInstance()->getConnection();
}

// Kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Lấy thông tin user hiện tại
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Kiểm tra quyền admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

// Kiểm tra quyền staff
function isStaff() {
    $user = getCurrentUser();
    return $user && ($user['role'] === 'admin' || $user['role'] === 'staff');
}

// Kiểm tra quyền customer
function isCustomer() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'customer';
}

// Redirect function
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// Generate order number
function generateOrderNumber() {
    return 'ORD' . date('YmdHis') . rand(1000, 9999);
}

// Include helpers
require_once __DIR__ . '/../includes/helpers.php';
?>
