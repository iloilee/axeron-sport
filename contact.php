<?php
require_once __DIR__ . '/config/session.php';
$pageTitle = 'Liên hệ - Axeron Sport';

$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic mock handling of the form
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // In a real application, we would save to DB or send an email here.
    if (!empty($fullname) && !empty($phone) && !empty($subject) && !empty($message)) {
        $successMessage = "Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi lại trong thời gian sớm nhất.";
    }
}

require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 md:py-12">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="mb-8 flex text-surface-variant text-label-sm items-center gap-2">
            <a class="hover:text-axeron-red transition-colors" href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>">Trang chủ</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-on-surface font-semibold">Liên hệ</span>
        </nav>
        <div class="text-center mb-12">
            <h1 class="font-display-lg text-display-lg md:text-[56px] text-on-background mb-4 uppercase tracking-tight">
                Liên hệ với <span class="text-axeron-red">chúng tôi</span></h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">Chúng tôi luôn sẵn sàng lắng
                nghe và hỗ trợ bạn. Vui lòng điền thông tin vào biểu mẫu bên dưới hoặc liên hệ trực tiếp qua các kênh
                thông tin.</p>
        </div>
        
        <?php if (!empty($successMessage)): ?>
        <div class="mb-8 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl flex items-center gap-3 shadow-sm max-w-3xl mx-auto">
            <span class="material-symbols-outlined text-2xl">check_circle</span>
            <p class="font-medium"><?= htmlspecialchars($successMessage) ?></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-gutter">
            <!-- Contact Info Panel (Bento Style) -->
            <div class="lg:col-span-5 flex flex-col gap-6">
                <!-- Info Card 1 -->
                <div class="bg-surface-container rounded-xl p-8 border border-surface-variant hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-start gap-4">
                        <div class="bg-primary-container text-on-primary-container p-3 rounded-full flex-shrink-0">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">location_on</span>
                        </div>
                        <div>
                            <h3 class="font-headline-md text-headline-md mb-2">Trụ sở chính</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant mb-2">123 Nguyễn Trãi, Quận 1, TP.HCM</p>
                            <a href="https://maps.google.com/?q=123+Nguyễn+Trãi,+Quận+1,+TP.HCM" target="_blank" class="text-axeron-blue hover:text-axeron-red transition-colors inline-flex items-center gap-1 font-medium text-sm">
                                <span class="material-symbols-outlined text-[16px]">directions</span>
                                Chỉ đường
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Info Card 2 & 3 (Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-surface-container rounded-xl p-6 border border-surface-variant hover:shadow-md transition-shadow duration-300">
                        <div class="bg-surface-variant text-on-surface p-2 rounded-full inline-flex mb-4">
                            <span class="material-symbols-outlined">call</span>
                        </div>
                        <h3 class="font-headline-md text-[20px] font-semibold mb-1">Hotline</h3>
                        <p class="font-body-md text-body-md font-bold">
                            <a href="tel:19001234" class="text-axeron-red hover:underline">1900 1234</a>
                        </p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Thứ 2 - CN: 8h00 - 22h00</p>
                    </div>
                    <div class="bg-surface-container rounded-xl p-6 border border-surface-variant hover:shadow-md transition-shadow duration-300">
                        <div class="bg-surface-variant text-on-surface p-2 rounded-full inline-flex mb-4">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <h3 class="font-headline-md text-[20px] font-semibold mb-1">Email Hỗ trợ</h3>
                        <p class="font-body-md text-body-md font-bold text-on-surface hover:text-axeron-red transition-colors">
                            <a href="mailto:support@axeron.vn">support@axeron.vn</a>
                        </p>
                        <p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Phản hồi trong 24h</p>
                    </div>
                </div>
                <!-- Socials -->
                <div class="bg-inverse-surface rounded-xl p-8 text-white mt-auto">
                    <h3 class="font-headline-md text-[20px] mb-4">Kết nối với Axeron</h3>
                    <div class="flex gap-4">
                        <a href="https://axeron.vn" target="_blank" aria-label="Website"
                            class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-axeron-red transition-colors">
                            <span class="material-symbols-outlined">language</span>
                        </a>
                        <a href="https://zalo.me/19001234" target="_blank" aria-label="Zalo"
                            class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-axeron-blue transition-colors">
                            <span class="material-symbols-outlined">forum</span>
                        </a>
                        <a href="https://youtube.com/@axeron" target="_blank" aria-label="YouTube"
                            class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-red-600 transition-colors">
                            <span class="material-symbols-outlined">smart_display</span>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Contact Form -->
            <div class="lg:col-span-7 bg-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.04)] border border-surface-variant p-8 md:p-10">
                <h2 class="font-headline-lg text-headline-lg mb-6 border-b border-surface-variant pb-4">Gửi tin nhắn cho chúng tôi</h2>
                <form action="contact.php" class="space-y-6" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="font-label-lg text-label-lg text-on-surface block" for="fullname">Họ và tên *</label>
                            <input class="w-full border border-surface-dim rounded-lg px-4 py-3 bg-surface focus:bg-white focus:outline-none focus:ring-2 focus:ring-axeron-blue focus:border-transparent transition-all font-body-md"
                                id="fullname" name="fullname" placeholder="Nhập họ và tên của bạn" required="" type="text" />
                        </div>
                        <div class="space-y-2">
                            <label class="font-label-lg text-label-lg text-on-surface block" for="phone">Số điện thoại *</label>
                            <input class="w-full border border-surface-dim rounded-lg px-4 py-3 bg-surface focus:bg-white focus:outline-none focus:ring-2 focus:ring-axeron-blue focus:border-transparent transition-all font-body-md"
                                id="phone" name="phone" placeholder="Nhập số điện thoại" required="" type="tel" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-lg text-label-lg text-on-surface block" for="email">Email</label>
                        <input class="w-full border border-surface-dim rounded-lg px-4 py-3 bg-surface focus:bg-white focus:outline-none focus:ring-2 focus:ring-axeron-blue focus:border-transparent transition-all font-body-md"
                            id="email" name="email" placeholder="Nhập địa chỉ email (không bắt buộc)" type="email" />
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-lg text-label-lg text-on-surface block" for="subject">Chủ đề *</label>
                        <select class="w-full border border-surface-dim rounded-lg px-4 py-3 bg-surface focus:bg-white focus:outline-none focus:ring-2 focus:ring-axeron-blue focus:border-transparent transition-all font-body-md text-on-surface"
                            id="subject" name="subject" required="">
                            <option value="">Chọn chủ đề cần hỗ trợ</option>
                            <option value="tuvan">Tư vấn sản phẩm</option>
                            <option value="donhang">Thông tin đơn hàng</option>
                            <option value="baohanh">Bảo hành &amp; Đổi trả</option>
                            <option value="khac">Vấn đề khác</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-lg text-label-lg text-on-surface block" for="message">Nội dung *</label>
                        <textarea class="w-full border border-surface-dim rounded-lg px-4 py-3 bg-surface focus:bg-white focus:outline-none focus:ring-2 focus:ring-axeron-blue focus:border-transparent transition-all font-body-md resize-y"
                            id="message" name="message" placeholder="Nhập nội dung chi tiết..." required="" rows="5"></textarea>
                    </div>
                    <button class="w-full bg-axeron-red text-white font-label-lg text-label-lg uppercase tracking-wider py-4 rounded-lg hover:bg-[#a01925] transition-colors duration-300 flex items-center justify-center gap-2 mt-4"
                        type="submit">
                        Gửi yêu cầu
                        <span class="material-symbols-outlined text-[20px]">send</span>
                    </button>
                </form>
            </div>
        </div>
        <!-- Google Maps Full Width Area -->
        <div class="mt-16 w-full rounded-xl overflow-hidden border border-surface-variant bg-surface-container h-[400px] relative shadow-sm">
            <iframe width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=123%20Nguy%E1%BB%85n%20Tr%C3%A3i,%20Qu%E1%BA%ADn%201,%20TP.HCM&t=&z=15&ie=UTF8&iwloc=&output=embed"></iframe>
            <!-- Map Overlay UI -->
            <div class="absolute top-4 left-4 bg-white p-4 rounded-lg shadow-md border border-surface-variant z-10 hidden md:block max-w-sm pointer-events-auto">
                <h4 class="font-headline-md text-[18px] mb-1">Axeron Sport HQ</h4>
                <a href="https://maps.google.com/?q=123+Nguyễn+Trãi,+Quận+1,+TP.HCM" target="_blank" class="font-label-sm text-label-sm text-axeron-blue hover:text-axeron-red transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">directions</span> Xem đường đi trên Google Maps
                </a>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
