<?php
/**
 * Footer Template - Axeron Sports Shop
 * Footer được include vào các trang PHP
 */

// Load site settings for footer
$db = db();
$footerSettings = $db->select("
    SELECT setting_key, setting_value
    FROM site_settings
    WHERE is_public = 1
    AND group_name IN ('contact', 'social', 'footer')
");
$footerData = [];
foreach ($footerSettings as $s) {
    $footerData[$s['setting_key']] = $s['setting_value'];
}

// Default values if not set
$siteName = $footerData['site_name'] ?? 'AXERON SPORT';
$contactPhone = $footerData['contact_phone'] ?? '1800 0021';
$contactEmail = $footerData['contact_email'] ?? 'contact@axeron.vn';
$contactAddress = $footerData['contact_address'] ?? '';
$workHours = $footerData['contact_work_hours'] ?? '08:30 - 21:30';
$footerAbout = $footerData['footer_about'] ?? 'Hệ thống cung cấp dụng cụ và thời trang thể thao chuyên nghiệp hàng đầu.';
$footerCopyright = '© 2026 Axeron Sport - Developed by Le Huu Loi';
$socialFacebook = $footerData['social_facebook'] ?? '#';
$socialYoutube = $footerData['social_youtube'] ?? '#';
$socialInstagram = $footerData['social_instagram'] ?? '#';
$socialZalo = $footerData['social_zalo'] ?? '#';
?>
<!-- Slide-out Cart Drawer -->
<div id="cart-drawer-backdrop" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300" onclick="closeCartDrawer()"></div>
<div id="cart-drawer" class="fixed top-0 right-0 h-full w-full sm:w-[450px] bg-surface-container-lowest shadow-2xl z-[70] translate-x-full transition-transform duration-300 flex flex-col">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between bg-surface-container-low">
        <h2 class="font-headline-md text-xl text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-axeron-red">shopping_bag</span>
            Giỏ Hàng <span id="cart-drawer-count" class="text-on-surface-variant font-label-md text-base font-normal ml-1">(0)</span>
        </h2>
        <button onclick="closeCartDrawer()" class="p-2 hover:bg-surface-container rounded-full text-on-surface-variant hover:text-axeron-red transition-colors" aria-label="Đóng">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    
    <!-- Body: Cart Items -->
    <div id="cart-drawer-body" class="flex-grow overflow-y-auto p-4 sm:p-6 space-y-4">
        <div class="text-center py-12">
            <span class="material-symbols-outlined text-4xl animate-spin text-outline-variant">progress_activity</span>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="border-t border-outline-variant p-6 bg-surface-container-low mt-auto">
        <div class="flex justify-between items-center mb-4">
            <span class="font-label-lg text-on-surface-variant uppercase tracking-widest">Tạm tính</span>
            <span id="cart-drawer-subtotal" class="font-headline-md text-axeron-red text-2xl font-bold">0đ</span>
        </div>
        <p class="text-sm text-on-surface-variant mb-4 text-center">Phí vận chuyển sẽ được tính ở bước thanh toán.</p>
        <div class="grid grid-cols-2 gap-3">
            <a href="<?= BASE_URL ?>/shop/cart.php" class="py-3 px-4 border border-outline-variant rounded-lg text-center font-label-lg hover:border-axeron-red hover:text-axeron-red transition-colors text-on-surface bg-surface-container-lowest">
                Xem giỏ hàng
            </a>
            <a href="<?= BASE_URL ?>/shop/checkout.php" class="py-3 px-4 bg-axeron-red text-white rounded-lg text-center font-label-lg hover:bg-primary transition-colors uppercase tracking-wider">
                Thanh toán
            </a>
        </div>
    </div>
</div>

<!-- SideNavBar (Floating Support) -->
<div class="fixed right-4 bottom-24 z-50 flex flex-col space-y-3">
    <!-- Chatbot Button -->
    <button aria-label="Chatbot AI" class="w-12 h-12 flex items-center justify-center bg-axeron-red rounded-full text-white hover:scale-110 transition-transform shadow-lg group relative animate-pulse" onclick="toggleChatbox()">
        <span class="material-symbols-outlined text-2xl">smart_toy</span>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-axeron-red text-white font-label-sm text-label-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Hỏi đáp AI</span>
    </button>
    
    <!-- Tra cứu đơn hàng Button -->
    <a aria-label="Tra cứu đơn hàng" class="w-12 h-12 flex items-center justify-center bg-[#FF9800] rounded-full text-white hover:scale-110 transition-transform shadow-md group relative" href="<?= BASE_URL ?>/shop/order-tracking.php">
        <span class="material-symbols-outlined text-2xl">content_paste_search</span>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-[#FF9800] text-white font-label-sm text-label-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Tra cứu đơn hàng</span>
    </a>
    
    <?php if (!empty($socialZalo) && $socialZalo !== '#'): ?>
    <a aria-label="Zalo" class="w-12 h-12 flex items-center justify-center bg-inverse-surface rounded-full text-white hover:scale-110 transition-transform shadow-md group relative" href="<?= htmlspecialchars($socialZalo) ?>" target="_blank">
        <span class="material-symbols-outlined text-2xl">chat</span>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-inverse-surface text-white font-label-sm text-label-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Zalo</span>
    </a>
    <?php endif; ?>
    <?php if (!empty($contactPhone)): ?>
    <a aria-label="Hotline" class="w-12 h-12 flex items-center justify-center bg-axeron-blue rounded-full text-white hover:scale-110 transition-transform shadow-md group relative" href="tel:<?= preg_replace('/[^0-9]/', '', $contactPhone) ?>">
        <span class="material-symbols-outlined text-2xl">call</span>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-axeron-blue text-white font-label-sm text-label-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap"><?= htmlspecialchars($contactPhone) ?></span>
    </a>
    <?php endif; ?>
    <button aria-label="Lên đầu trang" class="w-12 h-12 flex items-center justify-center bg-inverse-surface rounded-full text-white hover:scale-110 transition-transform shadow-md group relative" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <span class="material-symbols-outlined text-2xl">arrow_upward</span>
        <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-inverse-surface text-white font-label-sm text-label-sm px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Lên đầu</span>
    </button>
</div>

<!-- Footer Component -->
<footer class="w-full py-12 px-margin-desktop bg-inverse-surface text-white dark:bg-black full-width flat border-t border-inverse-surface">
    <div class="max-w-container-max mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Brand Info -->
        <div class="flex flex-col gap-4">
            <a class="text-headline-md font-headline-lg text-white font-black tracking-tight" href="<?= BASE_URL ?>">
                <?= strtoupper($siteName) ?>
            </a>
            <p class="font-body-md text-body-md text-surface-variant text-sm mt-4">
                <?= nl2br(htmlspecialchars($footerAbout)) ?>
            </p>
            <div class="flex gap-4 mt-4">
                <?php if (!empty($socialFacebook)): ?>
                <a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-axeron-red transition-colors" href="<?= htmlspecialchars($socialFacebook) ?>" target="_blank" aria-label="Facebook">
                    <span class="material-symbols-outlined text-lg">public</span>
                </a>
                <?php endif; ?>
                <?php if (!empty($socialYoutube)): ?>
                <a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-axeron-red transition-colors" href="<?= htmlspecialchars($socialYoutube) ?>" target="_blank" aria-label="YouTube">
                    <span class="material-symbols-outlined text-lg">play_arrow</span>
                </a>
                <?php endif; ?>
                <?php if (!empty($socialInstagram)): ?>
                <a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-axeron-red transition-colors" href="<?= htmlspecialchars($socialInstagram) ?>" target="_blank" aria-label="Instagram">
                    <span class="material-symbols-outlined text-lg">photo_camera</span>
                </a>
                <?php endif; ?>
                <?php if (!empty($contactEmail)): ?>
                <a class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-axeron-red transition-colors" href="mailto:<?= htmlspecialchars($contactEmail) ?>" aria-label="Email">
                    <span class="material-symbols-outlined text-lg">mail</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Về Chúng Tôi -->
        <div class="flex flex-col gap-3">
            <h4 class="font-headline-md text-label-lg text-white mb-2 uppercase">
                Về Chúng Tôi
            </h4>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/pages/about.php">
                Giới thiệu Axeron
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/blog/news.php">
                Tin tức & Sự kiện
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/pages/store-locator.php">
                Hệ thống cửa hàng
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/pages/contact.php">
                Liên hệ
            </a>
        </div>

        <!-- Hỗ Trợ Khách Hàng -->
        <div class="flex flex-col gap-3">
            <h4 class="font-headline-md text-label-lg text-white mb-2 uppercase">
                Hỗ Trợ Khách Hàng
            </h4>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/policies/privacy-policy.php">
                Chính sách quyền riêng tư
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/policies/purchase-policy.php">
                Chính sách mua hàng
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/policies/return-exchange-policy.php">
                Chính sách đổi và trả hàng
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/policies/shipping-policy.php">
                Chính sách giao hàng
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/policies/warranty-policy.php">
                Chính sách bảo hành
            </a>
            <a class="text-surface-variant hover:text-white transition-colors font-body-md text-body-md text-sm py-1" href="<?= BASE_URL ?>/policies/size-guide.php">
                Hướng dẫn chọn size
            </a>
        </div>

        <!-- Liên Hệ -->
        <div class="flex flex-col gap-4">
            <h4 class="font-headline-md text-label-lg text-white mb-2 uppercase">
                Gọi Mua Hàng (<?= htmlspecialchars($workHours) ?>)
            </h4>
            <?php if (!empty($contactPhone)): ?>
            <div class="flex items-center gap-3 bg-white/5 p-3 rounded-lg border border-white/10">
                <span class="material-symbols-outlined text-axeron-red text-3xl">call</span>
                <div>
                    <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $contactPhone)) ?>" class="hover:text-axeron-red transition-colors block font-headline-md text-headline-md text-white font-bold leading-none">
                        <?= htmlspecialchars($contactPhone) ?>
                    </a>
                    <?php if (!empty($footerData['contact_phone_2'])): ?>
                    <p class="font-body-md text-xs text-surface-variant mt-1">
                        Hỗ trợ: <?= htmlspecialchars($footerData['contact_phone_2']) ?>
                    </p>
                    <?php else: ?>
                    <p class="font-body-md text-[11px] text-surface-variant mt-1">
                        Tất cả các ngày trong tuần
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($contactEmail)): ?>
            <p class="font-body-md text-sm text-surface-variant mt-2">
                Email:<br />
                <a class="text-white hover:text-axeron-red transition-colors" href="mailto:<?= htmlspecialchars($contactEmail) ?>">
                    <?= htmlspecialchars($contactEmail) ?>
                </a>
            </p>
            <?php endif; ?>
            <?php if (!empty($contactAddress)): ?>
            <p class="font-body-md text-sm text-surface-variant">
                <a href="https://maps.google.com/?q=<?= urlencode(strip_tags($contactAddress)) ?>" target="_blank" class="hover:text-white transition-colors flex items-start gap-1">
                    <span class="material-symbols-outlined text-lg align-middle">location_on</span>
                    <span><?= nl2br(htmlspecialchars($contactAddress)) ?></span>
                </a>
            </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Copyright -->
    <div class="max-w-container-max mx-auto mt-12 pt-6 border-t border-white/10 text-center">
        <p class="font-body-md text-body-md text-surface-variant text-sm">
            <?= htmlspecialchars($footerCopyright) ?>
        </p>
    </div>
</footer>

<!-- Chatbox UI -->
<div id="ai-chatbox" class="fixed right-4 bottom-24 w-[340px] sm:w-[380px] bg-white dark:bg-black rounded-xl shadow-2xl z-[60] flex flex-col overflow-hidden transition-all duration-300 transform translate-y-8 opacity-0 pointer-events-none border border-axeron-red/20 hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-axeron-red to-red-800 text-white p-4 flex justify-between items-center cursor-pointer" onclick="toggleChatbox()">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined">smart_toy</span>
            <div>
                <h3 class="font-headline-md font-bold text-sm m-0 leading-tight">Axeron AI Assistant</h3>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                    <p class="text-[10px] text-white/90 m-0 leading-none">Trực tuyến</p>
                </div>
            </div>
        </div>
        <button class="material-symbols-outlined hover:text-white/80 transition-colors" onclick="toggleChatbox(); event.stopPropagation();" aria-label="Close">close</button>
    </div>
    
    <!-- Messages Body (Fixed height, scrollable) -->
    <div id="chat-messages" class="p-4 h-[350px] overflow-y-auto bg-surface dark:bg-black/50 space-y-3 font-body-md flex flex-col">
        <!-- Default message -->
        <div class="flex items-start gap-2 max-w-[90%] animate-fade-in-down shrink-0">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-axeron-red to-red-600 flex items-center justify-center shrink-0 shadow-md">
                <span class="material-symbols-outlined text-white text-sm">smart_toy</span>
            </div>
            <div class="bg-white dark:bg-inverse-surface border border-outline-variant p-3 rounded-2xl rounded-tl-sm text-sm text-on-surface shadow-sm">
                Chào bạn! Mình là trợ lý AI thông minh của Axeron Sport. Mình có thể giúp gì cho bạn hôm nay?
            </div>
        </div>
    </div>
    
    <!-- Input Area -->
    <div class="p-3 bg-white dark:bg-inverse-surface border-t border-outline-variant">
        <form id="chat-form" class="flex items-center gap-2 relative" onsubmit="sendChatMessage(event)">
            <input type="text" id="chat-input" class="flex-1 bg-surface-container rounded-full py-2 pl-4 pr-10 text-sm focus:outline-none focus:ring-1 focus:ring-axeron-red border border-transparent focus:border-axeron-red transition-all" placeholder="Nhập câu hỏi của bạn..." autocomplete="off">
            <button type="submit" id="chat-submit-btn" class="absolute right-1 w-8 h-8 flex items-center justify-center text-axeron-red hover:bg-red-50 rounded-full transition-colors disabled:opacity-50 disabled:hover:bg-transparent">
                <span class="material-symbols-outlined text-xl">send</span>
            </button>
        </form>
    </div>
</div>

<!-- Chatbox Logic -->
<script>
let chatSessionId = localStorage.getItem('axeron_chat_session_id') || '';
let chatHistoryLoaded = false;

async function toggleChatbox() {
    const chatbox = document.getElementById('ai-chatbox');
    if (chatbox.classList.contains('opacity-0')) {
        chatbox.classList.remove('hidden');
        // trigger reflow
        void chatbox.offsetWidth;
        chatbox.classList.remove('translate-y-8', 'opacity-0', 'pointer-events-none');
        document.getElementById('chat-input').focus();
        
        if (!chatHistoryLoaded && chatSessionId) {
            await loadChatHistory();
        }
    } else {
        chatbox.classList.add('translate-y-8', 'opacity-0', 'pointer-events-none');
        setTimeout(() => chatbox.classList.add('hidden'), 300);
    }
}

async function loadChatHistory() {
    chatHistoryLoaded = true;
    const baseUrl = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '';
    try {
        const response = await fetch(baseUrl + '/api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'history', session_id: chatSessionId })
        });
        const data = await response.json();
        if (data.success && data.data && data.data.messages && data.data.messages.length > 0) {
            document.getElementById('chat-messages').innerHTML = '';
            
            data.data.messages.forEach(msg => {
                appendMessage(msg.sender_type, msg.content, false);
            });
        }
    } catch (e) {
        console.error("Failed to load chat history", e);
    }
}

function appendMessage(sender, text, isHtml = false) {
    const container = document.getElementById('chat-messages');
    const msgDiv = document.createElement('div');
    msgDiv.className = `flex items-start gap-2 max-w-[90%] animate-fade-in-down shrink-0 ${sender === 'user' ? 'ml-auto flex-row-reverse' : ''}`;
    
    let avatarHtml = '';
    if (sender === 'bot') {
        avatarHtml = '<div class="w-8 h-8 rounded-full bg-gradient-to-br from-axeron-red to-red-600 flex items-center justify-center shrink-0 shadow-md"><span class="material-symbols-outlined text-white text-sm">smart_toy</span></div>';
    }
    
    const bgClass = sender === 'user' ? 'bg-axeron-red text-white rounded-tr-sm' : 'bg-white dark:bg-inverse-surface border border-outline-variant text-on-surface rounded-tl-sm';
    
    // Markdown formatting logic
    let formattedText = text;
    if (sender === 'bot' && !isHtml) {
        // Handle bold
        formattedText = formattedText.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold">$1</strong>');
        // Handle italic
        formattedText = formattedText.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Handle lists
        formattedText = formattedText.replace(/^- (.*)$/gm, '<li class="ml-4 list-disc">$1</li>');
        formattedText = formattedText.replace(/(\<li class="ml-4 list-disc"\>.*\<\/li\>)/s, '<ul class="my-2">$1</ul>');
        // Handle newlines
        formattedText = formattedText.replace(/\n/g, '<br>');
        
        // Custom style for product cards from AI if any
        if (formattedText.includes('[PRODUCT_CARD]')) {
            // Will handle product cards parsing later
        }
    }
    
    const contentHtml = `<div class="p-3 rounded-2xl text-sm shadow-sm ${bgClass}">${isHtml ? text : formattedText}</div>`;
    msgDiv.innerHTML = avatarHtml + contentHtml;
    
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

function appendTypingIndicator() {
    const container = document.getElementById('chat-messages');
    const msgDiv = document.createElement('div');
    msgDiv.id = 'chat-typing-indicator';
    msgDiv.className = 'flex items-start gap-2 max-w-[90%] animate-fade-in-down shrink-0';
    msgDiv.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-axeron-red to-red-600 flex items-center justify-center shrink-0 shadow-md">
            <span class="material-symbols-outlined text-white text-sm">smart_toy</span>
        </div>
        <div class="bg-white dark:bg-inverse-surface border border-outline-variant p-3 rounded-2xl rounded-tl-sm shadow-sm flex gap-1 items-center h-10">
            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
        </div>
    `;
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

function removeTypingIndicator() {
    const indicator = document.getElementById('chat-typing-indicator');
    if (indicator) indicator.remove();
}

async function sendChatMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const btn = document.getElementById('chat-submit-btn');
    const message = input.value.trim();
    
    if (!message) return;
    
    const baseUrl = typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '';
    
    appendMessage('user', message);
    input.value = '';
    btn.disabled = true;
    
    appendTypingIndicator();
    
    try {
        const response = await fetch(baseUrl + '/api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message, session_id: chatSessionId })
        });
        
        const data = await response.json();
        removeTypingIndicator();
        
        if (data.success) {
            if (data.data.session_id) {
                chatSessionId = data.data.session_id;
                localStorage.setItem('axeron_chat_session_id', chatSessionId);
            }
            appendMessage('bot', data.data.reply);
        } else {
            appendMessage('bot', data.message || 'Xin lỗi, đã có lỗi xảy ra.');
        }
    } catch (error) {
        removeTypingIndicator();
        appendMessage('bot', 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.');
        console.error('Chat error:', error);
    } finally {
        btn.disabled = false;
        input.focus();
    }
}
</script>

<!-- Toast Container cho notifications -->
<div id="toast-container" class="fixed top-24 right-4 z-[100] flex flex-col gap-2"></div>

<script>
// Toast notification function
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');

    const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-axeron-blue';
    const icon = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';

    toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-fade-in-down`;
    toast.innerHTML = `
        <span class="material-symbols-outlined">${icon}</span>
        <span class="font-body-md">${message}</span>
    `;

    container.appendChild(toast);

    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Format currency helper
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
}

// Parse currency from string
function parseCurrency(str) {
    return parseInt(str.replace(/[^\d]/g, '')) || 0;
}

// Add fade-in animation style
const style = document.createElement('style');
style.textContent = `
    @keyframes fade-in-down {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.3s ease-out;
    }
`;
document.head.appendChild(style);

<?php
// Global flash message handler
$globalFlash = getFlash();
if ($globalFlash && !empty($globalFlash['message'])):
?>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof showToast === 'function') {
        showToast(<?= json_encode($globalFlash['message']) ?>, <?= json_encode($globalFlash['type'] ?? 'success') ?>);
    }
});
<?php endif; ?>
</script>

<!-- AOS Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                once: true, // whether animation should happen only once - while scrolling down
                offset: 50, // offset (in px) from the original trigger point
                duration: 800, // values from 0 to 3000, with step 50ms
                easing: 'ease-out-cubic', // default easing for AOS animations
            });
        }
    });
</script>
