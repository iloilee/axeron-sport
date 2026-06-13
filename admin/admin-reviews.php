<?php
/**
 * Admin Reviews Management
 */

// Load stats
$stats = $db->selectOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star_1
    FROM reviews
    WHERE is_deleted = 0
");

// Load reviews
$keyword = $_GET['keyword'] ?? '';
$statusFilter = $_GET['status'] ?? 'pending';
$ratingFilter = isset($_GET['rating']) && $_GET['rating'] !== '' ? $_GET['rating'] : 'all';

$where = "WHERE 1=1";
$params = [];

if ($statusFilter === 'deleted') {
    $where .= " AND r.is_deleted = 1";
} else {
    $where .= " AND r.is_deleted = 0";
    if ($statusFilter && $statusFilter !== 'all') {
        $where .= " AND r.status = ?";
        $params[] = $statusFilter;
    }
}

if ($ratingFilter !== 'all') {
    $where .= " AND r.rating = ?";
    $params[] = (int)$ratingFilter;
}

if ($keyword) {
    $where .= " AND (p.product_name LIKE ? OR u.full_name LIKE ? OR o.order_code LIKE ?)";
    $k = "%$keyword%";
    $params[] = $k;
    $params[] = $k;
    $params[] = $k;
}

// Pagination
$limit = (int)($_GET['limit'] ?? 10);
if (!in_array($limit, [10, 20, 50, 100])) $limit = 10;
$currentPage = (int)($_GET['page'] ?? 1);
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $limit;

$totalRecordsQuery = "
    SELECT COUNT(*) as count 
    FROM reviews r
    JOIN products p ON r.product_id = p.product_id
    JOIN users u ON r.user_id = u.user_id
    LEFT JOIN orders o ON r.order_id = o.order_id
    $where
";
$totalRecords = $db->selectOne($totalRecordsQuery, $params)['count'] ?? 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 0;

$reviews = $db->select("
    SELECT r.*, p.product_name, p.slug, u.full_name, o.order_code
    FROM reviews r
    JOIN products p ON r.product_id = p.product_id
    JOIN users u ON r.user_id = u.user_id
    LEFT JOIN orders o ON r.order_id = o.order_id
    $where
    ORDER BY r.created_at DESC
    LIMIT $limit OFFSET $offset
", $params);
?>

<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white py-3 px-2 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
        <span class="text-sm text-gray-500 font-medium">Tổng Đánh Giá</span>
        <span class="text-xl font-bold text-gray-800 mt-1"><?= number_format($stats['total'] ?? 0) ?></span>
    </div>
    <div class="bg-white py-3 px-2 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
        <div class="flex items-center gap-0.5">
            <?php for($i=0; $i<5; $i++): ?><span class="material-symbols-outlined text-yellow-500 text-lg" style="font-variation-settings: 'FILL' 1, 'wght' 700;">star</span><?php endfor; ?>
        </div>
        <span class="text-xl font-bold text-gray-800 mt-1"><?= number_format($stats['star_5'] ?? 0) ?></span>
    </div>
    <div class="bg-white py-3 px-2 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
        <div class="flex items-center gap-0.5">
            <?php for($i=0; $i<4; $i++): ?><span class="material-symbols-outlined text-yellow-500 text-lg" style="font-variation-settings: 'FILL' 1, 'wght' 700;">star</span><?php endfor; ?>
        </div>
        <span class="text-xl font-bold text-gray-800 mt-1"><?= number_format($stats['star_4'] ?? 0) ?></span>
    </div>
    <div class="bg-white py-3 px-2 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
        <div class="flex items-center gap-0.5">
            <?php for($i=0; $i<3; $i++): ?><span class="material-symbols-outlined text-yellow-500 text-lg" style="font-variation-settings: 'FILL' 1, 'wght' 700;">star</span><?php endfor; ?>
        </div>
        <span class="text-xl font-bold text-gray-800 mt-1"><?= number_format($stats['star_3'] ?? 0) ?></span>
    </div>
    <div class="bg-white py-3 px-2 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
        <div class="flex items-center gap-0.5">
            <?php for($i=0; $i<2; $i++): ?><span class="material-symbols-outlined text-yellow-500 text-lg" style="font-variation-settings: 'FILL' 1, 'wght' 700;">star</span><?php endfor; ?>
        </div>
        <span class="text-xl font-bold text-gray-800 mt-1"><?= number_format($stats['star_2'] ?? 0) ?></span>
    </div>
    <div class="bg-white py-3 px-2 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
        <div class="flex items-center gap-0.5">
            <span class="material-symbols-outlined text-yellow-500 text-lg" style="font-variation-settings: 'FILL' 1, 'wght' 700;">star</span>
        </div>
        <span class="text-xl font-bold text-gray-800 mt-1"><?= number_format($stats['star_1'] ?? 0) ?></span>
    </div>
</div>



<div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
    <form method="GET" class="flex flex-col md:flex-row gap-4 items-end flex-wrap">
        <input type="hidden" name="action" value="reviews">
        <div class="flex-grow min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tìm kiếm</label>
            <div class="flex gap-2">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Sản phẩm, Người dùng, Đơn hàng..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                <div class="px-4 py-2 bg-red-50 border border-red-100 rounded-lg text-sm font-medium text-axeron-red whitespace-nowrap flex items-center">
                    Tổng: <strong class="text-base mx-1"><?= number_format($totalRecords) ?></strong> đánh giá
                </div>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Trạng thái</label>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Tất cả</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                <option value="hidden" <?= $statusFilter === 'hidden' ? 'selected' : '' ?>>Ẩn</option>
                <option value="deleted" <?= $statusFilter === 'deleted' ? 'selected' : '' ?>>Đã xóa</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Đánh giá sao</label>
            <select name="rating" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                <option value="all" <?= $ratingFilter === 'all' ? 'selected' : '' ?>>Tất cả số sao</option>
                <option value="5" <?= $ratingFilter === '5' ? 'selected' : '' ?>>5 Sao</option>
                <option value="4" <?= $ratingFilter === '4' ? 'selected' : '' ?>>4 Sao</option>
                <option value="3" <?= $ratingFilter === '3' ? 'selected' : '' ?>>3 Sao</option>
                <option value="2" <?= $ratingFilter === '2' ? 'selected' : '' ?>>2 Sao</option>
                <option value="1" <?= $ratingFilter === '1' ? 'selected' : '' ?>>1 Sao</option>
            </select>
        </div>
        <div>
            <button type="submit" class="px-6 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors h-[42px] font-medium">Lọc</button>
            <?php if ($keyword || $statusFilter !== 'pending' || $ratingFilter !== 'all'): ?>
            <a href="?action=reviews" class="px-4 py-2 text-gray-500 hover:text-gray-800 ml-2">Xóa lọc</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-16">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sản phẩm</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Người đánh giá</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-28">Số sao</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nội dung</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ngày đánh giá</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($reviews as $review): ?>
                <tr class="hover:bg-gray-50 <?= $review['is_deleted'] ? 'opacity-50' : '' ?>">
                    <td class="px-4 py-3 text-sm text-gray-500">#<?= $review['review_id'] ?></td>
                    <td class="px-4 py-3">
                        <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= $review['slug'] ?>" target="_blank"
                           class="font-medium text-gray-800 hover:text-axeron-red line-clamp-2">
                            <?= htmlspecialchars($review['product_name']) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <div class="font-medium"><?= htmlspecialchars($review['full_name']) ?></div>
                        <?php if($review['order_code']): ?>
                        <div class="text-xs text-gray-400 mt-0.5">Đơn: <?= $review['order_code'] ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-0.5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="material-symbols-outlined text-[16px] <?= $i <= $review['rating'] ? 'text-yellow-500' : 'text-gray-300' ?>">
                                star
                            </span>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px]">
                        <div class="truncate" title="<?= htmlspecialchars($review['comment'] ?? '') ?>">
                            <?= htmlspecialchars($review['comment'] ?? '') ?>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y', strtotime($review['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <?php if ($review['is_deleted']): ?>
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Đã xóa</span>
                        <?php else: ?>
                            <?php
                            $statusClass = match($review['status']) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-gray-200 text-gray-800',
                                'hidden' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $statusText = match($review['status']) {
                                'pending' => 'Chờ duyệt',
                                'approved' => 'Hiển thị',
                                'rejected' => 'Từ chối',
                                'hidden' => 'Ẩn',
                                default => $review['status']
                            };
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs <?= $statusClass ?>"><?= $statusText ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if (!$review['is_deleted']): ?>
                        <div class="flex gap-1 justify-center">
                            <!-- Nút 1: Duyệt / Ẩn -->
                            <?php if ($review['status'] === 'pending' || $review['status'] === 'hidden' || $review['status'] === 'rejected'): ?>
                            <a href="javascript:void(0)" onclick="updateReviewStatus(<?= $review['review_id'] ?>, 'approved')"
                               class="p-1.5 hover:bg-green-50 rounded text-gray-400 hover:text-green-600 transition-colors" title="Duyệt / Hiển thị">
                                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                            </a>
                            <?php elseif ($review['status'] === 'approved'): ?>
                            <a href="javascript:void(0)" onclick="updateReviewStatus(<?= $review['review_id'] ?>, 'hidden')"
                               class="p-1.5 hover:bg-gray-100 rounded text-gray-400 hover:text-gray-600 transition-colors" title="Ẩn">
                                <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                            </a>
                            <?php endif; ?>
                            
                            <!-- Nút 2: Xem chi tiết -->
                            <a href="javascript:void(0)" onclick="viewReviewDetail(<?= $review['review_id'] ?>)"
                               class="p-1.5 hover:bg-blue-50 rounded text-gray-400 hover:text-blue-500 transition-colors" title="Xem chi tiết">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </a>

                            <!-- Nút 3: Xóa -->
                            <a href="javascript:void(0)" onclick="deleteReview(<?= $review['review_id'] ?>)"
                               class="p-1.5 hover:bg-red-50 rounded text-gray-400 hover:text-red-500 transition-colors" title="Xóa">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="flex justify-center">
                            <a href="javascript:void(0)" onclick="viewReviewDetail(<?= $review['review_id'] ?>)"
                               class="p-1.5 hover:bg-blue-50 rounded text-gray-400 hover:text-blue-500 transition-colors" title="Xem chi tiết">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </a>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                        <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">chat_bubble_off</span>
                        <p>Không có đánh giá nào</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4">
        <?php include __DIR__ . '/includes/pagination.php'; ?>
    </div>
</div>

<!-- Review Detail Modal -->
<div id="reviewDetailModal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 overflow-hidden transform transition-all">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-800">Chi tiết đánh giá</h3>
            <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- User Info -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Người dùng</h4>
                    <div class="flex flex-col gap-2">
                        <div class="flex gap-2 items-center text-sm">
                            <span class="material-symbols-outlined text-[18px] text-gray-400">person</span>
                            <span id="detail_user_name" class="font-medium text-gray-800"></span>
                        </div>
                        <div class="flex gap-2 items-center text-sm">
                            <span class="material-symbols-outlined text-[18px] text-gray-400">mail</span>
                            <span id="detail_user_email" class="text-gray-600"></span>
                        </div>
                        <div class="mt-2 pt-2 border-t border-gray-200">
                            <button id="btnToggleBan" onclick="toggleUserBan()" class="w-full px-3 py-1.5 text-sm font-medium rounded-lg flex items-center justify-center gap-1 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">block</span>
                                <span id="textToggleBan">Khóa quyền đánh giá</span>
                            </button>
                            <p class="text-[11px] text-gray-500 text-center mt-1">Sử dụng khi phát hiện spam</p>
                        </div>
                    </div>
                </div>

                <!-- Product & Order Info -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Sản phẩm & Đơn hàng</h4>
                    <div class="flex flex-col gap-2">
                        <div class="flex gap-2 items-start text-sm">
                            <span class="material-symbols-outlined text-[18px] text-gray-400 mt-0.5">inventory_2</span>
                            <span id="detail_product_name" class="font-medium text-gray-800 line-clamp-2"></span>
                        </div>
                        <div class="flex gap-2 items-center text-sm">
                            <span class="material-symbols-outlined text-[18px] text-gray-400">receipt_long</span>
                            <span id="detail_order_code" class="text-gray-600"></span>
                        </div>
                        <div class="flex gap-2 items-center text-sm">
                            <span class="material-symbols-outlined text-[18px] text-gray-400">calendar_today</span>
                            <span id="detail_created_at" class="text-gray-600"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Content -->
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Nội dung đánh giá</h4>
                <div class="flex gap-1 mb-3" id="detail_stars">
                    <!-- Stars will be populated here -->
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-gray-700 whitespace-pre-wrap text-sm" id="detail_comment">
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
            <button onclick="closeReviewModal()" class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">Đóng</button>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let currentBanStatus = 0;

function updateReviewStatus(reviewId, newStatus) {
    let actionText = newStatus === 'approved' ? 'Duyệt / Hiển thị' : newStatus === 'rejected' ? 'Từ chối' : 'Ẩn';
    showConfirm(actionText + ' đánh giá này?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'update_review_status');
        formData.append('review_id', reviewId);
        formData.append('new_status', newStatus);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) { 
                showToast(result.message || "Thao tác thành công!", "success"); 
                setTimeout(() => location.reload(), 800); 
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

function deleteReview(reviewId) {
    showConfirm('Bạn có chắc chắn muốn xóa (ẩn) đánh giá này không? Hành động này sẽ thay đổi số sao trung bình của sản phẩm.', async () => { 
        const formData = new FormData();
        formData.append('ajax_action', 'delete_review');
        formData.append('review_id', reviewId);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) { 
                showToast(result.message || "Thao tác thành công!", "success"); 
                setTimeout(() => location.reload(), 800); 
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

async function viewReviewDetail(reviewId) {
    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_review_detail&id=' + reviewId);
        const result = await response.json();
        
        if (result.success && result.review) {
            const r = result.review;
            document.getElementById('detail_user_name').textContent = r.full_name;
            document.getElementById('detail_user_email').textContent = r.email;
            document.getElementById('detail_product_name').textContent = r.product_name;
            document.getElementById('detail_order_code').textContent = r.order_code || 'Không rõ';
            
            const date = new Date(r.created_at);
            document.getElementById('detail_created_at').textContent = date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
            
            document.getElementById('detail_comment').textContent = r.comment || '(Không có nội dung)';
            
            // Stars
            let starsHtml = '';
            for(let i=1; i<=5; i++) {
                starsHtml += `<span class="material-symbols-outlined text-[20px] ${i <= r.rating ? 'text-yellow-500' : 'text-gray-300'}">star</span>`;
            }
            document.getElementById('detail_stars').innerHTML = starsHtml;
            
            // Ban status
            currentUserId = r.user_id;
            currentBanStatus = parseInt(r.review_banned || 0);
            updateBanBtnUI();

            // Show modal
            document.getElementById('reviewDetailModal').classList.remove('hidden');
        } else {
            showToast(result.message || 'Không thể lấy thông tin!', 'error');
        }
    } catch (err) {
        showToast('Có lỗi xảy ra!', 'error');
    }
}

function updateBanBtnUI() {
    const btn = document.getElementById('btnToggleBan');
    const txt = document.getElementById('textToggleBan');
    if (currentBanStatus === 1) {
        btn.className = "w-full px-3 py-1.5 text-sm font-medium rounded-lg flex items-center justify-center gap-1 transition-colors bg-red-100 text-red-700 hover:bg-red-200 border border-red-200";
        txt.textContent = "Mở khóa quyền đánh giá";
    } else {
        btn.className = "w-full px-3 py-1.5 text-sm font-medium rounded-lg flex items-center justify-center gap-1 transition-colors bg-white border border-gray-300 text-gray-700 hover:bg-gray-50";
        txt.textContent = "Khóa quyền đánh giá";
    }
}

function toggleUserBan() {
    if (!currentUserId) return;
    
    const newStatus = currentBanStatus === 1 ? 0 : 1;
    const msg = newStatus === 1 ? 'Khóa quyền đánh giá của tài khoản này?' : 'Mở khóa quyền đánh giá cho tài khoản này?';
    
    showConfirm(msg, async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'ban_user_review');
        formData.append('user_id', currentUserId);
        formData.append('action', newStatus);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) { 
                showToast(result.message, "success"); 
                currentBanStatus = newStatus;
                updateBanBtnUI();
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

function closeReviewModal() {
    document.getElementById('reviewDetailModal').classList.add('hidden');
    currentUserId = null;
}
</script>
