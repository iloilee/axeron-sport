<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Dự án Đồng phục học sinh - sinh viên - Axeron';
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
            <span class="text-on-background font-bold uppercase">Đồng phục học sinh, sinh viên</span>
        </nav>

        <!-- Article Header -->
        <header class="mb-10 text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold uppercase text-axeron-red mb-4">ĐỒNG PHỤC HỌC SINH, SINH VIÊN</h1>
            <div class="w-24 h-[4px] bg-axeron-red mx-auto mb-6"></div>
        </header>

        <!-- Article Content -->
        <article class="prose prose-lg max-w-none text-[#4a4a4a] leading-relaxed">
            <p class="text-xl font-medium mb-6 text-center text-[#333]">Không chỉ dẫn đầu trong mảng dụng cụ và thiết bị thể thao, Axeron còn là đối tác tin cậy trong việc cung cấp đồng phục thể dục cho hàng trăm trường Tiểu học, THCS, THPT và Đại học trên cả nước.</p>
            
            <p>Đồng phục thể dục học sinh, sinh viên đòi hỏi sự năng động, thoải mái và bền bỉ. Với dây chuyền sản xuất hiện đại và quy trình kiểm soát chất lượng nghiêm ngặt, Axeron cam kết mang đến những bộ đồng phục không chỉ đẹp về thiết kế mà còn an toàn cho sức khỏe học đường.</p>
            
            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Đồng phục học sinh, sinh viên Axeron">
            
            <h3 class="text-2xl font-bold text-axeron-red mb-4 mt-8">Ưu điểm nổi bật của đồng phục Axeron:</h3>
            <ul class="list-disc pl-6 space-y-3 mb-8">
                <li><strong>Chất liệu vải cao cấp:</strong> Thấm hút mồ hôi tốt, co giãn 4 chiều, độ bền màu cao, không gây kích ứng da, phù hợp với khí hậu Việt Nam.</li>
                <li><strong>Thiết kế năng động:</strong> Kiểu dáng hiện đại, thời trang nhưng vẫn đảm bảo tác phong học đường nghiêm túc.</li>
                <li><strong>Công nghệ in thêu tiên tiến:</strong> Logo trường sắc nét, không bong tróc sau nhiều lần giặt.</li>
                <li><strong>May đo chuẩn xác:</strong> Bảng size đa dạng, linh hoạt phù hợp với mọi thể trạng học sinh các cấp.</li>
                <li><strong>Năng lực sản xuất lớn:</strong> Đảm bảo tiến độ giao hàng đúng hạn cho các trường với số lượng hàng chục ngàn bộ trước thềm năm học mới.</li>
            </ul>

            <img src="https://images.unsplash.com/photo-1515523110800-9415d13b84a8?q=80&w=1200&auto=format&fit=crop" class="w-full rounded-2xl my-10 shadow-xl" alt="Sinh viên năng động trong trang phục thể thao">

            <p>Hàng loạt các trường đại học lớn và các hệ thống trường liên cấp quốc tế đã lựa chọn Axeron làm đơn vị cung cấp độc quyền đồng phục thể chất, góp phần xây dựng hình ảnh nhà trường chuyên nghiệp và hiện đại.</p>

            <div class="bg-surface-container-low p-6 rounded-xl mt-8 border-l-4 border-axeron-red text-center">
                <p class="font-semibold text-lg text-[#222] mb-0">Nhà trường có nhu cầu thiết kế và đặt may đồng phục thể dục, xin vui lòng liên hệ Axeron để được tư vấn thiết kế miễn phí!</p>
            </div>
        </article>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
