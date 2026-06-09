<?php
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Chính sách bảo hành - Axeron Sport';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header (Suppressed Breadcrumbs for Policy Page focus) -->
    <section
        class="bg-surface-gray py-12 px-margin-mobile md:px-margin-desktop text-center border-b border-surface-variant">
        <div class="max-w-container-max mx-auto">
            <h1 class="font-display-lg text-display-lg md:text-display-lg text-on-surface mb-4">Chính Sách Bảo Hành</h1>
            <p class="font-body-lg text-on-surface-variant max-w-2xl mx-auto">Axeron Sport cam kết cung cấp các sản phẩm
                thể thao chất lượng cao. Dưới đây là quy định chi tiết về chính sách bảo hành để đảm bảo quyền lợi tốt
                nhất cho khách hàng.</p>
        </div>
    </section>
    <!-- Main Content Canvas -->
    <main class="py-16 px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
        <!-- Bento Grid Layout for Warranty Info -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter mb-16">
            <!-- Left Column: Scope & Duration -->
            <div class="md:col-span-8 flex flex-col gap-gutter">
                <!-- Duration Card -->
                <div
                    class="bg-white p-8 rounded-xl border border-surface-variant shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-primary-container rounded-full flex items-center justify-center mr-4">
                            <span class="material-symbols-outlined text-on-primary-container icon-fill">schedule</span>
                        </div>
                        <h2 class="font-headline-md text-text-dark">Thời Hạn Bảo Hành</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-surface-variant">
                            <span class="font-label-lg text-on-surface">Giày Thể Thao Cao Cấp</span>
                            <span class="font-label-lg text-axeron-red bg-error-container px-3 py-1 rounded-full">06
                                Tháng</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-surface-variant">
                            <span class="font-label-lg text-on-surface">Quần Áo &amp; Phụ Kiện</span>
                            <span class="font-label-lg text-axeron-red bg-error-container px-3 py-1 rounded-full">01
                                Tháng</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="font-label-lg text-on-surface">Dụng Cụ Tập Luyện Tạ/Máy</span>
                            <span class="font-label-lg text-axeron-red bg-error-container px-3 py-1 rounded-full">12
                                Tháng</span>
                        </div>
                        <p class="font-body-md text-on-surface-variant mt-4 italic text-sm">* Thời hạn tính từ ngày mua
                            hàng in trên hóa đơn hoặc kích hoạt bảo hành điện tử.</p>
                    </div>
                </div>
                <!-- Conditions Card -->
                <div
                    class="bg-white p-8 rounded-xl border border-surface-variant shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-axeron-blue rounded-full flex items-center justify-center mr-4">
                            <span class="material-symbols-outlined text-white icon-fill">verified_user</span>
                        </div>
                        <h2 class="font-headline-md text-text-dark">Điều Kiện Áp Dụng</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-surface-gray p-6 rounded-lg">
                            <h3 class="font-label-lg text-text-dark mb-3 flex items-center">
                                <span class="material-symbols-outlined text-axeron-red mr-2 text-lg">check_circle</span>
                                Được Bảo Hành
                            </h3>
                            <ul class="font-body-md text-on-surface-variant space-y-2 list-disc pl-5">
                                <li>Lỗi kỹ thuật từ nhà sản xuất (bong keo, đứt chỉ, lỗi chất liệu).</li>
                                <li>Sản phẩm còn trong thời hạn bảo hành.</li>
                                <li>Có hóa đơn mua hàng hợp lệ hoặc thông tin trên hệ thống.</li>
                                <li>Sản phẩm chưa qua sửa chữa tại nơi khác.</li>
                            </ul>
                        </div>
                        <div class="bg-surface-gray p-6 rounded-lg">
                            <h3 class="font-label-lg text-text-dark mb-3 flex items-center">
                                <span class="material-symbols-outlined text-outline mr-2 text-lg">cancel</span>
                                Từ Chối Bảo Hành
                            </h3>
                            <ul class="font-body-md text-on-surface-variant space-y-2 list-disc pl-5">
                                <li>Hư hỏng do sử dụng sai mục đích, sai hướng dẫn.</li>
                                <li>Hao mòn tự nhiên (mòn đế, phai màu).</li>
                                <li>Tác động cơ học, hóa chất, hoặc thiên tai.</li>
                                <li>Hàng khuyến mãi, thanh lý (Clearance sale).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Column: Process & Support (Asymmetric emphasis) -->
            <div class="md:col-span-4 flex flex-col gap-gutter">
                <!-- Process Card -->
                <div class="bg-inverse-surface text-white p-8 rounded-xl shadow-md h-full relative overflow-hidden">
                    <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
                        <span class="material-symbols-outlined text-[150px] icon-fill">sync</span>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center mb-6">
                            <span
                                class="material-symbols-outlined text-axeron-red text-[32px] mr-3">support_agent</span>
                            <h2 class="font-headline-md text-white">Quy Trình Xử Lý</h2>
                        </div>
                        <ol class="space-y-6 relative border-l-2 border-surface-variant/30 pl-6 ml-3">
                            <li class="relative">
                                <div
                                    class="absolute -left-[35px] top-0 w-6 h-6 bg-axeron-red rounded-full flex items-center justify-center font-label-sm text-white">
                                    1</div>
                                <h3 class="font-label-lg text-white mb-1">Tiếp Nhận Thông Tin</h3>
                                <p class="font-body-md text-surface-variant text-sm">Gửi sản phẩm đến cửa hàng gần nhất
                                    hoặc liên hệ Hotline để được hướng dẫn.</p>
                            </li>
                            <li class="relative">
                                <div
                                    class="absolute -left-[35px] top-0 w-6 h-6 bg-axeron-red rounded-full flex items-center justify-center font-label-sm text-white">
                                    2</div>
                                <h3 class="font-label-lg text-white mb-1">Kiểm Tra &amp; Thẩm Định</h3>
                                <p class="font-body-md text-surface-variant text-sm">Kỹ thuật viên kiểm tra lỗi (tối đa
                                    48h làm việc).</p>
                            </li>
                            <li class="relative">
                                <div
                                    class="absolute -left-[35px] top-0 w-6 h-6 bg-axeron-red rounded-full flex items-center justify-center font-label-sm text-white">
                                    3</div>
                                <h3 class="font-label-lg text-white mb-1">Xử Lý Bảo Hành</h3>
                                <p class="font-body-md text-surface-variant text-sm">Sửa chữa, thay thế hoặc đổi mới tùy
                                    tình trạng lỗi (3-7 ngày).</p>
                            </li>
                            <li class="relative">
                                <div
                                    class="absolute -left-[35px] top-0 w-6 h-6 bg-axeron-red rounded-full flex items-center justify-center font-label-sm text-white">
                                    4</div>
                                <h3 class="font-label-lg text-white mb-1">Bàn Giao</h3>
                                <p class="font-body-md text-surface-variant text-sm">Trả sản phẩm tại cửa hàng hoặc gửi
                                    chuyển phát nhanh về tận nơi.</p>
                            </li>
                        </ol>
                        <div class="mt-8 pt-6 border-t border-surface-variant/30">
                            <p class="font-label-sm text-surface-variant mb-2 uppercase tracking-wider">Cần Hỗ Trợ Ngay?
                            </p>
                            <a class="inline-flex items-center justify-center w-full bg-white text-text-dark font-label-lg py-3 px-4 rounded-lg hover:bg-surface-gray transition-colors"
                                href="tel:19001000">
                                <span class="material-symbols-outlined mr-2">call</span>
                                Gọi Hotline 1900 1000
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Maintenance Section -->
        <div class="bg-surface-gray rounded-xl p-8 md:p-12 border border-surface-variant">
            <div class="text-center mb-8">
                <h2 class="font-headline-lg text-text-dark mb-4">Chính Sách Bảo Trì Trọn Đời</h2>
                <p class="font-body-lg text-on-surface-variant max-w-3xl mx-auto">Ngoài thời gian bảo hành, Axeron Sport
                    hỗ trợ bảo trì trọn đời cho các sản phẩm giày thể thao mua tại hệ thống.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg text-center shadow-sm">
                    <span class="material-symbols-outlined text-[48px] text-axeron-red mb-4">cleaning_services</span>
                    <h3 class="font-label-lg text-text-dark mb-2">Vệ Sinh Cơ Bản</h3>
                    <p class="font-body-md text-on-surface-variant text-sm">Hỗ trợ làm sạch bề mặt, khử mùi miễn phí 1
                        lần/năm.</p>
                </div>
                <div class="bg-white p-6 rounded-lg text-center shadow-sm">
                    <span class="material-symbols-outlined text-[48px] text-axeron-red mb-4">hardware</span>
                    <h3 class="font-label-lg text-text-dark mb-2">Dán Keo &amp; Chỉ</h3>
                    <p class="font-body-md text-on-surface-variant text-sm">Hỗ trợ dán keo lại các vết hở nhỏ, đứt chỉ
                        ngoại vi (có tính phí vật tư nếu hư hỏng nặng).</p>
                </div>
                <div class="bg-white p-6 rounded-lg text-center shadow-sm">
                    <span class="material-symbols-outlined text-[48px] text-axeron-red mb-4">change_circle</span>
                    <h3 class="font-label-lg text-text-dark mb-2">Thu Cũ Đổi Mới</h3>
                    <p class="font-body-md text-on-surface-variant text-sm">Chương trình trade-in nâng cấp sản phẩm mới
                        với mức trợ giá lên đến 20%.</p>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
