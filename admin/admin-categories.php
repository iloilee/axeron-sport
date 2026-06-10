<?php
/**
 * Admin Categories Management - Tree View
 */

// Load all categories
$categories = $db->select("
    SELECT c.*, p.category_name as parent_name
    FROM categories c
    LEFT JOIN categories p ON c.parent_id = p.category_id
    ORDER BY c.sort_order, c.category_id
");

// Build hierarchical tree
function buildCategoryTree($categories, $parentId = null) {
    $tree = [];
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parentId) {
            $cat['children'] = buildCategoryTree($categories, $cat['category_id']);
            $tree[] = $cat;
        }
    }
    return $tree;
}

$categoryTree = buildCategoryTree($categories);

// Build flat list with depth for select options
function buildFlatList($categories, $parentId = null, $depth = 0) {
    $list = [];
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parentId) {
            $cat['depth'] = $depth;
            $list[] = $cat;
            $list = array_merge($list, buildFlatList($categories, $cat['category_id'], $depth + 1));
        }
    }
    return $list;
}

$flatCategories = buildFlatList($categories);

// Recursive function to render tree
function renderCategoryTree($tree, $level = 0) {
    foreach ($tree as $cat):
        $indent = $level * 24;
        $bgClass = $level === 0 ? 'bg-white' : ($level === 1 ? 'bg-gray-50/50' : 'bg-gray-50');
        $iconBg = $level === 0 ? 'bg-axeron-red/10 text-axeron-red' : ($level === 1 ? 'bg-blue-50 text-blue-500' : 'bg-gray-100 text-gray-400');
        $icon = $level === 0 ? 'folder' : ($level === 1 ? 'folder_open' : 'description');
        $fontClass = $level === 0 ? 'font-semibold text-gray-900' : ($level === 1 ? 'font-medium text-gray-800' : 'text-gray-600');
        $hasChildren = !empty($cat['children']);
?>
        <div class="category-row flex items-center justify-between px-4 py-3 hover:bg-blue-50/50 transition-colors border-b border-gray-100 last:border-b-0 <?= $bgClass ?>"
             style="padding-left: <?= 16 + $indent ?>px;">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <?php if ($level > 0): ?>
                <div class="flex items-center gap-1 text-gray-300 flex-shrink-0">
                    <?php for ($i = 0; $i < $level; $i++): ?>
                    <span class="<?= $i < $level - 1 ? 'w-3' : '' ?>"></span>
                    <?php endfor; ?>
                    <span class="material-symbols-outlined text-base">subdirectory_arrow_right</span>
                </div>
                <?php endif; ?>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $iconBg ?>">
                    <span class="material-symbols-outlined text-lg"><?= $icon ?></span>
                </div>
                <div class="min-w-0">
                    <p class="<?= $fontClass ?> truncate"><?= htmlspecialchars($cat['category_name']) ?></p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-gray-400">/<?= $cat['slug'] ?></span>
                        <?php if ($cat['parent_name']): ?>
                        <span class="text-xs text-gray-300">•</span>
                        <span class="text-xs text-gray-400">Thuộc: <span class="text-axeron-red"><?= htmlspecialchars($cat['parent_name']) ?></span></span>
                        <?php endif; ?>
                        <?php if ($hasChildren): ?>
                        <span class="text-xs text-gray-300">•</span>
                        <span class="text-xs text-blue-500"><?= count($cat['children']) ?> danh mục con</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                <?php if ($cat['is_visible']): ?>
                <span class="px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium inline-block whitespace-nowrap">Hiển thị</span>
                <?php else: ?>
                <span class="px-2.5 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-medium inline-block whitespace-nowrap">Ẩn</span>
                <?php endif; ?>
                <a href="javascript:void(0)" onclick="openCategoryModal(<?= $cat['category_id'] ?>)"
                   class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors" title="Sửa">
                    <span class="material-symbols-outlined text-gray-500 text-xl">edit</span>
                </a>
                <button onclick="deleteCategory(<?= $cat['category_id'] ?>)"
                        class="p-1.5 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                    <span class="material-symbols-outlined text-red-400 text-xl">delete</span>
                </button>
            </div>
        </div>
<?php
        // Render children recursively
        if ($hasChildren) {
            renderCategoryTree($cat['children'], $level + 1);
        }
    endforeach;
}
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <p class="text-sm text-gray-500 mt-1">Tổng cộng <?= count($categories) ?> danh mục</p>
    </div>
    <a href="javascript:void(0)" onclick="openCategoryModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors inline-flex items-center gap-2 shadow-sm">
        <span class="material-symbols-outlined text-xl">add</span>
        Thêm Danh Mục
    </a>
</div>

<!-- Category Tree -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-axeron-red">account_tree</span>
            <h3 class="font-bold text-gray-800">Cây Danh Mục</h3>
        </div>
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-axeron-red/10 inline-block"></span> Cấp 1
            </span>
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-blue-50 inline-block"></span> Cấp 2
            </span>
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-gray-100 inline-block"></span> Cấp 3+
            </span>
        </div>
    </div>
    <div class="max-h-[600px] overflow-y-auto">
        <?php if (!empty($categoryTree)): ?>
            <?php renderCategoryTree($categoryTree); ?>
        <?php else: ?>
            <div class="p-12 text-center text-gray-500">
                <span class="material-symbols-outlined text-5xl text-gray-300 mb-3 block">category</span>
                <p class="font-medium">Chưa có danh mục nào</p>
                <p class="text-sm mt-1">Nhấn "Thêm Danh Mục" để bắt đầu</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openCategoryModal(categoryId = null) {
    const isEdit = categoryId !== null;
    const title = isEdit ? 'Sửa Danh Mục' : 'Thêm Danh Mục Mới';

    // All categories with depth for hierarchical select
    const allCategories = <?= json_encode(array_map(function($c) {
        return [
            'category_id' => $c['category_id'],
            'category_name' => $c['category_name'],
            'depth' => $c['depth'],
            'parent_id' => $c['parent_id']
        ];
    }, $flatCategories)) ?>;

    // Build indented options for parent select
    const parentOptions = allCategories.map(c => {
        const prefix = '—'.repeat(c.depth);
        const indent = c.depth > 0 ? prefix + ' ' : '';
        return `<option value="${c.category_id}">${indent}${c.category_name}</option>`;
    }).join('');

    const modalContent = `
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">${title}</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="categoryForm" class="space-y-4">
                <input type="hidden" name="ajax_action" value="${isEdit ? 'update_category' : 'create_category'}">
                ${isEdit ? `<input type="hidden" name="category_id" value="${categoryId}">` : ''}

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên danh mục *</label>
                    <input type="text" name="category_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục cha</label>
                    <select name="parent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">-- Không có (Danh mục gốc) --</option>
                        ${parentOptions}
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Chọn danh mục cha để tạo danh mục con</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <input type="text" name="slug" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    <p class="text-xs text-gray-500 mt-1">Để trống để tự tạo từ tên</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex items-center gap-4 pt-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_visible" value="1" checked class="w-5 h-5 rounded">
                            <span class="text-sm">Hiển thị</span>
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

    // If editing, load category data and filter out self from parent options
    if (isEdit) {
        // Remove self from parent dropdown to prevent circular reference
        const parentSelect = document.querySelector('[name="parent_id"]');
        if (parentSelect) {
            const selfOption = parentSelect.querySelector(`option[value="${categoryId}"]`);
            if (selfOption) selfOption.remove();
        }

        fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_category&id=' + categoryId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.category) {
                    const c = data.category;
                    document.querySelector('[name="category_name"]').value = c.category_name || '';
                    document.querySelector('[name="parent_id"]').value = c.parent_id || '';
                    document.querySelector('[name="slug"]').value = c.slug || '';
                    document.querySelector('[name="description"]').value = c.description || '';
                    document.querySelector('[name="sort_order"]').value = c.sort_order || 0;
                    document.querySelector('[name="is_visible"]').checked = c.is_visible == 1;
                }
            });
    }

    // Handle form submit
    document.getElementById('categoryForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
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

async function deleteCategory(categoryId) {
    if (!confirm('Bạn có chắc muốn xóa danh mục này?')) return;

    try {
        const formData = new FormData();
        formData.append('ajax_action', 'delete_category');
        formData.append('category_id', categoryId);

        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Danh mục đã được xóa!', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (err) {
        showToast('Có lỗi xảy ra khi xóa danh mục!', 'error');
    }
}
</script>
