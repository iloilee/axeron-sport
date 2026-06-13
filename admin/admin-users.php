<?php
/**
 * Admin Users Management
 */

// Load users
$roleFilter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$limit = (int)($_GET['limit'] ?? 10);
if (!in_array($limit, [10, 20, 50, 100])) $limit = 10;
$currentPage = (int)($_GET['page'] ?? 1);
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $limit;

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

$totalRecordsQuery = "
    SELECT COUNT(*) as count 
    FROM users u 
    $where
";
$totalRecords = $db->selectOne($totalRecordsQuery, $params)['count'] ?? 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 0;

$users = $db->select("
    SELECT u.*, r.role_name,
           TIMESTAMPDIFF(SECOND, NOW(), u.locked_until) as lockout_seconds
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.role_id
    $where
    ORDER BY u.user_id ASC
    LIMIT $limit OFFSET $offset
", $params);

// Roles for filter
$roles = $db->select("SELECT role_id, role_name FROM roles");

// Get current user ID for JavaScript
$currentUserId = getUserId();

// Lấy thống kê
$thisMonthStart = date('Y-m-01');
$lastMonthStart = date('Y-m-01', strtotime('-1 month'));

$totalUsersCurrent = $db->selectOne("SELECT COUNT(*) as count FROM users")['count'];
$totalUsersPrev = $db->selectOne("SELECT COUNT(*) as count FROM users WHERE created_at < ?", [$thisMonthStart])['count'];

$newUsersCurrent = $db->selectOne("SELECT COUNT(*) as count FROM users WHERE created_at >= ?", [$thisMonthStart])['count'];
$newUsersPrev = $db->selectOne("SELECT COUNT(*) as count FROM users WHERE created_at >= ? AND created_at < ?", [$lastMonthStart, $thisMonthStart])['count'];

$staffCurrent = $db->selectOne("SELECT COUNT(*) as count FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name LIKE 'staff%'")['count'];
$staffPrev = $db->selectOne("SELECT COUNT(*) as count FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name LIKE 'staff%' AND u.created_at < ?", [$thisMonthStart])['count'];

$customerCurrent = $db->selectOne("SELECT COUNT(*) as count FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name = 'customer'")['count'];
$customerPrev = $db->selectOne("SELECT COUNT(*) as count FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name = 'customer' AND u.created_at < ?", [$thisMonthStart])['count'];

function calculateUserTrend($current, $prev) {
    if ($prev == 0) return ['trend' => 'up', 'percent' => $current > 0 ? 100 : 0];
    $diff = $current - $prev;
    $percent = ($diff / $prev) * 100;
    return [
        'trend' => $diff >= 0 ? 'up' : 'down',
        'percent' => abs(round($percent, 1))
    ];
}

$userStats = [
    'total' => ['count' => $totalUsersCurrent, 'trend' => calculateUserTrend($totalUsersCurrent, $totalUsersPrev)],
    'new' => ['count' => $newUsersCurrent, 'trend' => calculateUserTrend($newUsersCurrent, $newUsersPrev)],
    'staff' => ['count' => $staffCurrent, 'trend' => calculateUserTrend($staffCurrent, $staffPrev)],
    'customer' => ['count' => $customerCurrent, 'trend' => calculateUserTrend($customerCurrent, $customerPrev)]
];

function renderStatCard($title, $value, $trendData, $icon, $colorClass, $bgColorClass) {
    $trendIcon = $trendData['trend'] === 'up' ? 'trending_up' : 'trending_down';
    $trendColor = $trendData['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600';
    $percent = $trendData['percent'] . '%';
    
    return '
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500">'.$title.'</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900">'.number_format($value).'</h3>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg '.$bgColorClass.' '.$colorClass.'">
                <span class="material-symbols-outlined">'.$icon.'</span>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
            <span class="flex items-center text-xs font-medium '.$trendColor.'">
                <span class="material-symbols-outlined !text-sm mr-1">'.$trendIcon.'</span>
                '.$percent.'
            </span>
            <span class="text-xs text-slate-500">so với tháng trước</span>
        </div>
    </div>';
}
?>

<!-- Thống kê nhanh -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <?= renderStatCard('Tổng người dùng', $userStats['total']['count'], $userStats['total']['trend'], 'group', 'text-blue-600', 'bg-blue-50') ?>
    <?= renderStatCard('Người dùng mới', $userStats['new']['count'], $userStats['new']['trend'], 'person_add', 'text-green-600', 'bg-green-50') ?>
    <?= renderStatCard('Nhân viên', $userStats['staff']['count'], $userStats['staff']['trend'], 'badge', 'text-purple-600', 'bg-purple-50') ?>
    <?= renderStatCard('Khách hàng', $userStats['customer']['count'], $userStats['customer']['trend'], 'person', 'text-orange-600', 'bg-orange-50') ?>
</div>

<div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
    <div class="flex flex-col xl:flex-row gap-3 items-start xl:items-center">
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
        <div class="px-4 py-2 bg-red-50 border border-red-100 rounded-lg text-sm font-medium text-axeron-red whitespace-nowrap">
            Tổng số: <strong class="text-base"><?= number_format($totalRecords) ?></strong> người dùng
        </div>
    </div>
    <a href="javascript:void(0)" onclick="openUserModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2 whitespace-nowrap">
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
                        <?php elseif (!empty($user['locked_until']) && $user['lockout_seconds'] > 0): ?>
                        <?php 
                            $remaining_mins = floor($user['lockout_seconds'] / 60);
                            $remaining_secs = $user['lockout_seconds'] % 60;
                            $time_display = sprintf("%02d:%02d", $remaining_mins, $remaining_secs);
                        ?>
                        <span class="px-2.5 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium inline-block whitespace-nowrap">Khóa tạm (<?= $time_display ?>)</span>
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
                            <?php if (!$user['is_active'] || (!empty($user['locked_until']) && $user['lockout_seconds'] > 0)): ?>
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
    <div class="p-4">
        <?php include __DIR__ . '/includes/pagination.php'; ?>
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
                            <input type="password" id="admin_password" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none" placeholder="Để trống nếu không đổi">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none" placeholder="Nhập lại mật khẩu mới">
                        </div>
                        <div id="password-requirements" class="mt-2 p-3 bg-gray-50 rounded-xl border border-gray-200 text-sm" style="display:none;">
                            <p class="font-semibold text-sm text-gray-700 mb-1.5">Mật khẩu phải có:</p>
                            <ul class="space-y-1">
                                <li id="req-length" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-length">circle</span><span>Ít nhất 8 ký tự</span></li>
                                <li id="req-uppercase" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-uppercase">circle</span><span>Ít nhất 1 chữ hoa (A-Z)</span></li>
                                <li id="req-number" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-number">circle</span><span>Ít nhất 1 chữ số (0-9)</span></li>
                                <li id="req-special" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-special">circle</span><span>Ít nhất 1 ký tự đặc biệt (!@#$%...)</span></li>
                            </ul>
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
                <input type="password" id="admin_password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                <div id="password-requirements" class="mt-2 p-3 bg-gray-50 rounded-xl border border-gray-200 text-sm" style="display:none;">
                    <p class="font-semibold text-sm text-gray-700 mb-1.5">Mật khẩu phải có:</p>
                    <ul class="space-y-1">
                        <li id="req-length" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-length">circle</span><span>Ít nhất 8 ký tự</span></li>
                        <li id="req-uppercase" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-uppercase">circle</span><span>Ít nhất 1 chữ hoa (A-Z)</span></li>
                        <li id="req-number" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-number">circle</span><span>Ít nhất 1 chữ số (0-9)</span></li>
                        <li id="req-special" class="flex items-center gap-1.5 text-gray-500"><span class="material-symbols-outlined text-base" id="icon-special">circle</span><span>Ít nhất 1 ký tự đặc biệt (!@#$%...)</span></li>
                    </ul>
                </div>
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

    // Bật hiệu ứng realtime cho phần yêu cầu mật khẩu
    setTimeout(() => {
        const passwordInput = document.getElementById('admin_password');
        const reqBox = document.getElementById('password-requirements');
        if (passwordInput && reqBox) {
            const requirements = [
                { id: 'length',    test: v => v.length >= 8 },
                { id: 'uppercase', test: v => /[A-Z]/.test(v) },
                { id: 'number',    test: v => /[0-9]/.test(v) },
                { id: 'special',   test: v => /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?`~]/.test(v) }
            ];

            passwordInput.addEventListener('focus', () => reqBox.style.display = 'block');
            passwordInput.addEventListener('blur', () => {
                if (passwordInput.value === '') reqBox.style.display = 'none';
            });

            passwordInput.addEventListener('input', function() {
                const val = this.value;
                requirements.forEach(req => {
                    const li   = document.getElementById('req-' + req.id);
                    const icon = document.getElementById('icon-' + req.id);
                    if (req.test(val)) {
                        li.classList.remove('text-gray-500', 'text-red-500');
                        li.classList.add('text-green-600');
                        icon.textContent = 'check_circle';
                        icon.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";
                    } else {
                        li.classList.remove('text-green-600');
                        li.classList.add('text-gray-500');
                        icon.textContent = 'circle';
                        icon.style.fontVariationSettings = "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24";
                    }
                });
            });
        }
    }, 100);

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

        // Frontend validations
        const fullName = formData.get('full_name').trim();
        const phone = (formData.get('phone') || '').trim();
        const errors = [];
        
        if (fullName.length < 2 || fullName.length > 100) errors.push('Họ tên phải từ 2 đến 100 ký tự');
        if (/\d/.test(fullName)) errors.push('Họ tên không được chứa số');
        if (!/^[\p{L}\s'-]+$/u.test(fullName)) errors.push('Họ tên chứa ký tự không hợp lệ');
        
        if (phone && !/^0[0-9]{9,10}$/.test(phone.replace(/\s/g, ''))) errors.push('Số điện thoại không hợp lệ');

        const passwordInput = document.getElementById('admin_password');
        if (passwordInput && passwordInput.value) {
            const val = passwordInput.value;
            if (val.length < 8) errors.push('Mật khẩu phải có ít nhất 8 ký tự');
            if (!/[A-Z]/.test(val)) errors.push('Mật khẩu phải có ít nhất 1 chữ hoa');
            if (!/[0-9]/.test(val)) errors.push('Mật khẩu phải có ít nhất 1 chữ số');
            if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?`~]/.test(val)) errors.push('Mật khẩu phải có ít nhất 1 ký tự đặc biệt');
        }

        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');
        if (newPassword && newPassword !== confirmPassword) {
            errors.push('Mật khẩu xác nhận không khớp');
        }

        if (errors.length > 0) {
            showToast(errors[0], 'error'); // Hiện lỗi đầu tiên
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
