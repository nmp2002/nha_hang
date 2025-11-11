document.addEventListener('DOMContentLoaded', function () {
    const widget = document.getElementById('chatbot-widget');
    const toggle = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messagesEl = document.getElementById('chatbot-messages');

    // Hiển thị tin nhắn chào mừng khi mở lần đầu
    let firstOpen = true;

    function openWidget() {
        widget.classList.remove('chatbot-closed');
        widget.classList.add('chatbot-open');
        widget.setAttribute('aria-hidden', 'false');
        input.focus();
        
        // Hiển thị tin nhắn chào mừng lần đầu
        if (firstOpen && messagesEl.children.length === 0) {
            setTimeout(() => {
                addMessage('👋 Xin chào! Mình là trợ lý ảo của Cơm Quê Dượng Bầu. Mình có thể giúp gì cho bạn?', 'bot');
            }, 500);
            firstOpen = false;
        }
    }

    function closeWidget() {
        widget.classList.remove('chatbot-open');
        widget.classList.add('chatbot-closed');
        widget.setAttribute('aria-hidden', 'true');
    }

    toggle.addEventListener('click', function () {
        if (widget.classList.contains('chatbot-open')) closeWidget(); else openWidget();
    });
    closeBtn.addEventListener('click', closeWidget);

    function addMessage(text, from) {
        const m = document.createElement('div');
        m.className = 'chatbot-message ' + (from === 'user' ? 'user' : 'bot');
        m.innerHTML = text; // Allow HTML for links and formatting
        messagesEl.appendChild(m);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function showTypingIndicator() {
        const typing = document.createElement('div');
        typing.className = 'chatbot-typing';
        typing.id = 'typing-indicator';
        typing.innerHTML = '<span></span><span></span><span></span>';
        messagesEl.appendChild(typing);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function hideTypingIndicator() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        
        addMessage(text, 'user');
        input.value = '';
        
        // Hiển thị typing indicator
        showTypingIndicator();

        // Send to API
        fetch((window.APP_BASE_URL || '/') + 'chatbot_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        }).then(r => r.json()).then(data => {
            hideTypingIndicator();
            if (data && data.success) {
                // Delay để tạo cảm giác tự nhiên hơn
                setTimeout(() => {
                    addMessage(data.reply, 'bot');
                    // If API returned suggestions, render them
                    if (data.suggestions && Array.isArray(data.suggestions) && data.suggestions.length) {
                        renderQuickReplies(data.suggestions);
                    }
                    // If API returned an action, handle simple actions
                    if (data.action) {
                        handleAction(data.action, data.payload || {});
                    }
                }, 300);
            } else {
                addMessage((data && data.error) || 'Có lỗi khi gửi yêu cầu. Vui lòng thử lại!', 'bot');
            }
        }).catch(err => {
            console.error(err);
            hideTypingIndicator();
            addMessage('❌ Lỗi kết nối. Vui lòng thử lại hoặc gọi hotline <strong>076 537 1893</strong>', 'bot');
        });
    });
    
    // Gợi ý câu hỏi nhanh (rendered below the messages)
    const quickReplies = ['Xem thực đơn', 'Đặt bàn', 'Giờ mở cửa', 'Địa chỉ'];
    const quickContainer = document.getElementById('chatbot-quick');

    function renderQuickReplies(list) {
        if (!quickContainer) return;
        quickContainer.innerHTML = '';
        list.forEach(q => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'chat-quick-btn';
            btn.textContent = q;
            btn.addEventListener('click', function () {
                input.value = q;
                // submit automatically
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            });
            quickContainer.appendChild(btn);
        });
    }

    // render default quick replies
    renderQuickReplies(quickReplies);

    function handleAction(action, payload) {
        // Support simple client-side actions returned by API
        if (action === 'open_reservation') {
            const url = (window.APP_BASE_URL || '/') + 'pages/reservation.php';
            // show a clickable suggestion
            addMessage('Bạn có thể đặt bàn tại: <a href="' + url + '" target="_blank">Mở trang đặt bàn</a>', 'bot');
        }
        if (action === 'show_menu_item' && payload && payload.item_id) {
            const url = (window.APP_BASE_URL || '/') + 'pages/menu.php#dish-' + payload.item_id;
            addMessage('Mình tìm thấy món phù hợp: <a href="' + url + '" target="_blank">Xem chi tiết</a>', 'bot');
        }
    }
});
