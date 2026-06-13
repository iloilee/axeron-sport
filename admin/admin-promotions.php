<?php
/**
 * Admin Promotions Management
 */

// Load promotions
$promotions = $db->select("
    SELECT * FROM promotions
    ORDER BY is_active DESC, created_at DESC
");

// Load targets for modal
$all_categories = $db->select("SELECT category_id, category_name FROM categories WHERE is_visible = 1 ORDER BY category_name");
$all_products = $db->select("SELECT product_id, product_name FROM products WHERE is_deleted = 0 ORDER BY product_name");
?>

<div class="mb-6">
    <a href="javascript:void(0)" onclick="openPromotionModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">add</span>
        Thêm Khuyến Mãi
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tên / Mã</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Loại KM</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Giảm giá</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Điều kiện</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời gian</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Đã dùng</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($promotions as $promo): ?>
                <?php
                    $now = time();
                    $start = strtotime($promo['start_date']);
                    $end = strtotime($promo['end_date']);
                    $isExpired = $now > $end;
                    $isUpcoming = $now < $start;
                    $isActive = $promo['is_active'] && !$isExpired && !$isUpcoming;
                    
                    $typeLabels = [
                        'voucher' => ['Voucher', 'bg-purple-100 text-purple-800'],
                        'product' => ['Sản phẩm', 'bg-blue-100 text-blue-800'],
                        'category' => ['Danh mục', 'bg-teal-100 text-teal-800'],
                        'flashsale' => ['Flash Sale', 'bg-orange-100 text-orange-800']
                    ];
                    $t = $typeLabels[$promo['type']] ?? ['Khác', 'bg-gray-100 text-gray-800'];
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800"><?= htmlspecialchars($promo['promo_name']) ?></div>
                        <?php if ($promo['promo_code']): ?>
                            <code class="bg-gray-100 px-2 py-0.5 rounded font-mono text-xs mt-1 inline-block"><?= htmlspecialchars($promo['promo_code']) ?></code>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs font-medium <?= $t[1] ?>"><?= $t[0] ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($promo['discount_type'] === 'percent'): ?>
                        <span class="text-axeron-red font-bold"><?= (float)$promo['discount_value'] ?>%</span>
                        <?php else: ?>
                        <span class="text-axeron-red font-bold"><?= formatPrice($promo['discount_value']) ?></span>
                        <?php endif; ?>
                        <?php if ($promo['max_discount']): ?>
                        <span class="text-xs text-gray-500 block">Tối đa: <?= formatPrice($promo['max_discount']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        Tối thiểu: <?= formatPrice($promo['min_order_value']) ?>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <div><?= date('d/m/Y', $start) ?></div>
                        <div class="text-gray-500">→ <?= date('d/m/Y', $end) ?></div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?= $promo['used_count'] ?>
                        <?php if ($promo['usage_limit']): ?>
                        <span class="text-gray-400">/ <?= $promo['usage_limit'] ?></span>
                        <?php else: ?>
                        <span class="text-gray-400">/ ∞</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($isExpired): ?>
                        <span class="px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium inline-block whitespace-nowrap">Hết hạn</span>
                        <?php elseif ($isUpcoming): ?>
                        <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium inline-block whitespace-nowrap">Sắp diễn ra</span>
                        <?php elseif (!$promo['is_active']): ?>
                        <span class="px-2.5 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium inline-block whitespace-nowrap">Tạm ngưng</span>
                        <?php else: ?>
                        <span class="px-2.5 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-medium inline-block whitespace-nowrap">Đang hoạt động</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="javascript:void(0)" onclick="openPromotionModal(<?= $promo['promo_id'] ?>)"
                               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-gray-600">edit</span>
                            </a>
                            <button type="button" onclick="deletePromotion(<?= $promo['promo_id'] ?>)" class="p-2 hover:bg-red-50 rounded-lg">
                                <span class="material-symbols-outlined text-red-500">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($promotions)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">Chưa có khuyến mãi nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const allCategories = <?= json_encode($all_categories) ?>;
const allProducts = <?= json_encode($all_products) ?>;

function openPromotionModal(promoId = null) {
    const isEdit = promoId !== null;
    const title = isEdit ? 'Sửa Khuyến Mãi' : 'Thêm Khuyến Mãi Mới';

    const modalContent = `
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">${title}</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="promotionForm" class="space-y-4">
                <input type="hidden" name="ajax_action" value="${isEdit ? 'update_promotion' : 'create_promotion'}">
                ${isEdit ? `<input type="hidden" name="promo_id" value="${promoId}">` : ''}

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại chương trình *</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg" onchange="togglePromoTypeFields(this.value)">
                            <option value="voucher">Mã giảm giá (Voucher)</option>
                            <option value="product">Giảm giá Sản phẩm</option>
                            <option value="category">Giảm giá Danh mục</option>
                            <option value="flashsale">Flash Sale</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên chương trình *</label>
                        <input type="text" name="promo_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    </div>
                </div>

                <div id="promoCodeContainer">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã khuyến mãi *</label>
                    <input type="text" name="promo_code" placeholder="VD: SUMMER2024" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none uppercase">
                </div>

                <div id="promoTargetContainer" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="promoTargetLabel">Chọn mục tiêu *</label>
                    <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto" id="promoTargetCheckboxes">
                        <!-- Checkboxes will be injected here via JS -->
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại giảm giá *</label>
                        <select name="discount_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định (VNĐ)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị giảm *</label>
                        <input type="number" name="discount_value" min="0.1" step="any" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Đơn hàng tối thiểu (VNĐ)</label>
                        <input type="number" name="min_order_value" min="0" step="10000" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giảm tối đa (VNĐ)</label>
                        <input type="number" name="max_discount" min="0" step="10000" placeholder="Bỏ trống = không giới hạn" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu *</label>
                        <input type="date" name="start_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc *</label>
                        <input type="date" name="end_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số lần sử dụng tối đa</label>
                        <input type="number" name="usage_limit" min="0" placeholder="Bỏ trống = không giới hạn" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex items-center gap-4 pt-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded">
                            <span class="text-sm">Kích hoạt</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                    <button type="submit" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700">Lưu</button>
                </div>
            </form>
        </div>
    `;

    openModal(modalContent);

    const discountTypeSelect = document.querySelector('[name="discount_type"]');
    const discountValueInput = document.querySelector('[name="discount_value"]');

    function updateDiscountValueAttributes() {
        if (discountTypeSelect.value === 'percent') {
            discountValueInput.min = "0.1";
            discountValueInput.max = "100";
            discountValueInput.step = "0.1";
            discountValueInput.placeholder = "Nhập từ 0.1 đến 100";
        } else {
            discountValueInput.min = "1000";
            discountValueInput.removeAttribute('max');
            discountValueInput.step = "1000";
            discountValueInput.placeholder = "Ví dụ: 20000, 50000";
        }
    }

    if (discountTypeSelect && discountValueInput) {
        discountTypeSelect.addEventListener('change', updateDiscountValueAttributes);
        updateDiscountValueAttributes();
    }
    
    window.togglePromoTypeFields = function(type) {
        const codeCont = document.getElementById('promoCodeContainer');
        const codeInput = document.querySelector('[name="promo_code"]');
        const targetCont = document.getElementById('promoTargetContainer');
        const targetLabel = document.getElementById('promoTargetLabel');
        const targetChecks = document.getElementById('promoTargetCheckboxes');
        
        if (type === 'voucher') {
            codeCont.classList.remove('hidden');
            codeInput.required = true;
            targetCont.classList.add('hidden');
            targetChecks.innerHTML = '';
        } else {
            codeCont.classList.add('hidden');
            codeInput.required = false;
            codeInput.value = '';
            
            if (type === 'flashsale') {
                targetCont.classList.remove('hidden');
                targetLabel.textContent = 'Chọn sản phẩm tham gia Flash Sale *';
                renderCheckboxes('products', 'product_ids[]');
            } else if (type === 'product') {
                targetCont.classList.remove('hidden');
                targetLabel.textContent = 'Chọn sản phẩm giảm giá *';
                renderCheckboxes('products', 'product_ids[]');
            } else if (type === 'category') {
                targetCont.classList.remove('hidden');
                targetLabel.textContent = 'Chọn danh mục giảm giá *';
                renderCheckboxes('categories', 'category_ids[]');
            }
        }
    };
    
    function renderCheckboxes(source, name) {
        const container = document.getElementById('promoTargetCheckboxes');
        let html = '';
        if (source === 'products') {
            allProducts.forEach(p => {
                html += `<label class="flex items-center gap-2 mb-2"><input type="checkbox" name="${name}" value="${p.product_id}" class="w-4 h-4 text-axeron-red rounded"> <span class="text-sm truncate">${p.product_name}</span></label>`;
            });
        } else if (source === 'categories') {
            allCategories.forEach(c => {
                html += `<label class="flex items-center gap-2 mb-2"><input type="checkbox" name="${name}" value="${c.category_id}" class="w-4 h-4 text-axeron-red rounded"> <span class="text-sm">${c.category_name}</span></label>`;
            });
        }
        container.innerHTML = html || '<p class="text-gray-500 text-sm">Không có dữ liệu</p>';
    }

    // Set default dates
    if (!isEdit) {
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('[name="start_date"]').value = today;
        document.querySelector('[name="end_date"]').value = today;
    }

    // If editing, load promotion data
    if (isEdit) {
        fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_promotion&id=' + promoId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.promotion) {
                    const p = data.promotion;
                    document.querySelector('[name="type"]').value = p.type || 'voucher';
                    togglePromoTypeFields(p.type || 'voucher');
                    
                    document.querySelector('[name="promo_code"]').value = p.promo_code || '';
                    document.querySelector('[name="promo_name"]').value = p.promo_name || '';
                    document.querySelector('[name="discount_type"]').value = p.discount_type || 'percent';
                    document.querySelector('[name="discount_value"]').value = p.discount_value || '';
                    document.querySelector('[name="min_order_value"]').value = p.min_order_value || 0;
                    document.querySelector('[name="max_discount"]').value = p.max_discount || '';
                    document.querySelector('[name="start_date"]').value = p.start_date ? p.start_date.split(' ')[0] : '';
                    document.querySelector('[name="end_date"]').value = p.end_date ? p.end_date.split(' ')[0] : '';
                    document.querySelector('[name="usage_limit"]').value = p.usage_limit || '';
                    document.querySelector('[name="is_active"]').checked = p.is_active == 1;
                    updateDiscountValueAttributes();
                    
                    // Check targets
                    setTimeout(() => {
                        if (p.type === 'product' || p.type === 'flashsale') {
                            const pIds = data.product_ids || [];
                            pIds.forEach(id => {
                                const cb = document.querySelector(`input[name="product_ids[]"][value="${id}"]`);
                                if (cb) cb.checked = true;
                            });
                        } else if (p.type === 'category') {
                            const cIds = data.category_ids || [];
                            cIds.forEach(id => {
                                const cb = document.querySelector(`input[name="category_ids[]"][value="${id}"]`);
                                if (cb) cb.checked = true;
                            });
                        }
                    }, 100);
                }
            });
    }

    // Handle form submit
    document.getElementById('promotionForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Thao tác thành công!', 'success');
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    };
}

function deletePromotion(promoId) {
    showConfirm('Bạn có chắc muốn xóa khuyến mãi này?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'delete_promotion');
        formData.append('promo_id', promoId);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                showToast(result.message || 'Thao tác thành công!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}
</script>
