<?php
/**
 * CMS API - Axeron Sports Shop
 * Xử lý: Banners, Articles, Site Settings
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Load upload config - Ép buộc dùng local upload
require_once __DIR__ . '/../config/upload_config.php';
define('USE_CLOUDINARY', false);

$db = db();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                jsonResponse(false, 'Lỗi bảo mật (CSRF). Vui lòng tải lại trang và thử lại.');
            }
            handlePost($db);
            break;
        default:
            jsonResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

/**
 * Handle GET requests
 */
function handleGet($db) {
    $action = $_GET['action'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    // Enforce role-based security for admin GET requests
    if (in_array($action, ['banners', 'banner', 'articles', 'article', 'settings', 'setting'])) {
        if (!isLoggedIn() || !isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            jsonResponse(false, 'Unauthorized');
        }

        $requiredSection = '';
        if (strpos($action, 'banner') !== false) {
            $requiredSection = 'banners';
        } elseif (strpos($action, 'article') !== false) {
            $requiredSection = 'articles';
        } elseif (strpos($action, 'setting') !== false) {
            $requiredSection = 'settings';
        }

        if (!empty($requiredSection) && !hasPermission($requiredSection)) {
            header('HTTP/1.1 403 Forbidden');
            jsonResponse(false, 'Bạn không có quyền thực hiện thao tác này!');
        }
    }

    switch ($action) {
        // ===== BANNERS =====
        case 'banners':
            getBanners($db);
            break;
        case 'banner':
            getBanner($db, $id);
            break;
        case 'active_banners':
            getActiveBanners($db);
            break;

        // ===== ARTICLES =====
        case 'articles':
            getArticles($db);
            break;
        case 'article':
            getArticle($db, $id);
            break;
        case 'article_slug':
            getArticleBySlug($db, $_GET['slug'] ?? '');
            break;
        case 'featured_articles':
            getFeaturedArticles($db);
            break;

        // ===== SETTINGS =====
        case 'settings':
            getSettings($db);
            break;
        case 'public_settings':
            getPublicSettings($db);
            break;
        case 'setting':
            getSetting($db, $_GET['key'] ?? '');
            break;

        default:
            jsonResponse(false, 'Invalid action');
    }
}

/**
 * Handle POST requests
 */
function handlePost($db) {
    // Check admin authentication for write operations
    if (!isLoggedIn() || !isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        jsonResponse(false, 'Unauthorized');
    }

    $action = $_POST['action'] ?? '';

    // Enforce role-based security
    $requiredSection = '';
    if (strpos($action, 'banner') !== false) {
        $requiredSection = 'banners';
    } elseif (strpos($action, 'article') !== false) {
        $requiredSection = 'articles';
    } elseif (strpos($action, 'setting') !== false) {
        $requiredSection = 'settings';
    }

    if (!empty($requiredSection) && !hasPermission($requiredSection)) {
        header('HTTP/1.1 403 Forbidden');
        jsonResponse(false, 'Bạn không có quyền thực hiện thao tác này!');
    }

    switch ($action) {
        // ===== BANNERS =====
        case 'create_banner':
            createBanner($db);
            break;
        case 'update_banner':
            updateBanner($db);
            break;
        case 'delete_banner':
            deleteBanner($db);
            break;
        case 'upload_banner_image':
            uploadBannerImage($db);
            break;
        case 'reorder_banners':
            reorderBanners($db);
            break;

        // ===== ARTICLES =====
        case 'create_article':
            createArticle($db);
            break;
        case 'update_article':
            updateArticle($db);
            break;
        case 'delete_article':
            deleteArticle($db);
            break;
        case 'upload_article_image':
            uploadArticleImage($db);
            break;

        // ===== SETTINGS =====
        case 'update_setting':
            updateSetting($db);
            break;
        case 'update_settings':
            updateSettings($db);
            break;

        default:
            jsonResponse(false, 'Invalid action');
    }
}

// =====================================================
// BANNER FUNCTIONS
// =====================================================

function getBanners($db) {
    $banners = $db->select("
        SELECT b.*, u.full_name as created_by_name
        FROM banners b
        LEFT JOIN users u ON b.created_by = u.user_id
        ORDER BY b.position ASC, b.created_at DESC
    ");

    jsonResponse(true, 'Success', ['banners' => $banners]);
}

function getBanner($db, $id) {
    if ($id <= 0) {
        jsonResponse(false, 'Invalid banner ID');
    }

    $banner = $db->selectOne("SELECT * FROM banners WHERE banner_id = ?", [$id]);

    if (!$banner) {
        jsonResponse(false, 'Banner not found');
    }

    jsonResponse(true, 'Success', ['banner' => $banner]);
}

function getActiveBanners($db) {
    $banners = $db->select("
        SELECT banner_id, title, subtitle, image_url, image_url_mobile, link_url, link_type, button_text, position
        FROM banners
        WHERE is_active = 1
        AND (start_date IS NULL OR start_date <= NOW())
        AND (end_date IS NULL OR end_date >= NOW())
        ORDER BY position ASC
    ");

    jsonResponse(true, 'Success', ['banners' => $banners]);
}

function createBanner($db) {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $image_url_mobile = trim($_POST['image_url_mobile'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $link_type = $_POST['link_type'] ?? 'none';
    $target_id = !empty($_POST['target_id']) ? (int)$_POST['target_id'] : null;
    $button_text = trim($_POST['button_text'] ?? '');
    $position = (int)($_POST['position'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    if (empty($title) || empty($image_url)) {
        jsonResponse(false, 'Title and image are required');
    }

    $banner_id = $db->insert("
        INSERT INTO banners (title, subtitle, image_url, image_url_mobile, link_url, link_type, target_id, button_text, position, is_active, start_date, end_date, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ", [$title, $subtitle, $image_url, $image_url_mobile, $link_url, $link_type, $target_id, $button_text, $position, $is_active, $start_date, $end_date, getUserId()]);

    jsonResponse(true, 'Banner created successfully', ['banner_id' => $banner_id]);
}

function updateBanner($db) {
    $banner_id = (int)($_POST['banner_id'] ?? 0);

    if ($banner_id <= 0) {
        jsonResponse(false, 'Invalid banner ID');
    }

    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $image_url_mobile = trim($_POST['image_url_mobile'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $link_type = $_POST['link_type'] ?? 'none';
    $target_id = !empty($_POST['target_id']) ? (int)$_POST['target_id'] : null;
    $button_text = trim($_POST['button_text'] ?? '');
    $position = (int)($_POST['position'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    if (empty($title)) {
        jsonResponse(false, 'Title is required');
    }

    $db->update("
        UPDATE banners SET
            title = ?, subtitle = ?, image_url = ?, image_url_mobile = ?,
            link_url = ?, link_type = ?, target_id = ?, button_text = ?,
            position = ?, is_active = ?, start_date = ?, end_date = ?
        WHERE banner_id = ?
    ", [$title, $subtitle, $image_url, $image_url_mobile, $link_url, $link_type, $target_id, $button_text, $position, $is_active, $start_date, $end_date, $banner_id]);

    jsonResponse(true, 'Banner updated successfully');
}

function deleteBanner($db) {
    $banner_id = (int)($_POST['banner_id'] ?? 0);

    if ($banner_id <= 0) {
        jsonResponse(false, 'Invalid banner ID');
    }

    $db->update("DELETE FROM banners WHERE banner_id = ?", [$banner_id]);
    jsonResponse(true, 'Banner deleted successfully');
}

function reorderBanners($db) {
    $order = $_POST['order'] ?? [];

    if (!is_array($order) || empty($order)) {
        jsonResponse(false, 'Invalid order data');
    }

    foreach ($order as $position => $banner_id) {
        $db->update("UPDATE banners SET position = ? WHERE banner_id = ?", [(int)$position, (int)$banner_id]);
    }

    jsonResponse(true, 'Banners reordered successfully');
}

// =====================================================
// ARTICLE FUNCTIONS
// =====================================================

function getArticles($db) {
    $category = $_GET['category'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 12;
    $offset = ($page - 1) * $perPage;

    $where = "1=1";
    $params = [];

    if ($category) {
        $where .= " AND a.category = ?";
        $params[] = $category;
    }

    $total = $db->selectOne("SELECT COUNT(*) as total FROM articles a WHERE $where", $params);

    $articles = $db->select("
        SELECT a.*, u.full_name as author_name_display
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE $where
        ORDER BY a.is_featured DESC, a.published_at DESC
        LIMIT ? OFFSET ?
    ", array_merge($params, [$perPage, $offset]));

    jsonResponse(true, 'Success', [
        'articles' => $articles,
        'total' => (int)$total['total'],
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total['total'] / $perPage)
    ]);
}

function getArticle($db, $id) {
    if ($id <= 0) {
        jsonResponse(false, 'Invalid article ID');
    }

    $article = $db->selectOne("
        SELECT a.*, u.full_name as author_name_display
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE a.article_id = ?
    ", [$id]);

    if (!$article) {
        jsonResponse(false, 'Article not found');
    }

    // Increment view count
    $db->update("UPDATE articles SET view_count = view_count + 1 WHERE article_id = ?", [$id]);

    jsonResponse(true, 'Success', ['article' => $article]);
}

function getArticleBySlug($db, $slug) {
    $slug = trim($slug);

    if (empty($slug)) {
        jsonResponse(false, 'Slug is required');
    }

    $article = $db->selectOne("
        SELECT a.*, u.full_name as author_name_display
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.user_id
        WHERE a.slug = ? AND a.is_published = 1
    ", [$slug]);

    if (!$article) {
        jsonResponse(false, 'Article not found');
    }

    // Increment view count
    $db->update("UPDATE articles SET view_count = view_count + 1 WHERE article_id = ?", [$article['article_id']]);

    jsonResponse(true, 'Success', ['article' => $article]);
}

function getFeaturedArticles($db) {
    $limit = (int)($_GET['limit'] ?? 6);
    $category = $_GET['category'] ?? '';

    $where = "a.is_published = 1";
    $params = [];

    if ($category) {
        $where .= " AND a.category = ?";
        $params[] = $category;
    }

    $articles = $db->select("
        SELECT a.article_id, a.title, a.slug, a.excerpt, a.featured_image, a.category, a.published_at
        FROM articles a
        WHERE $where
        ORDER BY a.is_featured DESC, a.published_at DESC
        LIMIT ?
    ", array_merge($params, [$limit]));

    jsonResponse(true, 'Success', ['articles' => $articles]);
}

function createArticle($db) {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: createSlug($title);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $featured_image = trim($_POST['featured_image'] ?? '');
    $category = $_POST['category'] ?? 'blog';
    $tags = trim($_POST['tags'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '') ?: $title;
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($title)) {
        jsonResponse(false, 'Title is required');
    }

    // Check slug uniqueness
    $exists = $db->selectOne("SELECT article_id FROM articles WHERE slug = ?", [$slug]);
    if ($exists) {
        $slug .= '-' . time();
    }

    $published_at = $is_published ? date('Y-m-d H:i:s') : null;

    $article_id = $db->insert("
        INSERT INTO articles (title, slug, excerpt, content, featured_image, category, tags, author_id, author_name, is_featured, is_published, published_at, meta_title, meta_description, meta_keywords, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ", [$title, $slug, $excerpt, $content, $featured_image, $category, $tags, getUserId(), getUserData()['full_name'] ?? 'Admin', $is_featured, $is_published, $published_at, $meta_title, $meta_description, $meta_keywords, $sort_order]);

    jsonResponse(true, 'Article created successfully', ['article_id' => $article_id]);
}

function updateArticle($db) {
    $article_id = (int)($_POST['article_id'] ?? 0);

    if ($article_id <= 0) {
        jsonResponse(false, 'Invalid article ID');
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $featured_image = trim($_POST['featured_image'] ?? '');
    $category = $_POST['category'] ?? 'blog';
    $tags = trim($_POST['tags'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($title)) {
        jsonResponse(false, 'Title is required');
    }

    // Check slug uniqueness (excluding current article)
    if (!empty($slug)) {
        $exists = $db->selectOne("SELECT article_id FROM articles WHERE slug = ? AND article_id != ?", [$slug, $article_id]);
        if ($exists) {
            $slug .= '-' . time();
        }
    }

    // Get current article to check if publishing for first time
    $current = $db->selectOne("SELECT is_published, published_at FROM articles WHERE article_id = ?", [$article_id]);
    $published_at = $current['published_at'];
    if ($is_published && empty($published_at)) {
        $published_at = date('Y-m-d H:i:s');
    }

    $db->update("
        UPDATE articles SET
            title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?,
            category = ?, tags = ?, is_featured = ?, is_published = ?, published_at = ?,
            meta_title = ?, meta_description = ?, meta_keywords = ?, sort_order = ?
        WHERE article_id = ?
    ", [$title, $slug, $excerpt, $content, $featured_image, $category, $tags, $is_featured, $is_published, $published_at, $meta_title, $meta_description, $meta_keywords, $sort_order, $article_id]);

    jsonResponse(true, 'Article updated successfully');
}

function deleteArticle($db) {
    $article_id = (int)($_POST['article_id'] ?? 0);

    if ($article_id <= 0) {
        jsonResponse(false, 'Invalid article ID');
    }

    $db->update("DELETE FROM articles WHERE article_id = ?", [$article_id]);
    jsonResponse(true, 'Article deleted successfully');
}

// =====================================================
// SETTINGS FUNCTIONS
// =====================================================

function getSettings($db) {
    $group = $_GET['group'] ?? '';

    $where = "1=1";
    $params = [];

    if ($group) {
        $where .= " AND group_name = ?";
        $params[] = $group;
    }

    $settings = $db->select("
        SELECT * FROM site_settings
        WHERE $where
        ORDER BY sort_order ASC
    ", $params);

    jsonResponse(true, 'Success', ['settings' => $settings]);
}

function getPublicSettings($db) {
    $settings = $db->select("
        SELECT setting_key, setting_value, setting_type
        FROM site_settings
        WHERE is_public = 1
        ORDER BY sort_order ASC
    ");

    // Convert to key-value array
    $result = [];
    foreach ($settings as $s) {
        $result[$s['setting_key']] = $s['setting_value'];
    }

    jsonResponse(true, 'Success', ['settings' => $result]);
}

function getSetting($db, $key) {
    $setting = $db->selectOne("SELECT * FROM site_settings WHERE setting_key = ?", [$key]);

    if (!$setting) {
        jsonResponse(false, 'Setting not found');
    }

    jsonResponse(true, 'Success', ['setting' => $setting]);
}

function updateSetting($db) {
    $key = trim($_POST['key'] ?? '');
    $value = $_POST['value'] ?? '';

    if (empty($key)) {
        jsonResponse(false, 'Setting key is required');
    }

    $db->update("
        UPDATE site_settings SET setting_value = ?, updated_by = ?, updated_at = NOW()
        WHERE setting_key = ?
    ", [$value, getUserId(), $key]);

    jsonResponse(true, 'Setting updated successfully');
}

function updateSettings($db) {
    $settingsRaw = $_POST['settings'] ?? '';
    $settings = is_string($settingsRaw) ? json_decode($settingsRaw, true) : $settingsRaw;

    if (!is_array($settings) || empty($settings)) {
        jsonResponse(false, 'No settings provided');
    }

    foreach ($settings as $key => $value) {
        $db->update("
            UPDATE site_settings SET setting_value = ?, updated_by = ?, updated_at = NOW()
            WHERE setting_key = ?
        ", [$value, getUserId(), $key]);
    }

    jsonResponse(true, 'Settings updated successfully');
}

// =====================================================
// IMAGE UPLOAD FUNCTIONS
// =====================================================

function uploadBannerImage($db) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, 'No image uploaded or upload error');
    }

    // Validate
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes)) {
        jsonResponse(false, 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP');
    }

    if ($_FILES['image']['size'] > 10 * 1024 * 1024) {
        jsonResponse(false, 'Image size must be less than 10MB');
    }

    // Upload
    if (USE_CLOUDINARY) {
        $result = uploadToCloudinary($_FILES['image']['tmp_name'], [
            'public_id' => 'banner_' . time()
        ]);
    } else {
        $result = uploadToLocal($_FILES['image']['tmp_name'], BANNERS_DIR, 'banner_' . time());
    }

    if ($result && $result['success']) {
        jsonResponse(true, 'Image uploaded successfully', [
            'url' => $result['secure_url'] ?? $result['url']
        ]);
    }

    jsonResponse(false, 'Upload failed');
}

function uploadArticleImage($db) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, 'No image uploaded or upload error');
    }

    // Validate
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes)) {
        jsonResponse(false, 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP');
    }

    if ($_FILES['image']['size'] > 10 * 1024 * 1024) {
        jsonResponse(false, 'Image size must be less than 10MB');
    }

    // Upload
    if (USE_CLOUDINARY) {
        $result = uploadToCloudinary($_FILES['image']['tmp_name'], [
            'public_id' => 'article_' . time()
        ]);
    } else {
        $result = uploadToLocal($_FILES['image']['tmp_name'], ARTICLES_DIR, 'article_' . time());
    }

    if ($result && $result['success']) {
        jsonResponse(true, 'Image uploaded successfully', [
            'url' => $result['secure_url'] ?? $result['url']
        ]);
    }

    jsonResponse(false, 'Upload failed');
}
