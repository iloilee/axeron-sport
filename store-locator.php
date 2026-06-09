<?php
require_once __DIR__ . '/config/session.php';
$pageTitle = 'Hệ thống cửa hàng - Axeron Sport';
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 md:py-12">
        <div class="mb-12 text-center">
            <h1
                class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-text-dark mb-4">
                HỆ THỐNG CỬA HÀNG</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Tìm kiếm cửa hàng Axeron gần
                bạn nhất để trải nghiệm những sản phẩm thể thao đẳng cấp chuyên nghiệp.</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 h-auto lg:h-[800px]">
            <!-- Store List Sidebar -->
            <div
                class="lg:col-span-4 flex flex-col h-full bg-surface-container-lowest rounded-xl border border-surface-variant overflow-hidden shadow-sm">
                <!-- Search & Filter -->
                <div class="p-6 border-b border-surface-variant bg-surface-container-low">
                    <div class="relative mb-4">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                        <input
                            class="w-full pl-10 pr-4 py-3 rounded-lg border border-outline-variant focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue font-body-md text-body-md bg-white outline-none transition-all"
                            placeholder="Tìm kiếm địa chỉ, quận, huyện..." type="text" />
                    </div>
                    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                        <button
                            class="px-4 py-2 rounded-full bg-axeron-red text-white font-label-sm text-label-sm whitespace-nowrap transition-colors">Miền
                            Bắc</button>
                        <button
                            class="px-4 py-2 rounded-full bg-surface text-on-surface border border-outline-variant hover:bg-surface-variant font-label-sm text-label-sm whitespace-nowrap transition-colors">Miền
                            Trung</button>
                        <button
                            class="px-4 py-2 rounded-full bg-surface text-on-surface border border-outline-variant hover:bg-surface-variant font-label-sm text-label-sm whitespace-nowrap transition-colors">Miền
                            Nam</button>
                    </div>
                </div>
                <!-- Store List -->
                <div class="flex-grow overflow-y-auto">
                    <!-- Store Item 1 (Active) -->
                    <div
                        class="p-6 border-b border-surface-variant bg-surface-container hover:bg-surface-container-high transition-colors cursor-pointer border-l-4 border-l-axeron-red">
                        <h3 class="font-headline-md text-headline-md text-text-dark text-lg mb-2">Axeron Flagship Store
                            Hà Nội</h3>
                        <div class="flex items-start gap-3 text-on-surface-variant mb-2">
                            <span
                                class="material-symbols-outlined text-[20px] shrink-0 mt-0.5 text-axeron-red">location_on</span>
                            <p class="font-body-md text-body-md text-sm">123 Phố Huế, Quận Hai Bà Trưng, Hà Nội</p>
                        </div>
                        <div class="flex items-center gap-3 text-on-surface-variant mb-2">
                            <span class="material-symbols-outlined text-[20px] shrink-0 text-axeron-blue">call</span>
                            <p class="font-body-md text-body-md text-sm">1900 1234 - Ext 1</p>
                        </div>
                        <div class="flex items-center gap-3 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[20px] shrink-0 text-tertiary">schedule</span>
                            <p class="font-body-md text-body-md text-sm">08:00 - 22:00 (Thứ 2 - CN)</p>
                        </div>
                        <button
                            class="mt-4 text-axeron-blue font-label-sm text-label-sm flex items-center gap-1 hover:underline">
                            <span class="material-symbols-outlined text-[16px]">directions</span> Chỉ đường
                        </button>
                    </div>
                    <!-- Store Item 2 -->
                    <div
                        class="p-6 border-b border-surface-variant hover:bg-surface-container transition-colors cursor-pointer border-l-4 border-l-transparent">
                        <h3 class="font-headline-md text-headline-md text-text-dark text-lg mb-2">Axeron Lotte Center
                        </h3>
                        <div class="flex items-start gap-3 text-on-surface-variant mb-2">
                            <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">location_on</span>
                            <p class="font-body-md text-body-md text-sm">Tầng 3 Lotte Center, 54 Liễu Giai, Ba Đình, Hà
                                Nội</p>
                        </div>
                        <div class="flex items-center gap-3 text-on-surface-variant mb-2">
                            <span class="material-symbols-outlined text-[20px] shrink-0">call</span>
                            <p class="font-body-md text-body-md text-sm">1900 1234 - Ext 2</p>
                        </div>
                        <div class="flex items-center gap-3 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[20px] shrink-0">schedule</span>
                            <p class="font-body-md text-body-md text-sm">09:30 - 22:00 (Thứ 2 - CN)</p>
                        </div>
                    </div>
                    <!-- Store Item 3 -->
                    <div
                        class="p-6 border-b border-surface-variant hover:bg-surface-container transition-colors cursor-pointer border-l-4 border-l-transparent">
                        <h3 class="font-headline-md text-headline-md text-text-dark text-lg mb-2">Axeron Vincom Royal
                            City</h3>
                        <div class="flex items-start gap-3 text-on-surface-variant mb-2">
                            <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">location_on</span>
                            <p class="font-body-md text-body-md text-sm">B1-R4, Vincom Mega Mall Royal City, 72A Nguyễn
                                Trãi, Thanh Xuân, Hà Nội</p>
                        </div>
                        <div class="flex items-center gap-3 text-on-surface-variant mb-2">
                            <span class="material-symbols-outlined text-[20px] shrink-0">call</span>
                            <p class="font-body-md text-body-md text-sm">1900 1234 - Ext 3</p>
                        </div>
                        <div class="flex items-center gap-3 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[20px] shrink-0">schedule</span>
                            <p class="font-body-md text-body-md text-sm">09:30 - 22:00 (Thứ 2 - CN)</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Map View -->
            <div
                class="lg:col-span-8 bg-surface-container-high rounded-xl overflow-hidden shadow-sm relative min-h-[400px] lg:min-h-full">
                <!-- Fallback image representing a map for the prompt -->
                <img alt="Google Maps View" class="w-full h-full object-cover grayscale opacity-80"
                    data-alt="A highly detailed top-down view of a modern digital map interface displaying city streets and points of interest. The map uses a clean, light-mode color palette with soft whites, pale grays, and subtle blue accents for water bodies. Prominent red map marker pins indicate specific store locations, adding sharp contrast to the neutral background. The aesthetic is professional, clear, and highly functional, typical of a corporate store locator tool."
                    data-location="Hanoi, Vietnam"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCihwpXhZdysU0QKBPvCcFpStYY-4TtfEbiXzyVhofFkLi8T8M81gzxjKEN5ybbQfS8ePAafXdjaVNUzrc6xfZngITaPeRa5sWYcwg6Tpf2wGozkmHUKMRm-nE7zlDovSX400g1kncqxvDE1R9KcOUtFCk3dbz2yUvWcLXCVcf9Wt6v_ArpREPP3jgedpCs38rQcrH19HXX3vjRH9zPLyIvG8lQgofRbzuEtwBLuZVwUqVkR9NMVW3-Q8J76doCfa3DczI39PXiOoZM" />
                <!-- Mock Map Marker Overlay -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
                    <div class="bg-axeron-red text-white p-2 rounded-full shadow-md animate-bounce">
                        <span class="material-symbols-outlined"
                            style="font-variation-settings: 'FILL' 1;">location_on</span>
                    </div>
                    <div
                        class="bg-white px-4 py-2 rounded-lg shadow-lg mt-2 flex flex-col items-center pointer-events-none">
                        <span class="font-label-lg text-label-lg text-text-dark whitespace-nowrap">Axeron Flagship
                            Store</span>
                        <span class="font-label-sm text-label-sm text-axeron-red">123 Phố Huế</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
