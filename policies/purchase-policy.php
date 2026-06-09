<?php
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Chính sách mua hàng - Axeron Sport';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .scroll-mt-header {
        scroll-margin-top: 100px;
    }
</style>

<main class="w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
    <!-- Page Header -->
    <div class="mb-12 text-center">
        <h1 class="font-display-lg text-display-lg md:text-display-lg text-on-surface mb-4">Chính Sách Mua Hàng</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">Thông tin chi tiết về quy trình đặt hàng và thanh toán tại Axeron Sport.</p>
    </div>
    
    <!-- Content Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Sidebar Nav Content -->
            <aside class="lg:col-span-3">
                <div class="sticky top-28 bg-white border border-outline-variant p-6 rounded-lg">
                    <h3 class="font-headline-md text-headline-md text-dark mb-6 border-l-4 border-axeron-red pl-4">
                        DANH MỤC</h3>
                    <ul class="space-y-4">
                        <li><a class="flex items-center gap-3 text-on-surface hover:text-axeron-red transition-all font-medium py-2 border-b border-surface-container"
                                href="#online-process"><span
                                    class="material-symbols-outlined text-axeron-red">shopping_bag</span> Quy trình
                                đặt hàng</a></li>
                        <li><a class="flex items-center gap-3 text-on-surface hover:text-axeron-red transition-all font-medium py-2 border-b border-surface-container"
                                href="#store-purchase"><span
                                    class="material-symbols-outlined text-axeron-red">store</span> Mua tại cửa
                                hàng</a></li>
                        <li><a class="flex items-center gap-3 text-on-surface hover:text-axeron-red transition-all font-medium py-2 border-b border-surface-container"
                                href="#payment-methods"><span
                                    class="material-symbols-outlined text-axeron-red">payments</span> Thanh toán</a>
                        </li>
                        <li><a class="flex items-center gap-3 text-on-surface hover:text-axeron-red transition-all font-medium py-2 border-b border-surface-container"
                                href="#cancel-policy"><span
                                    class="material-symbols-outlined text-axeron-red">cancel</span> Hủy &amp; Thay
                                đổi</a></li>
                        <li><a class="flex items-center gap-3 text-on-surface hover:text-axeron-red transition-all font-medium py-2 border-b border-surface-container"
                                href="#obligations"><span
                                    class="material-symbols-outlined text-axeron-red">gavel</span> Nghĩa vụ các
                                bên</a></li>
                    </ul>
                </div>
            </aside>
            <!-- Main Policy Content -->
            <article class="lg:col-span-9 space-y-20">
                <!-- Online Purchase Process -->
                <div class="scroll-mt-header" id="online-process">
                    <h2 class="font-headline-lg text-headline-lg text-dark mb-8 flex items-center gap-3">
                        <span
                            class="bg-axeron-red text-white w-10 h-10 flex items-center justify-center rounded-lg">01</span>
                        QUY TRÌNH ĐẶT HÀNG TRỰC TUYẾN
                    </h2>
                    <p class="text-on-surface-variant mb-8 leading-relaxed">
                        Tại Axeron Sport, chúng tôi tối ưu hóa quy trình đặt hàng để quý khách có trải nghiệm mua
                        sắm nhanh chóng và thuận tiện nhất.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="bg-white border border-outline-variant p-6 hover:shadow-lg transition-all group">
                            <span
                                class="material-symbols-outlined text-axeron-red text-[40px] mb-4 group-hover:scale-110 transition-transform">search_check</span>
                            <h4 class="font-headline-md text-[18px] mb-2 uppercase">Bước 1: Chọn sản phẩm</h4>
                            <p class="text-body-md text-on-surface-variant">Quý khách truy cập website, tìm kiếm và
                                lựa chọn sản phẩm phù hợp theo danh mục hoặc thanh công cụ tìm kiếm.</p>
                        </div>
                        <div
                            class="bg-white border border-outline-variant p-6 hover:shadow-lg transition-all group">
                            <span
                                class="material-symbols-outlined text-axeron-red text-[40px] mb-4 group-hover:scale-110 transition-transform">shopping_cart_checkout</span>
                            <h4 class="font-headline-md text-[18px] mb-2 uppercase">Bước 2: Giỏ hàng</h4>
                            <p class="text-body-md text-on-surface-variant">Thêm sản phẩm vào giỏ hàng, kiểm tra lại
                                số lượng, kích cỡ và màu sắc trước khi tiến hành thanh toán.</p>
                        </div>
                        <div
                            class="bg-white border border-outline-variant p-6 hover:shadow-lg transition-all group">
                            <span
                                class="material-symbols-outlined text-axeron-red text-[40px] mb-4 group-hover:scale-110 transition-transform">local_shipping</span>
                            <h4 class="font-headline-md text-[18px] mb-2 uppercase">Bước 3: Thông tin giao hàng</h4>
                            <p class="text-body-md text-on-surface-variant">Điền đầy đủ thông vị nhận hàng, chọn
                                phương thức vận chuyển và áp dụng các mã giảm giá (nếu có).</p>
                        </div>
                        <div
                            class="bg-white border border-outline-variant p-6 hover:shadow-lg transition-all group">
                            <span
                                class="material-symbols-outlined text-axeron-red text-[40px] mb-4 group-hover:scale-110 transition-transform">verified</span>
                            <h4 class="font-headline-md text-[18px] mb-2 uppercase">Bước 4: Xác nhận đơn hàng</h4>
                            <p class="text-body-md text-on-surface-variant">Sau khi nhấn "Đặt hàng", hệ thống sẽ gửi
                                email/SMS xác nhận. Nhân viên CSKH sẽ liên hệ lại nếu cần thêm thông tin.</p>
                        </div>
                    </div>
                </div>
                <!-- Store Purchase -->
                <div class="scroll-mt-header" id="store-purchase">
                    <h2 class="font-headline-lg text-headline-lg text-dark mb-8 flex items-center gap-3">
                        <span
                            class="bg-axeron-red text-white w-10 h-10 flex items-center justify-center rounded-lg">02</span>
                        MUA HÀNG TẠI CỬA HÀNG
                    </h2>
                    <div
                        class="flex flex-col md:flex-row gap-8 items-center bg-surface-container rounded-xl overflow-hidden">
                        <div class="w-full md:w-1/2 h-[300px]">
                            <img alt="Store Front" class="w-full h-full object-cover"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDfip_tcNJEHcsh8yaUMfJlSSIp7JwK1Wjm-MWbgbpq_Mg9iK3f5EoONmyhGW5af2_cs2xh281o8ojK7gjcPbeyJoSN33xqQKBajjOD5e7dTsE6LfbuIs0CbAJvOGdT3RlrVpYKNZCPf06NqMLMk9A4bhIuLlxNBZFkX-yDHeuRH59m9s9khoMYvJSDIBKCegzzbgxDdE2zBuNtFsIdn8ZAP7cw2ymClh7PLrtaWIv3uOWcbrfPsqn9Jrrysc60Sxvo_8ZepL2ftdva" />
                        </div>
                        <div class="w-full md:w-1/2 p-8">
                            <ul class="space-y-6">
                                <li class="flex gap-4">
                                    <span class="material-symbols-outlined text-axeron-blue">location_on</span>
                                    <div>
                                        <h5 class="font-bold">Tìm cửa hàng gần nhất</h5>
                                        <p class="text-on-surface-variant text-sm">Tra cứu hệ thống showroom chính
                                            hãng Axeron trên toàn quốc.</p>
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="material-symbols-outlined text-axeron-blue">support_agent</span>
                                    <div>
                                        <h5 class="font-bold">Tư vấn chuyên nghiệp</h5>
                                        <p class="text-on-surface-variant text-sm">Đội ngũ chuyên gia hỗ trợ bạn
                                            chọn trang phục phù hợp với môn thể thao và vóc dáng.</p>
                                    </div>
                                </li>
                                <li class="flex gap-4">
                                    <span class="material-symbols-outlined text-axeron-blue">inventory</span>
                                    <div>
                                        <h5 class="font-bold">Thử đồ &amp; Kiểm tra</h5>
                                        <p class="text-on-surface-variant text-sm">Trải nghiệm trực tiếp chất liệu
                                            và độ vừa vặn của sản phẩm trước khi quyết định.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- Payment Methods -->
                <div class="scroll-mt-header" id="payment-methods">
                    <h2 class="font-headline-lg text-headline-lg text-dark mb-8 flex items-center gap-3">
                        <span
                            class="bg-axeron-red text-white w-10 h-10 flex items-center justify-center rounded-lg">03</span>
                        PHƯƠNG THỨC THANH TOÁN
                    </h2>
                    <div class="space-y-8">
                        <div
                            class="bg-surface-container-lowest rounded-xl border border-outline-variant p-8 hover:shadow-md transition-shadow duration-300">
                            <div class="flex items-center mb-6">
                                <div
                                    class="bg-primary-container text-on-primary rounded-full p-3 mr-4 flex items-center justify-center">
                                    <span class="material-symbols-outlined"
                                        style="font-variation-settings: 'FILL' 1;">payments</span>
                                </div>
                                <h2 class="font-headline-md text-headline-md text-on-background">1. Thanh toán tiền
                                    mặt (COD)</h2>
                            </div>
                            <div class="text-on-surface-variant space-y-4">
                                <p>Quý khách thanh toán trực tiếp cho nhân viên giao hàng khi nhận được sản phẩm.
                                </p>
                                <div class="bg-surface-container-low rounded-lg p-6 border border-outline-variant">
                                    <h3 class="font-label-lg text-label-lg text-on-background mb-3">Phí giao hàng dự
                                        kiến theo khu vực:</h3>
                                    <ul class="space-y-2 list-disc pl-5">
                                        <li><strong>Miền Bắc:</strong> 30.000 VNĐ - 35.000 VNĐ</li>
                                        <li><strong>Miền Trung:</strong> 35.000 VNĐ - 37.000 VNĐ</li>
                                        <li><strong>Miền Nam:</strong> 37.000 VNĐ - 40.000 VNĐ</li>
                                    </ul>
                                    <p class="text-sm mt-3 italic">* Phí giao hàng có thể thay đổi tùy theo trọng
                                        lượng và kích thước thực tế của đơn hàng.</p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-surface-container-lowest rounded-xl border border-outline-variant p-8 hover:shadow-md transition-shadow duration-300">
                            <div class="flex items-center mb-6">
                                <div
                                    class="bg-secondary-container text-on-secondary-fixed rounded-full p-3 mr-4 flex items-center justify-center">
                                    <span class="material-symbols-outlined"
                                        style="font-variation-settings: 'FILL' 1;">account_balance</span>
                                </div>
                                <h2 class="font-headline-md text-headline-md text-on-background">2. Chuyển khoản qua
                                    ngân hàng tại Việt Nam</h2>
                            </div>
                            <div class="text-on-surface-variant space-y-4">
                                <p>Quý khách có thể thanh toán bằng cách chuyển khoản qua các tài khoản ngân hàng
                                    dưới đây. Vui lòng ghi rõ mã đơn hàng trong nội dung chuyển khoản.</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div
                                        class="bg-surface-container-low rounded-lg p-5 border border-outline-variant">
                                        <div class="flex items-center mb-3">
                                            <span
                                                class="material-symbols-outlined text-secondary mr-2">account_balance_wallet</span>
                                            <h3 class="font-label-lg text-label-lg text-on-background">Vietcombank</h3>
                                        </div>
                                        <div class="space-y-1 text-sm">
                                            <p><span class="font-medium">Số tài khoản:</span> 7048124529xxx</p>
                                            <p><span class="font-medium">Chủ tài khoản:</span> Công ty CP Axeron Sports
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-surface-container-low rounded-lg p-5 border border-outline-variant">
                                        <div class="flex items-center mb-3">
                                            <span
                                                class="material-symbols-outlined text-secondary mr-2">account_balance_wallet</span>
                                            <h3 class="font-label-lg text-label-lg text-on-background">BIDV</h3>
                                        </div>
                                        <div class="space-y-1 text-sm">
                                            <p><span class="font-medium">Số tài khoản:</span> 12xxxx001568</p>
                                            <p><span class="font-medium">Chủ tài khoản:</span> Công ty CP Axeron Sports
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Cancel & Changes Policy -->
                <div class="scroll-mt-header bg-white border border-outline-variant p-8 rounded-2xl"
                    id="cancel-policy">
                    <h2 class="font-headline-lg text-headline-lg text-dark mb-6 flex items-center gap-3">
                        <span
                            class="bg-axeron-red text-white w-10 h-10 flex items-center justify-center rounded-lg">04</span>
                        HỦY &amp; THAY ĐỔI ĐƠN HÀNG
                    </h2>
                    <div class="space-y-4 text-on-surface-variant leading-relaxed">
                        <p class="font-bold text-dark">1. Hủy đơn hàng:</p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Quý khách có thể hủy đơn hàng hoàn toàn miễn phí trước khi đơn hàng được chuyển cho
                                đơn vị vận chuyển (thường trong vòng 2h kể từ khi đặt hàng).</li>
                            <li>Sau khi hàng đã được gửi đi, việc hủy đơn có thể phát sinh phí vận chuyển 2 chiều
                                tùy trường hợp.</li>
                        </ul>
                        <p class="font-bold text-dark mt-6">2. Thay đổi thông tin:</p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Để thay đổi địa chỉ hoặc số điện thoại nhận hàng, quý khách vui lòng liên hệ Hotline
                                1900 xxxx ngay lập tức.</li>
                            <li>Trong trường hợp thay đổi sản phẩm (size, màu), Axeron sẽ hỗ trợ cập nhật nếu sản
                                phẩm mới còn hàng và đơn chưa xuất kho.</li>
                        </ul>
                    </div>
                </div>
                <!-- Obligations -->
                <div class="scroll-mt-header" id="obligations">
                    <h2 class="font-headline-lg text-headline-lg text-dark mb-8 flex items-center gap-3">
                        <span
                            class="bg-axeron-red text-white w-10 h-10 flex items-center justify-center rounded-lg">05</span>
                        NGHĨA VỤ CÁC BÊN
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-on-background text-white p-8 rounded-xl">
                            <h4 class="font-display-lg text-[24px] mb-6 text-axeron-red">Nghĩa vụ của Axeron</h4>
                            <ul class="space-y-4 text-surface-variant">
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-red shrink-0">check_circle</span>
                                    Cung cấp sản phẩm chính hãng, đúng mô tả.</li>
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-red shrink-0">check_circle</span>
                                    Đảm bảo an toàn thông tin khách hàng.</li>
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-red shrink-0">check_circle</span>
                                    Thực hiện đúng cam kết bảo hành và đổi trả.</li>
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-red shrink-0">check_circle</span>
                                    Hỗ trợ giải quyết khiếu nại trong vòng 24h.</li>
                            </ul>
                        </div>
                        <div class="bg-surface-container p-8 rounded-xl border border-outline-variant">
                            <h4 class="font-display-lg text-[24px] mb-6 text-axeron-blue">Nghĩa vụ của Khách hàng
                            </h4>
                            <ul class="space-y-4 text-on-surface-variant">
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-blue shrink-0">info</span> Cung
                                    cấp thông tin giao hàng chính xác.</li>
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-blue shrink-0">info</span> Kiểm
                                    tra hàng kỹ lưỡng khi nhận.</li>
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-blue shrink-0">info</span>
                                    Thanh toán đầy đủ giá trị đơn hàng.</li>
                                <li class="flex gap-3"><span
                                        class="material-symbols-outlined text-axeron-blue shrink-0">info</span> Tuân
                                    thủ các quy định về đổi trả và bảo hành.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
</main>

<script>
    // Smooth scrolling for sidebar links
    document.querySelectorAll('aside a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });

                // Update active state visual
                document.querySelectorAll('aside a').forEach(a => a.classList.remove('text-axeron-red'));
                this.classList.add('text-axeron-red');
            }
        });
    });

    // Intersection Observer for highlighting sidebar based on scroll
    const sections = document.querySelectorAll('.scroll-mt-header');
    const navLinks = document.querySelectorAll('aside a[href^="#"]');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (pageYOffset >= (sectionTop - 150)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('text-axeron-red');
            if (link.getAttribute('href').includes(current)) {
                link.classList.add('text-axeron-red');
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
