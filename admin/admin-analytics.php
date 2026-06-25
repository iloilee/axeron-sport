<?php
/**
 * Analytics Dashboard - Axeron Sports Shop
 * Trang thống kê và báo cáo
 */
?>
<!-- Analytics Dashboard Content -->
<div class="space-y-4">



    <!-- Smart Insights Alerts (AI Suggestions) -->
    <div class="bg-gradient-to-r from-red-50 via-rose-50 to-red-50 rounded-xl p-4 shadow-sm border border-red-100 flex items-start gap-3">
        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shrink-0 shadow-sm">
            <span class="material-symbols-outlined text-axeron-red animate-pulse">tips_and_updates</span>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-red-900 flex items-center gap-2">
                Smart Insights <span class="bg-red-100 text-red-700 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Tự động</span>
            </h4>
            <div class="mt-1 text-sm text-red-800 space-y-1" id="smart-insights-content">
                <div class="flex items-center gap-2 animate-pulse">
                    <div class="h-4 bg-red-200 rounded w-3/4"></div>
                </div>
                <div class="flex items-center gap-2 animate-pulse">
                    <div class="h-4 bg-red-200 rounded w-1/2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl p-4 shadow-sm border">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Period Filter -->
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500 font-medium">Khoảng thời gian</label>
                <select id="filter-period" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent" onchange="handlePeriodChange()">
                    <option value="month">Theo tháng</option>
                    <option value="quarter">Theo quý</option>
                    <option value="year">Theo năm</option>
                    <option value="all">Tất cả thời gian</option>
                </select>
            </div>

            <!-- Month Selector -->
            <div class="flex flex-col gap-1" id="month-filter-container">
                <label class="text-xs text-gray-500 font-medium">Tháng</label>
                <select id="filter-month" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent" onchange="loadData()">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Year Selector (for month/quarter/year view) -->
            <div class="flex flex-col gap-1" id="year-filter-container">
                <label class="text-xs text-gray-500 font-medium">Năm</label>
                <select id="filter-year" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent" onchange="loadData()">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Quarter Selector -->
            <div class="flex flex-col gap-1 hidden" id="quarter-filter-container">
                <label class="text-xs text-gray-500 font-medium">Quý</label>
                <select id="filter-quarter" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent" onchange="loadData()">
                    <option value="1">Quý 1</option>
                    <option value="2">Quý 2</option>
                    <option value="3">Quý 3</option>
                    <option value="4">Quý 4</option>
                </select>
            </div>



            <!-- Tab Navigation (Moved) -->
            <div class="flex-1 flex justify-center">
                <div class="flex gap-2">
                    <button class="tab-btn px-3 py-2 font-medium text-sm transition-all border rounded-lg hover:text-axeron-red whitespace-nowrap flex-shrink-0 <?= ($tab ?? 'revenue') === 'revenue' ? 'active border-axeron-red text-axeron-red' : 'border-transparent text-gray-500 hover:bg-gray-50' ?>" data-tab="revenue" onclick="switchTab('revenue')">
                        <span class="material-symbols-outlined text-lg align-middle mr-1">trending_up</span>
                        Doanh Thu
                    </button>
                    <button class="tab-btn px-3 py-2 font-medium text-sm transition-all border rounded-lg hover:text-axeron-red whitespace-nowrap flex-shrink-0 <?= ($tab ?? '') === 'customers' ? 'active border-axeron-red text-axeron-red' : 'border-transparent text-gray-500 hover:bg-gray-50' ?>" data-tab="customers" onclick="switchTab('customers')">
                        <span class="material-symbols-outlined text-lg align-middle mr-1">people</span>
                        Khách Hàng
                    </button>
                    <button class="tab-btn px-3 py-2 font-medium text-sm transition-all border rounded-lg hover:text-axeron-red whitespace-nowrap flex-shrink-0 <?= ($tab ?? '') === 'products' ? 'active border-axeron-red text-axeron-red' : 'border-transparent text-gray-500 hover:bg-gray-50' ?>" data-tab="products" onclick="switchTab('products')">
                        <span class="material-symbols-outlined text-lg align-middle mr-1">inventory_2</span>
                        Sản Phẩm
                    </button>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                <button onclick="exportData('excel')" class="flex items-center gap-1.5 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap flex-shrink-0 text-sm">
                    <span class="material-symbols-outlined text-lg">table_chart</span>
                    Xuất Excel
                </button>
                <button onclick="exportData('pdf')" class="flex items-center gap-1.5 px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors whitespace-nowrap flex-shrink-0 text-sm">
                    <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                    Xuất PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards Revenue -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3" id="summary-cards-revenue">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between gap-1">
                <div class="flex-1 min-w-0">
                    <p class="text-gray-500 text-xs sm:text-sm">Tổng Doanh Thu</p>
                    <p class="text-base sm:text-lg font-bold tracking-tight text-green-600 mt-1" id="summary-revenue">--</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-green-600">payments</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Tổng Đơn Hàng</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1" id="summary-orders">--</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600">receipt_long</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between gap-1">
                <div class="flex-1 min-w-0">
                    <p class="text-gray-500 text-xs sm:text-sm">Giá Trị TB/Đơn</p>
                    <p class="text-base sm:text-lg font-bold tracking-tight text-purple-600 mt-1" id="summary-aov">--</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-purple-600">shopping_bag</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Khách Hàng</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1" id="summary-customers">--</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-600">people</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Tỷ lệ chuyển đổi</p>
                    <p class="text-2xl font-bold text-teal-600 mt-1" id="summary-conversion">--</p>
                </div>
                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-teal-600">insights</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards Customers -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 hidden" id="summary-cards-customers">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Tổng khách hàng</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1" id="customer-total">--</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-indigo-600">group</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">KH Mới (30 ngày)</p>
                    <p class="text-2xl font-bold text-green-600 mt-1" id="customer-new">--</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600">person_add</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Khách VIP</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1" id="customer-vip">--</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-yellow-600">workspace_premium</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Khách quay lại</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1" id="customer-returning">--</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600">repeat</span>
                </div>
            </div>
        </div>


    </div>

    <!-- Summary Cards Products -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 hidden" id="summary-cards-products">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Tổng SP bán ra</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1" id="product-total-sold">--</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600">inventory_2</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Tổng doanh thu</p>
                    <p class="text-2xl font-bold text-green-600 mt-1" id="product-total-revenue">--</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600">payments</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">SP bán chạy nhất</p>
                    <p class="text-lg font-bold text-orange-600 mt-1 truncate max-w-[160px]" id="product-best-seller" title="">--</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-600">local_fire_department</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">SP bán chậm</p>
                    <p class="text-2xl font-bold text-red-500 mt-1" id="product-slow-count">--</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-500">trending_down</span>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Tổng lượt xem</p>
                    <p class="text-2xl font-bold text-gray-600 mt-1" id="product-total-views">--</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-gray-600">visibility</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Area (Revenue Tab) -->
    <div class="bg-white rounded-xl p-6 shadow-sm border" id="chart-container-revenue">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-lg">Xu Hướng Doanh Thu</h3>
            <div class="flex gap-2">
                <button class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50" id="chart-bar" onclick="updateChartType('bar')">
                    <span class="material-symbols-outlined text-base">bar_chart</span>
                </button>
                <button class="px-3 py-1 text-sm rounded-lg border hover:bg-gray-50" id="chart-line" onclick="updateChartType('line')">
                    <span class="material-symbols-outlined text-base">show_chart</span>
                </button>
            </div>
        </div>
        <div class="h-80">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Chart Area (Customers Tab) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 hidden" id="chart-container-customers">
        <div class="bg-white rounded-xl p-6 shadow-sm border lg:col-span-2">
            <h3 class="font-bold text-lg mb-4">Top 10 Khách Hàng Doanh Thu</h3>
            <div class="h-64">
                <canvas id="customerBarChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border">
            <h3 class="font-bold text-lg mb-4">Phân loại Khách hàng</h3>
            <div class="h-64 flex justify-center">
                <canvas id="customerPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart Area (Products Tab) -->
    <div class="space-y-6 hidden" id="chart-container-products">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <h3 class="font-bold text-lg mb-4">Top 10 Doanh Thu Sản Phẩm</h3>
                <div class="h-72">
                    <canvas id="productRevenueChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <h3 class="font-bold text-lg mb-4">Top 10 Bán Chạy</h3>
                <div class="h-72">
                    <canvas id="productSellersChart"></canvas>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border">
            <h3 class="font-bold text-lg mb-4">Top 10 Xem Nhiều</h3>
            <div class="h-64">
                <canvas id="productViewChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <!-- Table Header with Search -->
        <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="font-bold text-lg" id="table-title">Dữ liệu chi tiết</h3>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none">
                    <input type="text" id="table-search" placeholder="Tìm kiếm..." class="pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent w-full sm:w-64" onkeyup="handleSearch()">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                </div>
                
                <!-- Rank Filter (Customers only) -->
                <select id="filter-rank" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red hidden w-full sm:w-auto" onchange="loadData()">
                    <option value="">Tất cả hạng (Khách hàng)</option>
                    <option value="VIP">VIP</option>
                    <option value="Tiềm năng">Tiềm năng</option>
                    <option value="Mới">Khách mới</option>
                    <option value="Bình thường">Bình thường</option>
                </select>

                <!-- Product Filters (Products only) -->
                <select id="filter-category" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red hidden w-full sm:w-auto" onchange="loadData()">
                    <option value="">Tất cả danh mục</option>
                    <!-- Populated by JS -->
                </select>
                <select id="filter-brand" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red hidden w-full sm:w-auto" onchange="loadData()">
                    <option value="">Tất cả thương hiệu</option>
                    <!-- Populated by JS -->
                </select>
                <select id="filter-stock" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red hidden w-full sm:w-auto" onchange="loadData()">
                    <option value="">Tất cả tồn kho</option>
                    <option value="available">Còn hàng (>10)</option>
                    <option value="low">Sắp hết (1-10)</option>
                    <option value="out">Hết hàng (0)</option>
                </select>
                <select id="filter-performance" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red hidden w-full sm:w-auto" onchange="loadData()">
                    <option value="">Tất cả hiệu suất</option>
                    <option value="hot">🔥 Best Seller</option>
                    <option value="trending">🚀 Trending</option>
                    <option value="cold">📉 Bán chậm</option>
                </select>
                <select id="table-sort" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red w-full sm:w-auto" onchange="handleSort()">
                    <option value="total_spent">Sắp xếp theo</option>
                    <option value="total_spent">Chi tiêu</option>
                    <option value="total_orders">Số đơn</option>
                    <option value="full_name">Tên</option>
                </select>
            </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full" id="analytics-table">
                <thead class="bg-gray-50">
                    <tr id="table-header">
                        <!-- Dynamic headers -->
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="table-body">
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            <span class="material-symbols-outlined text-4xl text-gray-300">hourglass_empty</span>
                            <p class="mt-2">Đang tải dữ liệu...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-100 flex justify-between items-center" id="pagination-container">
            <div class="text-sm text-gray-500" id="pagination-info">
                Hiển thị 0 kết quả
            </div>
            <div class="flex gap-2" id="pagination-controls">
                <!-- Dynamic pagination -->
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for export -->
<div id="export-data" style="display: none;">
    <table id="export-table">
        <!-- Populated by JS -->
    </table>
</div>

<script>
    // Base URL for API calls
    const BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
<script src="<?= BASE_URL ?>/js/admin-analytics.js?v=<?= time() + 8 ?>"></script>
