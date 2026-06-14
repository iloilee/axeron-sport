<?php
/**
 * Admin Featured Products Management - Drag and Drop Reordering
 */

// Load currently featured products
$featuredProducts = $db->select("
    SELECT p.*, c.category_name, b.brand_name,
           (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_url
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    WHERE p.is_visible = 1 AND p.is_featured = 1
    ORDER BY p.featured_sort_order ASC, p.updated_at DESC
");
?>

<!-- Include SortableJS Library from CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="mb-8 bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-axeron-red">search</span>
        Thêm sản phẩm nổi bật mới
    </h3>
    <div class="relative max-w-xl">
        <div class="flex gap-2">
            <div class="relative flex-1">
                <input type="text" id="searchProductInput" placeholder="Nhập tên sản phẩm để tìm kiếm..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent outline-none">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-lg">search</span>
            </div>
            <button onclick="clearSearch()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors text-sm font-medium">Xóa</button>
        </div>
        
        <!-- Search Results Dropdown -->
        <div id="searchResultsDropdown" class="absolute left-0 right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-100 z-50 hidden max-h-60 overflow-y-auto divide-y divide-gray-100">
            <!-- Populated dynamically -->
        </div>
    </div>
    <p class="text-xs text-gray-500 mt-2">Tìm kiếm các sản phẩm hiện có đang hiển thị trên web và không ở trong danh sách nổi bật.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-axeron-red">star</span>
            <h3 class="font-bold text-gray-800">Danh sách Sản Phẩm Nổi Bật (<?= count($featuredProducts) ?> sản phẩm)</h3>
        </div>
        <div class="text-xs text-gray-500 font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">drag_handle</span>
            Kéo thả hàng để thay đổi thứ tự hiển thị
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-16">Di chuyển</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sản phẩm</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Danh mục</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thương hiệu</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Giá</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-24">Thao tác</th>
                </tr>
            </thead>
            <tbody id="featured-products-tbody" class="divide-y divide-gray-100">
                <?php if (!empty($featuredProducts)): ?>
                    <?php foreach ($featuredProducts as $index => $product): ?>
                    <tr class="hover:bg-gray-50/70 transition-colors" data-product-id="<?= $product['product_id'] ?>">
                        <td class="px-6 py-4 text-center">
                            <span class="drag-handle material-symbols-outlined text-gray-400 cursor-grab active:cursor-grabbing select-none hover:text-gray-650 transition-colors">
                                drag_indicator
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="<?= $product['image_url'] ?? 'https://placehold.co/60x60' ?>"
                                     alt="" class="w-12 h-12 object-cover rounded-lg bg-gray-100">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800 truncate max-w-xs md:max-w-md"><?= htmlspecialchars($product['product_name']) ?></p>
                                    <p class="text-xs text-gray-500">ID: <?= $product['product_id'] ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-axeron-red"><?= formatPrice($product['base_price']) ?></td>
                        <td class="px-6 py-4">
                            <button type="button" onclick="removeFeaturedProduct(<?= $product['product_id'] ?>, '<?= htmlspecialchars($product['product_name']) ?>')"
                                    class="px-2.5 py-1 text-red-600 hover:bg-red-50 rounded-lg transition-colors inline-flex items-center gap-1 text-xs font-semibold">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="no-products-row">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <span class="material-symbols-outlined text-5xl text-gray-300 mb-2 block">star_border</span>
                            <p class="font-medium">Chưa có sản phẩm nào trong danh sách nổi bật</p>
                            <p class="text-sm mt-1">Sử dụng ô tìm kiếm phía trên để thêm sản phẩm nổi bật mới.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
let searchTimeout = null;

// Initialize SortableJS on table body
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('featured-products-tbody');
    if (!tbody || tbody.querySelector('.no-products-row')) return;

    new Sortable(tbody, {
        handle: '.drag-handle',
        animation: 180,
        ghostClass: 'bg-blue-50/50',
        chosenClass: 'bg-gray-100/30',
        dragClass: 'shadow-lg',
        scroll: true,
        forceAutoScrollFallback: true, // Forces SortableJS to use its fallback scroll handler instead of browser native
        scrollSensitivity: 150,        // Sensitivity (distance from screen edge)
        scrollSpeed: 25,               // Speed of scroll in pixels
        bubbleScroll: true,            // Bubble scroll up to parent scroll containers
        onEnd: async function() {
            // Update order automatically via AJAX
            const rows = tbody.querySelectorAll('tr[data-product-id]');
            const formData = new FormData();
            formData.append('ajax_action', 'save_featured_order');
            
            rows.forEach(row => {
                formData.append('product_ids[]', row.dataset.productId);
            });

            try {
                const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message || 'Lỗi khi lưu thứ tự!', 'error');
                }
            } catch (err) {
                showToast('Có lỗi xảy ra khi lưu thứ tự!', 'error');
            }
        }
    });
});

document.getElementById('searchProductInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    const dropdown = document.getElementById('searchResultsDropdown');

    if (query.length < 2) {
        dropdown.classList.add('hidden');
        dropdown.innerHTML = '';
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch('<?= BASE_URL ?>/admin/admin-api.php?action=search_products_to_feature&q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderSearchResults(data.products);
                }
            })
            .catch(err => console.error('Search error:', err));
    }, 300);
});

function renderSearchResults(products) {
    const dropdown = document.getElementById('searchResultsDropdown');
    dropdown.innerHTML = '';

    if (products.length === 0) {
        dropdown.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Không tìm thấy sản phẩm hợp lệ</div>';
        dropdown.classList.remove('hidden');
        return;
    }

    products.forEach(p => {
        const item = document.createElement('div');
        item.className = 'p-3 flex items-center justify-between hover:bg-gray-50 transition-colors';
        item.innerHTML = `
            <div class="flex flex-col min-w-0 pr-4">
                <span class="font-medium text-gray-800 text-sm truncate">${p.product_name}</span>
                <span class="text-xs text-gray-500">${p.category_name || 'N/A'} • ${new Intl.NumberFormat('vi-VN').format(p.base_price)}₫</span>
            </div>
            <button onclick="addFeaturedProduct(${p.product_id})" class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-xs font-semibold transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">add</span> Thêm
            </button>
        `;
        dropdown.appendChild(item);
    });

    dropdown.classList.remove('hidden');
}

function clearSearch() {
    document.getElementById('searchProductInput').value = '';
    const dropdown = document.getElementById('searchResultsDropdown');
    dropdown.classList.add('hidden');
    dropdown.innerHTML = '';
}

// Close search dropdown on click outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('searchResultsDropdown');
    const input = document.getElementById('searchProductInput');
    if (e.target !== input && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// AJAX to add featured product
async function addFeaturedProduct(productId) {
    const formData = new FormData();
    formData.append('ajax_action', 'add_featured_product');
    formData.append('product_id', productId);

    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            clearSearch();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(result.message || 'Lỗi khi thêm sản phẩm!', 'error');
        }
    } catch (err) {
        showToast('Có lỗi xảy ra!', 'error');
    }
}

// AJAX to remove featured product
function removeFeaturedProduct(productId, productName) {
    showConfirm(`Bạn có chắc muốn bỏ sản phẩm "${productName}" khỏi danh sách nổi bật không?`, async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'remove_featured_product');
        formData.append('product_id', productId);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(result.message || 'Lỗi khi xóa!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}
</script>
