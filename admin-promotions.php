<?php
/**
 * Admin Promotions Management
 */

// Load promotions
$promotions = $db->select("
    SELECT * FROM promotions
    ORDER BY is_active DESC, created_at DESC
");
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mã KM</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tên chương trình</th>
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
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <code class="bg-gray-100 px-2 py-1 rounded font-mono text-sm"><?= htmlspecialchars($promo['promo_code']) ?></code>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($promo['promo_name']) ?></td>
                    <td class="px-4 py-3">
                        <?php if ($promo['discount_type'] === 'percent'): ?>
                        <span class="text-axeron-red font-bold"><?= $promo['discount_value'] ?>%</span>
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
                            <form method="POST" class="inline" onsubmit="return confirm('Xóa khuyến mãi này?');">
                                <input type="hidden" name="ajax_action" value="delete_promotion">
                                <input type="hidden" name="promo_id" value="<?= $promo['promo_id'] ?>">
                                <button type="submit" class="p-2 hover:bg-red-50 rounded-lg">
                                    <span class="material-symbols-outlined text-red-500">delete</span>
                                </button>
                            </form>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã khuyến mãi *</label>
                        <input type="text" name="promo_code" required placeholder="VD: SUMMER2024" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none uppercase">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên chương trình *</label>
                        <input type="text" name="promo_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
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

    // Set default dates
    if (!isEdit) {
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('[name="start_date"]').value = today;
        document.querySelector('[name="end_date"]').value = today;
    }

    // If editing, load promotion data
    if (isEdit) {
        fetch('<?= BASE_URL ?>/admin-api.php?action=get_promotion&id=' + promoId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.promotion) {
                    const p = data.promotion;
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
                }
            });
    }

    // Handle form submit
    document.getElementById('promotionForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Thao tác thành công!');
                closeModal();
                location.reload();
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    };
}
</script>
