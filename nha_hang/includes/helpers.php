<?php
/**
 * Helper functions cho ứng dụng
 */

// Mapping trạng thái đơn hàng
function getOrderStatusText($status) {
    $statuses = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'preparing' => 'Đang chuẩn bị',
        'ready' => 'Sẵn sàng',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}

// Mapping trạng thái đặt bàn
function getReservationStatusText($status) {
    $statuses = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}

// Mapping vai trò người dùng
function getRoleText($role) {
    $roles = [
        'admin' => 'Quản trị viên',
        'staff' => 'Nhân viên',
        'customer' => 'Khách hàng'
    ];
    return $roles[$role] ?? $role;
}

// Format tiền VNĐ
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

// Format ngày giờ
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return date($format, strtotime($datetime));
}

// Format ngày
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Escape HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Sanitize input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return trim(strip_tags($data));
}
?>

