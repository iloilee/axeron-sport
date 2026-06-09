<?php
/**
 * News / Articles Page - Axeron Sports Shop
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();

// Get filter from URL
$category = $_GET['category'] ?? '';
$slug = $_GET['slug'] ?? '';

// If slug is provided, show single article
if (!empty($slug)) {
    $article = $db->selectOne("
        SELECT * FROM articles
        WHERE slug = ? AND is_published = 1
    ", [$slug]);

    if (!$article) {
        header('HTTP/1.0 404 Not Found');
        $pageTitle = 'Không tìm thấy bài viết';
    } else {
        $pageTitle = $article['title'];
        // Increment view
        $db->update("UPDATE articles SET view_count = view_count + 1 WHERE article_id = ?", [$article['article_id']]);
    }
} else {
    $pageTitle = 'Tin tức & Sự kiện';

    // Build query
    $where = "is_published = 1";
    $params = [];

    if ($category) {
        $where .= " AND category = ?";
        $params[] = $category;
    }

    // Pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 9;
    $offset = ($page - 1) * $perPage;

    // Get total
    $totalResult = $db->selectOne("SELECT COUNT(*) as total FROM articles WHERE $where", $params);
    $totalArticles = (int)$totalResult['total'];
    $totalPages = ceil($totalArticles / $perPage);

    // Get articles
    $articles = $db->select("
        SELECT article_id, title, slug, excerpt, featured_image, category, published_at, view_count
        FROM articles
        WHERE $where
        ORDER BY is_featured DESC, published_at DESC
        LIMIT ? OFFSET ?
    ", array_merge($params, [$perPage, $offset]));
}

// Get all categories for filter
$categories = $db->select("
    SELECT DISTINCT category, COUNT(*) as count
    FROM articles
    WHERE is_published = 1
    GROUP BY category
    ORDER BY count DESC
");

// Get featured articles for sidebar
$featuredArticles = $db->select("
    SELECT article_id, title, slug, featured_image, published_at
    FROM articles
    WHERE is_published = 1 AND is_featured = 1
    ORDER BY published_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle) ?> | Axeron Sport</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "axeron-red": "#BE1E2D",
                        "axeron-blue": "#2979FF",
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="min-h-screen">
        <?php if (!empty($slug) && isset($article)): ?>
        <!-- Single Article View -->
        <article class="max-w-4xl mx-auto px-4 py-12">
            <!-- Breadcrumb -->
            <nav class="mb-8">
                <ol class="flex items-center gap-2 text-sm text-gray-500">
                    <li><a href="<?= BASE_URL ?>" class="hover:text-axeron-red">Trang chủ</a></li>
                    <li>/</li>
                    <li><a href="<?= BASE_URL ?>/blog/news.php" class="hover:text-axeron-red">Tin tức</a></li>
                    <li>/</li>
                    <li class="text-gray-800"><?= htmlspecialchars($article['title']) ?></li>
                </ol>
            </nav>

            <!-- Article Header -->
            <header class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-3 py-1 bg-axeron-red text-white text-sm rounded-full">
                        <?= ucfirst($article['category']) ?>
                    </span>
                    <span class="text-gray-500 text-sm">
                        <?= $article['published_at'] ? date('d/m/Y', strtotime($article['published_at'])) : '' ?>
                    </span>
                    <span class="text-gray-400 text-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">visibility</span>
                        <?= number_format($article['view_count']) ?> lượt xem
                    </span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    <?= htmlspecialchars($article['title']) ?>
                </h1>
                <?php if (!empty($article['excerpt'])): ?>
                <p class="text-xl text-gray-600 leading-relaxed">
                    <?= htmlspecialchars($article['excerpt']) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($article['author_name'])): ?>
                <div class="flex items-center gap-3 mt-6 pt-6 border-t">
                    <div class="w-12 h-12 bg-axeron-red rounded-full flex items-center justify-center text-white font-bold text-lg">
                        <?= strtoupper(substr($article['author_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <p class="font-semibold"><?= htmlspecialchars($article['author_name']) ?></p>
                        <p class="text-sm text-gray-500">Tác giả</p>
                    </div>
                </div>
                <?php endif; ?>
            </header>

            <!-- Featured Image -->
            <?php if (!empty($article['featured_image'])): ?>
            <figure class="mb-8 rounded-xl overflow-hidden">
                <img src="<?= htmlspecialchars($article['featured_image']) ?>"
                     alt="<?= htmlspecialchars($article['title']) ?>"
                     class="w-full h-auto">
            </figure>
            <?php endif; ?>

            <!-- Article Content -->
            <div class="prose prose-lg max-w-none">
                <?= $article['content'] ?>
            </div>

            <!-- Tags -->
            <?php if (!empty($article['tags'])): ?>
            <div class="mt-8 pt-8 border-t flex flex-wrap gap-2">
                <?php foreach (explode(',', $article['tags']) as $tag): ?>
                <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">
                    #<?= trim(htmlspecialchars($tag)) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Share -->
            <div class="mt-8 pt-8 border-t">
                <p class="font-semibold mb-4">Chia sẻ bài viết:</p>
                <div class="flex gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/blog/news.php?slug=' . $article['slug']) ?>"
                       target="_blank"
                       class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700">
                        <span class="material-symbols-outlined">public</span>
                    </a>
                </div>
            </div>
        </article>

        <?php else: ?>
        <!-- Articles List View -->
        <div class="max-w-7xl mx-auto px-4 py-12">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Tin Tức & Sự Kiện</h1>
                <p class="text-gray-600">Cập nhật những tin tức mới nhất từ Axeron Sport</p>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Content -->
                <div class="lg:w-3/4">
                    <!-- Category Filter -->
                    <div class="flex flex-wrap gap-2 mb-8">
                        <a href="<?= BASE_URL ?>/blog/news.php"
                           class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?= empty($category) ? 'bg-axeron-red text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                            Tất cả
                        </a>
                        <?php foreach ($categories as $cat): ?>
                        <a href="<?= BASE_URL ?>/blog/news.php?category=<?= $cat['category'] ?>"
                           class="px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $category === $cat['category'] ? 'bg-axeron-red text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                            <?= ucfirst($cat['category']) ?> (<?= $cat['count'] ?>)
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Articles Grid -->
                    <?php if (!empty($articles)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php foreach ($articles as $article): ?>
                        <article class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow group cursor-pointer"
                                 onclick="window.location.href='<?= BASE_URL ?>/blog/news.php?slug=<?= htmlspecialchars($article['slug']) ?>'">
                            <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                                <img src="<?= $article['featured_image'] ?: 'https://placehold.co/600x400/e5e2e1/5b403f?text=Tin+Tuc' ?>"
                                     alt="<?= htmlspecialchars($article['title']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-5">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-2 py-0.5 bg-axeron-red/10 text-axeron-red text-xs rounded-full uppercase">
                                        <?= htmlspecialchars($article['category']) ?>
                                    </span>
                                    <span class="text-gray-400 text-xs">
                                        <?= $article['published_at'] ? date('d/m/Y', strtotime($article['published_at'])) : '' ?>
                                    </span>
                                </div>
                                <h3 class="font-semibold text-lg text-gray-900 mb-2 line-clamp-2 group-hover:text-axeron-red transition-colors">
                                    <?= htmlspecialchars($article['title']) ?>
                                </h3>
                                <p class="text-gray-600 text-sm line-clamp-3">
                                    <?= htmlspecialchars($article['excerpt'] ?: '') ?>
                                </p>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="flex justify-center mt-8">
                        <nav class="flex gap-2">
                            <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?><?= $category ? '&category=' . $category : '' ?>"
                               class="px-4 py-2 border rounded-lg hover:bg-gray-50">Trước</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?><?= $category ? '&category=' . $category : '' ?>"
                               class="px-4 py-2 border rounded-lg <?= $i === $page ? 'bg-axeron-red text-white' : 'hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?><?= $category ? '&category=' . $category : '' ?>"
                               class="px-4 py-2 border rounded-lg hover:bg-gray-50">Sau</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <span class="material-symbols-outlined text-6xl text-gray-300">article</span>
                        <h3 class="text-xl font-semibold text-gray-600 mt-4">Chưa có bài viết nào</h3>
                        <p class="text-gray-500 mt-2">Hãy quay lại sau hoặc chọn danh mục khác</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <aside class="lg:w-1/4">
                    <!-- Featured Articles -->
                    <?php if (!empty($featuredArticles)): ?>
                    <div class="bg-white rounded-xl p-5 shadow-sm mb-6">
                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-axeron-red">star</span>
                            Bài viết nổi bật
                        </h3>
                        <div class="space-y-4">
                            <?php foreach ($featuredArticles as $featured): ?>
                            <a href="<?= BASE_URL ?>/blog/news.php?slug=<?= htmlspecialchars($featured['slug']) ?>"
                               class="flex gap-3 group">
                                <?php if (!empty($featured['featured_image'])): ?>
                                <img src="<?= htmlspecialchars($featured['featured_image']) ?>"
                                     alt=""
                                     class="w-20 h-16 object-cover rounded-lg flex-shrink-0">
                                <?php else: ?>
                                <div class="w-20 h-16 bg-gray-100 rounded-lg flex-shrink-0 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-gray-400">image</span>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <h4 class="font-medium text-sm text-gray-800 line-clamp-2 group-hover:text-axeron-red transition-colors">
                                        <?= htmlspecialchars($featured['title']) ?>
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?= $featured['published_at'] ? date('d/m/Y', strtotime($featured['published_at'])) : '' ?>
                                    </p>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Categories -->
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <h3 class="font-bold text-lg mb-4">Danh mục</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="<?= BASE_URL ?>/blog/news.php"
                                   class="flex justify-between items-center py-2 px-3 rounded-lg hover:bg-gray-50 <?= empty($category) ? 'bg-gray-50 text-axeron-red' : 'text-gray-700' ?>">
                                    <span>Tất cả</span>
                                    <span class="text-sm text-gray-400"><?= array_sum(array_column($categories, 'count')) ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="<?= BASE_URL ?>/blog/news.php?category=<?= $cat['category'] ?>"
                                   class="flex justify-between items-center py-2 px-3 rounded-lg hover:bg-gray-50 <?= $category === $cat['category'] ? 'bg-gray-50 text-axeron-red' : 'text-gray-700' ?>">
                                    <span class="capitalize"><?= htmlspecialchars($cat['category']) ?></span>
                                    <span class="text-sm text-gray-400"><?= $cat['count'] ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
