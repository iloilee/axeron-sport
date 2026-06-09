<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Dự án Thiết bị phòng tập - Axeron';
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
            <span class="text-on-background font-bold uppercase">Thiết bị phòng tập</span>
        </nav>

        <!-- Article Header -->
        <header class="mb-10 text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold uppercase text-axeron-red mb-4">THIẾT BỊ PHÒNG TẬP</h1>
            <div class="w-24 h-[4px] bg-axeron-red mx-auto mb-6"></div>
        </header>

        <!-- Article Content -->
        <article class="prose prose-lg max-w-none text-[#4a4a4a] leading-relaxed">
            <p class="text-xl font-medium mb-6 text-center text-[#333]">Axeron cung cấp giải pháp trọn gói từ tư vấn thiết kế không gian đến setup toàn bộ trang thiết bị máy móc cho các phòng tập Gym, Fitness & Yoga đạt chuẩn quốc tế.</p>
            
            <p>Với xu hướng chăm sóc sức khỏe ngày càng tăng cao, việc sở hữu một phòng Gym hiện đại, trang bị đầy đủ máy móc tân tiến là lợi thế cạnh tranh vô cùng lớn. Axeron nhập khẩu và phân phối trực tiếp các dòng máy tập từ những thương hiệu hàng đầu thế giới, đảm bảo độ bền cơ học cao và công thái học chuẩn xác.</p>
            
            <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Thiết bị phòng tập Gym Axeron">
            
            <h3 class="text-2xl font-bold text-axeron-red mb-4 mt-8">Các dòng sản phẩm và dịch vụ nổi bật:</h3>
            <ul class="list-disc pl-6 space-y-3 mb-8">
                <li><strong>Máy tập Cardio:</strong> Máy chạy bộ điện công suất lớn, xe đạp tập, máy leo cầu thang, máy tập elip (Elliptical) với công nghệ màn hình cảm ứng, đo nhịp tim.</li>
                <li><strong>Máy tập cơ khối (Selectorized):</strong> Các loại máy ép ngực, kéo xô, đá đùi, móc đùi sau với thiết kế tạ khối an toàn, chuyển động mượt mà.</li>
                <li><strong>Máy tập tự do (Free Weights):</strong> Giàn tạ đa năng, khung gánh đùi (Squat Rack), thanh đòn Olympic, các loại tạ tay, tạ ấm (Kettlebell) đa dạng kích cỡ.</li>
                <li><strong>Thiết bị Yoga và phụ kiện:</strong> Thảm Yoga định tuyến, bóng tập, bục Aerobic, dây kháng lực.</li>
                <li><strong>Dịch vụ Setup trọn gói:</strong> Tư vấn sơ đồ bố trí máy móc 2D/3D tối ưu không gian, thi công sàn cao su giảm chấn, gương ốp tường và hệ thống ánh sáng.</li>
            </ul>

            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Không gian phòng Gym hiện đại">

            <p>Axeron đã thực hiện setup thành công cho hệ thống chuỗi phòng tập cao cấp, các phòng Gym nội khu cho resort, chung cư cao cấp và khách sạn 5 sao trên khắp lãnh thổ Việt Nam.</p>

            <div class="bg-surface-container-low p-6 rounded-xl mt-8 border-l-4 border-axeron-red text-center">
                <p class="font-semibold text-lg text-[#222] mb-0">Chủ đầu tư có nhu cầu mở phòng Gym, Fitness xin vui lòng liên hệ Axeron để nhận bảng dự toán và thiết kế hoàn toàn miễn phí!</p>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
