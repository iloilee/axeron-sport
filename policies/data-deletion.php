<?php
/**
 * Data Deletion Instructions (Dành cho Facebook Login)
 * Đường dẫn này sẽ được điền vào mục "User Data Deletion" trong Facebook App Dashboard.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Hướng dẫn xóa dữ liệu cá nhân - Axeron Sport</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "axeron-red": "#BE1E2D",
                        "surface": "#fcf9f8",
                        "on-surface": "#1b1c1c",
                        "on-surface-variant": "#5b403f",
                        "outline-variant": "#e3bebb",
                    },
                    fontFamily: { 
                        "headline-lg": ["Montserrat", "sans-serif"], 
                        "body-md": ["Noto Sans", "sans-serif"] 
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="flex-grow w-full max-w-4xl mx-auto px-4 py-12">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant p-8 md:p-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 text-axeron-red mb-6 border border-red-100">
                <span class="material-symbols-outlined text-3xl">delete_forever</span>
            </div>
            
            <h1 class="font-headline-lg text-3xl md:text-4xl font-bold text-gray-900 mb-6" style="font-family: 'Montserrat', sans-serif;">
                Hướng dẫn xóa dữ liệu cá nhân
            </h1>
            
            <div class="prose prose-red max-w-none text-gray-600 space-y-6">
                <p class="text-lg">
                    Axeron Sport tôn trọng quyền riêng tư của bạn. Nếu bạn đã đăng nhập vào hệ thống của chúng tôi bằng tài khoản Facebook và muốn xóa toàn bộ dữ liệu cá nhân (bao gồm tài khoản, email, hình ảnh, thông tin liên hệ), vui lòng làm theo các bước sau:
                </p>

                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-4" style="font-family: 'Montserrat', sans-serif;">Cách 1: Xóa trực tiếp trên website (Khuyến nghị)</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Đăng nhập vào tài khoản của bạn trên website Axeron Sport.</li>
                        <li>Truy cập vào trang <a href="<?= BASE_URL ?>/auth/account.php" class="text-axeron-red hover:underline font-medium">Tài khoản của tôi</a>.</li>
                        <li>Cuộn xuống dưới cùng, tìm khu vực <strong>Xóa Tài Khoản</strong>.</li>
                        <li>Bấm vào nút <strong>Xóa tài khoản</strong> và xác nhận để hoàn tất.</li>
                    </ol>
                    <p class="mt-4 text-sm text-gray-500 italic">
                        *Hành động này sẽ ngay lập tức vô hiệu hóa tài khoản và ẩn danh hóa toàn bộ thông tin cá nhân của bạn khỏi hệ thống.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-4" style="font-family: 'Montserrat', sans-serif;">Cách 2: Gỡ ứng dụng khỏi Facebook</h3>
                    <p class="mb-3 text-gray-700">Bạn cũng có thể gỡ liên kết giữa Facebook và Axeron Sport bằng cách:</p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Vào <strong>Cài đặt & Quyền riêng tư</strong> > <strong>Cài đặt</strong> trên Facebook.</li>
                        <li>Tìm mục <strong>Ứng dụng và trang web</strong>.</li>
                        <li>Tìm ứng dụng "Axeron Sport" và bấm <strong>Gỡ</strong>.</li>
                    </ol>
                    <p class="mt-4 text-sm text-gray-500">
                        Lưu ý: Việc gỡ ứng dụng trên Facebook sẽ ngăn chúng tôi truy cập thêm dữ liệu mới, nhưng để xóa dữ liệu cũ đã lưu, bạn vui lòng sử dụng Cách 1.
                    </p>
                </div>

                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Bạn cần hỗ trợ thêm?</h3>
                    <p>
                        Nếu bạn gặp bất kỳ khó khăn nào trong quá trình xóa dữ liệu, vui lòng liên hệ với chúng tôi qua email: <a href="mailto:support@axeronsport.xyz" class="text-axeron-red hover:underline font-medium">support@axeronsport.xyz</a>. Chúng tôi sẽ hỗ trợ xóa dữ liệu của bạn trong vòng 24-48 giờ làm việc.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
