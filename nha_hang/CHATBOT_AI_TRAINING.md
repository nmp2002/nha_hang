# 🤖 Hướng Dẫn Training AI Chatbot - Cơm Quê Dượng Bầu

## 📋 Tổng Quan

Chatbot được xây dựng với 2 chế độ:
1. **AI Mode (OpenAI GPT-3.5-turbo)** - Trả lời thông minh, tự nhiên
2. **Fallback Mode (Rule-based)** - Trả lời theo mẫu khi không có API key

---

## 🔑 Cấu Hình OpenAI API

### Bước 1: Lấy API Key từ OpenAI

1. Truy cập: https://platform.openai.com/api-keys
2. Đăng nhập hoặc đăng ký tài khoản OpenAI
3. Tạo API Key mới
4. Copy API key (chỉ hiển thị 1 lần)

### Bước 2: Cấu Hình API Key

**Cách 1: Qua Environment Variable (Khuyến nghị)**
```bash
# Windows PowerShell
$env:OPENAI_API_KEY="sk-your-api-key-here"

# Windows CMD
set OPENAI_API_KEY=sk-your-api-key-here

# Linux/Mac
export OPENAI_API_KEY="sk-your-api-key-here"
```

**Cách 2: Trong file config.php**
```php
// Thêm vào cuối file config/config.php
define('OPENAI_API_KEY', 'sk-your-api-key-here');
```

**⚠️ LƯU Ý BẢO MẬT:**
- Không commit API key lên GitHub
- Thêm `.env` vào `.gitignore`
- Sử dụng environment variables cho production

---

## 🎓 Training AI Chatbot

### 1. System Prompt đã được cấu hình

File: `chatbot_api.php` - Dòng ~30

Prompt hiện tại bao gồm:
- ✅ Thông tin nhà hàng (địa chỉ, giờ mở cửa, hotline)
- ✅ Danh sách dịch vụ
- ✅ Hướng dẫn phong cách trả lời
- ✅ Ví dụ câu trả lời mẫu

### 2. Tùy Chỉnh Training Prompt

Mở file `chatbot_api.php` và chỉnh sửa phần `$system_prompt`:

```php
$system_prompt = "Bạn là trợ lý ảo thân thiện của nhà hàng 'Cơm Quê Dượng Bầu'...

[THÊM THÔNG TIN MỚI TẠI ĐÂY]

VÍ DỤ:
MENU MỚI:
- Món A: Giá X, mô tả...
- Món B: Giá Y, mô tả...

KHUYẾN MÃI:
- Giảm 20% vào thứ 3
- Combo gia đình...
";
```

### 3. Điều Chỉnh Tham Số AI

```php
$payload = [
    'model' => 'gpt-3.5-turbo',  // hoặc 'gpt-4' nếu có
    'max_tokens' => 300,          // Độ dài câu trả lời (100-500)
    'temperature' => 0.7,         // Độ sáng tạo (0.0-1.0)
    'presence_penalty' => 0.6,    // Khuyến khích chủ đề mới
    'frequency_penalty' => 0.3    // Tránh lặp từ
];
```

**Giải thích tham số:**
- `temperature`: 
  - 0.0-0.3: Chính xác, cứng nhắc
  - 0.4-0.7: Cân bằng (khuyến nghị)
  - 0.8-1.0: Sáng tạo, có thể không chính xác
  
- `max_tokens`: 
  - 100-200: Câu ngắn
  - 200-400: Câu vừa (khuyến nghị)
  - 400+: Câu dài, chi tiết

---

## 🛠️ Nâng Cấp Fallback Responses

### Thêm Mẫu Câu Trả Lời Mới

File: `chatbot_api.php` - Dòng ~90

```php
$faq = [
    // Pattern => Answer
    '/pattern_regex/i' => 'Câu trả lời...',
    
    // Ví dụ thêm mới:
    '/món chay|vegetarian/i' => '🥗 Nhà hàng có các món chay: Đậu hũ kho, rau xào...',
    '/parking|đỗ xe/i' => '🅿️ Có bãi đỗ xe tại tầng hầm chung cư, miễn phí 2h đầu.',
];
```

### Tips Viết Pattern Regex

- `/xin chào|hello|hi/i` - Match nhiều từ
- `/đặt.*bàn/i` - Match "đặt bàn", "đặt chỗ bàn"...
- `/món.*(gì|nào)/i` - Match "món gì", "món nào"...
- `i` flag - Case insensitive (không phân biệt hoa thường)

---

## 📊 Theo Dõi & Phân Tích

### 1. Xem Chat Logs

Truy cập: `admin/chat_logs.php`

Bảng `chat_logs` lưu:
- `user_id` - ID người dùng
- `message` - Câu hỏi
- `reply` - Câu trả lời
- `source` - 'openai' hoặc 'fallback'
- `created_at` - Thời gian

### 2. Phân Tích Để Cải Thiện

**Câu hỏi thường gặp không có trong FAQ:**
```sql
SELECT message, COUNT(*) as count 
FROM chat_logs 
WHERE source = 'fallback' 
GROUP BY message 
ORDER BY count DESC 
LIMIT 20;
```

**Thời gian phản hồi trung bình:**
- OpenAI: 2-5 giây
- Fallback: < 0.1 giây

---

## 🎨 Tùy Chỉnh Giao Diện

### Màu Sắc Theme

File: `assets/css/chatbot.css`

```css
/* Đổi màu chủ đạo */
#chatbot-widget .chatbot-toggle {
    background: linear-gradient(135deg, #c97d1a 0%, #d4a574 100%);
}

/* Màu tin nhắn user */
.chatbot-message.user {
    background: linear-gradient(135deg, #6b5b73 0%, #8b7193 100%);
}
```

### Thêm Quick Reply Buttons (Optional)

File: `includes/chatbot.php` - sau form:

```html
<div class="chatbot-quick-replies">
    <button onclick="sendQuickReply('Xem thực đơn')">🍲 Thực đơn</button>
    <button onclick="sendQuickReply('Đặt bàn')">📅 Đặt bàn</button>
    <button onclick="sendQuickReply('Giờ mở cửa')">🕐 Giờ mở cửa</button>
</div>
```

File: `assets/js/chatbot.js` - thêm function:

```javascript
function sendQuickReply(text) {
    document.getElementById('chatbot-input').value = text;
    document.getElementById('chatbot-form').dispatchEvent(new Event('submit'));
}
```

---

## 🚀 Tối Ưu Hiệu Suất

### 1. Caching Responses

```php
// Thêm vào chatbot_api.php
$cache_key = 'chatbot_' . md5($message_safe);
$cached = apcu_fetch($cache_key);

if ($cached) {
    echo json_encode(['success' => true, 'reply' => $cached, 'cached' => true]);
    exit;
}

// ... gọi API ...

// Cache kết quả 1 giờ
apcu_store($cache_key, $reply, 3600);
```

### 2. Rate Limiting

```php
// Giới hạn 10 tin nhắn / phút / user
$rate_key = 'rate_' . ($user_id ?? $_SERVER['REMOTE_ADDR']);
$count = (int)apcu_fetch($rate_key);

if ($count > 10) {
    echo json_encode(['success' => false, 'error' => 'Bạn gửi tin nhắn quá nhanh. Vui lòng đợi!']);
    exit;
}

apcu_store($rate_key, $count + 1, 60);
```

---

## 🔒 Bảo Mật

### 1. Validate Input

```php
// Thêm vào chatbot_api.php
if (strlen($message_safe) < 2) {
    echo json_encode(['success' => false, 'error' => 'Tin nhắn quá ngắn']);
    exit;
}

if (preg_match('/<script|javascript:/i', $message)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}
```

### 2. CORS Protection

```php
// Đầu file chatbot_api.php
$allowed_origins = [BASE_URL];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}
```

---

## 📈 Nâng Cao

### 1. Multi-language Support

```php
$lang = $user['language'] ?? 'vi';

$prompts = [
    'vi' => 'Bạn là trợ lý tiếng Việt...',
    'en' => 'You are an English assistant...',
];

$system_prompt = $prompts[$lang];
```

### 2. Context Memory (Conversation History)

```php
// Lưu lịch sử chat trong session
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

$_SESSION['chat_history'][] = ['role' => 'user', 'content' => $message_safe];

// Gửi lịch sử cho AI
$messages = array_merge(
    [['role' => 'system', 'content' => $system_prompt]],
    $_SESSION['chat_history']
);

// Giới hạn 10 tin nhắn gần nhất
$_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -10);
```

### 3. Intent Recognition

```php
function detectIntent($message) {
    $intents = [
        'booking' => '/đặt.*bàn|reserve|book/i',
        'menu' => '/thực đơn|menu|món/i',
        'location' => '/địa chỉ|ở đâu|location/i',
    ];
    
    foreach ($intents as $intent => $pattern) {
        if (preg_match($pattern, $message)) {
            return $intent;
        }
    }
    return 'general';
}

$intent = detectIntent($message_safe);
// Xử lý theo intent...
```

---

## 🧪 Testing

### Test API Endpoint

```bash
# PowerShell
$body = '{"message":"xin chào"}' | ConvertTo-Json
Invoke-WebRequest -Uri "http://localhost/nha_hang/chatbot_api.php" -Method POST -Body $body -ContentType "application/json"

# cURL (Git Bash)
curl -X POST http://localhost/nha_hang/chatbot_api.php \
  -H "Content-Type: application/json" \
  -d '{"message":"xin chào"}'
```

### Test Cases

1. ✅ Tin nhắn rỗng
2. ✅ Tin nhắn dài (>2000 ký tự)
3. ✅ HTML/Script injection
4. ✅ Câu hỏi thông thường
5. ✅ Câu hỏi phức tạp
6. ✅ Rate limiting

---

## 📚 Tài Nguyên

- OpenAI API Docs: https://platform.openai.com/docs
- Regex Testing: https://regex101.com
- PHP cURL: https://www.php.net/manual/en/book.curl.php

---

## 🆘 Troubleshooting

### Lỗi: "No message provided"
- Check format JSON body
- Verify Content-Type header

### Lỗi: "Request error: ..."
- Check API key
- Check internet connection
- Verify OpenAI service status

### Chatbot không hiển thị
- Clear browser cache
- Check console errors (F12)
- Verify chatbot.js loaded

### Response chậm
- Giảm max_tokens
- Implement caching
- Consider fallback timeout

---

## 💡 Best Practices

1. ✅ Luôn test trước khi deploy
2. ✅ Monitor chat logs thường xuyên
3. ✅ Cập nhật FAQ dựa trên câu hỏi phổ biến
4. ✅ Giữ responses ngắn gọn (2-3 câu)
5. ✅ Sử dụng emoji phù hợp
6. ✅ Luôn có fallback option
7. ✅ Bảo mật API key
8. ✅ Set rate limiting

---

**Cập nhật lần cuối:** 2025-11-09
**Version:** 2.0
**Tác giả:** AI Assistant
