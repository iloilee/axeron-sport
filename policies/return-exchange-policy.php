<?php
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Chính sách đổi và trả hàng - Axeron Sport';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .bento-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 1) 0%, rgba(246, 243, 242, 0.6) 100%);
        border: 1px solid #e3bebb;
        transition: all 0.3s ease;
    }

    .bento-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        border-color: #be1e2d;
    }
</style>

<main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-20">
    <!-- Header Section -->
    <div class="mb-12 md:mb-16 text-center max-w-3xl mx-auto">
        <h1 class="font-display-lg text-display-lg md:text-display-lg text-on-surface mb-4">Chính Sách Đổi Và Trả Hàng</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant">
            Tại Axeron Sport, chúng tôi cam kết mang đến những sản phẩm chất lượng cao nhất. Nếu bạn không hài lòng,
            chúng tôi hỗ trợ <strong>hoàn tiền 100% hoặc đổi mới</strong> trong vòng <strong>7 ngày</strong> (5 ngày
            đối với máy tập) cho các trường hợp lỗi từ nhà sản xuất, không đúng mô tả hoặc hư hỏng do vận chuyển.
        </p>
        <div class="mt-6 inline-flex items-center gap-2 bg-error-container text-on-error-container px-4 py-2 rounded-lg font-label-sm text-label-sm">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">warning</span>
            <span>Lưu ý: Phản hồi lỗi vận chuyển phải được thực hiện trong vòng 24 giờ kể từ khi nhận hàng.</span>
        </div>
    </div>
    <!-- Bento Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
        <!-- Điều kiện đổi trả (Conditions) - Spans 8 cols -->
        <section class="md:col-span-8 bento-card rounded-xl p-8 flex flex-col gap-6">
            <div class="flex items-center gap-4 border-b border-outline-variant pb-4">
                <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">fact_check</span>
                </div>
                <h2 class="font-headline-md text-headline-md text-on-surface">1. Điều kiện được đổi và trả hàng</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-2">
                <div class="flex gap-4 items-start">
                    <span class="material-symbols-outlined text-axeron-red mt-1">inventory_2</span>
                    <div>
                        <h3 class="font-label-lg text-label-lg mb-1">Tình trạng sản phẩm</h3>
                        <p class="text-on-surface-variant text-sm">Sản phẩm phải còn nguyên bao bì đóng gói, nhãn
                            mác nguyên vẹn như lúc ban đầu.</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <span class="material-symbols-outlined text-axeron-red mt-1">receipt_long</span>
                    <div>
                        <h3 class="font-label-lg text-label-lg mb-1">Chứng từ đi kèm</h3>
                        <p class="text-on-surface-variant text-sm">Bắt buộc phải có hóa đơn mua hàng hoặc phiếu hoàn
                            trả đi kèm kiện hàng.</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <span class="material-symbols-outlined text-axeron-red mt-1">build</span>
                    <div>
                        <h3 class="font-label-lg text-label-lg mb-1">Phạm vi bảo hành</h3>
                        <p class="text-on-surface-variant text-sm">Sản phẩm gặp lỗi kỹ thuật nằm trong phạm vi được
                            bảo hành của nhà sản xuất.</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <span class="material-symbols-outlined text-axeron-red mt-1">local_shipping</span>
                    <div>
                        <h3 class="font-label-lg text-label-lg mb-1">Lỗi vận chuyển</h3>
                        <p class="text-on-surface-variant text-sm">Cung cấp biên bản xác nhận hư hỏng từ đơn vị vận
                            chuyển ngay khi nhận.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Chi phí vận chuyển (Shipping Costs) - Spans 4 cols -->
        <section class="md:col-span-4 bento-card rounded-xl p-8 bg-surface-container-low flex flex-col gap-6">
            <div class="flex items-center gap-4 border-b border-outline-variant pb-4">
                <div class="w-12 h-12 rounded-full bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">payments</span>
                </div>
                <h2 class="font-headline-md text-headline-md text-on-surface">3. Chi phí</h2>
            </div>
            <ul class="space-y-4">
                <li class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-secondary text-xl">arrow_right</span>
                    <span class="text-on-surface-variant text-sm"><strong>Trả hàng thông thường:</strong> Khách hàng
                        chịu trách nhiệm thanh toán phí vận chuyển chiều về.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-secondary text-xl">arrow_right</span>
                    <span class="text-on-surface-variant text-sm"><strong>Từ chối nhận hàng:</strong> Khách hàng sẽ
                        phải thanh toán chi phí vận chuyển cả 2 chiều nếu từ chối nhận hàng mà không có lý do chính
                        đáng.</span>
                </li>
            </ul>
        </section>
        <!-- Quy trình thực hiện (Process) - Full width -->
        <section class="md:col-span-12 bento-card rounded-xl p-8 mt-4 relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-5 pointer-events-none">
                <span class="material-symbols-outlined" style="font-size: 200px;">autorenew</span>
            </div>
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-full bg-tertiary-fixed flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">support_agent</span>
                </div>
                <h2 class="font-headline-md text-headline-md text-on-surface">2. Quy trình thực hiện</h2>
            </div>
            <div class="flex flex-col md:flex-row gap-8 relative z-10">
                <div class="flex-1 bg-white p-6 rounded-lg border border-surface-variant shadow-sm relative">
                    <div class="absolute -top-3 -left-3 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
                        1</div>
                    <h3 class="font-label-lg text-label-lg mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">mail</span>
                        Gửi Email Yêu Cầu
                    </h3>
                    <p class="text-on-surface-variant text-sm mb-2">Liên hệ với bộ phận CSKH qua địa chỉ email chính
                        thức:</p>
                    <a class="text-primary font-bold hover:underline inline-flex items-center gap-1"
                        href="mailto:contact@axeron.vn">
                        contact@axeron.vn
                    </a>
                </div>
                <div class="flex-1 bg-white p-6 rounded-lg border border-surface-variant shadow-sm relative">
                    <div class="absolute -top-3 -left-3 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
                        2</div>
                    <h3 class="font-label-lg text-label-lg mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">description</span>
                        Cung Cấp Thông Tin
                    </h3>
                    <p class="text-on-surface-variant text-sm">Vui lòng cung cấp mô tả chi tiết về lỗi, đính kèm
                        hình ảnh/video rõ nét chụp cận cảnh tình trạng hư hỏng của sản phẩm.</p>
                </div>
                <div class="flex-1 bg-white p-6 rounded-lg border border-surface-variant shadow-sm relative">
                    <div class="absolute -top-3 -left-3 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
                        3</div>
                    <h3 class="font-label-lg text-label-lg mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">verified_user</span>
                        Xác Nhận &amp; Xử Lý
                    </h3>
                    <p class="text-on-surface-variant text-sm">Nếu do lỗi vận chuyển, cung cấp thêm bản scan biên
                        bản báo cáo của đơn vị giao hàng. Chúng tôi sẽ phản hồi trong 24h.</p>
                </div>
            </div>
        </section>
        <!-- Các trường hợp không chấp nhận (Exceptions) - Full width -->
        <section class="md:col-span-12 mt-4 flex flex-col gap-6">
            <h2 class="font-headline-md text-headline-md text-error flex items-center gap-3">
                <span class="material-symbols-outlined">block</span>
                4. Các trường hợp không chấp nhận đổi trả
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-surface p-6 rounded-lg border-l-4 border-error shadow-sm">
                    <h3 class="font-label-lg text-label-lg mb-2">Thay đổi mẫu mã</h3>
                    <p class="text-on-surface-variant text-sm">Khách hàng muốn thay đổi chủng loại, mẫu mã sản phẩm
                        mà không thông báo trước cho bộ phận bán hàng.</p>
                </div>
                <div class="bg-surface p-6 rounded-lg border-l-4 border-error shadow-sm">
                    <h3 class="font-label-lg text-label-lg mb-2">Lỗi do người sử dụng</h3>
                    <p class="text-on-surface-variant text-sm">Sản phẩm bị hỏng hóc, rách bao bì, trầy xước, vỡ...
                        do khách hàng vận hành sai hướng dẫn hoặc bảo quản không đúng cách.</p>
                </div>
                <div class="bg-surface p-6 rounded-lg border-l-4 border-error shadow-sm">
                    <h3 class="font-label-lg text-label-lg mb-2">Vi phạm quy định</h3>
                    <p class="text-on-surface-variant text-sm">Khách hàng không thực hiện đúng các quy định yêu cầu
                        để được hưởng chế độ bảo hành (ví dụ: mất hóa đơn, tem mác).</p>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
