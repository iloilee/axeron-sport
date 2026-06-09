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

/**
 * Upload file lên thư mục local
 *
 * @param string $tmpFilePath Đường dẫn file tạm
 * @param string $subDir Thư mục con (products/, banners/, articles/)
 * @param string $customName Tên file tùy chỉnh (không bao gồm extension)
 * @return array|false
 */
function uploadToLocal($tmpFilePath, $subDir = 'products/', $customName = null) {
    // Validate file
    if (!file_exists($tmpFilePath)) {
        return ['success' => false, 'error' => 'File không tồn tại'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpFilePath);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_TYPES)) {
        return ['success' => false, 'error' => 'Định dạng file không được hỗ trợ'];
    }

    if (filesize($tmpFilePath) > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Kích thước file vượt quá 10MB'];
    }

    // Enforce trailing slash on subDir
    if (!empty($subDir)) {
        $subDir = rtrim($subDir, '/') . '/';
    }

    // Tạo thư mục nếu chưa có
    $uploadDir = UPLOAD_DIR . $subDir;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Tạo tên file duy nhất
    $extension = strtolower(pathinfo($customName ?: basename($_FILES['image']['name'] ?? 'image.jpg'), PATHINFO_EXTENSION));
    if (empty($extension)) $extension = 'jpg';
    $filename = ($customName ?: uniqid('img_') . '_' . time()) . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    // Di chuyển file
    if (move_uploaded_file($tmpFilePath, $targetPath)) {
        $relativePath = $subDir . $filename;
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $relativePath,
            'url' => UPLOAD_URL . $relativePath,
            'full_path' => $targetPath
        ];
    }

    return ['success' => false, 'error' => 'Không thể lưu file'];
}

/**
 * Xóa file ảnh local
 *
 * @param string $relativePath Đường dẫn tương đối
 * @return bool
 */
function deleteLocalImage($relativePath) {
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
