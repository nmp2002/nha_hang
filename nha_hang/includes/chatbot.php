<?php
// Chatbot widget include. Rendered only for logged-in users from header.php
?>
<!-- Chatbot widget -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot.css">
<div id="chatbot-widget" class="chatbot-closed" aria-hidden="true">
    <div class="chatbot-header">
        <span>👋 Hỗ trợ khách hàng</span>
        <button id="chatbot-close" aria-label="Đóng">×</button>
    </div>
    <div class="chatbot-body" id="chatbot-body">
        <div class="chatbot-messages" id="chatbot-messages"></div>
        <div class="chatbot-quick" id="chatbot-quick" aria-hidden="false"></div>
        <form id="chatbot-form" class="chatbot-form">
            <input type="text" id="chatbot-input" placeholder="Hỏi về thực đơn, đặt bàn, giờ mở cửa..." autocomplete="off">
            <button type="submit">Gửi</button>
        </form>
    </div>
    <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Mở chat">
        💬
    </button>
</div>
<script>
    // Expose BASE_URL for chatbot JS
    window.APP_BASE_URL = '<?php echo BASE_URL; ?>';
</script>
<script src="<?php echo BASE_URL; ?>assets/js/chatbot.js" defer></script>
