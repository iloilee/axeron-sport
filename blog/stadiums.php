<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Dự án Sân Vận Động - Axeron';
require_once __DIR__ . '/../includes/head.php';
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-sm mb-8 text-on-surface-variant flex items-center gap-2">
            <a href="<?= BASE_URL ?>" class="hover:text-axeron-red transition-colors">Trang chủ</a>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
            <span>Các dự án</span>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
            <span class="text-on-background font-bold uppercase">Sân Vận Động</span>
        </nav>

        <!-- Article Header -->
        <header class="mb-10 text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold uppercase text-axeron-red mb-4">SÂN VẬN ĐỘNG</h1>
            <div class="w-24 h-[4px] bg-axeron-red mx-auto mb-6"></div>
        </header>

        <!-- Article Content -->
        <article class="prose prose-lg max-w-none text-[#4a4a4a] leading-relaxed">
            <p class="text-xl font-medium mb-6 text-center text-[#333]">Axeron Sport tự hào là một trong những đơn vị tiên phong trong việc cung cấp các giải pháp toàn diện cho các hệ thống sân vận động, sân cỏ nhân tạo trên toàn quốc.</p>
            
            <p>Chúng tôi chuyên tư vấn, thiết kế và thi công hệ thống sân cỏ nhân tạo tiêu chuẩn, hệ thống đèn chiếu sáng, lưới bao, ghế ngồi khán đài và các thiết bị phụ trợ khác dành riêng cho các sân bóng đá, điền kinh và thể thao ngoài trời.</p>
            
            <img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Dự án sân vận động Axeron">
            
            <h3 class="text-2xl font-bold text-axeron-red mb-4 mt-8">Hạng mục thi công Sân vận động của Axeron:</h3>
            <ul class="list-disc pl-6 space-y-3 mb-8">
                <li><strong>Khảo sát và thiết kế:</strong> Tư vấn giải pháp tối ưu cho từng quỹ đất và nhu cầu sử dụng.</li>
                <li><strong>Thi công nền móng sân:</strong> San lấp mặt bằng, làm hệ thống thoát nước đạt chuẩn.</li>
                <li><strong>Hệ thống mặt sân:</strong> Lắp đặt cỏ nhân tạo chất lượng cao (FIFA Quality) hoặc thảm thi đấu tổng hợp (đường chạy điền kinh).</li>
                <li><strong>Hệ thống chiếu sáng:</strong> Lắp đặt đèn LED chuyên dụng chống chói, tiết kiệm điện cho thi đấu ban đêm.</li>
                <li><strong>Phụ kiện sân bãi:</strong> Cung cấp khung thành, lưới chắn bóng, băng ghế chỉ đạo và ghế ngồi khán đài.</li>
            </ul>

            <img src="https://images.unsplash.com/photo-1508344928928-7137b29de216?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Sân cỏ nhân tạo">

            <p>Với đội ngũ kỹ sư và chuyên gia dày dạn kinh nghiệm, chúng tôi đã và đang thực hiện hàng trăm dự án lớn nhỏ: từ sân vận động trung tâm các tỉnh, học viện bóng đá, trường học, cho đến khu đô thị và các sân mini phong trào khắp 63 tỉnh thành.</p>

            <div class="bg-surface-container-low p-6 rounded-xl mt-8 border-l-4 border-axeron-red text-center">
                <p class="font-semibold text-lg text-[#222] mb-0">Hãy liên hệ với Axeron để được tư vấn thiết kế và thi công sân vận động thể thao chất lượng và chuyên nghiệp nhất!</p>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
