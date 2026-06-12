/**
 * Admin Analytics - JavaScript Handler
 * Xử lý AJAX, charts, pagination, sort, search, và export
 */

let currentTab = 'revenue';
let currentData = [];
let chartInstance = null;
let chartType = 'line';

// State
let currentPage = 1;
let totalPages = 1;
let currentSort = 'total_spent';
let currentOrder = 'desc';
let searchTimeout = null;

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    loadData();
    setupEventListeners();
});

/**
 * Initialize Chart.js
 */
function initializeChart() {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Doanh Thu (VNĐ)',
                data: [],
                borderColor: '#BE1E2D',
                backgroundColor: 'rgba(190, 30, 45, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Search input
    const searchInput = document.getElementById('table-search');
    searchInput.addEventListener('keyup', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadData();
        }, 500);
    });
}

/**
 * Handle period filter change
 */
function handlePeriodChange() {
    const period = document.getElementById('filter-period').value;
    const yearFilter = document.getElementById('year-filter-container');
    const quarterFilter = document.getElementById('quarter-filter-container');

    // Show/hide year and quarter filters based on period
    if (period === 'quarter') {
        quarterFilter.classList.remove('hidden');
        document.getElementById('sort-select')?.classList.add('hidden');
    } else {
        quarterFilter.classList.add('hidden');
    }

    currentPage = 1;
    loadData();
}

/**
 * Switch between tabs
 */
function switchTab(tab) {
    currentTab = tab;

    // Update tab active state
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-axeron-red', 'text-axeron-red');
        btn.classList.add('border-transparent', 'text-gray-500', 'hover:bg-gray-50');
    });

    const activeTab = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    if (activeTab) {
        activeTab.classList.add('active', 'border-axeron-red', 'text-axeron-red');
        activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:bg-gray-50');
    }

    // Show/hide chart container
    const chartContainer = document.getElementById('chart-container');
    if (chartContainer) {
        chartContainer.style.display = tab === 'revenue' ? 'block' : 'none';
    }

    // Update sort options based on tab
    updateSortOptions();

    // Reset and load data
    currentPage = 1;
    loadData();
}

/**
 * Update sort options based on current tab
 */
function updateSortOptions() {
    const sortSelect = document.getElementById('table-sort');
    if (!sortSelect) return;

    let options = '';

    switch (currentTab) {
        case 'customers':
            options = `
                <option value="total_spent" ${currentSort === 'total_spent' ? 'selected' : ''}>Chi tiêu</option>
                <option value="total_orders" ${currentSort === 'total_orders' ? 'selected' : ''}>Số đơn hàng</option>
                <option value="avg_order_value" ${currentSort === 'avg_order_value' ? 'selected' : ''}>Giá trị TB</option>
                <option value="full_name" ${currentSort === 'full_name' ? 'selected' : ''}>Tên</option>
            `;
            break;
        case 'products':
            options = `
                <option value="total_sold" ${currentSort === 'total_sold' ? 'selected' : ''}>Số lượng bán</option>
                <option value="total_revenue" ${currentSort === 'total_revenue' ? 'selected' : ''}>Doanh thu</option>
                <option value="avg_price" ${currentSort === 'avg_price' ? 'selected' : ''}>Giá TB</option>
                <option value="product_name" ${currentSort === 'product_name' ? 'selected' : ''}>Tên SP</option>
            `;
            break;
        case 'revenue':
            options = `
                <option value="month">Tháng</option>
            `;
            break;
    }

    sortSelect.innerHTML = options;
}

/**
 * Handle sort change
 */
function handleSort() {
    currentSort = document.getElementById('table-sort').value;
    currentPage = 1;
    loadData();
}

/**
 * Handle search
 */
function handleSearch() {
    // Search is handled by setupEventListeners with debounce
}

/**
 * Load data from API
 */
async function loadData() {
    showLoading();

    const period = document.getElementById('filter-period').value;
    const year = document.getElementById('filter-year').value;
    const quarter = document.getElementById('filter-quarter').value;
    const search = document.getElementById('table-search').value;

    // Determine correct base URL for API
    let apiBase = BASE_URL;
    // For admin page, API is at the same level
    let apiUrl = apiBase + '/api/analytics-api.php?action=';

    // Build URL based on current tab
    switch (currentTab) {
        case 'revenue':
            apiUrl += 'revenue&period=' + period + '&year=' + year;
            if (period === 'quarter') apiUrl += '&quarter=' + quarter;
            break;
        case 'customers':
            apiUrl += 'customers&period=' + period + '&year=' + year + '&sort=' + currentSort + '&order=' + currentOrder + '&page=' + currentPage + '&search=' + encodeURIComponent(search);
            break;
        case 'products':
            apiUrl += 'products&period=' + period + '&year=' + year + '&sort=' + currentSort + '&order=' + currentOrder + '&page=' + currentPage + '&search=' + encodeURIComponent(search);
            break;
        default:
            apiUrl += 'summary&period=' + period;
    }

    try {
        const response = await fetch(apiUrl, {
            credentials: 'same-origin'  // Include cookies for session
        });

        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }

        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text.substring(0, 200));
            throw new Error('Invalid JSON response from server');
        }

        if (result.success) {
            currentData = result.data;
            updateSummaryCards(result.data);
            updateTable(result.data);
            updateChart(result.data);
            updateDateRangeLabel(result.data);
        } else {
            showToast(result.message || 'Lỗi tải dữ liệu', 'error');
        }
    } catch (error) {
        console.error('Error loading data:', error, 'URL:', apiUrl);
        showToast('Lỗi kết nối server: ' + error.message, 'error');
    }
}

/**
 * Update summary cards
 */
function updateSummaryCards(data) {
    if (data.summary) {
        document.getElementById('summary-revenue').textContent = data.summary.total_revenue_formatted || formatCurrency(data.summary.total_revenue || 0);
        document.getElementById('summary-orders').textContent = formatNumber(data.summary.total_orders || 0);
        document.getElementById('summary-aov').textContent = formatCurrency(data.summary.avg_monthly_revenue || 0);
        document.getElementById('summary-customers').textContent = formatNumber(data.summary.customers || 0);
        
        let convRate = data.summary.conversion_rate || 0;
        document.getElementById('summary-conversion').textContent = convRate + '%';
    } else if (data.totals) {
        // For products tab
        document.getElementById('summary-revenue').textContent = data.totals.revenue_formatted || formatCurrency(data.totals.revenue || 0);
        document.getElementById('summary-orders').textContent = formatNumber(data.totals.sold || 0);
        document.getElementById('summary-aov').textContent = (data.products?.length || 0) + ' sản phẩm';
        if(document.getElementById('summary-customers')) document.getElementById('summary-customers').textContent = '--';
        if(document.getElementById('summary-conversion')) document.getElementById('summary-conversion').textContent = '--%';
    }
}

/**
 * Update data table
 */
function updateTable(data) {
    const tableHeader = document.getElementById('table-header');
    const tableBody = document.getElementById('table-body');
    const tableTitle = document.getElementById('table-title');

    let headers = '';
    let rows = '';
    let pagination = '';

    switch (currentTab) {
        case 'customers':
            tableTitle.textContent = 'Xếp Hạng Khách Hàng';
            headers = `
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortBy('full_name')">Khách Hàng</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortBy('total_orders')">Số Đơn</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortBy('total_spent')">Tổng Chi Tiêu</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Giá Trị TB</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Đơn Cuối</th>
            `;

            if (data.customers && data.customers.length > 0) {
                data.customers.forEach((c, index) => {
                    const rank = (currentPage - 1) * 20 + index + 1;
                    rows += `
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-axeron-red rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        ${c.full_name?.charAt(0).toUpperCase() || '?'}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">${escapeHtml(c.full_name || 'N/A')}</p>
                                        <p class="text-sm text-gray-500">${escapeHtml(c.email || '')}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">${c.total_orders || 0}</td>
                            <td class="px-4 py-3 font-bold text-green-600">${c.total_spent_formatted || formatCurrency(c.total_spent || 0)}</td>
                            <td class="px-4 py-3 text-gray-600">${c.avg_order_value_formatted || formatCurrency(c.avg_order_value || 0)}</td>
                            <td class="px-4 py-3 text-gray-500 text-sm">${c.last_order_date ? formatDate(c.last_order_date) : 'N/A'}</td>
                        </tr>
                    `;
                });
            }

            if (data.pagination) {
                updatePagination(data.pagination);
            }
            break;

        case 'products':
            tableTitle.textContent = 'Báo Cáo Sản Phẩm';
            headers = `
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Sản Phẩm</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Danh Mục</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Đã Bán</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Doanh Thu</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tỷ Lệ</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng Thái</th>
            `;

            if (data.products && data.products.length > 0) {
                data.products.forEach(p => {
                    const statusClass = p.status === 'hot' ? 'bg-red-100 text-red-800' : (p.status === 'cold' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800');
                    const statusText = p.status === 'hot' ? 'Hot' : (p.status === 'cold' ? 'Ế' : 'Bình thường');

                    rows += `
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">${escapeHtml(p.product_name || 'N/A')}</p>
                                <p class="text-sm text-gray-500">${escapeHtml(p.brand_name || '')}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">${escapeHtml(p.category_name || 'N/A')}</td>
                            <td class="px-4 py-3 text-center font-medium">${formatNumber(p.total_sold || 0)}</td>
                            <td class="px-4 py-3 font-bold text-axeron-red">${p.total_revenue_formatted || formatCurrency(p.total_revenue || 0)}</td>
                            <td class="px-4 py-3">
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-axeron-red h-2 rounded-full" style="width: ${p.sold_percentage || 0}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">${p.sold_percentage || 0}%</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs ${statusClass}">${statusText}</span>
                            </td>
                        </tr>
                    `;
                });
            }

            if (data.pagination) {
                updatePagination(data.pagination);
            }
            break;

        case 'revenue':
            tableTitle.textContent = 'Chi Tiết Doanh Thu Theo Thời Gian';
            headers = `
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời Gian</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Số Đơn</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Doanh Thu</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Giá Trị TB</th>
            `;

            const periodType = data.period_type || 'month';
            if (data.data && data.data.length > 0) {
                data.data.forEach(d => {
                    let timeLabel = '';
                    if (periodType === 'year') {
                        timeLabel = 'Tháng ' + d.month;
                    } else if (periodType === 'quarter') {
                        timeLabel = 'Tháng ' + d.month;
                    } else {
                        timeLabel = 'Ngày ' + d.day;
                    }

                    rows += `
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">${timeLabel}</td>
                            <td class="px-4 py-3 text-center">${formatNumber(d.total_orders || 0)}</td>
                            <td class="px-4 py-3 font-bold text-green-600">${d.revenue_formatted || formatCurrency(d.revenue || 0)}</td>
                            <td class="px-4 py-3 text-gray-600">${d.avg_order_value_formatted || formatCurrency(d.avg_order_value || 0)}</td>
                        </tr>
                    `;
                });
            }
            break;
    }

    // Add empty state if no data
    if (!rows) {
        rows = `
            <tr>
                <td colspan="${currentTab === 'revenue' ? 4 : 6}" class="px-4 py-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-gray-300">analytics</span>
                    <p class="text-gray-500 mt-4">Chưa có dữ liệu trong khoảng thời gian này</p>
                </td>
            </tr>
        `;
    }

    tableHeader.innerHTML = headers;
    tableBody.innerHTML = rows;

    // Update export table
    updateExportTable();
}

/**
 * Update chart
 */
function updateChart(data) {
    if (currentTab !== 'revenue' || !chartInstance) return;

    const periodType = data.period_type || 'month';
    const chartData = data.data || [];

    // Generate labels and values
    let labels = [];
    let values = [];

    if (periodType === 'year') {
        // Monthly labels
        labels = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
        values = labels.map((_, i) => {
            const item = chartData.find(d => d.month === i + 1);
            return item ? item.revenue : 0;
        });
    } else if (periodType === 'quarter') {
        // Months in quarter
        labels = [];
        values = [];
        const quarterMonths = {
            1: [1, 2, 3],
            2: [4, 5, 6],
            3: [7, 8, 9],
            4: [10, 11, 12]
        };
        const months = quarterMonths[data.quarter] || [1, 2, 3];
        months.forEach(m => {
            labels.push('T' + m);
            const item = chartData.find(d => d.month === m);
            values.push(item ? item.revenue : 0);
        });
    }

    // Update chart
    chartInstance.data.labels = labels;
    chartInstance.data.datasets[0].data = values;
    chartInstance.data.datasets[0].type = chartType;
    chartInstance.update();
}

/**
 * Update chart type
 */
function updateChartType(type) {
    chartType = type;
    chartInstance.data.datasets[0].type = type;
    chartInstance.update();

    // Update button states
    document.getElementById('chart-bar').classList.toggle('bg-gray-100', type !== 'bar');
    document.getElementById('chart-line').classList.toggle('bg-gray-100', type !== 'line');
}

/**
 * Update pagination
 */
function updatePagination(pagination) {
    const info = document.getElementById('pagination-info');
    const controls = document.getElementById('pagination-controls');

    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.current_page * pagination.per_page, pagination.total_items);

    info.textContent = `Hiển thị ${start}-${end} / ${pagination.total_items} kết quả`;

    let pages = '';
    const totalPages = pagination.total_pages;
    const currentPage = pagination.current_page;

    // Previous button
    pages += `<button onclick="goToPage(${currentPage - 1})" class="px-3 py-2 rounded-lg border ${currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}" ${currentPage <= 1 ? 'disabled' : ''}>
        <span class="material-symbols-outlined text-base">chevron_left</span>
    </button>`;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            pages += `<button onclick="goToPage(${i})" class="px-4 py-2 rounded-lg border ${i === currentPage ? 'bg-axeron-red text-white' : 'hover:bg-gray-50'}">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            pages += `<span class="px-2">...</span>`;
        }
    }

    // Next button
    pages += `<button onclick="goToPage(${currentPage + 1})" class="px-3 py-2 rounded-lg border ${currentPage >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'}" ${currentPage >= totalPages ? 'disabled' : ''}>
        <span class="material-symbols-outlined text-base">chevron_right</span>
    </button>`;

    controls.innerHTML = pages;
}

/**
 * Go to page
 */
function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadData();
}

/**
 * Sort by column
 */
function sortBy(column) {
    if (currentSort === column) {
        currentOrder = currentOrder === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort = column;
        currentOrder = 'desc';
    }
    currentPage = 1;
    loadData();
}

/**
 * Update date range label
 */
function updateDateRangeLabel(data) {
    const label = document.getElementById('date-range-label');
    const period = document.getElementById('filter-period').value;
    const year = document.getElementById('filter-year').value;
    const quarter = document.getElementById('filter-quarter').value;

    let text = '';
    switch (period) {
        case 'month':
            text = 'Tháng ' + (new Date().getMonth() + 1) + ' / ' + year;
            break;
        case 'quarter':
            text = 'Quý ' + quarter + ' / ' + year;
            break;
        case 'year':
            text = 'Năm ' + year;
            break;
        case 'all':
            text = 'Tất cả thời gian';
            break;
    }

    label.textContent = text;
}

/**
 * Show loading state
 */
function showLoading() {
    const tableBody = document.getElementById('table-body');
    tableBody.innerHTML = `
        <tr>
            <td colspan="10" class="px-4 py-12 text-center">
                <div class="flex items-center justify-center">
                    <span class="material-symbols-outlined text-4xl text-gray-300 animate-spin">progress_activity</span>
                    <span class="ml-3 text-gray-500">Đang tải dữ liệu...</span>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Export data
 */
async function exportData(type) {
    if (!currentData || (!currentData.data && !currentData.customers && !currentData.products)) {
        showToast('Chưa có dữ liệu để xuất', 'error');
        return;
    }

    showToast('Đang chuẩn bị file...', 'info');

    if (type === 'excel') {
        await exportToExcel();
    } else if (type === 'pdf') {
        await exportToPDF();
    }
}

/**
 * Export to Excel using SheetJS
 */
async function exportToExcel() {
    try {
        const table = document.getElementById('analytics-table');
        const wb = XLSX.utils.table_to_book(table);

        const period = document.getElementById('filter-period').value;
        const year = document.getElementById('filter-year').value;
        const quarter = document.getElementById('filter-quarter').value;
        const tabNames = { revenue: 'Doanh Thu', customers: 'Khach Hang', products: 'San Pham' };
        const filename = `BaoCao_${tabNames[currentTab] || 'Data'}_${year}_${Date.now()}.xlsx`;

        XLSX.writeFile(wb, filename);
        showToast('Đã xuất file Excel!', 'success');
    } catch (error) {
        console.error('Excel export error:', error);
        showToast('Lỗi xuất Excel', 'error');
    }
}

/**
 * Export to PDF using jsPDF
 */
async function exportToPDF() {
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const period = document.getElementById('filter-period').value;
        const year = document.getElementById('filter-year').value;
        const tabNames = { revenue: 'Doanh Thu', customers: 'Khach Hang', products: 'San Pham' };

        // Title
        doc.setFontSize(18);
        doc.setTextColor(190, 30, 45);
        doc.text('BAO CAO ' + (tabNames[currentTab] || 'DU LIEU').toUpperCase(), 10, 15);

        // Subtitle
        doc.setFontSize(12);
        doc.setTextColor(100, 100, 100);
        let periodText = 'Thang ' + (new Date().getMonth() + 1) + ' / ' + year;
        if (period === 'quarter') periodText = 'Quy ' + document.getElementById('filter-quarter').value + ' / ' + year;
        if (period === 'year') periodText = 'Nam ' + year;
        if (period === 'all') periodText = 'Tat ca thoi gian';
        doc.text('Khoang thoi gian: ' + periodText, 10, 25);

        // Summary
        const revenue = document.getElementById('summary-revenue').textContent;
        const orders = document.getElementById('summary-orders').textContent;
        doc.setFontSize(10);
        doc.text('Tong doanh thu: ' + revenue, 10, 35);
        doc.text('Tong don hang: ' + orders, 10, 42);

        // Table
        const tableElement = document.getElementById('analytics-table');
        doc.autoTable({
            html: tableElement,
            startY: 50,
            styles: { fontSize: 8, cellPadding: 3 },
            headStyles: { fillColor: [190, 30, 45], textColor: 255 },
            alternateRowStyles: { fillColor: [245, 245, 245] }
        });

        // Footer
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.setTextColor(150);
            doc.text('Axeron Sport - ' + new Date().toLocaleDateString('vi-VN'), 10, doc.internal.pageSize.height - 10);
            doc.text('Trang ' + i + ' / ' + pageCount, doc.internal.pageSize.width - 30, doc.internal.pageSize.height - 10);
        }

        const filename = `BaoCao_${tabNames[currentTab] || 'Data'}_${Date.now()}.pdf`;
        doc.save(filename);
        showToast('Da xuat file PDF!', 'success');
    } catch (error) {
        console.error('PDF export error:', error);
        showToast('Loi xuat PDF', 'error');
    }
}

/**
 * Update export table
 */
function updateExportTable() {
    const exportTable = document.getElementById('export-table');
    const mainTable = document.getElementById('analytics-table');

    if (mainTable) {
        exportTable.innerHTML = mainTable.innerHTML;
    }
}

/**
 * Helper: Format currency (VND)
 */
function formatCurrency(amount) {
    if (amount === null || amount === undefined) return '0 ₫';
    return new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
}

/**
 * Helper: Format number
 */
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return new Intl.NumberFormat('vi-VN').format(num);
}

/**
 * Helper: Format date
 */
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

/**
 * Helper: Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Toast notification (inherited from admin.php)
 */
function showToast(message, type = 'success') {
    if (typeof showToastCustom === 'function') {
        showToastCustom(message, type);
    } else {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-axeron-blue';
        toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3`;
        toast.innerHTML = `<span>${message}</span>`;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}