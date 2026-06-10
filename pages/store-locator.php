<?php
require_once dirname(__DIR__) . '/config/session.php';
$pageTitle = 'Hệ thống cửa hàng - Axeron Sport';
require_once dirname(__DIR__) . '/includes/head.php';
require_once dirname(__DIR__) . '/includes/header.php';

$storesData = [
    [
        "id" => 1,
        "name" => "Axeron Flagship Store HCM",
        "address" => "123 Nguyễn Trãi, Quận 1, TP.HCM",
        "phone" => "1900 1234 - Ext 1",
        "hours" => "08:00 - 22:00 (Thứ 2 - CN)",
        "region" => "Nam",
        "mapUrl" => "https://maps.google.com/maps?q=" . urlencode("123 Nguyễn Trãi, Quận 1, TP.HCM") . "&t=&z=15&ie=UTF8&iwloc=&output=embed"
    ],
    [
        "id" => 2,
        "name" => "Axeron Flagship Store Hà Nội",
        "address" => "123 Phố Huế, Quận Hai Bà Trưng, Hà Nội",
        "phone" => "1900 1234 - Ext 2",
        "hours" => "08:00 - 22:00 (Thứ 2 - CN)",
        "region" => "Bắc",
        "mapUrl" => "https://maps.google.com/maps?q=" . urlencode("123 Phố Huế, Hà Nội") . "&t=&z=15&ie=UTF8&iwloc=&output=embed"
    ],
    [
        "id" => 3,
        "name" => "Axeron Lotte Center",
        "address" => "Tầng 3 Lotte Center, 54 Liễu Giai, Ba Đình, Hà Nội",
        "phone" => "1900 1234 - Ext 3",
        "hours" => "09:30 - 22:00 (Thứ 2 - CN)",
        "region" => "Bắc",
        "mapUrl" => "https://maps.google.com/maps?q=" . urlencode("Lotte Center Hanoi") . "&t=&z=15&ie=UTF8&iwloc=&output=embed"
    ],
    [
        "id" => 4,
        "name" => "Axeron Vincom Đà Nẵng",
        "address" => "910A Ngô Quyền, Sơn Trà, Đà Nẵng",
        "phone" => "1900 1234 - Ext 4",
        "hours" => "09:00 - 21:30 (Thứ 2 - CN)",
        "region" => "Trung",
        "mapUrl" => "https://maps.google.com/maps?q=" . urlencode("Vincom Plaza Ngô Quyền Đà Nẵng") . "&t=&z=15&ie=UTF8&iwloc=&output=embed"
    ]
];
?>

<main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 md:py-12">
    <div class="mb-12 text-center">
        <h1 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-text-dark mb-4">
            HỆ THỐNG CỬA HÀNG</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">Tìm kiếm cửa hàng Axeron gần
            bạn nhất để trải nghiệm những sản phẩm thể thao đẳng cấp chuyên nghiệp.</p>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 h-auto lg:h-[800px]">
        <!-- Store List Sidebar -->
        <div class="lg:col-span-4 flex flex-col h-full bg-surface-container-lowest rounded-xl border border-surface-variant overflow-hidden shadow-sm">
            <!-- Search & Filter -->
            <div class="p-6 border-b border-surface-variant bg-surface-container-low">
                <div class="relative mb-4">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                    <input id="searchInput"
                        class="w-full pl-10 pr-4 py-3 rounded-lg border border-outline-variant focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue font-body-md text-body-md bg-white outline-none transition-all"
                        placeholder="Tìm kiếm địa chỉ, quận, huyện..." type="text" />
                </div>
                <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide" id="regionFilters">
                    <button data-region="" class="filter-btn px-4 py-2 rounded-full bg-axeron-red text-white font-label-sm text-label-sm whitespace-nowrap transition-colors">Tất cả</button>
                    <button data-region="Bắc" class="filter-btn px-4 py-2 rounded-full bg-surface text-on-surface border border-outline-variant hover:bg-surface-variant font-label-sm text-label-sm whitespace-nowrap transition-colors">Miền Bắc</button>
                    <button data-region="Trung" class="filter-btn px-4 py-2 rounded-full bg-surface text-on-surface border border-outline-variant hover:bg-surface-variant font-label-sm text-label-sm whitespace-nowrap transition-colors">Miền Trung</button>
                    <button data-region="Nam" class="filter-btn px-4 py-2 rounded-full bg-surface text-on-surface border border-outline-variant hover:bg-surface-variant font-label-sm text-label-sm whitespace-nowrap transition-colors">Miền Nam</button>
                </div>
            </div>
            <!-- Store List -->
            <div id="storeListContainer" class="flex-grow overflow-y-auto">
                <!-- Stores will be rendered here by JS -->
            </div>
        </div>
        
        <!-- Map View -->
        <div class="lg:col-span-8 bg-surface-container-high rounded-xl overflow-hidden shadow-sm relative min-h-[400px] lg:min-h-full">
            <iframe id="mapIframe" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?= $storesData[0]['mapUrl'] ?>"></iframe>
        </div>
    </div>
</main>

<script>
    const stores = <?= json_encode($storesData) ?>;
    let currentRegion = "";
    let searchQuery = "";
    let activeStoreId = stores[0].id;

    const storeListContainer = document.getElementById('storeListContainer');
    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const mapIframe = document.getElementById('mapIframe');

    function renderStores() {
        storeListContainer.innerHTML = '';
        
        const filteredStores = stores.filter(store => {
            const matchesRegion = currentRegion === "" || store.region === currentRegion;
            const matchesSearch = store.name.toLowerCase().includes(searchQuery) || 
                                  store.address.toLowerCase().includes(searchQuery);
            return matchesRegion && matchesSearch;
        });

        if (filteredStores.length === 0) {
            storeListContainer.innerHTML = '<div class="p-6 text-center text-on-surface-variant">Không tìm thấy cửa hàng nào phù hợp.</div>';
            return;
        }

        filteredStores.forEach(store => {
            const isActive = store.id === activeStoreId;
            const activeClasses = isActive 
                ? 'bg-surface-container-high border-l-axeron-red' 
                : 'bg-surface-container border-l-transparent hover:bg-surface-container-high';
            
            const directionsUrl = `https://maps.google.com/?q=${encodeURIComponent(store.address)}`;
            const telLink = store.phone.replace(/[^0-9]/g, ''); // Basic clean for tel protocol
            
            const storeHtml = `
                <div class="p-6 border-b border-surface-variant transition-colors cursor-pointer border-l-4 ${activeClasses}" onclick="selectStore(${store.id})">
                    <h3 class="font-headline-md text-headline-md text-text-dark text-lg mb-2">${store.name}</h3>
                    <div class="flex items-start gap-3 text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5 ${isActive ? 'text-axeron-red' : ''}">location_on</span>
                        <p class="font-body-md text-body-md text-sm">${store.address}</p>
                    </div>
                    <div class="flex items-center gap-3 text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[20px] shrink-0 ${isActive ? 'text-axeron-blue' : ''}">call</span>
                        <p class="font-body-md text-body-md text-sm">
                            <a href="tel:${telLink}" class="hover:text-axeron-blue transition-colors" onclick="event.stopPropagation()">${store.phone}</a>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-on-surface-variant mb-2">
                        <span class="material-symbols-outlined text-[20px] shrink-0 text-tertiary">schedule</span>
                        <p class="font-body-md text-body-md text-sm">${store.hours}</p>
                    </div>
                    <a href="${directionsUrl}" target="_blank" onclick="event.stopPropagation()" class="mt-4 text-axeron-blue font-label-sm text-label-sm flex items-center gap-1 hover:underline inline-flex">
                        <span class="material-symbols-outlined text-[16px]">directions</span> Chỉ đường
                    </a>
                </div>
            `;
            storeListContainer.insertAdjacentHTML('beforeend', storeHtml);
        });
    }

    function selectStore(id) {
        activeStoreId = id;
        const store = stores.find(s => s.id === id);
        if (store) {
            mapIframe.src = store.mapUrl;
        }
        renderStores(); // Re-render to update active styling
    }

    searchInput.addEventListener('input', (e) => {
        searchQuery = e.target.value.toLowerCase();
        renderStores();
    });

    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Update active state of buttons
            filterBtns.forEach(b => {
                b.classList.remove('bg-axeron-red', 'text-white');
                b.classList.add('bg-surface', 'text-on-surface');
            });
            e.target.classList.remove('bg-surface', 'text-on-surface');
            e.target.classList.add('bg-axeron-red', 'text-white');

            currentRegion = e.target.getAttribute('data-region');
            renderStores();
        });
    });

    // Initial render
    renderStores();
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
</body>
</html>
