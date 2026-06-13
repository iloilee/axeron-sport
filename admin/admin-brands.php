<?php
/**
 * Admin Brands Management
 */

// Pagination
$limitOptions = [9, 18, 27, 54];
$limit = (int)($_GET['limit'] ?? 9);
if (!in_array($limit, $limitOptions)) $limit = 9;
$currentPage = (int)($_GET['page'] ?? 1);
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $limit;

$totalRecordsQuery = "SELECT COUNT(*) as count FROM brands";
$totalRecords = $db->selectOne($totalRecordsQuery)['count'] ?? 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 0;

// Load brands
$brands = $db->select("SELECT * FROM brands ORDER BY brand_name LIMIT $limit OFFSET $offset");
?>

<div class="mb-6 flex justify-between items-center">
    <div class="px-4 py-2 bg-red-50 border border-red-100 rounded-lg text-sm font-medium text-axeron-red whitespace-nowrap">
        Tổng số: <strong class="text-base"><?= number_format($totalRecords) ?></strong> thương hiệu
    </div>
    <a href="javascript:void(0)" onclick="openBrandModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors inline-flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">add</span>
        Thêm Thương Hiệu
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($brands as $brand): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between mb-4">
            <?php if ($brand['logo_url']): ?>
            <img src="<?= $brand['logo_url'] ?>" alt="<?= htmlspecialchars($brand['brand_name']) ?>" class="h-12 object-contain">
            <?php else: ?>
            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-gray-400 text-2xl">branding_watermark</span>
            </div>
            <?php endif; ?>
            <div class="flex gap-1">
                <a href="javascript:void(0)" onclick="openBrandModal(<?= $brand['brand_id'] ?>)"
                   class="p-2 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined text-gray-600">edit</span>
                </a>
                <button type="button" onclick="deleteBrand(<?= $brand['brand_id'] ?>)" class="p-2 hover:bg-red-50 rounded-lg">
                    <span class="material-symbols-outlined text-red-500">delete</span>
                </button>
            </div>
        </div>
        <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($brand['brand_name']) ?></h3>
        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($brand['description'] ?? 'Không có mô tả') ?></p>
        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
            <?php if ($brand['is_active']): ?>
            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Hoạt động</span>
            <?php else: ?>
            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Tạm ngưng</span>
            <?php endif; ?>
            <button type="button" onclick="toggleBrandStatus(<?= $brand['brand_id'] ?>, <?= $brand['is_active'] ? '0' : '1' ?>)" class="text-sm text-axeron-blue hover:underline">
                <?= $brand['is_active'] ? 'Tạm ngưng' : 'Kích hoạt' ?>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 mt-6">
    <?php include __DIR__ . '/includes/pagination.php'; ?>
</div>

<script>
function openBrandModal(brandId = null) {
    const isEdit = brandId !== null;
    const title = isEdit ? 'Sửa Thương Hiệu' : 'Thêm Thương Hiệu Mới';

    const modalContent = `
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">${title}</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="brandForm" class="space-y-4">
                <input type="hidden" name="ajax_action" value="${isEdit ? 'update_brand' : 'create_brand'}">
                ${isEdit ? `<input type="hidden" name="brand_id" value="${brandId}">` : ''}

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên thương hiệu *</label>
                    <input type="text" name="brand_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                    <input type="url" name="logo_url" placeholder="https://..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded">
                        <span class="text-sm">Hoạt động</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                    <button type="submit" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700">Lưu</button>
                </div>
            </form>
        </div>
    `;

    openModal(modalContent);

    // If editing, load brand data
    if (isEdit) {
        fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_brand&id=' + brandId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.brand) {
                    const b = data.brand;
                    document.querySelector('[name="brand_name"]').value = b.brand_name || '';
                    document.querySelector('[name="logo_url"]').value = b.logo_url || '';
                    document.querySelector('[name="description"]').value = b.description || '';
                    document.querySelector('[name="is_active"]').checked = b.is_active == 1;
                }
            });
    }

    // Handle form submit
    document.getElementById('brandForm').onsubmit = async function(e) {
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

function deleteBrand(brandId) {
    showConfirm('Bạn có chắc muốn xóa thương hiệu này?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'delete_brand');
        formData.append('brand_id', brandId);

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

function toggleBrandStatus(brandId, newStatus) {
    showConfirm('Bạn có chắc muốn thay đổi trạng thái?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'toggle_brand_status');
        formData.append('brand_id', brandId);
        formData.append('new_status', newStatus);

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
