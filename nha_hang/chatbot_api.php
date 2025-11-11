<?php
// Simple chatbot API endpoint
// Accepts POST { message: "..." } and returns JSON { success: true, reply: "..." }

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/config.php';

// Read input
$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (!$message) {
    echo json_encode(['success' => false, 'error' => 'No message provided']);
    exit;
}

// Basic sanitize and limit
$message_safe = mb_substr(strip_tags($message), 0, 2000);

// Current user info (may be null)
$user = getCurrentUser();
$user_id = $user['id'] ?? null;
$username = $user['username'] ?? null;

// Try to use OpenAI (if key provided via env or constant), otherwise fallback to FAQ
$openai_key = getenv('OPENAI_API_KEY') ?: (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');

if ($openai_key) {
    // Enhanced system prompt with restaurant-specific training
    $system_prompt = "Bạn là trợ lý ảo thân thiện của nhà hàng 'Cơm Quê Dượng Bầu' - một nhà hàng chuyên về món ăn miền Tây với slogan 'Chuẩn vị cơm nhà'.

THÔNG TIN NHÀ HÀNG:
- Tên: Cơm Quê Dượng Bầu
- Địa chỉ: Lầu 3, Chung cư 40E Ngô Đức Kế, Quận 1, TP. HCM
- Hotline: 076 537 1893
- Giờ mở cửa: 10:00 - 22:00 (T2-CN, cả tuần)
- Phong cách: Món ăn miền Tây, cơm quê truyền thống
- Đặc sản: Thịt kho tiêu, canh chua cá hú, các món cơm quê đậm đà

DỊCH VỤ:
- Ăn tại quán
- Đặt món mang về
- Đặt bàn trước
- Thanh toán: Tiền mặt, MOMO, ZaloPay, chuyển khoản QR

HƯỚNG DẪN TRẢ LỜI:
1. Luôn thân thiện, lịch sự và nhiệt tình
2. Trả lời ngắn gọn (2-3 câu), dễ hiểu
3. Nếu khách hỏi về thực đơn, gợi ý truy cập: " . BASE_URL . "pages/menu.php
4. Nếu khách muốn đặt bàn, hướng dẫn: " . BASE_URL . "pages/reservation.php hoặc gọi 076 537 1893
5. Nếu không biết câu trả lời chính xác, khuyến khích gọi hotline
6. Sử dụng emoji phù hợp để tạo không khí thân thiện
7. Luôn kết thúc bằng câu hỏi hoặc lời mời chào để tạo tương tác

VÍ DỤ CÂU TRẢ LỜI TỐT:
- 'Dạ, nhà hàng mở cửa từ 10h sáng đến 10h tối hàng ngày ạ 😊 Quý khách muốn đặt bàn cho khung giờ nào?'
- 'Món thịt kho tiêu là đặc sản của quán, đậm đà chuẩn vị miền Tây! 🍲 Quý khách có muốn xem thêm món khác không?'";

    // Call OpenAI Chat Completions (gpt-3.5-turbo)
    $payload = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $message_safe]
        ],
        'max_tokens' => 300,
        'temperature' => 0.7,
        'presence_penalty' => 0.6,
        'frequency_penalty' => 0.3
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_key,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo json_encode(['success' => false, 'error' => 'Request error: ' . $err]);
        exit;
    }

    $json = json_decode($resp, true);
    if (isset($json['choices'][0]['message']['content'])) {
        $reply = trim($json['choices'][0]['message']['content']);
        // Log to DB
        try {
            $db = getDB();
            $stmt = $db->prepare('INSERT INTO chat_logs (user_id, username, message, reply, source) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $username, $message_safe, $reply, 'openai']);
        } catch (Exception $e) {
            // ignore logging errors
        }

        echo json_encode(['success' => true, 'reply' => $reply]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid response from OpenAI', 'raw' => $json]);
        exit;
    }
}

// Enhanced fallback rule-based/FAQ responses
$faq = [
    '/xin chào|chào|hello|hi/i' => '👋 Xin chào! Mình là trợ lý ảo của Cơm Quê Dượng Bầu. Mình có thể giúp gì cho bạn hôm nay? (Đặt bàn, xem thực đơn, giờ mở cửa...)',
    
    '/thực đơn|món ăn|menu|có món gì|món gì ngon/i' => '🍲 Nhà hàng chúng mình chuyên về món cơm quê miền Tây đậm đà! Bạn có thể xem thực đơn đầy đủ tại: <a href="' . BASE_URL . 'pages/menu.php" target="_blank">Xem thực đơn</a><br>Đặc sản: Thịt kho tiêu, canh chua cá hú, các món kho quẹt... Bạn thích món nào?',
    
    '/đặt bàn|đặt chỗ|reserve|book/i' => '📅 Để đặt bàn, bạn có thể:<br>1. Đặt online: <a href="' . BASE_URL . 'pages/reservation.php" target="_blank">Đặt bàn ngay</a><br>2. Gọi hotline: <strong>076 537 1893</strong><br>Bạn muốn đặt cho bao nhiêu người và khung giờ nào?',
    
    '/giờ|mở cửa|mở|đóng cửa|giờ hoạt động/i' => '🕐 Nhà hàng mở cửa:<br><strong>10:00 - 22:00</strong> (Thứ 2 - Chủ nhật)<br>Bạn muốn đến vào khung giờ nào để mình hỗ trợ đặt bàn nhé?',
    
    '/địa chỉ|ở đâu|chỗ nào|vị trí|location/i' => '📍 Địa chỉ nhà hàng:<br><strong>Lầu 3, Chung cư 40E Ngô Đức Kế, Quận 1, TP. HCM</strong><br>☎️ Hotline: 076 537 1893<br>Bạn cần hướng dẫn đường đi không?',
    
    '/giao hàng|ship|delivery|mang về|take.*away/i' => '🛵 Hiện tại nhà hàng hỗ trợ:<br>✅ Đặt món mang về (tại quán)<br>✅ Giao hàng qua hotline: <strong>076 537 1893</strong><br>Bạn có thể đặt món online và đến lấy hoặc gọi để đặt ship nhé!',
    
    '/thanh toán|payment|pay|momo|zalo.*pay|vnpay|chuyển khoản/i' => '💳 Nhà hàng chấp nhận:<br>✅ Tiền mặt<br>✅ MOMO<br>✅ ZaloPay<br>✅ Chuyển khoản QR<br>Bạn muốn thanh toán bằng hình thức nào?',
    
    '/giá|bao nhiêu|price|cost|đắt|rẻ/i' => '💰 Giá món ăn dao động từ 30.000đ - 150.000đ tùy món. Xem giá chi tiết tại: <a href="' . BASE_URL . 'pages/menu.php" target="_blank">Thực đơn</a><br>Bạn quan tâm món nào để mình tư vấn cụ thể?',
    
    '/đặc sản|món ngon|recommend|gợi ý/i' => '⭐ Những món đặc sản NÊN THỬ:<br>🥘 Thịt kho tiêu<br>🐟 Canh chua cá hú<br>🍲 Cơm quê với món kho quẹt<br>Tất cả đều chuẩn vị miền Tây, đậm đà như cơm nhà! Bạn thích món nào?',
    
    '/đánh giá|review|comment|ý kiến/i' => '⭐ Cảm ơn bạn quan tâm! Bạn có thể để lại đánh giá sau khi dùng bữa hoặc liên hệ hotline <strong>076 537 1893</strong> để góp ý. Nhà hàng luôn lắng nghe để cải thiện dịch vụ!',
    
    '/cảm ơn|thank|thanks|cám ơn/i' => '🙏 Rất vui được hỗ trợ bạn! Hẹn gặp bạn tại Cơm Quê Dượng Bầu nhé! Nếu cần gì thêm, cứ nhắn mình hoặc gọi <strong>076 537 1893</strong> ạ!',
];

$matched = false;
foreach ($faq as $pattern => $answer) {
    if (preg_match($pattern, $message_safe)) {
        $reply = $answer;
        $matched = true;
        break;
    }
}

if (!$matched) {
    $reply = '😊 Mình là trợ lý ảo của Cơm Quê Dượng Bầu. Mình có thể giúp bạn về:<br>• Xem thực đơn 🍲<br>• Đặt bàn 📅<br>• Giờ mở cửa 🕐<br>• Địa chỉ & liên hệ 📍<br><br>Bạn cần hỗ trợ gì, cứ hỏi mình nhé! Hoặc gọi hotline <strong>076 537 1893</strong> để được tư vấn trực tiếp.';
}

// Log fallback reply
try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO chat_logs (user_id, username, message, reply, source) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$user_id, $username, $message_safe, $reply, 'fallback']);
} catch (Exception $e) {
    // ignore logging errors
}

echo json_encode(['success' => true, 'reply' => $reply]);
