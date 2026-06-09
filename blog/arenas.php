<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Dự án Nhà Thi Đấu Đa Năng - Axeron';
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
            <span class="text-on-background font-bold uppercase">Nhà Thi Đấu</span>
        </nav>

        <!-- Article Header -->
        <header class="mb-10 text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold uppercase text-axeron-red mb-4">NHÀ THI ĐẤU ĐA NĂNG</h1>
            <div class="w-24 h-[4px] bg-axeron-red mx-auto mb-6"></div>
        </header>

        <!-- Article Content -->
        <article class="prose prose-lg max-w-none text-[#4a4a4a] leading-relaxed">
            <p class="text-xl font-medium mb-6 text-center text-[#333]">Nhà thi đấu đa năng là công trình trọng điểm trong hệ thống thiết chế thể thao, phục vụ nhu cầu tập luyện và tổ chức các giải đấu chuyên nghiệp trong nhà như: Bóng rổ, Cầu lông, Bóng chuyền, Bóng bàn, Futsal...</p>
            
            <p>Hiểu được tính chất khắt khe của các bộ môn thể thao trong nhà, Axeron cung cấp giải pháp thi công toàn diện, đặc biệt chú trọng vào hệ thống mặt sàn thi đấu và trang thiết bị chuyên dụng.</p>
            
            <img src="https://images.unsplash.com/photo-1547347298-4074fc3086f0?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Nhà thi đấu đa năng">
            
            <h3 class="text-2xl font-bold text-axeron-red mb-4 mt-8">Các hạng mục chuyên sâu của Axeron:</h3>
            <ul class="list-disc pl-6 space-y-3 mb-8">
                <li><strong>Thảm và sàn thi đấu:</strong> Thi công sàn gỗ thể thao chuyên dụng chịu lực hoặc thảm nhựa PVC đa năng đạt tiêu chuẩn BWF (Cầu lông), FIBA (Bóng rổ).</li>
                <li><strong>Thiết bị thi đấu:</strong> Cung cấp trụ bóng rổ tiêu chuẩn quốc tế, trụ bóng chuyền, lưới cầu lông, bàn bóng bàn thi đấu.</li>
                <li><strong>Khán đài và thiết bị phụ trợ:</strong> Ghế nhựa khán đài, bảng rổ điện tử, bảng tỷ số (scoreboard) tích hợp phần mềm thông minh.</li>
                <li><strong>Hệ thống đèn:</strong> Chiếu sáng chuyên dụng chống lóa cho vận động viên, đảm bảo độ rọi chuẩn cho truyền hình trực tiếp.</li>
            </ul>

            <img src="https://images.unsplash.com/photo-1505666287802-931dc83948e9?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Mặt sàn nhà thi đấu">

            <p>Công ty cổ phần Axeron tự hào là nhà thầu thể thao đã hoàn thiện hàng loạt các công trình nhà thi đấu của các trung tâm thể dục thể thao quận, huyện, các trường đại học và các khu liên hợp thể thao quy mô lớn trên toàn quốc.</p>

            <div class="bg-surface-container-low p-6 rounded-xl mt-8 border-l-4 border-axeron-red text-center">
                <p class="font-semibold text-lg text-[#222] mb-0">Liên hệ ngay với đội ngũ chuyên gia của Axeron để nhận tư vấn và báo giá thiết kế - thi công nhà thi đấu đa năng.</p>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
