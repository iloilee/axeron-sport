<?php
/**
 * Bottom Navigation Bar cho Mobile
 */
?>
<div class="md:hidden fixed bottom-0 left-0 w-full bg-surface-container-lowest dark:bg-black border-t border-outline-variant z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] pb-safe-area">
    <div class="flex justify-around items-center h-16">
        <a href="<?= BASE_URL ?>/" class="flex flex-col items-center justify-center w-full h-full text-on-surface-variant hover:text-axeron-red transition-colors <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'text-axeron-red' : '' ?>">
            <span class="material-symbols-outlined text-[24px]">home</span>
            <span class="font-label-sm text-[10px] mt-1">Trang chủ</span>
        </a>
        
        <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="flex flex-col items-center justify-center w-full h-full text-on-surface-variant hover:text-axeron-red transition-colors <?= (basename($_SERVER['PHP_SELF']) == 'product-catalog.php') ? 'text-axeron-red' : '' ?>">
            <span class="material-symbols-outlined text-[24px]">category</span>
            <span class="font-label-sm text-[10px] mt-1">Danh mục</span>
        </a>
        
        <a href="<?= BASE_URL ?>/shop/cart.php" class="flex flex-col items-center justify-center w-full h-full text-on-surface-variant hover:text-axeron-red transition-colors relative <?= (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'text-axeron-red' : '' ?>">
            <div class="relative">
                <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
                <span class="absolute -top-1 -right-2 bg-axeron-red text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full" id="mobile-nav-cart-count">
                    <?= isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : '0' ?>
                </span>
            </div>
            <span class="font-label-sm text-[10px] mt-1">Giỏ hàng</span>
        </a>
        
        <a href="<?= BASE_URL ?>/auth/account.php" class="flex flex-col items-center justify-center w-full h-full text-on-surface-variant hover:text-axeron-red transition-colors <?= (basename($_SERVER['PHP_SELF']) == 'account.php' || basename($_SERVER['PHP_SELF']) == 'login.php') ? 'text-axeron-red' : '' ?>">
            <span class="material-symbols-outlined text-[24px]">person</span>
            <span class="font-label-sm text-[10px] mt-1">Tài khoản</span>
        </a>
    </div>
</div>

<style>
/* CSS padding-bottom to avoid content hiding behind mobile nav on mobile devices */
@media (max-width: 767px) {
    body {
        padding-bottom: calc(4rem + env(safe-area-inset-bottom));
    }
    .pb-safe-area {
        padding-bottom: env(safe-area-inset-bottom);
    }
    /* Hide floating support buttons on mobile or adjust them */
    .fixed.right-4.bottom-24 {
        bottom: calc(5.5rem + env(safe-area-inset-bottom)) !important;
    }
    #ai-chatbox {
        bottom: calc(5.5rem + env(safe-area-inset-bottom)) !important;
    }
}
</style>
