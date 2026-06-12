<?php
/**
 * Cart - Giỏ hàng
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();
$cartItems = [];
$cartSubtotal = 0;
$cartCount = 0;

// Kiểm tra user có tồn tại không (tránh lỗi khi re-import database)
$userId = getUserId();
$userValid = false;
if (isLoggedIn() && $userId) {
    $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
    $userValid = !empty($userCheck);
}

if (isLoggedIn() && $userValid) {
    $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);

    if ($cart) {
        $cartItems = $db->select("
            SELECT
                ci.cart_item_id,
                ci.quantity,
                pv.variant_id,
                pv.sku,
                pv.color,
                pv.size,
                pv.extra_price,
                pv.stock_quantity,
                p.product_id,
                p.product_name,
                p.slug,
                p.base_price,
                pi.image_url,
                (p.base_price + pv.extra_price) as unit_price,
                (ci.quantity * (p.base_price + pv.extra_price)) as item_total
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0
            ORDER BY ci.added_at DESC
        ", [$cart['cart_id']]);

        foreach ($cartItems as $item) {
            $cartSubtotal += $item['item_total'];
            $cartCount += $item['quantity'];
        }
    }
} else {
    // Session cart
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $sessionItem) {
            $item = $db->selectOne("
                SELECT
                    ? as cart_item_id,
                    ? as quantity,
                    pv.variant_id,
                    pv.sku,
                    pv.color,
                    pv.size,
                    pv.extra_price,
                    pv.stock_quantity,
                    p.product_id,
                    p.product_name,
                    p.slug,
                    p.base_price,
                    pi.image_url,
                    (p.base_price + pv.extra_price) as unit_price,
                    (? * (p.base_price + pv.extra_price)) as item_total
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.product_id
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                WHERE pv.variant_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0
            ", [$sessionItem['variant_id'], $sessionItem['quantity'], $sessionItem['quantity'], $sessionItem['variant_id']]);

            if ($item) {
                $item['quantity'] = min($item['quantity'], $item['stock_quantity']);
                $cartItems[] = $item;
                $cartSubtotal += $item['item_total'];
                $cartCount += $item['quantity'];
            }
        }
    }
}

// Lấy phí ship mặc định từ database
$defaultShipping = $db->selectOne("SELECT base_price FROM shipping_prices WHERE shipping_id = 1");
$baseShippingFee = $defaultShipping ? (float)$defaultShipping['base_price'] : 30000;

// Calculate shipping (free if over 2000k)
$shippingFee = $cartSubtotal >= 2000000 ? 0 : $baseShippingFee;
$totalAmount = $cartSubtotal + $shippingFee;

// Lấy flash message nếu có (từ trang checkout đá về)
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Giỏ hàng - Axeron</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-background": "#1b1c1c", "inverse-surface": "#303030", "text-dark": "#212121",
                        "tertiary-container": "#006a85", background: "#fcf9f8", "on-primary-fixed-variant": "#930019",
                        "inverse-primary": "#ffb3b0", "secondary-fixed-dim": "#b0c6ff", "error-container": "#ffdad6",
                        "outline-variant": "#e3bebb", "tertiary-fixed": "#baeaff", "on-secondary": "#ffffff",
                        "secondary-fixed": "#d9e2ff", "on-primary-fixed": "#410006", "surface-container": "#f0eded",
                        error: "#ba1a1a", "axeron-red": "#BE1E2D", "on-tertiary": "#ffffff",
                        "surface-dim": "#dcd9d9", "on-primary-container": "#ffd3d1", "secondary-container": "#0f6df3",
                        "surface-tint": "#b91a2a", primary: "#98001b", "surface-gray": "#F5F5F5",
                        "surface-bright": "#fcf9f8", "surface-container-highest": "#e5e2e1", "on-surface": "#1b1c1c",
                        white: "#FFFFFF", tertiary: "#005066", "surface-container-high": "#eae7e7",
                        "on-error-container": "#93000a", "primary-container": "#be1e2d", "primary-fixed": "#ffdad8",
                        "surface-container-lowest": "#ffffff", "inverse-on-surface": "#f3f0ef",
                        "on-tertiary-container": "#abe6ff", "surface-variant": "#e5e2e1",
                        "on-secondary-container": "#fefcff", secondary: "#0056c5", outline: "#8f6f6e",
                        "axeron-blue": "#2979FF", "tertiary-fixed-dim": "#85d1ef", surface: "#fcf9f8",
                        "on-secondary-fixed-variant": "#00429b", "on-tertiary-fixed": "#001f29",
                        "on-tertiary-fixed-variant": "#004d62", "surface-container-low": "#f6f3f2",
                        "on-secondary-fixed": "#001945", "primary-fixed-dim": "#ffb3b0",
                        "on-surface-variant": "#5b403f", "on-primary": "#ffffff", "on-error": "#ffffff"
                    },
                    borderRadius: { DEFAULT: "0.125rem", lg: "0.25rem", xl: "0.5rem", full: "0.75rem" },
                    spacing: { "margin-desktop": "24px", gutter: "16px", "container-max": "1200px", base: "8px", "margin-mobile": "16px" },
                    fontFamily: { "body-lg": ["Noto Sans", "sans-serif"], "headline-lg-mobile": ["Montserrat", "sans-serif"], "label-sm": ["Noto Sans", "sans-serif"], "display-lg": ["Montserrat", "sans-serif"], "body-md": ["Noto Sans", "sans-serif"], "headline-md": ["Montserrat", "sans-serif"], "headline-lg": ["Montserrat", "sans-serif"], "label-lg": ["Noto Sans", "sans-serif"] },
                    fontSize: { "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }], "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "700" }], "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }], "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "800" }], "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }], "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }], "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }], "label-lg": ["14px", { lineHeight: "20px", fontWeight: "700" }] }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        input[type="number"]::-webkit-inner-spin-button, input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased flex flex-col min-h-screen">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <h1 class="font-headline-lg text-headline-lg md:text-display-lg font-bold mb-8 uppercase text-text-dark">Giỏ hàng của bạn</h1>

        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <span class="material-symbols-outlined text-8xl text-on-surface-variant mb-6">shopping_cart</span>
                <h2 class="font-headline-lg text-2xl text-on-surface mb-4">Giỏ hàng trống</h2>
                <p class="text-on-surface-variant mb-8">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm</p>
                <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="inline-flex items-center gap-2 bg-axeron-red text-white px-8 py-4 rounded-lg font-bold hover:bg-primary transition-colors">
                    Tiếp tục mua sắm
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items Section -->
                <div class="lg:w-2/3 flex flex-col gap-6" id="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="flex flex-col sm:flex-row gap-4 bg-surface-lowest border border-surface-container-high rounded-xl p-4 hover:shadow-sm transition-shadow cart-item" data-item-id="<?= $item['cart_item_id'] ?>">
                        <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="w-full sm:w-32 h-32 bg-surface-container-lowest rounded-lg overflow-hidden flex-shrink-0">
                            <img alt="<?= htmlspecialchars($item['product_name']) ?>" class="w-full h-full object-cover"
                                src="<?= htmlspecialchars(getImageUrl($item['image_url'], 'https://placehold.co/128x128/f0eded/5b403f?text=Product')) ?>"/>
                        </a>
                        <div class="flex-grow flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                <div>
                                    <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="font-headline-md text-body-lg font-semibold text-text-dark hover:text-axeron-red transition-colors">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </a>
                                    <p class="font-body-md text-on-surface-variant text-sm mt-1">
                                        Màu: <?= htmlspecialchars($item['color'] ?? 'Mặc định') ?> | Size: <?= htmlspecialchars($item['size'] ?? 'N/A') ?>
                                    </p>
                                </div>
                                <button onclick="removeItem(<?= $item['cart_item_id'] ?>)" class="text-on-surface-variant hover:text-error transition-colors" aria-label="Remove item">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                            <div class="flex justify-between items-end mt-4">
                                <div class="font-headline-md text-body-lg font-bold text-axeron-red item-price" data-price="<?= $item['unit_price'] ?>">
                                    <?= formatPrice($item['item_total']) ?>
                                </div>
                                <div class="flex items-center border border-outline-variant rounded-full overflow-hidden bg-surface-container-low">
                                    <button onclick="decreaseQty(<?= $item['cart_item_id'] ?>)" class="qty-btn px-3 py-1 hover:bg-surface-variant text-on-surface transition-colors" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                        <span class="material-symbols-outlined text-sm">remove</span>
                                    </button>
                                    <input class="w-12 text-center bg-transparent border-none font-label-lg text-label-lg focus:ring-0 p-1 quantity-input" type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" data-item-id="<?= $item['cart_item_id'] ?>" data-stock="<?= $item['stock_quantity'] ?>"/>
                                    <button onclick="increaseQty(<?= $item['cart_item_id'] ?>)" class="qty-btn px-3 py-1 hover:bg-surface-variant text-on-surface transition-colors" <?= $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : '' ?>>
                                        <span class="material-symbols-outlined text-sm">add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary Section -->
                <div class="lg:w-1/3">
                    <div class="bg-surface-container-low rounded-xl p-6 border border-surface-container-high sticky top-24">
                        <h2 class="font-headline-md text-headline-md font-semibold text-text-dark mb-6">Tóm tắt đơn hàng</h2>
                        <div class="flex justify-between mb-4 font-body-md">
                            <span class="text-on-surface-variant">Tạm tính:</span>
                            <span class="font-semibold text-text-dark" id="subtotal"><?= formatPrice($cartSubtotal) ?></span>
                        </div>
                        <div class="mb-4 font-body-md">
                            <span class="text-on-surface-variant mb-2 block font-semibold">Phương thức vận chuyển:</span>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer p-3 border border-outline-variant rounded-lg hover:bg-surface-variant transition-colors">
                                    <input type="radio" name="shipping_method" value="standard" class="text-axeron-red focus:ring-axeron-red w-4 h-4" checked onchange="recalculateTotals()">
                                    <div class="flex-1 flex justify-between text-sm">
                                        <span>Giao hàng tiêu chuẩn</span>
                                        <span class="font-semibold text-axeron-red" id="cart-standard-fee">
                                            <?= $cartSubtotal >= 2000000 ? 'Miễn phí' : formatPrice($baseShippingFee) ?>
                                        </span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-3 border border-outline-variant rounded-lg hover:bg-surface-variant transition-colors">
                                    <input type="radio" name="shipping_method" value="express" class="text-axeron-red focus:ring-axeron-red w-4 h-4" onchange="recalculateTotals()">
                                    <div class="flex-1 flex justify-between text-sm">
                                        <span>Giao nhanh (Express)</span>
                                        <span class="font-semibold text-axeron-red" id="cart-express-fee">
                                            <?= $cartSubtotal >= 2000000 ? 'Miễn phí' : formatPrice($baseShippingFee + 15000) ?>
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-between mb-6 font-body-md border-t border-outline-variant pt-4">
                            <span class="text-on-surface-variant">Phí vận chuyển:</span>
                            <span class="font-semibold text-text-dark" id="shipping-fee">
                                <?= $shippingFee > 0 ? formatPrice($shippingFee) : 'Miễn phí' ?>
                            </span>
                        </div>
                        <div id="freeship-notice-container" class="bg-green-50 border border-green-200 rounded-lg p-3 mb-6">
                            <p class="text-sm text-green-700" id="freeship-notice-text">
                                <?php if ($cartSubtotal < 2000000): ?>
                                    <span class="font-semibold">Mua thêm <span id="freeship-amount"><?= formatPrice(2000000 - $cartSubtotal) ?></span></span> để được freeship!
                                <?php else: ?>
                                    <span class="font-semibold">Tuyệt vời! Đơn hàng của bạn đã được freeship!</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="border-t border-outline-variant pt-4 mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-label-lg text-label-lg font-bold text-text-dark">Tổng tiền:</span>
                                <span class="font-headline-lg text-headline-md font-bold text-axeron-red" id="total-amount"><?= formatPrice($totalAmount) ?></span>
                            </div>
                            <p class="font-body-md text-xs text-on-surface-variant text-right">(Đã bao gồm VAT nếu có)</p>
                        </div>

                        <!-- Discount Code -->
                        <div class="mb-6">
                            <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 uppercase tracking-wide" for="discount-code">Mã giảm giá</label>
                            <div class="flex gap-2">
                                <input class="flex-grow bg-surface border border-outline-variant rounded-lg px-4 py-2 font-body-md focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors" id="discount-code" placeholder="Nhập mã..." type="text"/>
                                <button onclick="applyPromo()" class="bg-surface-variant text-on-surface font-label-lg text-label-lg font-semibold px-4 py-2 rounded-lg hover:bg-surface-container-high transition-colors">Áp dụng</button>
                            </div>
                            <p id="promo-message" class="text-sm mt-2 hidden"></p>
                        </div>

                        <button onclick="proceedToCheckout()" class="w-full bg-axeron-red text-white font-label-lg text-label-lg font-bold uppercase py-4 rounded-lg hover:bg-primary transition-colors flex items-center justify-center gap-2">
                            TIẾN HÀNH THANH TOÁN
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </button>

                        <div class="mt-4 text-center">
                            <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="text-axeron-blue hover:underline text-sm">
                                Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Confirm Delete Modal -->
        <div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
            <!-- Modal content -->
            <div class="relative bg-surface rounded-xl shadow-xl w-[90%] max-w-md overflow-hidden transform transition-all scale-100 p-6 flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-error-container text-error flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-3xl">delete</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface mb-2">Xóa sản phẩm</h3>
                <p class="font-body-md text-on-surface-variant text-center mb-6">Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không?</p>
                <div class="flex gap-4 w-full">
                    <button onclick="closeDeleteModal()" class="flex-1 py-3 px-4 rounded-lg border border-outline-variant text-on-surface font-label-lg text-label-lg hover:bg-surface-container transition-colors">Hủy</button>
                    <button onclick="confirmRemoveItem()" class="flex-1 py-3 px-4 rounded-lg bg-error text-on-error font-label-lg text-label-lg hover:bg-[#93000a] transition-colors shadow-sm">Xóa</button>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <!-- Load main.js TRƯỚC inline script -->
    <script src="<?= BASE_URL ?>/js/main.js?v=<?= time() ?>"></script>

    <script>
        let currentSubtotal = <?= $cartSubtotal ?>;
        let appliedPromo = null;
        const isUserLoggedIn = <?= isLoggedIn() ? 'true' : 'false' ?>;

        // Debounce utility
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Tiến hành thanh toán
        function proceedToCheckout() {
            window.location.href = BASE_URL + '/shop/checkout.php';
        }

        // Theo dõi trạng thái đang cập nhật của từng item
        const updatingItems = new Set();

        async function updateQuantityItem(cartItemId, quantity) {
            if (quantity < 1) return;

            // Disable buttons while updating
            const itemElement = document.querySelector(`[data-item-id="${cartItemId}"]`);
            if (!itemElement) return;

            // Tránh gọi nhiều lần cùng lúc
            if (updatingItems.has(cartItemId)) return;
            updatingItems.add(cartItemId);

            const btns = itemElement.querySelectorAll('.quantity-btn');
            btns.forEach(btn => btn.disabled = true);

            try {
                const result = await updateCartItem(cartItemId, quantity);

                if (result.success) {
                    const priceEl = itemElement.querySelector('.item-price');
                    const inputEl = itemElement.querySelector('.quantity-input');
                    const unitPrice = parseInt(priceEl.dataset.price);

                    inputEl.value = quantity;
                    priceEl.textContent = new Intl.NumberFormat('vi-VN').format(unitPrice * quantity) + 'đ';

                    recalculateTotals();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                console.error('Update quantity error:', error);
                showToast('Có lỗi xảy ra', 'error');
            } finally {
                // Re-enable buttons
                updatingItems.delete(cartItemId);
                btns[0].disabled = quantity <= 1;
                btns[1].disabled = quantity >= parseInt(itemElement.querySelector('.quantity-input').max);
            }
        }

        // Debounced version for input change
        const debouncedUpdateQuantity = debounce((cartItemId, quantity) => {
            updateQuantityItem(cartItemId, quantity);
        }, 300);

        // Tăng số lượng
        function increaseQty(cartItemId) {
            const itemElement = document.querySelector(`[data-item-id="${cartItemId}"]`);
            if (!itemElement || updatingItems.has(cartItemId)) return;

            const input = itemElement.querySelector('.quantity-input');
            const currentQty = parseInt(input.value) || 1;
            const maxQty = parseInt(input.max) || 999;

            if (currentQty < maxQty) {
                debouncedUpdateQuantity(cartItemId, currentQty + 1);
            }
        }

        // Giảm số lượng
        function decreaseQty(cartItemId) {
            const itemElement = document.querySelector(`[data-item-id="${cartItemId}"]`);
            if (!itemElement || updatingItems.has(cartItemId)) return;

            const input = itemElement.querySelector('.quantity-input');
            const currentQty = parseInt(input.value) || 1;

            if (currentQty > 1) {
                debouncedUpdateQuantity(cartItemId, currentQty - 1);
            } else if (currentQty === 1) {
                removeItem(cartItemId);
            }
        }

        let itemToDelete = null;

        function closeDeleteModal() {
            document.getElementById('delete-confirm-modal').classList.add('hidden');
            itemToDelete = null;
        }

        function removeItem(cartItemId) {
            itemToDelete = cartItemId;
            document.getElementById('delete-confirm-modal').classList.remove('hidden');
        }

        async function confirmRemoveItem() {
            if (!itemToDelete) return;
            const cartItemId = itemToDelete;
            closeDeleteModal();

            const result = await removeFromCart(cartItemId);

            if (result) {
                const itemElement = document.querySelector(`[data-item-id="${cartItemId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }

                const remainingItems = document.querySelectorAll('.cart-item');
                if (remainingItems.length === 0) {
                    location.reload();
                }

                recalculateTotals();
            }
        }

        function recalculateTotals() {
            const items = document.querySelectorAll('.cart-item');
            let subtotal = 0;

            items.forEach(item => {
                const priceEl = item.querySelector('.item-price');
                const qtyInput = item.querySelector('.quantity-input');
                const unitPrice = parseInt(priceEl.dataset.price);
                const qty = parseInt(qtyInput.value) || 0;
                subtotal += unitPrice * qty;
            });

            currentSubtotal = subtotal;
            document.getElementById('subtotal').textContent = new Intl.NumberFormat('vi-VN').format(subtotal) + 'đ';

            const methodEl = document.querySelector('input[name="shipping_method"]:checked');
            const method = methodEl ? methodEl.value : 'standard';
            const baseShippingFee = <?= $baseShippingFee ?>;
            let finalShippingFee = method === 'express' ? baseShippingFee + 15000 : baseShippingFee;

            if (subtotal >= 2000000) {
                finalShippingFee = 0;
                document.getElementById('cart-standard-fee').textContent = 'Miễn phí';
                document.getElementById('cart-standard-fee').classList.remove('text-axeron-red');
                document.getElementById('cart-standard-fee').classList.add('text-green-600');
                
                document.getElementById('cart-express-fee').textContent = 'Miễn phí';
                document.getElementById('cart-express-fee').classList.remove('text-axeron-red');
                document.getElementById('cart-express-fee').classList.add('text-green-600');
            } else {
                document.getElementById('cart-standard-fee').textContent = new Intl.NumberFormat('vi-VN').format(baseShippingFee) + 'đ';
                document.getElementById('cart-standard-fee').classList.add('text-axeron-red');
                document.getElementById('cart-standard-fee').classList.remove('text-green-600');
                
                document.getElementById('cart-express-fee').textContent = new Intl.NumberFormat('vi-VN').format(baseShippingFee + 15000) + 'đ';
                document.getElementById('cart-express-fee').classList.add('text-axeron-red');
                document.getElementById('cart-express-fee').classList.remove('text-green-600');
            }

            document.getElementById('shipping-fee').textContent = finalShippingFee > 0 ? new Intl.NumberFormat('vi-VN').format(finalShippingFee) + 'đ' : 'Miễn Phí';

            // Cập nhật thông báo freeship
            const freeshipText = document.getElementById('freeship-notice-text');
            if (freeshipText) {
                if (subtotal < 2000000) {
                    const remaining = 2000000 - subtotal;
                    freeshipText.innerHTML = `<span class="font-semibold">Mua thêm <span id="freeship-amount">${new Intl.NumberFormat('vi-VN').format(remaining)}đ</span></span> để được freeship!`;
                } else {
                    freeshipText.innerHTML = `<span class="font-semibold">Tuyệt vời! Đơn hàng của bạn đã được freeship!</span>`;
                }
            }

            let finalTotal = subtotal + finalShippingFee;
            if (appliedPromo) {
                let actualDiscount = 0;
                if (appliedPromo.discount_type === 'percent') {
                    actualDiscount = subtotal * (appliedPromo.discount_value / 100);
                    if (appliedPromo.max_discount) {
                        actualDiscount = Math.min(actualDiscount, parseInt(appliedPromo.max_discount));
                    }
                } else {
                    actualDiscount = parseInt(appliedPromo.discount_value);
                }
                actualDiscount = Math.min(actualDiscount, subtotal + finalShippingFee);
                finalTotal -= actualDiscount;
            }

            document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(finalTotal) + 'đ';

            let totalQty = 0;
            items.forEach(item => {
                totalQty += parseInt(item.querySelector('.quantity-input').value) || 0;
            });
            updateCartBadge(totalQty);
        }

        async function applyPromo() {
            const code = document.getElementById('discount-code').value.trim();
            if (!code) {
                showToast('Vui lòng nhập mã giảm giá', 'error');
                return;
            }

            try {
                const response = await fetch(BASE_URL + '/api/cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'apply_promo', code: code })
                });

                const data = await response.json();

                if (data.success) {
                    appliedPromo = data.data.promo;
                    document.getElementById('promo-message').textContent = 'Áp dụng thành công: ' + appliedPromo.promo_name;
                    document.getElementById('promo-message').className = 'text-sm mt-2 text-green-600';
                    document.getElementById('promo-message').classList.remove('hidden');
                    showToast('Áp dụng mã giảm giá thành công!', 'success');
                    recalculateTotals();
                } else {
                    document.getElementById('promo-message').textContent = data.message;
                    document.getElementById('promo-message').className = 'text-sm mt-2 text-red-600';
                    document.getElementById('promo-message').classList.remove('hidden');
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                showToast('Có lỗi xảy ra', 'error');
            }
        }

        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const cartItemId = parseInt(this.dataset.itemId);
                let quantity = parseInt(this.value) || 0;
                
                if (quantity <= 0) {
                    this.value = 1; // tạm thời set về 1
                    removeItem(cartItemId);
                    return;
                }
                
                quantity = Math.min(quantity, parseInt(this.max) || 999);
                this.value = quantity;
                debouncedUpdateQuantity(cartItemId, quantity);
            });
        });
    </script>
</body>
</html>
