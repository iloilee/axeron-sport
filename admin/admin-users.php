<?php
/**
 * Admin Users Management
 */

// Load users
$roleFilter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($roleFilter) {
    $where .= " AND u.role_id = ?";
    $params[] = $roleFilter;
}

$users = $db->select("
    SELECT u.*, r.role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.role_id
    $where
    ORDER BY u.created_at DESC
", $params);

// Roles for filter
$roles = $db->select("SELECT role_id, role_name FROM roles");

// Get current user ID for JavaScript
$currentUserId = getUserId();
?>

<div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="hidden" name="action" value="users">
        <input type="text" name="search" placeholder="Tìm theo tên, email, SĐT..." value="<?= htmlspecialchars($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent outline-none">
        <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg" onchange="this.form.submit()">
            <option value="">Tất cả vai trò</option>
            <?php foreach ($roles as $role): ?>
            <?php
            $rFriendly = match($role['role_name']) {
                'admin' => 'Admin',
                'staff' => 'Nhân viên',
                'customer' => 'Khách hàng',
                'staff_accounts' => 'Nhân viên QL tài khoản',
                'staff_products' => 'Nhân viên QL sản phẩm',
                'staff_orders' => 'Nhân viên QL đơn hàng',
                'staff_analytics' => 'Nhân viên QL thống kê',
                'staff_cms' => 'Nhân viên QL trang chủ',
                default => ucfirst($role['role_name'])
            };
            ?>
            <option value="<?= $role['role_id'] ?>" <?= $roleFilter == $role['role_id'] ? 'selected' : '' ?>>
                <?= $rFriendly ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="javascript:void(0)" onclick="openUserModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">person_add</span>
        Thêm Người Dùng
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Người dùng</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Số điện thoại</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vai trò</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ngày tạo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($users as $user): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($user['avatar_url'])): ?>
                                <?php 
                                    $avatarPath = strpos($user['avatar_url'], 'http') === 0 ? htmlspecialchars($user['avatar_url']) : BASE_URL . '/' . ltrim(htmlspecialchars($user['avatar_url']), '/');
                                ?>
                                <img src="<?= $avatarPath ?>" alt="<?= htmlspecialchars($user['full_name']) ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                            <?php else: ?>
                                <div class="w-10 h-10 bg-axeron-red rounded-full flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($user['full_name']) ?></p>
                                <p class="text-xs text-gray-500">ID: <?= $user['user_id'] ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $roleClass = match($user['role_name']) {
                            'admin' => 'bg-red-100 text-red-800',
                            'staff', 'staff_accounts', 'staff_products', 'staff_orders', 'staff_analytics', 'staff_cms' => 'bg-blue-100 text-blue-800',
                            'customer' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        $roleText = match($user['role_name']) {
                            'admin' => 'Admin',
                            'staff' => 'Nhân viên',
                            'customer' => 'Khách hàng',
                            'staff_accounts' => 'Nhân viên QL tài khoản',
                            'staff_products' => 'Nhân viên QL sản phẩm',
                            'staff_orders' => 'Nhân viên QL đơn hàng',
                            'staff_analytics' => 'Nhân viên QL thống kê',
                            'staff_cms' => 'Nhân viên QL trang chủ',
                            default => ucfirst($user['role_name'])
                        };
                        ?>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium inline-block whitespace-nowrap <?= $roleClass ?>"><?= $roleText ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if (!$user['is_active']): ?>
                        <span class="px-2.5 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium inline-block whitespace-nowrap">Bị khóa</span>
                        <?php elseif (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()): ?>
                        <span class="px-2.5 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium inline-block whitespace-nowrap">Khóa tạm (<?= date('H:i', strtotime($user['locked_until'])) ?>)</span>
                        <?php else: ?>
                        <span class="px-2.5 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-medium inline-block whitespace-nowrap">Hoạt động</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="javascript:void(0)" onclick="openUserModal(<?= $user['user_id'] ?>)"
                               class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Sửa">
                                <span class="material-symbols-outlined text-gray-600">edit</span>
                            </a>
                            <?php if ($user['user_id'] != $currentUserId): ?>
                            <?php if (!$user['is_active'] || (!empty($user['locked_until']) && strtotime($user['locked_until']) > time())): ?>
                            <a href="javascript:void(0)" onclick="toggleUserStatus(<?= $user['user_id'] ?>, 1)"
                               class="p-2 hover:bg-green-50 rounded-lg transition-colors" title="Mở khóa">
                                <span class="material-symbols-outlined text-green-600">lock_open</span>
                            </a>
                            <?php else: ?>
                            <a href="javascript:void(0)" onclick="toggleUserStatus(<?= $user['user_id'] ?>, 0)"
                               class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Khóa tài khoản">
                                <span class="material-symbols-outlined text-red-500">lock</span>
                            </a>
                            <?php endif; ?>
                            <a href="javascript:void(0)" onclick="deleteUser(<?= $user['user_id'] ?>)"
                               class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                <span class="material-symbols-outlined text-red-500">delete</span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">Không tìm thấy người dùng nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const currentUserId = <?= $currentUserId ?>;
const roles = <?= json_encode($roles) ?>;

function openUserModal(userId = null) {
    const isEdit = userId !== null;
    const isCurrentUser = isEdit && userId === currentUserId;
    const title = isEdit ? 'Sửa Người Dùng' : 'Thêm Người Dùng Mới';

    let passwordSection = '';
    let roleSelect = '<select name="role_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">';
    roles.forEach(r => {
        let label = r.role_name;
        switch(r.role_name) {
            case 'admin': label = 'Admin'; break;
            case 'staff': label = 'Nhân viên'; break;
            case 'customer': label = 'Khách hàng'; break;
            case 'staff_accounts': label = 'Nhân viên QL tài khoản'; break;
            case 'staff_products': label = 'Nhân viên QL sản phẩm'; break;
            case 'staff_orders': label = 'Nhân viên QL đơn hàng'; break;
            case 'staff_analytics': label = 'Nhân viên QL thống kê'; break;
            case 'staff_cms': label = 'Nhân viên QL trang chủ'; break;
        }
        let selectedAttr = (!isEdit && r.role_name === 'customer') ? 'selected' : '';
        roleSelect += `<option value="${r.role_id}" ${selectedAttr}>${label}</option>`;
    });
    roleSelect += '</select>';

    if (isEdit) {
        if (isCurrentUser) {
            passwordSection = `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800 mb-3">Bạn đang chỉnh sửa tài khoản của chính mình. Nếu muốn đổi mật khẩu, hãy điền thông tin bên dưới.</p>
                </div>
                <div class="border-t pt-4 mt-4">
                    <h4 class="font-semibold text-gray-700 mb-3">Đổi mật khẩu (tùy chọn)</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                            <input type="password" name="new_password" minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none" placeholder="Để trống nếu không đổi">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none" placeholder="Nhập lại mật khẩu mới">
                        </div>
                    </div>
                </div>
            `;
        }
        // Không cho phép đổi role của chính mình
        if (isCurrentUser) {
            roleSelect = `<input type="hidden" name="role_id" value="1"><span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Admin</span>`;
        }
    } else {
        passwordSection = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu *</label>
                <input type="password" name="password" required minlength="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                <p class="text-xs text-gray-500 mt-1">Tối thiểu 6 ký tự</p>
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
            <form id="userForm" class="space-y-4">
                <input type="hidden" name="ajax_action" value="${isEdit ? 'update_user' : 'create_user'}">
                ${isEdit ? `<input type="hidden" name="user_id" value="${userId}">` : ''}

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên *</label>
                    <input type="text" name="full_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                    <input type="text" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>

                ${passwordSection}

                ${!isCurrentUser ? `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò *</label>
                    ${roleSelect}
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded">
                        <span class="text-sm">Hoạt động</span>
                    </label>
                </div>
                ` : ''}

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                    <button type="submit" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700">Lưu</button>
                </div>
            </form>
        </div>
    `;

    openModal(modalContent);

    // If editing, load user data
    if (isEdit) {
        fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_user&id=' + userId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.user) {
                    const u = data.user;
                    document.querySelector('[name="full_name"]').value = u.full_name || '';
                    document.querySelector('[name="email"]').value = u.email || '';
                    document.querySelector('[name="phone"]').value = u.phone || '';
                    if (document.querySelector('[name="role_id"]')) {
                        document.querySelector('[name="role_id"]').value = u.role_id || '';
                    }
                    if (document.querySelector('[name="is_active"]')) {
                        document.querySelector('[name="is_active"]').checked = u.is_active == 1;
                    }
                }
            });
    }

    // Handle form submit
    document.getElementById('userForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Validate password match if changing password
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');
        if (newPassword && newPassword !== confirmPassword) {
            showToast('Mật khẩu xác nhận không khớp!', 'error');
            return;
        }

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

function toggleUserStatus(userId, newStatus) {
    showConfirm(newStatus ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'toggle_user_status');
        formData.append('user_id', userId);
        formData.append('new_status', newStatus);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            if (result.success) { showToast("Thao tác thành công!", "success"); setTimeout(() => location.reload(), 1000); }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

function deleteUser(userId) {
    showConfirm('Bạn có chắc muốn xóa người dùng này?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'delete_user');
        formData.append('user_id', userId);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            if (result.success) { showToast("Thao tác thành công!", "success"); setTimeout(() => location.reload(), 1000); }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}
</script>
