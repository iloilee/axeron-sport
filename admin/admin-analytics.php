<?php
/**
 * Analytics Dashboard - Axeron Sports Shop
 * Trang thống kê và báo cáo
 */
?>
<!-- Analytics Dashboard Content -->
<div class="space-y-4">




    <!-- Filter Bar -->
    <div class="bg-white rounded-xl p-4 shadow-sm border">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Period Filter -->
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-500 font-medium">Khoảng thời gian</label>
                <select id="filter-period" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent" onchange="handlePeriodChange()">
                    <option value="month">Tháng này</option>
                    <option value="quarter">Quý này</option>
                    <option value="year">Năm nay</option>
                    <option value="all">Tất cả thời gian</option>
                </select>
            </div>

            <!-- Year Selector (for quarter/year view) -->
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

            <!-- Date Range Label -->
            <div class="flex items-center px-4 py-2 bg-gray-50 rounded-lg text-sm text-gray-600">
                <span class="material-symbols-outlined text-base mr-2">date_range</span>
                <span id="date-range-label">Đang tải...</span>
            </div>

            <!-- Tab Navigation (Moved) -->
            <div class="flex-1 flex justify-center">
                <div class="flex gap-2">
                    <button class="tab-btn px-4 py-2 font-medium text-sm transition-all border rounded-lg hover:text-axeron-red <?= ($tab ?? 'revenue') === 'revenue' ? 'active border-axeron-red text-axeron-red' : 'border-transparent text-gray-500 hover:bg-gray-50' ?>" data-tab="revenue" onclick="switchTab('revenue')">
                        <span class="material-symbols-outlined text-lg align-middle mr-1">trending_up</span>
                        Doanh Thu
                    </button>
                    <button class="tab-btn px-4 py-2 font-medium text-sm transition-all border rounded-lg hover:text-axeron-red <?= ($tab ?? '') === 'customers' ? 'active border-axeron-red text-axeron-red' : 'border-transparent text-gray-500 hover:bg-gray-50' ?>" data-tab="customers" onclick="switchTab('customers')">
                        <span class="material-symbols-outlined text-lg align-middle mr-1">people</span>
                        Khách Hàng
                    </button>
                    <button class="tab-btn px-4 py-2 font-medium text-sm transition-all border rounded-lg hover:text-axeron-red <?= ($tab ?? '') === 'products' ? 'active border-axeron-red text-axeron-red' : 'border-transparent text-gray-500 hover:bg-gray-50' ?>" data-tab="products" onclick="switchTab('products')">
                        <span class="material-symbols-outlined text-lg align-middle mr-1">inventory_2</span>
                        Sản Phẩm
                    </button>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                <button onclick="exportData('excel')" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <span class="material-symbols-outlined text-lg">table_chart</span>
                    Xuất Excel
                </button>
                <button onclick="exportData('pdf')" class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <span class="material-symbols-outlined text-lg">picture_as_pdf</span>
                    Xuất PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3" id="summary-cards">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Tổng Doanh Thu</p>
                    <p class="text-2xl font-bold text-green-600 mt-1" id="summary-revenue">--</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
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

        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Giá Trị TB/Đơn</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1" id="summary-aov">--</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
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

        <!-- Conversion Rate Card -->
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

    <!-- Chart Area (Revenue Tab) -->
    <div class="bg-white rounded-xl p-6 shadow-sm border" id="chart-container">
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

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <!-- Table Header with Search -->
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-lg" id="table-title">Dữ liệu chi tiết</h3>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="table-search" placeholder="Tìm kiếm..." class="pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent w-64" onkeyup="handleSearch()">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                </div>
                <select id="table-sort" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-axeron-red" onchange="handleSort()">
                    <option value="total_spent">Sắp xếp theo...</option>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
<script src="<?= BASE_URL ?>/js/admin-analytics.js?v=<?= time() + 2 ?>"></script>
