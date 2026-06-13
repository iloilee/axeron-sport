<?php
/**
 * Admin Pagination Component
 * Variables required:
 * $totalRecords - Total number of records
 * $totalPages - Total number of pages
 * $currentPage - Current active page
 * $limit - Number of records per page
 */

if (!isset($totalPages) || $totalPages <= 0) return;

// Build query string for existing filters to preserve them in links
$queryParams = $_GET;
unset($queryParams['page']); // We will set 'page' manually for the links
unset($queryParams['limit']); // We'll keep limit via a separate variable or form

// Generate the base URL query string
$queryString = http_build_query($queryParams);
$baseUrl = '?' . ($queryString ? $queryString . '&' : '');

if (!isset($limitOptions)) {
    $limitOptions = [10, 20, 50, 100];
}
?>

<div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 border-t pt-4">
    <!-- Left side: Total records and Limit selector -->
    <div class="flex items-center gap-4 text-sm text-gray-600">
        <span>Tổng cộng: <strong class="text-gray-900"><?= number_format($totalRecords) ?></strong> bản ghi</span>
        
        <form method="GET" class="flex items-center gap-2 m-0">
            <?php foreach ($queryParams as $k => $v): ?>
                <?php if (is_array($v)): ?>
                    <?php foreach ($v as $vi): ?>
                        <input type="hidden" name="<?= htmlspecialchars($k) ?>[]" value="<?= htmlspecialchars($vi) ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            
            <label for="pagination_limit" class="sr-only">Số bản ghi trên trang</label>
            <select name="limit" id="pagination_limit" onchange="this.form.submit()" class="border-gray-300 rounded text-sm focus:ring-axeron-red focus:border-axeron-red py-1 pl-2 pr-8">
                <?php foreach ($limitOptions as $opt): ?>
                    <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>><?= $opt ?>/trang</option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Right side: Pagination Links -->
    <?php if ($totalPages > 1): ?>
    <nav class="flex items-center gap-1" aria-label="Pagination">
        <!-- First Page -->
        <a href="<?= $baseUrl ?>limit=<?= $limit ?>&page=1" 
           class="p-2 rounded hover:bg-gray-100 text-gray-500 <?= $currentPage <= 1 ? 'pointer-events-none opacity-50' : '' ?>" title="Trang đầu">
            <span class="material-symbols-outlined text-sm">keyboard_double_arrow_left</span>
        </a>
        
        <!-- Previous Page -->
        <a href="<?= $baseUrl ?>limit=<?= $limit ?>&page=<?= max(1, $currentPage - 1) ?>" 
           class="p-2 rounded hover:bg-gray-100 text-gray-500 <?= $currentPage <= 1 ? 'pointer-events-none opacity-50' : '' ?>" title="Trang trước">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
        </a>

        <!-- Page Numbers -->
        <div class="flex items-center gap-1">
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if ($startPage > 1): ?>
                <span class="px-2 text-gray-400">...</span>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="<?= $baseUrl ?>limit=<?= $limit ?>&page=<?= $i ?>" 
                   class="min-w-[32px] h-8 flex items-center justify-center rounded text-sm font-medium transition-colors
                   <?= $i === $currentPage 
                       ? 'bg-axeron-red text-white' 
                       : 'text-gray-700 hover:bg-gray-100' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($endPage < $totalPages): ?>
                <span class="px-2 text-gray-400">...</span>
            <?php endif; ?>
        </div>

        <!-- Next Page -->
        <a href="<?= $baseUrl ?>limit=<?= $limit ?>&page=<?= min($totalPages, $currentPage + 1) ?>" 
           class="p-2 rounded hover:bg-gray-100 text-gray-500 <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>" title="Trang sau">
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </a>
        
        <!-- Last Page -->
        <a href="<?= $baseUrl ?>limit=<?= $limit ?>&page=<?= $totalPages ?>" 
           class="p-2 rounded hover:bg-gray-100 text-gray-500 <?= $currentPage >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>" title="Trang cuối">
            <span class="material-symbols-outlined text-sm">keyboard_double_arrow_right</span>
        </a>
    </nav>
    <?php endif; ?>
</div>
