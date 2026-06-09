<?php
require_once __DIR__ . '/config/session.php';
$pageTitle = 'Về Axeron - Axeron Sport';
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main>
        <!-- Hero Section -->
        <section
            class="relative h-[614px] min-h-[500px] flex items-center justify-center overflow-hidden bg-on-surface text-white">
            <div class="absolute inset-0 z-0">
                <img alt="Vận động viên đang khởi động trên đường chạy đua dưới ánh sáng mặt trời rực rỡ"
                    class="w-full h-full object-cover opacity-50"
                    data-alt="A dramatic, high-contrast action shot of an athlete in mid-sprint on a modern running track. The scene is bathed in golden hour sunlight, casting long, sharp shadows that emphasize speed and power. The color palette features deep asphalt tones against vibrant red track lines, aligning with a corporate yet high-performance sports aesthetic. The mood is intense, focused, and dynamic."
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCnxaSL0kECEu5PtqbDx7diVXLCEk9KqTf34og1ACm_PUzWT5jK3CIUAGFS7fkYceNJcuLJJ5U7bnZtH7yLnAoCAKUjz5bp9LV8xgwRWAuWvj6UEH9oBnCt0IekhNjUHQsHXVNG_nq-pNjPlVwEczUX_EkdAHB9-_LnPkBbMOvtrdQ_a7nfahD2ndOG451iKB3PEp1T0yYSyXsk1MFzxNg_BBxNcW0l850vpuGbZ_Su2G12aFdCspuCYvmylvFFktQcohHsL_Mx_sou" />
            </div>
            <div class="relative z-10 text-center px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
                <h1 class="font-display-lg text-display-lg uppercase mb-4 text-white drop-shadow-lg">
                    Hơn 30 Năm<br /><span class="text-axeron-red">Tiên Phong</span> Thể Thao
                </h1>
                <p class="font-body-lg text-body-lg text-surface-variant max-w-2xl mx-auto drop-shadow-md">
                    Axeron tự hào là thương hiệu hàng đầu Việt Nam, đồng hành cùng khát vọng vươn tầm của thể thao nước
                    nhà bằng những sản phẩm chất lượng vượt trội.
                </p>
            </div>
        </section>
        <!-- Tầm nhìn & Sứ mệnh (Bento Grid) -->
        <section class="py-16 md:py-24 bg-surface-container-low">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                    <!-- Sứ mệnh -->
                    <div
                        class="md:col-span-8 bg-white p-8 md:p-12 rounded-xl shadow-sm border border-outline-variant flex flex-col justify-center">
                        <div class="w-12 h-12 bg-axeron-red rounded-full flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined fill text-white" data-icon="flag">flag</span>
                        </div>
                        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface mb-4">Sứ
                            Mệnh</h2>
                        <p class="font-body-lg text-body-lg text-on-surface-variant">
                            Nâng tầm thể chất người Việt thông qua việc cung cấp các trang thiết bị, dụng cụ và trang
                            phục thể thao đạt tiêu chuẩn quốc tế. Chúng tôi tin rằng mỗi bước chạy, mỗi nhịp đập con tim
                            đều xứng đáng được trang bị tốt nhất để vượt qua giới hạn bản thân.
                        </p>
                    </div>
                    <!-- Tầm nhìn -->
                    <div
                        class="md:col-span-4 bg-axeron-red p-8 md:p-12 rounded-xl shadow-md flex flex-col justify-center text-white">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-6">
                            <span class="material-symbols-outlined fill text-white"
                                data-icon="visibility">visibility</span>
                        </div>
                        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg mb-4">Tầm Nhìn</h2>
                        <p class="font-body-md text-body-md opacity-90">
                            Trở thành tập đoàn công nghiệp thể thao số 1 Đông Nam Á, đưa thương hiệu Việt tỏa sáng trên
                            đấu trường quốc tế.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Lịch sử (Timeline/Asymmetric) -->
        <section class="py-16 md:py-24 bg-surface">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
                <div class="text-center mb-16">
                    <h2
                        class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface uppercase mb-4">
                        Chặng Đường Phát Triển</h2>
                    <div class="w-24 h-1 bg-axeron-red mx-auto"></div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div
                        class="order-2 lg:order-1 relative rounded-xl overflow-hidden shadow-lg border border-outline-variant">
                        <img alt="Khuôn viên nhà máy sản xuất dụng cụ thể thao hiện đại"
                            class="w-full h-[400px] object-cover"
                            data-alt="A clean, modern sporting goods manufacturing facility interior. Rows of high-tech sewing and molding machines are visible under bright, industrial fluorescent lighting. The color scheme is predominantly crisp white and sterile gray, accented by bright red raw materials indicating the brand's signature color. The environment feels efficient, precise, and highly professional."
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCkxmvNhxEm0y3HDD8uObM3eZxVUx21VQ-yyoXF59wW9xUKBv0K7bev8C1essElAUNCpxNcqqCp8zKmGJY4pRFscC6lkUZjRfhjcJXRPIpG9B_j-4nZ97PS3w3FimJKLoY5lbmamN9QuMhfQjHpUwO6kRmKBlXEqueKtpYvpmLxuRIWtAIi87H5m2TybpblErfeCkXVRc3_NRk4cN-I811ZOAt7N8-Xi7slnjUSO9IDp2MImjqBJMy6YiKQ_gJTGdXrdvcRtEO4obhG" />
                    </div>
                    <div class="order-1 lg:order-2 space-y-8">
                        <div class="relative pl-8 border-l-2 border-axeron-red">
                            <div
                                class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-axeron-red border-4 border-surface">
                            </div>
                            <h3 class="font-headline-md text-headline-md text-axeron-red mb-2">1992 - Khởi Nguồn</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Bắt đầu từ một xưởng sản xuất
                                bóng nhỏ, đặt những viên gạch đầu tiên cho khát vọng lớn.</p>
                        </div>
                        <div class="relative pl-8 border-l-2 border-outline-variant">
                            <div
                                class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-surface-variant border-4 border-surface">
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface mb-2">2005 - Chuyển Mình</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Chuyển đổi thành mô hình Tập
                                đoàn, mở rộng sản xuất đa dạng các dòng trang phục và dụng cụ thể thao chuyên nghiệp.
                            </p>
                        </div>
                        <div class="relative pl-8 border-l-2 border-outline-variant">
                            <div
                                class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-surface-variant border-4 border-surface">
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface mb-2">2018 - Vươn Tầm Quốc Tế
                            </h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Trở thành nhà tài trợ chính
                                thức cho các đội tuyển thể thao quốc gia và xuất khẩu sản phẩm sang thị trường khu vực.
                            </p>
                        </div>
                        <div class="relative pl-8 border-l-2 border-transparent">
                            <div
                                class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-surface-variant border-4 border-surface">
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface mb-2">Hiện Tại - Dẫn Đầu</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Khẳng định vị thế số 1 Việt
                                Nam, ứng dụng công nghệ hiện đại vào từng sợi vải, từng đường may.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Thành tựu (Stats Grid) -->
        <section class="py-16 md:py-24 bg-on-surface text-white">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div class="p-6">
                        <div class="font-display-lg text-display-lg text-axeron-red mb-2">32+</div>
                        <div class="font-label-lg text-label-lg uppercase tracking-wider text-surface-variant">Năm Kinh
                            Nghiệm</div>
                    </div>
                    <div class="p-6">
                        <div class="font-display-lg text-display-lg text-axeron-red mb-2">500+</div>
                        <div class="font-label-lg text-label-lg uppercase tracking-wider text-surface-variant">Cửa Hàng
                            Toàn Quốc</div>
                    </div>
                    <div class="p-6">
                        <div class="font-display-lg text-display-lg text-axeron-red mb-2">5M+</div>
                        <div class="font-label-lg text-label-lg uppercase tracking-wider text-surface-variant">Khách
                            Hàng Tin Dùng</div>
                    </div>
                    <div class="p-6">
                        <div class="font-display-lg text-display-lg text-axeron-red mb-2">Top 1</div>
                        <div class="font-label-lg text-label-lg uppercase tracking-wider text-surface-variant">Thương
                            Hiệu Thể Thao VN</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
