<?php
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Chính sách quyền riêng tư - Axeron Sport';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Content -->
    <main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <!-- Breadcrumb -->
        <div class="mb-8 text-on-surface-variant font-label-sm flex items-center space-x-2">
            <a class="hover:text-axeron-red transition-colors" href="<?= BASE_URL ?>">Trang chủ</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-on-background font-semibold">Chính sách quyền riêng tư</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
            <!-- Sidebar Table of Contents -->
            <aside class="md:col-span-3 hidden md:block">
                <div
                    class="sticky top-24 bg-surface-container-lowest p-6 rounded-xl border border-surface-variant shadow-sm">
                    <h3
                        class="font-headline-md text-headline-md text-on-background mb-6 border-b border-surface-variant pb-4">
                        Nội dung</h3>
                    <ul class="space-y-4 font-body-md text-on-surface-variant">
                        <li>
                            <a class="block hover:text-axeron-red transition-colors font-medium" href="#muc-dich">1. Mục đích thu thập thông tin</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#thong-tin-thu-thap">2. Thông tin được thu thập</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#pham-vi">3. Phạm vi sử dụng thông tin</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#cookie-session">4. Cookie và Session</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#chia-se">5. Chia sẻ thông tin với bên thứ ba</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#thoi-gian">6. Thời gian lưu trữ thông tin</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#quyen-khach-hang">7. Quyền của khách hàng</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#cam-ket">8. Cam kết bảo mật thông tin</a>
                        </li>
                        <li>
                            <a class="block hover:text-axeron-red transition-colors" href="#lien-he">9. Thông tin liên hệ</a>
                        </li>
                    </ul>
                </div>
            </aside>
            <!-- Privacy Policy Content -->
            <article
                class="md:col-span-9 bg-surface-container-lowest p-6 md:p-10 rounded-xl border border-surface-variant shadow-sm">
                <div class="mb-10 text-center md:text-left">
                    <h1 class="font-display-lg text-display-lg text-on-background mb-4">Chính sách quyền riêng tư</h1>
                    <p class="font-body-lg text-on-surface-variant">Cập nhật lần cuối: 12/06/2026</p>
                </div>
                <div class="space-y-10 font-body-md text-on-surface leading-relaxed">
                    <section class="scroll-mt-24" id="muc-dich">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="target">my_location</span>
                            1. Mục đích thu thập thông tin
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p>Việc thu thập dữ liệu trên website Axeron Sport nhằm mục đích cung cấp trải nghiệm mua sắm an toàn, cá nhân hóa và tối ưu nhất cho khách hàng. Các thông tin thu thập giúp chúng tôi xử lý đơn hàng, liên hệ xác nhận, hỗ trợ dịch vụ và cải thiện các tính năng của website như tìm kiếm, gợi ý sản phẩm và AI Chatbot.</p>
                        </div>
                    </section>
                    
                    <section class="scroll-mt-24" id="thong-tin-thu-thap">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="database">database</span>
                            2. Thông tin được thu thập
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p>Khi đăng ký tài khoản, mua sắm hoặc sử dụng website, chúng tôi thu thập các thông tin sau:</p>
                            <ul class="list-disc pl-5 space-y-2 text-on-surface-variant">
                                <li><strong>Thông tin cá nhân cơ bản:</strong> Họ tên, Email, Số điện thoại, Mật khẩu, Địa chỉ giao hàng.</li>
                                <li><strong>Hành vi mua sắm & sử dụng:</strong> Lịch sử đơn hàng, Lịch sử tìm kiếm sản phẩm, Sản phẩm đã xem, Dữ liệu giỏ hàng, Đánh giá sản phẩm.</li>
                                <li><strong>Dữ liệu tương tác hệ thống:</strong> Lịch sử trò chuyện với AI Chatbot (nhằm cải thiện chất lượng hỗ trợ), Log truy cập trang (Product View Logs, Search Logs) phục vụ cho tính năng Recommendation Engine (gợi ý sản phẩm).</li>
                            </ul>
                        </div>
                    </section>

                    <section class="scroll-mt-24" id="pham-vi">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="visibility">visibility</span>
                            3. Phạm vi sử dụng thông tin
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p>Website Axeron Sport sử dụng thông tin thành viên cung cấp để:</p>
                            <ul class="list-disc pl-5 space-y-2 text-on-surface-variant">
                                <li>Xử lý và giao đơn hàng thành công đến khách hàng;</li>
                                <li>Cá nhân hóa trải nghiệm (ví dụ: gợi ý sản phẩm phù hợp dựa trên lịch sử mua sắm và tìm kiếm);</li>
                                <li>Hỗ trợ khách hàng qua Chatbot AI dựa trên ngữ cảnh và lịch sử truy cập;</li>
                                <li>Gửi các thông báo về đơn hàng, khuyến mãi hoặc cập nhật quan trọng từ Axeron Sport;</li>
                                <li>Ngăn ngừa các hoạt động phá hoại, lừa đảo hoặc giả mạo;</li>
                                <li>Giải quyết khiếu nại, hỗ trợ bảo hành và đổi trả.</li>
                            </ul>
                        </div>
                    </section>

                    <section class="scroll-mt-24" id="cookie-session">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="cookie">cookie</span>
                            4. Cookie và Session
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p>Axeron Sport sử dụng Cookie và Session để tối ưu hóa trải nghiệm người dùng:</p>
                            <ul class="list-disc pl-5 space-y-2 text-on-surface-variant">
                                <li><strong>Duy trì trạng thái đăng nhập:</strong> Giúp bạn không phải đăng nhập lại nhiều lần.</li>
                                <li><strong>Lưu giỏ hàng tạm thời:</strong> Đảm bảo sản phẩm bạn chọn không bị mất khi thoát trình duyệt.</li>
                                <li><strong>Ghi nhớ tùy chọn:</strong> Ví dụ như lịch sử bộ lọc, địa chỉ tìm kiếm.</li>
                                <li><strong>Phân tích hành vi:</strong> Hỗ trợ thống kê để nâng cao chất lượng dịch vụ mua sắm.</li>
                            </ul>
                        </div>
                    </section>

                    <section class="scroll-mt-24" id="chia-se">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="share">share</span>
                            5. Chia sẻ thông tin với bên thứ ba
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p><strong>Axeron Sport không bán hoặc chia sẻ dữ liệu cá nhân của bạn cho bên thứ ba vì mục đích thương mại.</strong></p>
                            <p>Thông tin chỉ được chia sẻ trong các trường hợp thật sự cần thiết sau:</p>
                            <ul class="list-disc pl-5 space-y-2 text-on-surface-variant">
                                <li><strong>Đơn vị vận chuyển:</strong> Để thực hiện giao hàng đến địa chỉ của bạn.</li>
                                <li><strong>Đơn vị thanh toán (PayOS):</strong> Để xử lý các giao dịch chuyển khoản trực tuyến an toàn.</li>
                                <li><strong>Cơ quan nhà nước có thẩm quyền:</strong> Khi có yêu cầu hợp pháp theo quy định của pháp luật.</li>
                            </ul>
                        </div>
                    </section>

                    <section class="scroll-mt-24" id="thoi-gian">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="schedule">schedule</span>
                            6. Thời gian lưu trữ thông tin
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p>Dữ liệu cá nhân của thành viên sẽ được lưu trữ cho đến khi có yêu cầu hủy bỏ hoặc thành viên tự đăng nhập và thực hiện xóa tài khoản. Trong mọi trường hợp khác, thông tin cá nhân của thành viên sẽ được bảo mật trên máy chủ của Axeron Sport.</p>
                        </div>
                    </section>
                    
                    <section class="scroll-mt-24" id="quyen-khach-hang">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="manage_accounts">manage_accounts</span>
                            7. Quyền của khách hàng
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p>Tại Axeron Sport, khách hàng có các quyền sau đối với dữ liệu của mình:</p>
                            <ul class="list-disc pl-5 space-y-2 text-on-surface-variant">
                                <li><strong>Xem và cập nhật:</strong> Xem và thay đổi thông tin cá nhân (địa chỉ, số điện thoại) trong mục Quản lý tài khoản.</li>
                                <li><strong>Thay đổi mật khẩu:</strong> Khách hàng có thể đổi mật khẩu bất kỳ lúc nào để bảo vệ tài khoản.</li>
                                <li><strong>Yêu cầu chỉnh sửa:</strong> Yêu cầu bộ phận CSKH chỉnh sửa các thông tin không chính xác nếu không thể tự làm.</li>
                                <li><strong>Yêu cầu xóa tài khoản:</strong> Khách hàng có quyền yêu cầu xóa vĩnh viễn tài khoản và dữ liệu liên quan khỏi hệ thống của Axeron Sport.</li>
                            </ul>
                        </div>
                    </section>

                    <section class="scroll-mt-24" id="cam-ket">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="shield">shield</span>
                            8. Cam kết bảo mật thông tin
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p><strong>Bảo mật tài khoản:</strong> Thông tin cá nhân của bạn được cam kết bảo mật tuyệt đối. Đặc biệt, mật khẩu người dùng được mã hóa bằng thuật toán băm (hashing) trước khi lưu trữ trong cơ sở dữ liệu và hoàn toàn không thể khôi phục dưới dạng văn bản gốc.</p>
                            <p><strong>Bảo mật thanh toán:</strong> Axeron Sport không lưu trữ thông tin tài khoản ngân hàng hoặc thông tin thẻ thanh toán của khách hàng. Mọi giao dịch thanh toán trực tuyến đều được xử lý thông qua cổng thanh toán PayOS được cấp phép và tuân thủ các tiêu chuẩn bảo mật.</p>
                            <p>Trong trường hợp máy chủ bị tấn công dẫn đến mất mát dữ liệu, Axeron Sport sẽ có trách nhiệm thông báo vụ việc cho cơ quan chức năng điều tra xử lý kịp thời và thông báo cho thành viên được biết.</p>
                        </div>
                    </section>

                    <section class="scroll-mt-24" id="lien-he">
                        <h2 class="font-headline-md text-headline-md text-primary mb-4 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-axeron-red" data-icon="contact_support">contact_support</span>
                            9. Thông tin liên hệ
                        </h2>
                        <div class="pl-8 space-y-4">
                            <p>Mọi thắc mắc liên quan đến Chính sách quyền riêng tư, hoặc yêu cầu xử lý dữ liệu, vui lòng liên hệ với chúng tôi qua:</p>
                            <div class="bg-surface p-4 rounded-lg border border-outline-variant mt-2">
                                <p class="font-semibold text-on-surface">Axeron Sport</p>
                                <p>Email: <a href="mailto:support@axeron.vn" class="text-axeron-red hover:underline">support@axeron.vn</a></p>
                                <p>Hotline: <a href="tel:19001234" class="text-axeron-red hover:underline">1900 1234</a></p>
                                <p>Địa chỉ: Số 123 Đường Axeron, Quận 1, TP. Hồ Chí Minh</p>
                            </div>
                        </div>
                    </section>
                </div>
            </article>
        </div>
    </main>
    <!-- SideNavBar (Floating Action Buttons) -->
    
    <!-- Footer -->

<script>
        // Simple scroll spy for table of contents
        document.addEventListener('DOMContentLoaded', function () {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('aside ul li a');

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
                    link.classList.remove('text-axeron-red', 'font-bold');
                    link.classList.add('text-on-surface-variant', 'font-medium');
                    if (link.getAttribute('href').includes(current) && current !== '') {
                        link.classList.remove('text-on-surface-variant', 'font-medium');
                        link.classList.add('text-axeron-red', 'font-bold');
                    }
                });
            });
        });
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
