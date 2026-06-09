<?php
/**
 * Admin Shipping Price Management
 */

// Load shipping prices
$shippingPrices = $db->select("
    SELECT * FROM shipping_prices
    ORDER BY province_city ASC
");

// List of all Vietnam provinces/cities for the dropdown filter
$provinces = [
    "TP. Hồ Chí Minh", "Hà Nội", "Đà Nẵng", "Hải Phòng", "Cần Thơ",
    "An Giang", "Bà Rịa - Vũng Tàu", "Bắc Giang", "Bắc Kạn", "Bạc Liêu",
    "Bắc Ninh", "Bến Tre", "Bình Định", "Bình Dương", "Bình Phước",
    "Bình Thuận", "Cà Mau", "Cao Bằng", "Đắk Lắk", "Đắk Nông",
    "Điện Biên", "Đồng Nai", "Đồng Tháp", "Gia Lai", "Hà Giang",
    "Hà Nam", "Hà Tĩnh", "Hải Dương", "Hậu Giang", "Hòa Bình",
    "Hưng Yên", "Khánh Hòa", "Kiên Giang", "Kon Tum", "Lai Châu",
    "Lâm Đồng", "Lạng Sơn", "Lào Cai", "Long An", "Nam Định",
    "Nghệ An", "Ninh Bình", "Ninh Thuận", "Phú Thọ", "Phú Yên",
    "Quảng Bình", "Quảng Nam", "Quảng Ngãi", "Quảng Ninh", "Quảng Trị",
    "Sóc Trăng", "Sơn La", "Tây Ninh", "Thái Bình", "Thái Nguyên",
    "Thanh Hóa", "Thừa Thiên Huế", "Tiền Giang", "Trà Vinh", "Tuyên Quang",
    "Vĩnh Long", "Vĩnh Phúc", "Yên Bái"
];

$configuredCities = array_column($shippingPrices, 'province_city');
?>

<div class="mb-6">
    <a href="javascript:void(0)" onclick="openShippingPriceModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">add_location</span>
        Thêm Cấu Hình Vận Chuyển
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tỉnh / Thành Phố</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Phí Vận Chuyển</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời Gian Dự Kiến</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($shippingPrices as $sp): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">
                        <?= htmlspecialchars($sp['province_city']) ?>
                        <?php if ($sp['shipping_id'] == 1): ?>
                            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Mặc định</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-semibold text-axeron-red"><?= formatPrice($sp['base_price']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= $sp['estimated_days'] ?> ngày làm việc</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="javascript:void(0)" onclick="openShippingPriceModal(<?= $sp['shipping_id'] ?>)"
                               class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Sửa">
                                <span class="material-symbols-outlined text-gray-600">edit</span>
                            </a>
                            <?php if ($sp['shipping_id'] != 1): ?>
                            <a href="javascript:void(0)" onclick="deleteShippingPrice(<?= $sp['shipping_id'] ?>)"
                               class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                <span class="material-symbols-outlined text-red-500">delete</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($shippingPrices)): ?>
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">Chưa có cấu hình phí vận chuyển nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const provinces = <?= json_encode($provinces) ?>;
const configuredCities = <?= json_encode($configuredCities) ?>;

function openShippingPriceModal(shippingId = null) {
    const isEdit = shippingId !== null;
    const title = isEdit ? 'Sửa Cấu Hình Phí Vận Chuyển' : 'Thêm Cấu Hình Phí Vận Chuyển';

    let cityField = '';
    if (isEdit) {
        cityField = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh / Thành phố</label>
                <input type="text" id="province_city_display" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                <input type="hidden" name="province_city" id="province_city">
            </div>
        `;
    } else {
        const availableCities = provinces.filter(c => !configuredCities.includes(c));
        let selectOptions = availableCities.map(c => `<option value="${c}">${c}</option>`).join('');
        
        if (availableCities.length === 0) {
            selectOptions = `<option value="">Đã cấu hình phí cho tất cả tỉnh thành</option>`;
        }

        cityField = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tỉnh / Thành phố *</label>
                <select name="province_city" id="province_city" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    ${selectOptions}
                </select>
            </div>
        `;
    }

    const modalContent = `
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">${title}</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="shippingPriceForm" class="space-y-4">
                <input type="hidden" name="ajax_action" value="${isEdit ? 'update_shipping_price' : 'add_shipping_price'}">
                ${isEdit ? `<input type="hidden" name="shipping_id" value="${shippingId}">` : ''}

                ${cityField}

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phí vận chuyển (VNĐ) *</label>
                        <input type="number" name="base_price" id="base_price" min="0" step="1000" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian dự kiến (ngày) *</label>
                        <input type="number" name="estimated_days" id="estimated_days" min="1" step="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
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

    // If editing, load shipping price data
    if (isEdit) {
        fetch('<?= BASE_URL ?>/admin-api.php?action=get_shipping_price&id=' + shippingId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.shipping) {
                    const sp = data.shipping;
                    document.getElementById('province_city_display').value = sp.province_city;
                    document.getElementById('province_city').value = sp.province_city;
                    document.getElementById('base_price').value = Math.round(sp.base_price);
                    document.getElementById('estimated_days').value = sp.estimated_days;
                } else {
                    showToast(data.message || 'Không thể tải dữ liệu cấu hình!', 'error');
                }
            });
    } else {
        // Set defaults for add
        document.getElementById('base_price').value = 30000;
        document.getElementById('estimated_days').value = 3;
    }

    // Handle form submit
    document.getElementById('shippingPriceForm').onsubmit = async function(e) {
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

async function deleteShippingPrice(shippingId) {
    if (!confirm('Bạn có chắc muốn xóa cấu hình phí vận chuyển này? Các đơn hàng cũ đã giao/hủy áp dụng cấu hình này sẽ tự động được chuyển về mức mặc định.')) return;

    const formData = new FormData();
    formData.append('ajax_action', 'delete_shipping_price');
    formData.append('shipping_id', shippingId);

    try {
        const response = await fetch('<?= BASE_URL ?>/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) location.reload();
    } catch (err) {
        showToast('Có lỗi xảy ra!', 'error');
    }
}
</script>
