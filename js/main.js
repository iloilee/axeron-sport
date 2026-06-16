/**
 * Axeron Sports Shop - Main JavaScript
 * Logic frontend chung cho toàn bộ website
 */

// Define BASE_URL if not already defined
if (typeof BASE_URL === 'undefined') {
    var BASE_URL = window.location.origin + '/axeron-sport-website-master';
}

// ==========================================
// CART MANAGEMENT
// ==========================================

/**
 * Thêm sản phẩm vào giỏ hàng
 */
async function addToCart(productId, variantId, quantity = 1) {
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('variant_id', variantId);
        formData.append('quantity', quantity);

        const response = await fetch(`${BASE_URL}/api/add-to-cart.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            updateCartBadge(data.cart_count);
            if (typeof openCartDrawer === 'function') {
                openCartDrawer();
            } else {
                showToast('Đã thêm sản phẩm vào giỏ hàng!', 'success');
            }
            return true;
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'error');
            return false;
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        showToast('Không thể thêm vào giỏ hàng', 'error');
        return false;
    }
}

/**
 * Cập nhật số lượng sản phẩm trong giỏ hàng
 */
async function updateCartItem(cartItemId, quantity) {
    try {
        const response = await fetch(`${BASE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                cart_item_id: cartItemId,
                quantity: quantity
            })
        });

        const data = await response.json();

        if (data.success) {
            const count = data.data ? data.data.cart_count : data.cart_count;
            updateCartBadge(count);
            return data;
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'error');
            return data;
        }
    } catch (error) {
        console.error('Update cart error:', error);
        showToast('Không thể cập nhật giỏ hàng', 'error');
        return { success: false };
    }
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 */
async function removeFromCart(cartItemId) {
    try {
        const response = await fetch(`${BASE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                cart_item_id: cartItemId
            })
        });

        const data = await response.json();

        if (data.success) {
            const count = data.data ? data.data.cart_count : data.cart_count;
            updateCartBadge(count);
            showToast('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
            return true;
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'error');
            return false;
        }
    } catch (error) {
        console.error('Remove from cart error:', error);
        showToast('Không thể xóa sản phẩm', 'error');
        return false;
    }
}

// ==========================================
// CART DRAWER MANAGEMENT
// ==========================================

function openCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    const backdrop = document.getElementById('cart-drawer-backdrop');
    if (drawer && backdrop) {
        backdrop.classList.remove('hidden');
        // Kích hoạt transition
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            drawer.classList.remove('translate-x-full');
        }, 10);
        loadCartDrawerItems();
    } else {
        // Nếu không có drawer (ở trang checkout chẳng hạn), redirect sang cart
        window.location.href = BASE_URL + '/shop/cart.php';
    }
}

function closeCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    const backdrop = document.getElementById('cart-drawer-backdrop');
    if (drawer && backdrop) {
        backdrop.classList.add('opacity-0');
        drawer.classList.add('translate-x-full');
        setTimeout(() => {
            backdrop.classList.add('hidden');
        }, 300); // Đợi transition CSS chạy xong
    }
}

async function loadCartDrawerItems() {
    const body = document.getElementById('cart-drawer-body');
    const subtotalEl = document.getElementById('cart-drawer-subtotal');
    const countEl = document.getElementById('cart-drawer-count');
    
    if (!body) return;
    
    body.innerHTML = '<div class="text-center py-12"><span class="material-symbols-outlined text-4xl animate-spin text-outline-variant">progress_activity</span></div>';
    
    try {
        const response = await fetch(`${BASE_URL}/api/cart.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get' })
        });
        
        const data = await response.json();
        if (data.success) {
            const resultData = data.data || {};
            updateCartBadge(resultData.count || 0);
            if (countEl) countEl.textContent = `(${resultData.count || 0})`;
            
            if (!resultData.items || resultData.items.length === 0) {
                body.innerHTML = `
                    <div class="text-center py-16 flex flex-col items-center">
                        <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">shopping_cart</span>
                        <p class="text-on-surface-variant font-medium mb-6">Giỏ hàng của bạn đang trống</p>
                        <button onclick="closeCartDrawer()" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-primary transition-colors">Tiếp tục mua sắm</button>
                    </div>
                `;
                if (subtotalEl) subtotalEl.textContent = '0đ';
                return;
            }
            
            let html = '';
            let subtotal = 0;
            
            resultData.items.forEach(item => {
                const img = window.getImageUrl ? window.getImageUrl(item.image_url) : (item.image_url || 'https://placehold.co/100x100');
                const priceFormat = new Intl.NumberFormat('vi-VN').format(item.unit_price).replace(/,/g, '.') + 'đ';
                const originalPriceFormat = new Intl.NumberFormat('vi-VN').format(item.original_price).replace(/,/g, '.') + 'đ';
                subtotal += item.unit_price * item.quantity;
                
                html += `
                    <div class="flex gap-4 border border-outline-variant p-3 rounded-xl relative group bg-surface-container-lowest hover:border-axeron-red/50 transition-colors">
                        <img src="${img}" alt="${item.product_name}" class="w-20 h-20 object-cover rounded-md bg-surface-container">
                        <div class="flex-1 flex flex-col justify-between">
                            <div class="pr-6">
                                <h4 class="font-headline-sm text-sm text-on-surface line-clamp-2 leading-tight mb-1">${item.product_name}</h4>
                                <div class="text-xs text-on-surface-variant flex gap-2">
                                    ${item.color ? `<span>Màu: <strong class="text-on-surface">${item.color}</strong></span>` : ''}
                                    ${item.size ? `<span>Size: <strong class="text-on-surface">${item.size}</strong></span>` : ''}
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex flex-col">
                                    <span class="font-bold text-axeron-red">${priceFormat}</span>
                                    ${item.unit_price < item.original_price ? `<span class="text-xs text-on-surface-variant line-through">${originalPriceFormat}</span>` : ''}
                                </div>
                                
                                <div class="flex items-center border border-outline-variant rounded-md overflow-hidden bg-white">
                                    <button class="w-7 h-7 flex items-center justify-center hover:bg-surface-container transition-colors text-on-surface-variant" onclick="updateDrawerCartItem(${item.cart_item_id}, ${parseInt(item.quantity) - 1}, ${item.stock_quantity})">-</button>
                                    <span class="w-8 text-center font-medium text-sm text-on-surface flex items-center justify-center">${item.quantity}</span>
                                    <button class="w-7 h-7 flex items-center justify-center hover:bg-surface-container transition-colors text-on-surface-variant" onclick="updateDrawerCartItem(${item.cart_item_id}, ${parseInt(item.quantity) + 1}, ${item.stock_quantity})">+</button>
                                </div>
                            </div>
                        </div>
                        <button onclick="removeDrawerCartItem(${item.cart_item_id})" class="absolute top-2 right-2 text-outline-variant hover:text-axeron-red transition-colors p-1 bg-surface-container-lowest rounded-full shadow-sm opacity-0 group-hover:opacity-100" aria-label="Xóa">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                `;
            });
            
            body.innerHTML = html;
            if (subtotalEl) subtotalEl.textContent = new Intl.NumberFormat('vi-VN').format(subtotal).replace(/,/g, '.') + 'đ';
            
        } else {
            body.innerHTML = '<div class="text-center py-8 text-axeron-red">Có lỗi khi tải giỏ hàng</div>';
        }
    } catch (e) {
        body.innerHTML = '<div class="text-center py-8 text-axeron-red">Lỗi kết nối</div>';
    }
}

async function updateDrawerCartItem(cartItemId, quantity, maxStock) {
    if (quantity < 1) return removeDrawerCartItem(cartItemId);
    if (maxStock !== undefined && quantity > maxStock) {
        showToast('Số lượng sản phẩm vượt quá tồn kho hiện có.', 'error');
        return;
    }
    await updateCartItem(cartItemId, quantity);
    loadCartDrawerItems();
}

async function removeDrawerCartItem(cartItemId) {
    await removeFromCart(cartItemId);
    loadCartDrawerItems();
}

/**
 * Cập nhật badge số lượng giỏ hàng
 */
function updateCartBadge(count) {
    const badges = document.querySelectorAll('.cart-badge');
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

// ==========================================
// QUANTITY CONTROLS
// ==========================================

/**
 * Khởi tạo các nút tăng/giảm số lượng
 */
function initQuantityControls(container = document) {
    // Tìm tất cả các input số lượng
    const quantityInputs = container.querySelectorAll('input[type="number"]');

    quantityInputs.forEach(input => {
        const wrapper = input.closest('.flex.items-center');
        if (!wrapper) return;

        const decreaseBtn = wrapper.querySelector('button:first-child');
        const increaseBtn = wrapper.querySelector('button:last-child');

        if (decreaseBtn && !decreaseBtn.hasAttribute('onclick')) {
            decreaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        if (increaseBtn && !increaseBtn.hasAttribute('onclick')) {
            increaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value) || 1;
                const maxValue = parseInt(input.max) || 999;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    });
}

// ==========================================
// SIZE SELECTOR
// ==========================================

/**
 * Khởi tạo chọn size cho trang chi tiết sản phẩm
 */
function initSizeSelector() {
    const sizeButtons = document.querySelectorAll('.size-selector button, [data-size]');

    sizeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.disabled) return;

            // Remove active from all
            sizeButtons.forEach(btn => {
                btn.classList.remove('border-2', 'border-axeron-red', 'bg-axeron-red/5', 'text-axeron-red', 'font-bold');
                btn.classList.add('border-outline-variant', 'text-on-surface-variant');
            });

            // Add active to clicked
            button.classList.remove('border-outline-variant', 'text-on-surface-variant');
            button.classList.add('border-2', 'border-axeron-red', 'bg-axeron-red/5', 'text-axeron-red', 'font-bold');
        });
    });
}

// ==========================================
// TOAST NOTIFICATIONS
// ==========================================

/**
 * Hiển thị thông báo toast
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container') || createToastContainer();

    // Prevent spam: check if the exact same message is currently displaying
    const existingMessages = Array.from(container.children).map(toast => {
        const span = toast.querySelector('.font-body-md');
        return span ? span.textContent.trim() : '';
    });
    
    if (existingMessages.includes(message.trim())) {
        return;
    }

    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-axeron-blue';
    const icon = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';

    toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-toast`;
    toast.innerHTML = `
        <span class="material-symbols-outlined">${icon}</span>
        <span class="font-body-md">${message}</span>
    `;

    container.appendChild(toast);

    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-x-full', 'transition-all', 'duration-300');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed top-24 right-4 z-[100] flex flex-col gap-2';
    document.body.appendChild(container);
    return container;
}

// ==========================================
// PRICE & CURRENCY FORMATTING
// ==========================================

/**
 * Format số thành tiền VND
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
}

/**
 * Parse tiền từ string
 */
function parseCurrency(str) {
    return parseInt(str.replace(/[^\d]/g, '')) || 0;
}

// ==========================================
// FORM VALIDATION
// ==========================================

/**
 * Validate email
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate phone (Việt Nam)
 */
function isValidPhone(phone) {
    const re = /^(0[0-9]{9,10})$/;
    return re.test(phone.replace(/\s/g, ''));
}

/**
 * Validate form inputs
 */
function validateForm(form) {
    let isValid = true;
    const errors = [];

    // Required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });

    // Email fields
    form.querySelectorAll('[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            field.classList.add('border-red-500');
            isValid = false;
        }
    });

    // Phone fields
    form.querySelectorAll('[data-validate="phone"]').forEach(field => {
        if (field.value && !isValidPhone(field.value)) {
            field.classList.add('border-red-500');
            isValid = false;
        }
    });

    return isValid;
}

// ==========================================
// SLIDER / CAROUSEL
// ==========================================

/**
 * Khởi tạo slider cho trang chủ
 */
function initSlider() {
    let currentSlide = 1;
    const totalSlides = document.querySelectorAll('[id^="slide-"]').length;

    if (totalSlides === 0) return;

    window.updateSlider = function() {
        for (let i = 1; i <= totalSlides; i++) {
            const slide = document.getElementById(`slide-${i}`);
            const dot = document.getElementById(`dot-${i}`);

            if (slide) {
                slide.classList.toggle('opacity-0', currentSlide !== i);
                slide.classList.toggle('opacity-100', currentSlide === i);
            }

            if (dot) {
                dot.className = currentSlide === i
                    ? 'w-8 h-3 rounded-full bg-white transition-all'
                    : 'w-3 h-3 rounded-full bg-white/50 transition-all hover:bg-white/80';
            }
        }
    };

    window.nextSlide = function() {
        currentSlide = currentSlide >= totalSlides ? 1 : currentSlide + 1;
        updateSlider();
    };

    window.prevSlide = function() {
        currentSlide = currentSlide <= 1 ? totalSlides : currentSlide - 1;
        updateSlider();
    };

    window.goToSlide = function(slideIndex) {
        currentSlide = slideIndex;
        updateSlider();
    };

    // Auto advance every 4 seconds
    setInterval(window.nextSlide, 4000);
}

// ==========================================
// PAGINATION
// ==========================================

/**
 * Chuyển trang
 */
function goToPage(page, url) {
    const urlObj = new URL(window.location.href);
    urlObj.searchParams.set('page', page);
    window.location.href = urlObj.toString();
}

// ==========================================
// FILTER & SORT
// ==========================================

/**
 * Áp dụng bộ lọc
 */
function applyFilters(form) {
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }

    // Add current search if exists
    const urlParams = new URLSearchParams(window.location.search);
    const search = urlParams.get('search');
    const category = urlParams.get('category');

    if (search) params.set('search', search);
    if (category) params.set('category', category);

    window.location.href = window.location.pathname + '?' + params.toString();
}

/**
 * Xóa tất cả bộ lọc
 */
function clearFilters() {
    window.location.href = window.location.pathname;
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize quantity controls
    initQuantityControls();

    // Initialize size selector
    initSizeSelector();

    // Initialize slider
    initSlider();

    // Add form validation to forms
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                showToast('Vui lòng điền đầy đủ thông tin', 'error');
            }
        });
    });
});

// ==========================================
// WISHLIST MANAGEMENT
// ==========================================

async function addToWishlist(productId, btnElement) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('product_id', productId);
        formData.append('csrf_token', csrfToken);

        const response = await fetch(`${BASE_URL}/api/wishlist-handler.php`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.redirect) {
            window.location.href = `${BASE_URL}/auth/login.php?redirect=${encodeURIComponent(window.location.href)}`;
            return;
        }

        if (data.success) {
            if (btnElement) {
                const icon = btnElement.querySelector('.material-symbols-outlined');
                if (data.status === 'added') {
                    icon.style.fontVariationSettings = "'FILL' 1";
                    icon.classList.add('text-axeron-red');
                    icon.classList.remove('text-on-surface-variant', 'hover:text-axeron-red');
                    showToast('Đã thêm vào danh sách yêu thích!', 'success');
                } else {
                    icon.style.fontVariationSettings = "'FILL' 0";
                    icon.classList.remove('text-axeron-red');
                    icon.classList.add('text-on-surface-variant', 'hover:text-axeron-red');
                    showToast('Đã xóa khỏi danh sách yêu thích!', 'success');
                }
            } else {
                showToast(data.status === 'added' ? 'Đã thêm vào yêu thích!' : 'Đã xóa khỏi yêu thích!', 'success');
                // Nếu đang ở trang wishlist, tự reload
                if (window.location.href.includes('wishlist.php') && data.status === 'removed') {
                    setTimeout(() => window.location.reload(), 1000);
                }
            }
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        console.error('Wishlist error:', error);
        showToast('Không thể thực hiện tác vụ này', 'error');
    }
}

// Export functions globally
window.addToCart = addToCart;
window.updateCartItem = updateCartItem;
window.removeFromCart = removeFromCart;
window.updateCartBadge = updateCartBadge;
window.showToast = showToast;
window.formatCurrency = formatCurrency;
window.parseCurrency = parseCurrency;
window.validateForm = validateForm;
window.goToPage = goToPage;
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;
window.addToWishlist = addToWishlist;
