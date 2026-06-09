<?php
/**
 * Cloudinary Configuration
 *
 * Hướng dẫn setup Cloudinary (MIỄN PHÍ):
 * 1. Đăng ký tại https://cloudinary.com/signup
 * 2. Vào Dashboard → Lấy CLOUD_NAME, API_KEY, API_SECRET
 * 3. Thay thế giá trị bên dưới
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Asset\Configuration;

// Cấu hình Cloudinary - THAY ĐỔI THEO TÀI KHOẢN CỦA BẠN
define('CLOUDINARY_CLOUD_NAME', 'dx8yqkmzp');        // Ví dụ: 'abc123'
define('CLOUDINARY_API_KEY', '556438877966755');    // Ví dụ: '123456789012345'
define('CLOUDINARY_API_SECRET', 'wJLxKzN8yQmRsTcUvWxY');  // Ví dụ: 'abcdefghijklmnop'

// Cấu hình upload mặc định
define('CLOUDINARY_FOLDER', 'axeron-products');     // Thư mục trên Cloudinary
define('CLOUDINARY_PUBLIC_ID_PREFIX', 'product');  // Prefix cho public ID

// Khởi tạo Cloudinary instance
try {
    $cloudinaryConfig = Configuration::configure(
        CLOUDINARY_CLOUD_NAME,
        CLOUDINARY_API_KEY,
        CLOUDINARY_API_SECRET
    );

    $cloudinary = new Cloudinary($cloudinaryConfig);
} catch (Exception $e) {
    error_log("Cloudinary configuration error: " . $e->getMessage());
    $cloudinary = null;
}

/**
 * Upload ảnh lên Cloudinary
 *
 * @param string $fileAbsolutePath Đường dẫn file ảnh cục bộ
 * @param array  $options          Các tùy chọn bổ sung
 * @return array|false ['public_id', 'url', 'secure_url'] or false
 */
function uploadToCloudinary($fileAbsolutePath, $options = []) {
    global $cloudinary;

    if (!$cloudinary || !file_exists($fileAbsolutePath)) {
        return false;
    }

    try {
        // Kiểm tra file là ảnh
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileAbsolutePath);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimeTypes)) {
            throw new Exception('File không phải định dạng ảnh hợp lệ');
        }

        // Get file size in bytes
        $fileSize = filesize($fileAbsolutePath);
        if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            throw new Exception('Kích thước ảnh vượt quá giới hạn (10MB)');
        }

        // Setup upload options
        $uploadOptions = [
            'folder' => CLOUDINARY_FOLDER,
            'public_id' => $options['public_id'] ?? generateUniquePublicId(),
            'resource_type' => 'image',
            'overwrite' => true,
            'context' => [
                'alt' => $options['alt_text'] ?? ''
            ]
        ];

        // Upload
        $result = $cloudinary->upload()->upload($fileAbsolutePath, $uploadOptions);

        if ($result['http_code'] == 200) {
            return [
                'success' => true,
                'public_id' => $result['public_id'],
                'url' => $result['url'] ?? null,
                'secure_url' => $result['secure_url'] ?? null,
                'original_filename' => basename($fileAbsolutePath)
            ];
        }

        return ['success' => false, 'error' => 'Upload failed'];

    } catch (Exception $e) {
        error_log("Cloudinary upload error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Upload từ URL (direct upload without local storage)
 *
 * @param string $url  URL của ảnh
 * @param array  $options Các tùy chọn bổ sung
 * @return array|false Kết quả upload
 */
function uploadFromUrl($url, $options = []) {
    global $cloudinary;

    if (!$cloudinary) {
        return false;
    }

    try {
        $uploadOptions = [
            'folder' => CLOUDINARY_FOLDER,
            'public_id' => $options['public_id'] ?? generateUniquePublicId(),
            'source' => $url
        ];

        $result = $cloudinary->upload()->upload(null, $uploadOptions);

        if ($result['http_code'] == 200) {
            return [
                'success' => true,
                'public_id' => $result['public_id'],
                'url' => $result['url'] ?? null,
                'secure_url' => $result['secure_url'] ?? null
            ];
        }

        return ['success' => false, 'error' => 'Upload from URL failed'];

    } catch (Exception $e) {
        error_log("Cloudinary upload from URL error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Xóa ảnh khỏi Cloudinary
 *
 * @param string $publicId Public ID của ảnh
 * @return array Result
 */
function deleteFromCloudinary($publicId) {
    global $cloudinary;

    if (!$cloudinary) {
        return ['success' => false, 'error' => 'Cloudinary not initialized'];
    }

    try {
        $result = $cloudinary->delete_asset()->delete_asset($publicId);

        if ($result['http_code'] == 200) {
            return ['success' => true, 'message' => 'Image deleted successfully'];
        }

        return ['success' => false, 'error' => 'Delete failed'];

    } catch (Exception $e) {
        error_log("Cloudinary delete error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Tạo public ID duy nhất
 */
function generateUniquePublicId() {
    return 'product_' . uniqid() . '_' . time();
}

/**
 * Transform url (resize, crop, etc.)
 *
 * @param string $secureUrl Secure URL từ Cloudinary
 * @param array  $options   Biến đổi (width, height, crop, format)
 * @return string URL đã transform
 */
function transformImageUrl($secureUrl, $options = []) {
    $transformations = [];

    // Default transformations
    $options = array_merge([
        'width' => 600,
        'height' => 600,
        'crop' => 'cover',
        'format' => 'auto',
        'quality' => 'auto'
    ], $options);

    $transformations[] = sprintf('%s,%s', $options['width'], $options['height']);
    $transformations[] = $options['crop'];

    if ($options['format']) {
        $transformations[] = 'f_' . $options['format'];
    }

    if ($options['quality']) {
        $transformations[] = 'q_' . $options['quality'];
    }

    $transString = implode(',', $transformations);

    // Parse URL để thêm transformations
    $parts = parse_url($secureUrl);
    $pathParts = explode('/', trim($parts['path'], '/'));
    $imageName = end($pathParts);

    // Remove extension for transformation
    $imageNameWithoutExt = preg_replace('/\.(jpg|jpeg|png|gif|webp)$/i', '', $imageName);

    return sprintf(
        '%s://%s/%s/w_%s,c_%s/q_%s/f_%s/%s%s.jpg',
        $parts['scheme'],
        $parts['host'],
        $parts['path'] ? rtrim(dirname($parts['path']), '/') : '',
        $options['width'],
        $options['crop'],
        $options['quality'],
        $options['format'],
        $imageNameWithoutExt,
        isset($parts['query']) ? '?' . $parts['query'] : ''
    );
}
