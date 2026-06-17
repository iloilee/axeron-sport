<?php
/**
 * Local Upload Configuration
 * Giải pháp upload ảnh lưu trong thư mục assets/images/products/
 * KHÔNG cần Cloudinary
 */

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Subdirectories
define('PRODUCT_IMAGES_DIR', 'products/');
define('BANNERS_DIR', 'banners/');
define('ARTICLES_DIR', 'articles/');

require_once __DIR__ . '/cloudinary_config.php';

/**
 * Upload file lên thư mục local (Chuyển hướng sang Cloudinary)
 *
 * @param string $tmpFilePath Đường dẫn file tạm
 * @param string $subDir Thư mục con (products/, banners/, articles/)
 * @param string $customName Tên file tùy chỉnh (không bao gồm extension)
 * @return array|false
 */
function uploadToLocal($tmpFilePath, $subDir = 'products/', $customName = null) {
    // Gọi hàm uploadToCloudinary từ cloudinary_config.php
    $options = [];
    if ($customName) {
        $options['public_id'] = $customName;
    }
    
    // Đảm bảo không bị lỗi MIME type, uploadToCloudinary sẽ check
    $result = uploadToCloudinary($tmpFilePath, $options);
    
    if ($result && $result['success']) {
        return [
            'success' => true,
            'filename' => basename($result['secure_url']),
            'path' => $result['secure_url'], // Sử dụng luôn secure_url
            'url' => $result['secure_url'],
            'full_path' => $result['secure_url'],
            'public_id' => $result['public_id'] // Để lưu nếu cần
        ];
    }
    
    return ['success' => false, 'error' => $result['error'] ?? 'Upload lên Cloudinary thất bại'];
}

/**
 * Xóa file ảnh (Chuyển hướng sang Cloudinary)
 *
 * @param string $relativePath Đường dẫn tương đối hoặc public ID
 * @return bool
 */
function deleteLocalImage($relativePath) {
    // Nếu truyền vào là public ID của Cloudinary (ví dụ: axeron-products/product_1234)
    // Nếu là đường dẫn cục bộ (không dùng nữa), cứ kệ
    if (strpos($relativePath, 'http') === 0 && strpos($relativePath, 'cloudinary.com') !== false) {
        // Trích xuất public ID từ URL
        $parts = explode('/upload/', $relativePath);
        if (count($parts) > 1) {
            $pathAfterUpload = $parts[1]; // v123456/axeron-products/product_abc.jpg
            $pathParts = explode('/', $pathAfterUpload);
            array_shift($pathParts); // Bỏ v123456
            $publicIdWithExt = implode('/', $pathParts);
            $publicId = preg_replace('/\.[a-zA-Z0-9]+$/', '', $publicIdWithExt); // Xoá extension
            
            $res = deleteFromCloudinary($publicId);
            return $res['success'];
        }
    }
    
    // Fallback thử xoá local file cũ nếu còn
    $fullPath = UPLOAD_DIR . $relativePath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Tạo thumbnail cho ảnh (tùy chọn)
 *
 * @param string $sourcePath Đường dẫn file nguồi
 * @param int $width Chiều rộng mới
 * @param int $height Chiều cao mới
 * @return string|false
 */
function createThumbnail($sourcePath, $width = 300, $height = 300) {
    // Lấy extension
    $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'png':
            $image = imagecreatefrompng($sourcePath);
            break;
        case 'gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$image) return false;

    $origWidth = imagesx($image);
    $origHeight = imagesy($image);

    // Tính toán tỷ lệ
    $ratio = min($width / $origWidth, $height / $origHeight);
    $newWidth = (int)($origWidth * $ratio);
    $newHeight = (int)($origHeight * $ratio);

    // Tạo ảnh mới
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Giữ transparency cho PNG
    if ($ext === 'png') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }

    // Resize
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    // Lưu thumbnail
    $thumbPath = preg_replace('/(\.[^.]+)$/', '_thumb$1', $sourcePath);

    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($newImage, $thumbPath, 85);
            break;
        case 'png':
            imagepng($newImage, $thumbPath);
            break;
        case 'gif':
            imagegif($newImage, $thumbPath);
            break;
    }

    imagedestroy($image);
    imagedestroy($newImage);

    return $thumbPath;
}
