<?php
/**
 * Admin Reviews Management
 */

// Load reviews
$statusFilter = $_GET['status'] ?? 'pending';
$ratingFilter = isset($_GET['rating']) && $_GET['rating'] !== '' ? $_GET['rating'] : 'all';

$where = "WHERE 1=1";
$params = [];

if ($statusFilter && $statusFilter !== 'all') {
    $where .= " AND r.status = ?";
    $params[] = $statusFilter;
}

if ($ratingFilter !== 'all') {
    $where .= " AND r.rating = ?";
    $params[] = (int)$ratingFilter;
}

$reviews = $db->select("
    SELECT r.*, p.product_name, p.slug, u.full_name
    FROM reviews r
    JOIN products p ON r.product_id = p.product_id
    JOIN users u ON r.user_id = u.user_id
    $where
    ORDER BY r.created_at DESC
    LIMIT 100
", $params);
?>

<div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
    <form method="GET" class="flex gap-4 items-end flex-wrap">
        <input type="hidden" name="action" value="reviews">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Trạng thái</label>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none" onchange="this.form.submit()">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                <option value="hidden" <?= $statusFilter === 'hidden' ? 'selected' : '' ?>>Ẩn</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Đánh giá sao</label>
            <select name="rating" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none" onchange="this.form.submit()">
                <option value="all" <?= $ratingFilter === 'all' ? 'selected' : '' ?>>Tất cả số sao</option>
                <option value="5" <?= $ratingFilter === '5' ? 'selected' : '' ?>>5 Sao</option>
                <option value="4" <?= $ratingFilter === '4' ? 'selected' : '' ?>>4 Sao</option>
                <option value="3" <?= $ratingFilter === '3' ? 'selected' : '' ?>>3 Sao</option>
                <option value="2" <?= $ratingFilter === '2' ? 'selected' : '' ?>>2 Sao</option>
                <option value="1" <?= $ratingFilter === '1' ? 'selected' : '' ?>>1 Sao</option>
            </select>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sản phẩm</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Người dùng</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Đánh giá</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bình luận</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ngày</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($reviews as $review): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= $review['slug'] ?>" target="_blank"
                           class="font-medium text-gray-800 hover:text-axeron-red">
                            <?= htmlspecialchars($review['product_name']) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($review['full_name']) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-0.5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="material-symbols-outlined text-sm <?= $i <= $review['rating'] ? 'text-yellow-500' : 'text-gray-300' ?>">
                                star
                            </span>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                        <?= htmlspecialchars($review['comment'] ?? '') ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $statusClass = match($review['status']) {
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'hidden' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        $statusText = match($review['status']) {
                            'pending' => 'Chờ duyệt',
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Từ chối',
                            'hidden' => 'Ẩn',
                            default => $review['status']
                        };
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs <?= $statusClass ?>"><?= $statusText ?></span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y', strtotime($review['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <?php if ($review['status'] === 'pending'): ?>
                            <a href="javascript:void(0)" onclick="updateReviewStatus(<?= $review['review_id'] ?>, 'approved')"
                               class="p-2 hover:bg-green-50 rounded-lg transition-colors" title="Duyệt">
                                <span class="material-symbols-outlined text-green-600">check</span>
                            </a>
                            <a href="javascript:void(0)" onclick="updateReviewStatus(<?= $review['review_id'] ?>, 'rejected')"
                               class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Từ chối">
                                <span class="material-symbols-outlined text-red-500">close</span>
                            </a>
                            <?php elseif ($review['status'] === 'approved'): ?>
                            <a href="javascript:void(0)" onclick="updateReviewStatus(<?= $review['review_id'] ?>, 'hidden')"
                               class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Ẩn">
                                <span class="material-symbols-outlined text-gray-600">visibility_off</span>
                            </a>
                            <?php elseif ($review['status'] === 'hidden'): ?>
                            <a href="javascript:void(0)" onclick="updateReviewStatus(<?= $review['review_id'] ?>, 'approved')"
                               class="p-2 hover:bg-green-50 rounded-lg transition-colors" title="Hiển thị lại">
                                <span class="material-symbols-outlined text-green-600">visibility</span>
                            </a>
                            <?php endif; ?>
                            <a href="javascript:void(0)" onclick="deleteReview(<?= $review['review_id'] ?>)"
                               class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                <span class="material-symbols-outlined text-red-500">delete</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">Không có đánh giá nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function updateReviewStatus(reviewId, newStatus) {
    const statusLabels = {
        'approved': 'duyệt',
        'rejected': 'từ chối',
        'hidden': 'ẩn'
    };

    if (!confirm(`${newStatus === 'approved' ? 'Duyệt' : newStatus === 'rejected' ? 'Từ chối' : 'Ẩn'} đánh giá này?`)) return;

    const formData = new FormData();
    formData.append('ajax_action', 'update_review_status');
    formData.append('review_id', reviewId);
    formData.append('new_status', newStatus);

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

async function deleteReview(reviewId) {
    if (!confirm('Bạn có chắc chắn muốn xóa đánh giá này không? Hành động này không thể hoàn tác.')) return;

    const formData = new FormData();
    formData.append('ajax_action', 'delete_review');
    formData.append('review_id', reviewId);

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
