/**
 * Axeron Sports Shop - Main JavaScript
 * Logic frontend chung cho toàn bộ website
 */

// Define BASE_URL if not already defined
if (typeof BASE_URL === 'undefined') {
    var BASE_URL = window.location.origin + '/axeron-sport-website-main';
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
            showToast('Đã thêm sản phẩm vào giỏ hàng!', 'success');
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
            updateCartBadge(data.cart_count);
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
            updateCartBadge(data.cart_count);
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

        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        if (increaseBtn) {
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

    // Auto advance every 5 seconds
    setInterval(window.nextSlide, 5000);
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
