<?php
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Chính sách vận chuyển - Axeron Sport';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Content Canvas -->
    <main class="w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <!-- Page Header -->
        <div class="mb-12 text-center">
            <h1 class="font-display-lg text-display-lg md:text-display-lg text-on-surface mb-4">Chính Sách Vận Chuyển</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">Mọi thông tin chi tiết về quy
                trình đặt hàng, thanh toán và giao nhận tại Axeron Sport.</p>
        </div>
        <!-- Content Grid (Bento Style) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter md:gap-6">
            <!-- Card 1: Thanh Toán -->
            <div
                class="bg-white rounded-xl p-6 md:p-8 border border-surface-variant shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-primary-fixed-dim rounded-bl-full opacity-20 -z-10 transition-transform group-hover:scale-110">
                </div>
                <div class="flex items-center gap-4 mb-6 text-axeron-red">
                    <div class="p-3 bg-surface-container-low rounded-lg">
                        <span class="material-symbols-outlined fill-icon text-[32px]" data-icon="payments"
                            data-weight="fill">payments</span>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface">Phương Thức Thanh Toán</h2>
                </div>
                <div class="space-y-4 font-body-md text-body-md text-on-surface-variant">
                    <p>Axeron Sport hỗ trợ đa dạng phương thức thanh toán nhằm mang lại sự tiện lợi tối đa cho khách
                        hàng:</p>
                    <ul class="list-none space-y-3">
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-axeron-blue text-[20px] mt-1"
                                data-icon="check_circle">check_circle</span>
                            <span><strong>Thanh toán khi nhận hàng (COD):</strong> Quý khách thanh toán bằng tiền mặt
                                cho nhân viên giao hàng sau khi đã kiểm tra sản phẩm.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-axeron-blue text-[20px] mt-1"
                                data-icon="check_circle">check_circle</span>
                            <span><strong>Chuyển khoản ngân hàng:</strong> Thanh toán an toàn và nhanh chóng qua hệ
                                thống các ngân hàng nội địa.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-axeron-blue text-[20px] mt-1"
                                data-icon="check_circle">check_circle</span>
                            <span><strong>Ví điện tử &amp; Thẻ tín dụng:</strong> Hỗ trợ thanh toán qua VNPay, Momo,
                                ZaloPay, Visa, Mastercard, JCB.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Card 2: Phí Vận Chuyển -->
            <div
                class="bg-white rounded-xl p-6 md:p-8 border border-surface-variant shadow-sm hover:shadow-md transition-shadow duration-300 relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-secondary-fixed-dim rounded-bl-full opacity-20 -z-10 transition-transform group-hover:scale-110">
                </div>
                <div class="flex items-center gap-4 mb-6 text-axeron-blue">
                    <div class="p-3 bg-surface-container-low rounded-lg">
                        <span class="material-symbols-outlined fill-icon text-[32px]" data-icon="local_shipping"
                            data-weight="fill">local_shipping</span>
                    </div>
                    <h2 class="font-headline-md text-headline-md text-on-surface">Phí Vận Chuyển</h2>
                </div>
                <div class="space-y-4 font-body-md text-body-md text-on-surface-variant">
                    <p>Mức phí vận chuyển được tính toán tự động dựa trên trọng lượng đơn hàng và khoảng cách địa lý.
                    </p>
                    <div class="bg-surface-gray p-4 rounded-lg border border-outline-variant">
                        <p class="font-label-lg text-label-lg text-on-surface mb-2">Chính sách ưu đãi:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Miễn phí vận chuyển</strong> cho mọi đơn hàng có giá trị từ <strong>1.500.000
                                    VNĐ</strong> trở lên trên toàn quốc.</li>
                            <li>Đồng giá phí ship <strong>30.000 VNĐ</strong> cho các đơn hàng dưới 1.500.000 VNĐ tại
                                khu vực nội thành Hà Nội và TP.HCM.</li>
                            <li>Phí ship tỉnh: <strong>40.000 VNĐ - 50.000 VNĐ</strong> tùy khu vực.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Card 3: Thời Gian Giao Hàng -->
            <div
                class="bg-white rounded-xl p-6 md:p-8 border border-surface-variant shadow-sm hover:shadow-md transition-shadow duration-300 md:col-span-2 relative overflow-hidden group">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-transparent to-surface-container-low opacity-50 -z-10">
                </div>
                <div class="flex flex-col md:flex-row gap-8 items-start md:items-center">
                    <div class="flex-1">
                        <div class="flex items-center gap-4 mb-6 text-tertiary">
                            <div class="p-3 bg-surface-container-low rounded-lg">
                                <span class="material-symbols-outlined fill-icon text-[32px]" data-icon="schedule"
                                    data-weight="fill">schedule</span>
                            </div>
                            <h2 class="font-headline-md text-headline-md text-on-surface">Thời Gian Giao Hàng Dự Kiến
                            </h2>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant mb-4">Axeron Sport hợp tác với các
                            đơn vị vận chuyển uy tín hàng đầu (GHTK, Viettel Post, J&amp;T) để đảm bảo hàng hóa đến tay
                            bạn nhanh chóng và an toàn nhất.</p>
                    </div>
                    <div class="w-full md:w-1/2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-lg border border-outline-variant text-center">
                            <span class="block font-headline-md text-headline-md text-axeron-red mb-1">1 - 2 Ngày</span>
                            <span class="block font-label-sm text-label-sm text-on-surface-variant uppercase">Nội Thành
                                HN &amp; TP.HCM</span>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-outline-variant text-center">
                            <span class="block font-headline-md text-headline-md text-axeron-red mb-1">3 - 5 Ngày</span>
                            <span class="block font-label-sm text-label-sm text-on-surface-variant uppercase">Các Tỉnh
                                Thành Khác</span>
                        </div>
                    </div>
                </div>
                <p class="mt-4 font-label-sm text-label-sm text-outline italic">*Lưu ý: Thời gian giao hàng có thể kéo
                    dài hơn dự kiến trong các dịp Lễ, Tết hoặc điều kiện thời tiết bất lợi. Chúng tôi sẽ thông báo trước
                    nếu có sự chậm trễ.</p>
            </div>
            <!-- Card 4: Kiểm Hàng -->
            <div
                class="bg-white rounded-xl p-6 md:p-8 border border-surface-variant shadow-sm hover:shadow-md transition-shadow duration-300 md:col-span-2 flex flex-col md:flex-row gap-6 relative overflow-hidden group">
                <div class="md:w-1/3 w-full h-48 md:h-auto rounded-lg bg-surface-gray bg-cover bg-center"
                    data-alt="A medium shot of a delivery person in uniform handing a well-packaged cardboard box to a customer at a modern front door. The lighting is bright and natural, suggesting daytime. The mood is reliable and professional, reinforcing the trust in the shipping process."
                    style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDNnJiX8K61jINnMdjMK0IJSVfFIMh1EsQTm9REFBQaxIOpONebUKpOtyhy2muymk8MeW3-DN-ppRAcJelMuncUyEho52a27qC0x5YT_hQdoSFca5fdJNWYsG1zTc2ObJI-IBeIAMcdDlgl-AADYVe5TKkh8fVeEhxiAdkMcS9pT5ZeDDBxK-nAg7QPRWo5obAkjb1y03ddTTVZ13-SvLTVGPSKQzQaSDzLFAcsquy92BSr2ypvWw3hxc6YCzUvW05WBfDkeBxkrs4r');">
                </div>
                <div class="md:w-2/3 w-full">
                    <div class="flex items-center gap-4 mb-4 text-secondary">
                        <div class="p-3 bg-surface-container-low rounded-lg">
                            <span class="material-symbols-outlined fill-icon text-[32px]" data-icon="inventory_2"
                                data-weight="fill">inventory_2</span>
                        </div>
                        <h2 class="font-headline-md text-headline-md text-on-surface">Chính Sách Kiểm Hàng</h2>
                    </div>
                    <div class="space-y-4 font-body-md text-body-md text-on-surface-variant">
                        <p>Để đảm bảo quyền lợi, Axeron Sport khuyến khích khách hàng thực hiện các bước sau khi nhận
                            hàng:</p>
                        <ol class="list-decimal list-inside space-y-2">
                            <li><strong>Đồng kiểm cùng bưu tá:</strong> Quý khách được quyền mở kiện hàng kiểm tra ngoại
                                quan (số lượng, màu sắc, tình trạng sản phẩm) trước khi thanh toán và nhận hàng.</li>
                            <li><strong>Không thử sản phẩm:</strong> Vui lòng không thử sản phẩm (quần áo, giày dép)
                                hoặc xé bỏ nhãn mác, niêm phong trong quá trình đồng kiểm.</li>
                            <li><strong>Từ chối nhận hàng:</strong> Nếu phát hiện sản phẩm bị lỗi do vận chuyển, sai mẫu
                                mã, hoặc thiếu số lượng, quý khách có quyền từ chối nhận hàng và yêu cầu bưu tá hoàn
                                trả. Vui lòng liên hệ ngay Hotline để được hỗ trợ xử lý đổi mới.</li>
                            <li><strong>Quay video mở hàng:</strong> Trong trường hợp nhận hàng qua bảo vệ hoặc người
                                thân, khuyến khích quay video quá trình bóc hộp để làm bằng chứng nếu cần khiếu nại đổi
                                trả.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- SideNavBar (Suppressed as this is a linear info page, not a dashboard) -->
    <!-- Footer -->


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
