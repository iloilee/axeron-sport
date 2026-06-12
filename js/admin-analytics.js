/**
 * Admin Analytics - JavaScript Handler
 * Xử lý AJAX, charts, pagination, sort, search, và export
 */

let currentTab = 'revenue';
let currentData = [];
let chartInstance = null;
let customerBarChartInstance = null;
let customerPieChartInstance = null;
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

    // Create Gradient for fill area
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(190, 30, 45, 0.4)'); // Axeron Red, more transparent
    gradient.addColorStop(1, 'rgba(190, 30, 45, 0.0)');

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Doanh Thu',
                data: [],
                borderColor: '#BE1E2D',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#BE1E2D',
                pointBorderWidth: 2,
                pointRadius: 0,          // Hide points by default
                pointHoverRadius: 6,     // Show on hover
                pointHoverBackgroundColor: '#BE1E2D',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false // Clean look without legend if single dataset
                },
                tooltip: {
                    backgroundColor: 'rgba(26, 26, 26, 0.9)', // Dark background
                    titleFont: { size: 13, family: "'Noto Sans', sans-serif" },
                    bodyFont: { size: 14, weight: 'bold', family: "'Montserrat', sans-serif" },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false, // Don't show the color box
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6b7280',
                        font: { family: "'Noto Sans', sans-serif" }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6',
                        drawBorder: false,
                        borderDash: [5, 5] // Dashed grid lines
                    },
                    ticks: {
                        color: '#6b7280',
                        font: { family: "'Noto Sans', sans-serif" },
                        maxTicksLimit: 6,
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return (value / 1000000000) + ' Tỷ';
                            }
                            if (value >= 1000000) {
                                return (value / 1000000) + ' Tr';
                            }
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });

    // Initialize Customer Charts
    const barCtx = document.getElementById('customerBarChart');
    if (barCtx) {
        customerBarChartInstance = new Chart(barCtx.getContext('2d'), {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Doanh thu', data: [], backgroundColor: '#BE1E2D', borderRadius: 4 }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + ' Tr';
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    const pieCtx = document.getElementById('customerPieChart');
    if (pieCtx) {
        customerPieChartInstance = new Chart(pieCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['VIP', 'Tiềm năng', 'Mới', 'Bình thường', 'Rời bỏ'],
                datasets: [{
                    data: [0, 0, 0, 0, 0],
                    backgroundColor: ['#eab308', '#3b82f6', '#22c55e', '#9ca3af', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }
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
    const monthFilter = document.getElementById('month-filter-container');
    const yearFilter = document.getElementById('year-filter-container');
    const quarterFilter = document.getElementById('quarter-filter-container');
    const sortSelect = document.getElementById('sort-select');

    // Hide all first
    if(monthFilter) monthFilter.classList.add('hidden');
    if(quarterFilter) quarterFilter.classList.add('hidden');
    if(yearFilter) yearFilter.classList.add('hidden');

    // Show based on period
    if (period === 'month') {
        if(monthFilter) monthFilter.classList.remove('hidden');
        if(yearFilter) yearFilter.classList.remove('hidden');
    } else if (period === 'quarter') {
        if(quarterFilter) quarterFilter.classList.remove('hidden');
        if(yearFilter) yearFilter.classList.remove('hidden');
        if(sortSelect) sortSelect.classList.add('hidden');
    } else if (period === 'year') {
        if(yearFilter) yearFilter.classList.remove('hidden');
    } else if (period === 'all') {
        // Hide everything for 'all'
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

    // Show/hide chart containers and summary cards
    const chartContainerRev = document.getElementById('chart-container-revenue');
    const chartContainerCus = document.getElementById('chart-container-customers');
    const summaryRev = document.getElementById('summary-cards-revenue');
    const summaryCus = document.getElementById('summary-cards-customers');
    const rankFilter = document.getElementById('filter-rank');

    if (chartContainerRev) chartContainerRev.classList.toggle('hidden', tab !== 'revenue');
    if (chartContainerCus) chartContainerCus.classList.toggle('hidden', tab !== 'customers');
    if (summaryRev) summaryRev.classList.toggle('hidden', tab === 'customers');
    if (summaryCus) summaryCus.classList.toggle('hidden', tab !== 'customers');
    if (rankFilter) rankFilter.classList.toggle('hidden', tab !== 'customers');

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
    const month = document.getElementById('filter-month') ? document.getElementById('filter-month').value : new Date().getMonth() + 1;
    const search = document.getElementById('table-search').value;
    const rankFilter = document.getElementById('filter-rank') ? document.getElementById('filter-rank').value : '';

    // Determine correct base URL for API
    let apiBase = BASE_URL;
    // For admin page, API is at the same level
    let apiUrl = apiBase + '/api/analytics-api.php?action=';

    // Build URL based on current tab
    switch (currentTab) {
        case 'revenue':
            apiUrl += 'revenue&period=' + period + '&year=' + year;
            if (period === 'quarter') apiUrl += '&quarter=' + quarter;
            if (period === 'month') apiUrl += '&month=' + month;
            break;
        case 'customers':
            apiUrl += 'customers&period=' + period + '&year=' + year + '&sort=' + currentSort + '&order=' + currentOrder + '&page=' + currentPage + '&search=' + encodeURIComponent(search) + '&rank=' + encodeURIComponent(rankFilter);
            if (period === 'month') apiUrl += '&month=' + month;
            break;
        case 'products':
            apiUrl += 'products&period=' + period + '&year=' + year + '&sort=' + currentSort + '&order=' + currentOrder + '&page=' + currentPage + '&search=' + encodeURIComponent(search);
            if (period === 'month') apiUrl += '&month=' + month;
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
            updateCustomerCharts(result.data);

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
    if (currentTab === 'customers' && data.customer_summary) {
        document.getElementById('customer-total').textContent = formatNumber(data.customer_summary.total_customers || 0);
        document.getElementById('customer-new').textContent = formatNumber(data.customer_summary.new_customers || 0);
        document.getElementById('customer-vip').textContent = formatNumber(data.customer_summary.vip_customers || 0);
        document.getElementById('customer-returning').textContent = formatNumber(data.customer_summary.returning_customers || 0);
        document.getElementById('customer-churn').textContent = formatNumber(data.customer_summary.churn_customers || 0);
    } else if (data.summary) {
        document.getElementById('summary-revenue').textContent = data.summary.total_revenue_formatted || formatCurrency(data.summary.total_revenue || 0);
        document.getElementById('summary-orders').textContent = formatNumber(data.summary.total_orders || 0);
        document.getElementById('summary-aov').textContent = formatCurrency(data.summary.aov !== undefined ? data.summary.aov : (data.summary.avg_monthly_revenue || 0));
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
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Lần Mua Cuối</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hạng</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hành Động</th>
            `;

            if (data.customers && data.customers.length > 0) {
                data.customers.forEach((c, index) => {
                    let rankClass = 'bg-gray-100 text-gray-800';
                    if (c.customer_rank === 'VIP') rankClass = 'bg-yellow-100 text-yellow-800';
                    else if (c.customer_rank === 'Tiềm năng') rankClass = 'bg-blue-100 text-blue-800';
                    else if (c.customer_rank === 'Mới') rankClass = 'bg-green-100 text-green-800';
                    else if (c.customer_rank === 'Rời bỏ') rankClass = 'bg-red-100 text-red-800';

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
                            <td class="px-4 py-3 text-gray-500 text-sm">${c.last_order_date ? formatDate(c.last_order_date) : 'N/A'}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs ${rankClass}">${c.customer_rank || 'Bình thường'}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="admin.php?action=customer_detail&id=${c.user_id}" class="text-blue-600 hover:text-blue-800 font-medium">Xem</a>
                            </td>
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
                    } else if (periodType === 'all') {
                        timeLabel = 'Tháng ' + d.month + '/' + d.year;
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
    } else if (periodType === 'month') {
        // Days in month
        labels = [];
        values = [];
        const daysInMonth = new Date(new Date().getFullYear(), data.month || new Date().getMonth() + 1, 0).getDate();
        for (let i = 1; i <= daysInMonth; i++) {
            labels.push(i.toString());
            const item = chartData.find(d => d.day === i);
            values.push(item ? item.revenue : 0);
        }
    } else if (periodType === 'all') {
        // All time: Group by Year and Month from data
        labels = [];
        values = [];
        chartData.forEach(d => {
            labels.push(`T${d.month}/${d.year}`);
            values.push(d.revenue || 0);
        });
    }

    // Update chart
    chartInstance.data.labels = labels;
    chartInstance.data.datasets[0].data = values;
    chartInstance.data.datasets[0].type = chartType;
    chartInstance.update();
}

function updateCustomerCharts(data) {
    if (currentTab !== 'customers') return;

    // Bar chart: Top 10 customers by revenue
    if (customerBarChartInstance && data.customers) {
        // Sort by total spent desc
        let top10 = [...data.customers].sort((a, b) => b.total_spent - a.total_spent).slice(0, 10);
        customerBarChartInstance.data.labels = top10.map(c => c.full_name || 'N/A');
        customerBarChartInstance.data.datasets[0].data = top10.map(c => c.total_spent || 0);
        customerBarChartInstance.data.datasets[0].backgroundColor = top10.map(c => {
            if (c.customer_rank === 'VIP') return '#eab308';
            if (c.customer_rank === 'Tiềm năng') return '#3b82f6';
            if (c.customer_rank === 'Mới') return '#22c55e';
            if (c.customer_rank === 'Rời bỏ') return '#ef4444';
            return '#9ca3af'; // Bình thường
        });
        customerBarChartInstance.update();
    }

    // Pie chart: Segmentation
    if (customerPieChartInstance && data.charts && data.charts.segmentation) {
        const seg = data.charts.segmentation;
        customerPieChartInstance.data.datasets[0].data = [
            seg['VIP'] || 0,
            seg['Tiềm năng'] || 0,
            seg['Mới'] || 0,
            seg['Bình thường'] || 0,
            seg['Rời bỏ'] || 0
        ];
        customerPieChartInstance.update();
    }
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
    return new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 1 }).format(amount) + ' ₫';
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