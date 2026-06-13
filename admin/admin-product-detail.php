<?php
if (!hasPermission('product_detail')) {
    setFlash('error', 'Bạn không có quyền truy cập trang này!');
    header('Location: ' . BASE_URL . '/admin/admin.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Không tìm thấy sản phẩm!');
    header('Location: ' . BASE_URL . '/admin/admin.php?action=analytics');
    exit;
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="admin.php?action=analytics" onclick="sessionStorage.setItem('returnTab', 'products')" class="p-2 border rounded-lg hover:bg-gray-50 transition-colors">
                <span class="material-symbols-outlined align-middle">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold" id="product-title">Chi Tiết Sản Phẩm</h2>
                <p class="text-gray-500" id="product-subtitle">Đang tải thông tin...</p>
            </div>
        </div>
    </div>

    <!-- Product Info Header -->
    <div class="bg-white rounded-xl shadow-sm border p-6 flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-48 h-48 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center shrink-0 border">
            <img id="product-image" src="" alt="Product Image" class="w-full h-full object-cover hidden">
            <span id="product-image-placeholder" class="material-symbols-outlined text-5xl text-gray-400">inventory_2</span>
        </div>
        <div class="flex-1 space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-gray-900" id="info-name">--</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded" id="info-brand">--</span>
                        <span class="text-sm font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded" id="info-category">--</span>
                        <span class="text-sm font-medium px-2 py-1 rounded" id="info-status">--</span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Giá Bán</p>
                    <p class="text-2xl font-bold text-axeron-red" id="info-price">--</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Đã Bán</p>
                    <p class="text-lg font-bold text-gray-900" id="metric-sold">--</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Tồn Kho</p>
                    <p class="text-lg font-bold" id="metric-stock">--</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Lượt Xem</p>
                    <p class="text-lg font-bold text-gray-900" id="metric-views">--</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Tỷ Lệ Chuyển Đổi</p>
                    <p class="text-lg font-bold text-green-600" id="metric-cr">--</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Chart & Reviews -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <h3 class="font-bold text-lg mb-4">Doanh Thu 12 Tháng Qua</h3>
                <div class="h-64">
                    <canvas id="productRevenueDetailChart"></canvas>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg">Đánh Giá Gần Đây</h3>
                    <div class="flex items-center gap-2" id="rating-summary">
                        <!-- Rating summary -->
                    </div>
                </div>
                <div class="space-y-4" id="reviews-container">
                    <div class="text-center py-6 text-gray-500">Đang tải...</div>
                </div>
            </div>
        </div>

        <!-- Right Column: Audiences & Related -->
        <div class="space-y-6">
            <!-- Audience Segment -->
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <h3 class="font-bold text-lg mb-4">Phân Khúc Người Xem</h3>
                <div class="h-48 flex justify-center">
                    <canvas id="productAudienceChart"></canvas>
                </div>
            </div>

            <!-- Related Keywords -->
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-500">search</span>
                    Từ Khóa Liên Quan
                </h3>
                <div class="flex flex-wrap gap-2" id="keywords-container">
                    <span class="text-gray-400">Đang tải...</span>
                </div>
            </div>

            <!-- Bought Together -->
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-500">shopping_cart</span>
                    Thường Mua Kèm
                </h3>
                <div class="space-y-3" id="bought-together-container">
                    <div class="text-center text-gray-400 py-2">Đang tải...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productId = <?= $id ?>;
    
    // Load Product Data
    fetch('<?= BASE_URL ?>/api/analytics-api.php?action=product_detail&id=' + productId)
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                renderProductData(result.data);
            } else {
                alert(result.message || 'Lỗi tải dữ liệu sản phẩm');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi kết nối server');
        });
        
    function renderProductData(data) {
        const p = data.product;
        const m = data.performance;
        
        // 1. Basic Info
        document.getElementById('info-name').textContent = p.product_name || 'N/A';
        document.getElementById('product-subtitle').textContent = `Mã SP: ${p.product_id} • Cập nhật: ` + (new Date().toLocaleDateString('vi-VN'));
        document.getElementById('info-brand').textContent = p.brand_name || 'Không có';
        document.getElementById('info-category').textContent = p.category_name || 'Không có';
        
        // Price Formatting
        const priceFormatted = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(p.base_price || 0);
        document.getElementById('info-price').textContent = priceFormatted;
        
        // Image
        if (p.primary_image) {
            const imgEl = document.getElementById('product-image');
            imgEl.src = p.primary_image.startsWith('http') ? p.primary_image : '<?= BASE_URL ?>' + p.primary_image;
            imgEl.classList.remove('hidden');
            document.getElementById('product-image-placeholder').classList.add('hidden');
        }
        
        // Status Badge
        const statusEl = document.getElementById('info-status');
        if (m.current_stock > 10) {
            statusEl.textContent = 'Còn hàng';
            statusEl.className = 'text-sm font-medium text-green-700 bg-green-100 px-2 py-1 rounded';
        } else if (m.current_stock > 0) {
            statusEl.textContent = 'Sắp hết';
            statusEl.className = 'text-sm font-medium text-yellow-700 bg-yellow-100 px-2 py-1 rounded';
        } else {
            statusEl.textContent = 'Hết hàng';
            statusEl.className = 'text-sm font-medium text-red-700 bg-red-100 px-2 py-1 rounded';
        }

        // 2. Metrics
        document.getElementById('metric-sold').textContent = new Intl.NumberFormat('vi-VN').format(m.total_sold || 0);
        const stockEl = document.getElementById('metric-stock');
        stockEl.textContent = new Intl.NumberFormat('vi-VN').format(m.current_stock || 0);
        if (m.current_stock <= 10) stockEl.classList.add('text-red-500'); else stockEl.classList.add('text-green-600');
        
        document.getElementById('metric-views').textContent = new Intl.NumberFormat('vi-VN').format(m.view_count || 0);
        document.getElementById('metric-cr').textContent = (m.conversion_rate || 0) + '%';
        
        // 3. Revenue Chart
        if (data.chart_data) {
            const ctxRev = document.getElementById('productRevenueDetailChart').getContext('2d');
            new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                    datasets: [{
                        label: 'Doanh thu',
                        data: data.chart_data,
                        borderColor: '#BE1E2D',
                        backgroundColor: 'rgba(190, 30, 45, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
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
                                    return new Intl.NumberFormat('vi-VN').format(value);
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // 4. Audience Chart
        if (data.top_viewers) {
            const ctxAud = document.getElementById('productAudienceChart').getContext('2d');
            new Chart(ctxAud, {
                type: 'doughnut',
                data: {
                    labels: data.top_viewers.map(item => item.full_name || 'Không xác định'),
                    datasets: [{
                        data: data.top_viewers.map(item => item.view_count),
                        backgroundColor: ['#eab308', '#3b82f6', '#22c55e', '#9ca3af', '#f97316'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                    }
                }
            });
        }
        
        // 5. Related Keywords
        const keysContainer = document.getElementById('keywords-container');
        if (data.related_keywords && data.related_keywords.length > 0) {
            keysContainer.innerHTML = '';
            data.related_keywords.forEach(k => {
                keysContainer.innerHTML += `<span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 cursor-default" title="${k.search_count} lượt tìm kiếm">${k.keyword}</span>`;
            });
        } else {
            keysContainer.innerHTML = '<span class="text-gray-400 text-sm">Không có dữ liệu tìm kiếm</span>';
        }
        
        // 6. Bought Together
        const btContainer = document.getElementById('bought-together-container');
        if (data.bought_together && data.bought_together.length > 0) {
            btContainer.innerHTML = '';
            data.bought_together.forEach(bt => {
                const price = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(bt.base_price || 0);
                btContainer.innerHTML += `
                    <a href="admin.php?action=product_detail&id=${bt.product_id}" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg group transition-colors">
                        <div class="flex-1 truncate pr-2">
                            <p class="text-sm font-medium text-gray-800 group-hover:text-blue-600 truncate">${bt.product_name}</p>
                            <p class="text-xs text-gray-500">${price}</p>
                        </div>
                        <div class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded">
                            +${bt.together_count} đơn
                        </div>
                    </a>
                `;
            });
        } else {
            btContainer.innerHTML = '<div class="text-center text-gray-400 py-2 text-sm">Không có sản phẩm nào</div>';
        }
        
        // 7. Recent Reviews
        const revContainer = document.getElementById('reviews-container');
        if (data.reviews && data.reviews.length > 0) {
            revContainer.innerHTML = '';
            
            // Calculate avg rating
            let sum = 0;
            data.reviews.forEach(r => { sum += Number(r.rating) || 0; });
            const avg = (sum / data.reviews.length).toFixed(1);
            
            document.getElementById('rating-summary').innerHTML = `
                <span class="text-xl font-bold text-yellow-500">${avg}</span>
                <span class="text-yellow-400 text-lg">★</span>
                <span class="text-sm text-gray-500 ml-1">(${data.reviews.length} đánh giá)</span>
            `;
            
            data.reviews.forEach(r => {
                const date = new Date(r.created_at).toLocaleDateString('vi-VN');
                const stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
                revContainer.innerHTML += `
                    <div class="border-b border-gray-100 last:border-0 pb-3 last:pb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-800">${r.full_name || 'Khách'}</p>
                                <div class="text-yellow-400 text-sm tracking-widest">${stars}</div>
                            </div>
                            <span class="text-xs text-gray-400">${date}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">${r.comment || ''}</p>
                    </div>
                `;
            });
        } else {
            document.getElementById('rating-summary').innerHTML = '<span class="text-sm text-gray-500">Chưa có đánh giá</span>';
            revContainer.innerHTML = '<div class="text-center py-6 text-gray-400">Chưa có đánh giá nào cho sản phẩm này.</div>';
        }
    }
});
</script>
