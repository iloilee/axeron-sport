<?php
/**
 * Order Tracking Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();
$error = '';
$orderCode = '';
$phone = '';

// Nếu user đã đăng nhập, và không submit form, có thể gợi ý sang trang Lịch sử
if (isLoggedIn()) {
    $isLoggedInUser = true;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $currentUser = $db->selectOne("SELECT phone FROM users WHERE user_id = ?", [getUserId()]);
        if ($currentUser && !empty($currentUser['phone'])) {
            $phone = $currentUser['phone'];
        }
    }
} else {
    $isLoggedInUser = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderCode = sanitize($_POST['order_code'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($orderCode) || empty($phone)) {
        $error = 'Vui lòng nhập đầy đủ Mã đơn hàng và Số điện thoại.';
    } else {
        $order = $db->selectOne("SELECT order_id, guest_token FROM orders WHERE order_code = ? AND recipient_phone = ?", [$orderCode, $phone]);
        
        if ($order) {
            redirect(BASE_URL . '/shop/order-confirmation.php?id=' . $order['order_id'] . '&token=' . $order['guest_token']);
        } else {
            $error = 'Không tìm thấy đơn hàng phù hợp. Vui lòng kiểm tra lại thông tin.';
        }
    }
}
?>
<?php 
$pageTitle = 'Tra cứu đơn hàng - Axeron Sport'; 
require_once __DIR__ . '/../includes/head.php'; 
?>
<div class="flex flex-col min-h-screen w-full bg-surface">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-[1400px] mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <?php if ($isLoggedInUser): ?>
        <div class="border-b border-outline-variant mb-8 w-full">
            <nav class="-mb-px flex space-x-6 md:space-x-8 overflow-x-auto no-scrollbar w-full" aria-label="Tabs">
                <a href="<?= BASE_URL ?>/shop/order-history.php" class="whitespace-nowrap border-b-[3px] py-4 px-1 text-lg md:text-xl font-bold uppercase tracking-wide border-transparent text-on-surface-variant hover:border-outline-variant hover:text-on-surface transition-colors">
                    Đơn Hàng
                </a>
                <a href="<?= BASE_URL ?>/shop/my-reviews.php" class="whitespace-nowrap border-b-[3px] py-4 px-1 text-lg md:text-xl font-bold uppercase tracking-wide border-transparent text-on-surface-variant hover:border-outline-variant hover:text-on-surface transition-colors">
                    Đánh Giá
                </a>
                <a href="<?= BASE_URL ?>/shop/order-tracking.php" class="whitespace-nowrap border-b-[3px] py-4 px-1 text-lg md:text-xl font-bold uppercase tracking-wide border-axeron-red text-axeron-red transition-colors">
                    Tra Cứu Đơn Hàng
                </a>
            </nav>
        </div>
        <?php endif; ?>

        <div class="flex justify-center w-full">
            <div class="max-w-md w-full space-y-8 bg-surface-container-lowest p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-surface-container <?= $isLoggedInUser ? 'mt-8' : 'mt-12' ?>">
                <div class="text-center">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-4xl text-axeron-red">manage_search</span>
                </div>
                <h2 class="mt-2 text-center text-3xl font-extrabold text-on-surface font-headline-lg uppercase">Tra cứu đơn hàng</h2>
                <p class="mt-2 text-center text-sm text-on-surface-variant">
                    Kiểm tra trạng thái đơn hàng của bạn bằng mã đơn hàng và số điện thoại.
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-axeron-red p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <span class="material-symbols-outlined text-axeron-red">error</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-axeron-red font-medium">
                                <?= htmlspecialchars($error) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="" method="POST">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="order_code" class="block text-sm font-medium text-gray-700 mb-1">Mã đơn hàng</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-gray-400 text-lg">receipt</span>
                            </div>
                            <input id="order_code" name="order_code" type="text" required value="<?= htmlspecialchars($orderCode) ?>"
                                class="appearance-none rounded-lg relative block w-full pl-10 px-3 py-3 border border-outline-variant placeholder-gray-400 text-on-surface focus:outline-none focus:ring-axeron-red focus:border-axeron-red focus:z-10 sm:text-sm" placeholder="VD: ORD-000123">
                        </div>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại đặt hàng</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-gray-400 text-lg">phone</span>
                            </div>
                            <input id="phone" name="phone" type="text" required value="<?= htmlspecialchars($phone) ?>"
                                class="appearance-none rounded-lg relative block w-full pl-10 px-3 py-3 border border-outline-variant placeholder-gray-400 text-on-surface focus:outline-none focus:ring-axeron-red focus:border-axeron-red focus:z-10 sm:text-sm" placeholder="Số điện thoại của bạn">
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-axeron-red hover:bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-axeron-red transition-colors">
                        Tra Cứu Ngay
                    </button>
                </div>
            </form>
            
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
</body>
</html>
