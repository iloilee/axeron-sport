<?php
require_once __DIR__ . '/../config/session.php';
$pageTitle = 'Hướng dẫn chọn size - Axeron Sport';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main Content Canvas -->
    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="font-display-lg text-display-lg md:text-display-lg text-on-background mb-4 uppercase">Hướng dẫn
                chọn size</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">
                Tìm kích thước hoàn hảo của bạn với hướng dẫn chi tiết từ Axeron Sport. Sự vừa vặn mang lại hiệu suất
                tối đa.
            </p>
        </div>
        <!-- Tabs Navigation -->
        <div class="flex justify-center border-b border-outline-variant mb-12">
            <div class="flex space-x-8">
                <button
                    class="font-headline-md text-headline-md pb-4 border-b-4 border-axeron-red text-axeron-red transition-colors hover:text-axeron-red focus:outline-none"
                    id="tab-shoes" onclick="switchTab('shoes')">
                    Giày
                </button>
                <button
                    class="font-headline-md text-headline-md pb-4 border-b-4 border-transparent text-on-surface-variant hover:text-on-surface transition-colors focus:outline-none"
                    id="tab-clothes" onclick="switchTab('clothes')">
                    Quần áo
                </button>
                <button
                    class="font-headline-md text-headline-md pb-4 border-b-4 border-transparent text-on-surface-variant hover:text-on-surface transition-colors focus:outline-none"
                    id="tab-accessories" onclick="switchTab('accessories')">
                    Phụ kiện
                </button>
            </div>
        </div>
        <!-- Tab Contents -->
        <!-- Shoes Tab -->
        <div class="space-y-12 block" id="content-shoes">
            <!-- Measuring Guide Section (Bento Grid Style) -->
            <section class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                <!-- Text / Instructions -->
                <div
                    class="md:col-span-5 bg-surface-container rounded-xl p-8 flex flex-col justify-center shadow-sm border border-outline-variant/30">
                    <h2 class="font-headline-lg text-headline-lg text-on-background mb-6">Cách Đo Chiều Dài Chân</h2>
                    <ol class="space-y-6">
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold">
                                1</div>
                            <div>
                                <h3 class="font-label-lg text-label-lg mb-1">Chuẩn bị</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant">Đặt một tờ giấy sát tường
                                    trên mặt phẳng cứng. Mang loại tất bạn dự định sử dụng với giày.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold">
                                2</div>
                            <div>
                                <h3 class="font-label-lg text-label-lg mb-1">Đánh dấu</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant">Đứng thẳng, gót chân chạm
                                    tường. Nhờ người khác đánh dấu điểm xa nhất của ngón chân dài nhất.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold">
                                3</div>
                            <div>
                                <h3 class="font-label-lg text-label-lg mb-1">Đo lường</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant">Đo khoảng cách từ mép giấy
                                    (phía tường) đến điểm đã đánh dấu bằng centimeter.</p>
                            </div>
                        </li>
                    </ol>
                </div>
                <!-- Illustration Image -->
                <div
                    class="md:col-span-7 bg-surface-container-low rounded-xl overflow-hidden shadow-sm border border-outline-variant/30 relative h-[400px]">
                    <img alt="Hướng dẫn đo chân" class="w-full h-full object-cover"
                        data-alt="A modern, minimalist top-down photo showing a person measuring their foot on a piece of white paper against a pristine white wall. The person is wearing sleek, athletic black socks. A sleek metallic ruler and a red pen rest nearby. The lighting is bright, natural studio light highlighting the crisp textures. The overall aesthetic is clean, clinical, and high-end sportswear, featuring deep blacks, stark whites, and a subtle accent of vibrant red."
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDoMZb4I0xoyWnZ7VvpZhqRdq7JfroRzpODpyZz6wPC78CZQWDMys9xRpg-h0ssHdZX96Opq9pZdGGIPJOto1g2qu6mwvi3q-1YAMoBtmj9f2CDpkMCAWjgY7AK5dNCHjJDhiubojydSDVhpq9PV3C_UX9vsNsZl75xrFW69QxESOcXpoVqgMLss0ZceRSLTvv2j3vgAYxeWAJXjAFZTJm1P9lHIT3gTTV19PgwEGIR_vBl9BBecFP5mZlA8nqbR58eYKq3134e65S8" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-end p-6">
                        <span
                            class="text-white font-label-lg text-label-lg bg-black/40 px-3 py-1 rounded-full backdrop-blur-sm">Hình
                            minh họa</span>
                    </div>
                </div>
            </section>
            <!-- Size Chart Table - Nam -->
            <section id="shoes-male-table" class="bg-surface rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden">
                <div
                    class="p-6 border-b border-outline-variant bg-surface-container-lowest flex justify-between items-center">
                    <h2 class="font-headline-md text-headline-md text-on-background">Bảng Quy Đổi Size Giày Nam</h2>
                    <button onclick="toggleShoeGender()" id="btn-shoe-gender"
                        class="flex items-center gap-2 text-axeron-blue hover:text-secondary-container transition-colors font-label-lg">
                        <span class="material-symbols-outlined text-[20px]">swap_horiz</span>
                        Đổi sang Nữ
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">CM (Chiều dài chân)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">EU</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">US</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">UK</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">25.0</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">40</td>
                                <td class="py-4 px-6 font-body-md text-body-md">7</td>
                                <td class="py-4 px-6 font-body-md text-body-md">6</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md">25.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">40.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">7.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">6.5</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">26.0</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">41</td>
                                <td class="py-4 px-6 font-body-md text-body-md">8</td>
                                <td class="py-4 px-6 font-body-md text-body-md">7</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md">26.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">42</td>
                                <td class="py-4 px-6 font-body-md text-body-md">8.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">7.5</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">27.0</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">42.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">9</td>
                                <td class="py-4 px-6 font-body-md text-body-md">8</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md">27.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">43</td>
                                <td class="py-4 px-6 font-body-md text-body-md">9.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">8.5</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">28.0</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">44</td>
                                <td class="py-4 px-6 font-body-md text-body-md">10</td>
                                <td class="py-4 px-6 font-body-md text-body-md">9</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- Size Chart Table - Nữ (Hidden by default) -->
            <section id="shoes-female-table" class="bg-surface rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden hidden">
                <div
                    class="p-6 border-b border-outline-variant bg-surface-container-lowest flex justify-between items-center">
                    <h2 class="font-headline-md text-headline-md text-on-background">Bảng Quy Đổi Size Giày Nữ</h2>
                    <button onclick="toggleShoeGender()" id="btn-shoe-gender-f"
                        class="flex items-center gap-2 text-axeron-blue hover:text-secondary-container transition-colors font-label-lg">
                        <span class="material-symbols-outlined text-[20px]">swap_horiz</span>
                        Đổi sang Nam
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">CM (Chiều dài chân)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">EU</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">US</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">UK</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">22.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">36</td>
                                <td class="py-4 px-6 font-body-md text-body-md">5.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">3</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md">23.0</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">36.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">6</td>
                                <td class="py-4 px-6 font-body-md text-body-md">3.5</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">23.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">37</td>
                                <td class="py-4 px-6 font-body-md text-body-md">6.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">4</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md">24.0</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">38</td>
                                <td class="py-4 px-6 font-body-md text-body-md">7</td>
                                <td class="py-4 px-6 font-body-md text-body-md">5</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">24.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">39</td>
                                <td class="py-4 px-6 font-body-md text-body-md">7.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">5.5</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md">25.0</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">40</td>
                                <td class="py-4 px-6 font-body-md text-body-md">8</td>
                                <td class="py-4 px-6 font-body-md text-body-md">6</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md">25.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">41</td>
                                <td class="py-4 px-6 font-body-md text-body-md">8.5</td>
                                <td class="py-4 px-6 font-body-md text-body-md">6.5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- Fit Advice -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                <div class="bg-surface-container p-6 rounded-xl border border-outline-variant/30 flex gap-4">
                    <span class="material-symbols-outlined text-axeron-red text-[32px] mt-1"
                        data-icon="info">info</span>
                    <div>
                        <h3 class="font-headline-md text-headline-md mb-2">Giày Chạy Bộ (Running)</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">Nên chọn lớn hơn 0.5 size so với
                            kích thước chân thực tế để tạo không gian cho bàn chân nở ra khi vận động mạnh và tránh tổn
                            thương ngón chân.</p>
                    </div>
                </div>
                <div class="bg-surface-container p-6 rounded-xl border border-outline-variant/30 flex gap-4">
                    <span class="material-symbols-outlined text-axeron-blue text-[32px] mt-1"
                        data-icon="lightbulb">lightbulb</span>
                    <div>
                        <h3 class="font-headline-md text-headline-md mb-2">Lời Khuyên Chung</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">Nếu chân bạn có bề ngang rộng (wide
                            fit), hãy cân nhắc tăng thêm 0.5 size. Đo chân vào cuối ngày khi bàn chân ở trạng thái nở to
                            nhất.</p>
                    </div>
                </div>
            </section>
        </div>
        <!-- Clothes Tab (Hidden by default) -->
        <div class="hidden space-y-12" id="content-clothes">
            <!-- Hướng dẫn đo -->
            <section class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                <div class="md:col-span-6 bg-surface-container rounded-xl p-8 flex flex-col justify-center shadow-sm border border-outline-variant/30">
                    <h2 class="font-headline-lg text-headline-lg text-on-background mb-6">Cách Đo Số Đo Cơ Thể</h2>
                    <ol class="space-y-5">
                        <li class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold">1</div>
                            <div>
                                <h3 class="font-label-lg text-label-lg mb-1">Vòng ngực</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant">Đo vòng quanh phần đầy nhất của ngực, giữ thước dây nằm ngang.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold">2</div>
                            <div>
                                <h3 class="font-label-lg text-label-lg mb-1">Vòng eo</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant">Đo vòng quanh phần nhỏ nhất của eo (thường ngang rốn).</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold">3</div>
                            <div>
                                <h3 class="font-label-lg text-label-lg mb-1">Vòng hông</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant">Đo vòng quanh phần rộng nhất của hông.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold">4</div>
                            <div>
                                <h3 class="font-label-lg text-label-lg mb-1">Chiều dài tay</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant">Đo từ đầu vai đến cổ tay, cánh tay hơi cong tự nhiên.</p>
                            </div>
                        </li>
                    </ol>
                </div>
                <div class="md:col-span-6 bg-surface-container-low rounded-xl p-8 shadow-sm border border-outline-variant/30">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="material-symbols-outlined text-axeron-red text-[32px]">straighten</span>
                        <h2 class="font-headline-lg text-headline-lg text-on-background">Mẹo Chọn Size Chuẩn</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-white p-4 rounded-lg border border-outline-variant/30">
                            <h3 class="font-label-lg text-label-lg text-axeron-red mb-1">Áo thể thao (Fit)</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Nếu bạn thích áo ôm vừa (Slim Fit), chọn đúng size. Nếu thích rộng thoải mái (Regular/Oversize), tăng 1 size.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-outline-variant/30">
                            <h3 class="font-label-lg text-label-lg text-axeron-blue mb-1">Quần thể thao</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Ưu tiên đo vòng eo và vòng hông. Nếu số đo nằm giữa 2 size, nên chọn size lớn hơn để thoải mái vận động.</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-outline-variant/30">
                            <h3 class="font-label-lg text-label-lg text-on-background mb-1">Chất liệu co giãn</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Các sản phẩm thun co giãn 4 chiều (Dri-FIT, Flex) có thể chọn đúng size hoặc giảm 1 size tùy sở thích.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bảng size Áo Nam -->
            <section id="clothes-male-table" class="bg-surface rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden">
                <div class="p-6 border-b border-outline-variant bg-surface-container-lowest flex justify-between items-center">
                    <h2 class="font-headline-md text-headline-md text-on-background">Bảng Size Áo & Quần Thể Thao Nam</h2>
                    <button onclick="toggleClothesGender()" id="btn-clothes-gender"
                        class="flex items-center gap-2 text-axeron-blue hover:text-secondary-container transition-colors font-label-lg">
                        <span class="material-symbols-outlined text-[20px]">swap_horiz</span>
                        Đổi sang Nữ
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Size</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Chiều cao (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Cân nặng (kg)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Vòng ngực (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Vòng eo (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Vòng hông (cm)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">S</td>
                                <td class="py-4 px-6 font-body-md text-body-md">160 - 165</td>
                                <td class="py-4 px-6 font-body-md text-body-md">55 - 62</td>
                                <td class="py-4 px-6 font-body-md text-body-md">86 - 90</td>
                                <td class="py-4 px-6 font-body-md text-body-md">72 - 76</td>
                                <td class="py-4 px-6 font-body-md text-body-md">88 - 92</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">M</td>
                                <td class="py-4 px-6 font-body-md text-body-md">165 - 170</td>
                                <td class="py-4 px-6 font-body-md text-body-md">62 - 70</td>
                                <td class="py-4 px-6 font-body-md text-body-md">90 - 96</td>
                                <td class="py-4 px-6 font-body-md text-body-md">76 - 82</td>
                                <td class="py-4 px-6 font-body-md text-body-md">92 - 96</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">L</td>
                                <td class="py-4 px-6 font-body-md text-body-md">170 - 175</td>
                                <td class="py-4 px-6 font-body-md text-body-md">70 - 78</td>
                                <td class="py-4 px-6 font-body-md text-body-md">96 - 102</td>
                                <td class="py-4 px-6 font-body-md text-body-md">82 - 88</td>
                                <td class="py-4 px-6 font-body-md text-body-md">96 - 100</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">XL</td>
                                <td class="py-4 px-6 font-body-md text-body-md">175 - 182</td>
                                <td class="py-4 px-6 font-body-md text-body-md">78 - 88</td>
                                <td class="py-4 px-6 font-body-md text-body-md">102 - 108</td>
                                <td class="py-4 px-6 font-body-md text-body-md">88 - 94</td>
                                <td class="py-4 px-6 font-body-md text-body-md">100 - 106</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">2XL</td>
                                <td class="py-4 px-6 font-body-md text-body-md">182 - 188</td>
                                <td class="py-4 px-6 font-body-md text-body-md">88 - 98</td>
                                <td class="py-4 px-6 font-body-md text-body-md">108 - 114</td>
                                <td class="py-4 px-6 font-body-md text-body-md">94 - 100</td>
                                <td class="py-4 px-6 font-body-md text-body-md">106 - 112</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- Bảng size Áo Nữ (Hidden by default) -->
            <section id="clothes-female-table" class="bg-surface rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden hidden">
                <div class="p-6 border-b border-outline-variant bg-surface-container-lowest flex justify-between items-center">
                    <h2 class="font-headline-md text-headline-md text-on-background">Bảng Size Áo & Quần Thể Thao Nữ</h2>
                    <button onclick="toggleClothesGender()" id="btn-clothes-gender-f"
                        class="flex items-center gap-2 text-axeron-blue hover:text-secondary-container transition-colors font-label-lg">
                        <span class="material-symbols-outlined text-[20px]">swap_horiz</span>
                        Đổi sang Nam
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Size</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Chiều cao (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Cân nặng (kg)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Vòng ngực (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Vòng eo (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Vòng hông (cm)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">XS</td>
                                <td class="py-4 px-6 font-body-md text-body-md">150 - 155</td>
                                <td class="py-4 px-6 font-body-md text-body-md">40 - 46</td>
                                <td class="py-4 px-6 font-body-md text-body-md">78 - 82</td>
                                <td class="py-4 px-6 font-body-md text-body-md">60 - 64</td>
                                <td class="py-4 px-6 font-body-md text-body-md">84 - 88</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">S</td>
                                <td class="py-4 px-6 font-body-md text-body-md">155 - 160</td>
                                <td class="py-4 px-6 font-body-md text-body-md">46 - 52</td>
                                <td class="py-4 px-6 font-body-md text-body-md">82 - 86</td>
                                <td class="py-4 px-6 font-body-md text-body-md">64 - 68</td>
                                <td class="py-4 px-6 font-body-md text-body-md">88 - 92</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">M</td>
                                <td class="py-4 px-6 font-body-md text-body-md">160 - 165</td>
                                <td class="py-4 px-6 font-body-md text-body-md">52 - 58</td>
                                <td class="py-4 px-6 font-body-md text-body-md">86 - 90</td>
                                <td class="py-4 px-6 font-body-md text-body-md">68 - 72</td>
                                <td class="py-4 px-6 font-body-md text-body-md">92 - 96</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">L</td>
                                <td class="py-4 px-6 font-body-md text-body-md">165 - 170</td>
                                <td class="py-4 px-6 font-body-md text-body-md">58 - 65</td>
                                <td class="py-4 px-6 font-body-md text-body-md">90 - 96</td>
                                <td class="py-4 px-6 font-body-md text-body-md">72 - 78</td>
                                <td class="py-4 px-6 font-body-md text-body-md">96 - 102</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">XL</td>
                                <td class="py-4 px-6 font-body-md text-body-md">170 - 175</td>
                                <td class="py-4 px-6 font-body-md text-body-md">65 - 72</td>
                                <td class="py-4 px-6 font-body-md text-body-md">96 - 102</td>
                                <td class="py-4 px-6 font-body-md text-body-md">78 - 84</td>
                                <td class="py-4 px-6 font-body-md text-body-md">102 - 108</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Lời khuyên quần áo -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                <div class="bg-surface-container p-6 rounded-xl border border-outline-variant/30 flex gap-4">
                    <span class="material-symbols-outlined text-axeron-red text-[32px] mt-1">checkroom</span>
                    <div>
                        <h3 class="font-headline-md text-headline-md mb-2">Áo Polo & Áo Thun</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">Nếu bạn thường mặc đồ thể thao khi tập luyện cường độ cao, nên chọn chất liệu thấm hút mồ hôi và size vừa vặn để không gây vướng víu.</p>
                    </div>
                </div>
                <div class="bg-surface-container p-6 rounded-xl border border-outline-variant/30 flex gap-4">
                    <span class="material-symbols-outlined text-axeron-blue text-[32px] mt-1">info</span>
                    <div>
                        <h3 class="font-headline-md text-headline-md mb-2">Quần Short & Quần Dài</h3>
                        <p class="font-body-md text-body-md text-on-surface-variant">Quần thể thao Axeron có lưng thun co giãn. Nếu số đo vòng eo của bạn nằm giữa 2 size, hãy chọn size nhỏ hơn để vừa khít hơn.</p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Accessories Tab (Hidden by default) -->
        <div class="hidden space-y-12" id="content-accessories">
            <!-- Bảng size Mũ -->
            <section class="bg-surface rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden">
                <div class="p-6 border-b border-outline-variant bg-surface-container-lowest flex items-center gap-4">
                    <span class="material-symbols-outlined text-axeron-red text-[28px]">checkroom</span>
                    <h2 class="font-headline-md text-headline-md text-on-background">Bảng Size Mũ / Nón Thể Thao</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Size</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Vòng đầu (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Đối tượng</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">S</td>
                                <td class="py-4 px-6 font-body-md text-body-md">54 - 56</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nữ / Thiếu niên</td>
                                <td class="py-4 px-6 font-body-md text-body-md text-on-surface-variant">Đầu nhỏ</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">M</td>
                                <td class="py-4 px-6 font-body-md text-body-md">56 - 58</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam / Nữ</td>
                                <td class="py-4 px-6 font-body-md text-body-md text-on-surface-variant">Phổ biến nhất</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">L</td>
                                <td class="py-4 px-6 font-body-md text-body-md">58 - 60</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam</td>
                                <td class="py-4 px-6 font-body-md text-body-md text-on-surface-variant">Đầu trung bình - lớn</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">XL</td>
                                <td class="py-4 px-6 font-body-md text-body-md">60 - 62</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam</td>
                                <td class="py-4 px-6 font-body-md text-body-md text-on-surface-variant">Đầu lớn</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Bảng size Găng tay -->
            <section class="bg-surface rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden">
                <div class="p-6 border-b border-outline-variant bg-surface-container-lowest flex items-center gap-4">
                    <span class="material-symbols-outlined text-axeron-blue text-[28px]">front_hand</span>
                    <h2 class="font-headline-md text-headline-md text-on-background">Bảng Size Găng Tay Thể Thao</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Size</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Chu vi bàn tay (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Chiều dài tay (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Đối tượng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">S</td>
                                <td class="py-4 px-6 font-body-md text-body-md">17 - 19</td>
                                <td class="py-4 px-6 font-body-md text-body-md">16 - 17</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nữ / Nam tay nhỏ</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">M</td>
                                <td class="py-4 px-6 font-body-md text-body-md">19 - 21</td>
                                <td class="py-4 px-6 font-body-md text-body-md">17 - 18</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam / Nữ tay lớn</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">L</td>
                                <td class="py-4 px-6 font-body-md text-body-md">21 - 23</td>
                                <td class="py-4 px-6 font-body-md text-body-md">18 - 19</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">XL</td>
                                <td class="py-4 px-6 font-body-md text-body-md">23 - 25</td>
                                <td class="py-4 px-6 font-body-md text-body-md">19 - 20</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam tay lớn</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Bảng size Tất -->
            <section class="bg-surface rounded-xl shadow-sm border border-outline-variant/50 overflow-hidden">
                <div class="p-6 border-b border-outline-variant bg-surface-container-lowest flex items-center gap-4">
                    <span class="material-symbols-outlined text-on-background text-[28px]">directions_run</span>
                    <h2 class="font-headline-md text-headline-md text-on-background">Bảng Size Tất Thể Thao</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low border-b border-outline-variant">
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Size tất</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Size giày tương ứng (EU)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Chiều dài chân (cm)</th>
                                <th class="py-4 px-6 font-label-lg text-label-lg text-on-surface-variant uppercase tracking-wider">Đối tượng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">S (35-38)</td>
                                <td class="py-4 px-6 font-body-md text-body-md">35 - 38</td>
                                <td class="py-4 px-6 font-body-md text-body-md">22 - 24</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nữ / Thiếu niên</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-lowest/50">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">M (39-42)</td>
                                <td class="py-4 px-6 font-body-md text-body-md">39 - 42</td>
                                <td class="py-4 px-6 font-body-md text-body-md">24 - 27</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam / Nữ chân lớn</td>
                            </tr>
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="py-4 px-6 font-body-md text-body-md font-bold text-on-background">L (43-46)</td>
                                <td class="py-4 px-6 font-body-md text-body-md">43 - 46</td>
                                <td class="py-4 px-6 font-body-md text-body-md">27 - 29</td>
                                <td class="py-4 px-6 font-body-md text-body-md">Nam chân lớn</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Lời khuyên phụ kiện -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
                <div class="bg-surface-container p-6 rounded-xl border border-outline-variant/30 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-axeron-red text-[40px] mb-3">checkroom</span>
                    <h3 class="font-headline-md text-headline-md mb-2">Mũ / Nón</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Đo vòng đầu bằng thước dây, đặt ngang trán phía trên tai khoảng 1cm. Hầu hết mũ Axeron có quai điều chỉnh.</p>
                </div>
                <div class="bg-surface-container p-6 rounded-xl border border-outline-variant/30 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-axeron-blue text-[40px] mb-3">front_hand</span>
                    <h3 class="font-headline-md text-headline-md mb-2">Găng Tay</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Đo chu vi bàn tay quanh phần rộng nhất (không tính ngón cái). Găng tay nên ôm sát nhưng không bó chặt.</p>
                </div>
                <div class="bg-surface-container p-6 rounded-xl border border-outline-variant/30 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-on-background text-[40px] mb-3">directions_run</span>
                    <h3 class="font-headline-md text-headline-md mb-2">Tất Thể Thao</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">Chọn tất theo size giày. Tất thể thao nên vừa khít, có đệm ở mũi và gót để giảm ma sát khi vận động.</p>
                </div>
            </section>
        </div>
    </main>
    <!-- SideNavBar (FAB/Support) -->
    
    <!-- Footer -->

<script>
        function switchTab(tabId) {
            // Hide all content
            document.getElementById('content-shoes').classList.add('hidden');
            document.getElementById('content-shoes').classList.remove('block');
            document.getElementById('content-clothes').classList.add('hidden');
            document.getElementById('content-clothes').classList.remove('block');
            document.getElementById('content-accessories').classList.add('hidden');
            document.getElementById('content-accessories').classList.remove('block');

            // Reset all tab styles
            const tabs = ['shoes', 'clothes', 'accessories'];
            tabs.forEach(t => {
                const el = document.getElementById('tab-' + t);
                el.className = 'font-headline-md text-headline-md pb-4 border-b-4 border-transparent text-on-surface-variant hover:text-on-surface transition-colors focus:outline-none';
            });

            // Show selected content and style selected tab
            document.getElementById('content-' + tabId).classList.remove('hidden');
            document.getElementById('content-' + tabId).classList.add('block');

            const activeTab = document.getElementById('tab-' + tabId);
            activeTab.className = 'font-headline-md text-headline-md pb-4 border-b-4 border-axeron-red text-axeron-red transition-colors hover:text-axeron-red focus:outline-none';
        }

        // Toggle giữa bảng size giày Nam / Nữ
        function toggleShoeGender() {
            const maleTable = document.getElementById('shoes-male-table');
            const femaleTable = document.getElementById('shoes-female-table');
            if (maleTable.classList.contains('hidden')) {
                maleTable.classList.remove('hidden');
                femaleTable.classList.add('hidden');
            } else {
                maleTable.classList.add('hidden');
                femaleTable.classList.remove('hidden');
            }
        }

        // Toggle giữa bảng size quần áo Nam / Nữ
        function toggleClothesGender() {
            const maleTable = document.getElementById('clothes-male-table');
            const femaleTable = document.getElementById('clothes-female-table');
            if (maleTable.classList.contains('hidden')) {
                maleTable.classList.remove('hidden');
                femaleTable.classList.add('hidden');
            } else {
                maleTable.classList.add('hidden');
                femaleTable.classList.remove('hidden');
            }
        }
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
