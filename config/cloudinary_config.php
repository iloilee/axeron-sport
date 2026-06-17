<?php
/**
 * Cloudinary Configuration (cURL version - No SDK required)
 *
 * Hướng dẫn setup Cloudinary (MIỄN PHÍ):
 * 1. Đăng ký tại https://cloudinary.com/signup
 * 2. Vào Dashboard → Lấy CLOUD_NAME, API_KEY, API_SECRET
 * 3. Thay thế giá trị bên dưới
 */

// Lấy cấu hình Cloudinary từ .env
require_once __DIR__ . '/env.php';

define('CLOUDINARY_CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME') ?: ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? ''));
define('CLOUDINARY_API_KEY', getenv('CLOUDINARY_API_KEY') ?: ($_ENV['CLOUDINARY_API_KEY'] ?? ''));
define('CLOUDINARY_API_SECRET', getenv('CLOUDINARY_API_SECRET') ?: ($_ENV['CLOUDINARY_API_SECRET'] ?? ''));

// Cấu hình upload mặc định
define('CLOUDINARY_FOLDER', 'axeron-products');     // Thư mục trên Cloudinary
define('CLOUDINARY_PUBLIC_ID_PREFIX', 'product');  // Prefix cho public ID

/**
 * Tạo chữ ký Cloudinary (Signature)
 */
function getCloudinarySignature($paramsToSign, $apiSecret) {
    ksort($paramsToSign);
    $stringToSign = '';
    foreach ($paramsToSign as $k => $v) {
        if ($v !== '') {
            $stringToSign .= ($stringToSign ? '&' : '') . "$k=$v";
        }
    }
    $stringToSign .= $apiSecret;
    return sha1($stringToSign);
}

/**
 * Upload ảnh lên Cloudinary bằng cURL
 *
 * @param string $fileAbsolutePath Đường dẫn file ảnh cục bộ
 * @param array  $options          Các tùy chọn bổ sung
 * @return array|false ['public_id', 'url', 'secure_url'] or false
 */
function uploadToCloudinary($fileAbsolutePath, $options = []) {
    if (!file_exists($fileAbsolutePath)) {
        return ['success' => false, 'error' => 'File không tồn tại'];
    }

    try {
        $cloudName = CLOUDINARY_CLOUD_NAME;
        $apiKey = CLOUDINARY_API_KEY;
        $apiSecret = CLOUDINARY_API_SECRET;

        if (!$cloudName || !$apiKey || !$apiSecret) {
            throw new Exception("Cloudinary credentials missing");
        }

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

        $url = "https://api.cloudinary.com/v1_1/$cloudName/image/upload";
        
        $timestamp = time();
        $publicId = $options['public_id'] ?? generateUniquePublicId();
        $folder = CLOUDINARY_FOLDER;

        $paramsToSign = [
            'folder' => $folder,
            'overwrite' => '1',
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        
        // Add alt text context if provided
        if (!empty($options['alt_text'])) {
            $paramsToSign['context'] = "alt=" . $options['alt_text'];
        }

        $signature = getCloudinarySignature($paramsToSign, $apiSecret);

        $postFields = [
            'file' => new CURLFile($fileAbsolutePath, $mime, basename($fileAbsolutePath)),
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder,
            'public_id' => $publicId,
            'overwrite' => '1'
        ];
        
        if (!empty($options['alt_text'])) {
            $postFields['context'] = "alt=" . $options['alt_text'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode == 200 && isset($result['secure_url'])) {
            return [
                'success' => true,
                'public_id' => $result['public_id'],
                'url' => $result['url'],
                'secure_url' => $result['secure_url'],
                'original_filename' => basename($fileAbsolutePath)
            ];
        }

        return ['success' => false, 'error' => $result['error']['message'] ?? 'Upload failed'];

    } catch (Exception $e) {
        error_log("Cloudinary upload error: " . $e->getMessage());
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
    $cloudName = CLOUDINARY_CLOUD_NAME;
    $apiKey = CLOUDINARY_API_KEY;
    $apiSecret = CLOUDINARY_API_SECRET;

    if (!$cloudName || !$apiKey || !$apiSecret) {
        return ['success' => false, 'error' => 'Cloudinary not initialized'];
    }

    try {
        $timestamp = time();
        $paramsToSign = [
            'public_id' => $publicId,
            'timestamp' => $timestamp
        ];
        $signature = getCloudinarySignature($paramsToSign, $apiSecret);

        $postFields = [
            'api_key' => $apiKey,
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'signature' => $signature
        ];

        $url = "https://api.cloudinary.com/v1_1/$cloudName/image/destroy";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode == 200 && ($result['result'] === 'ok' || $result['result'] === 'not found')) {
            return ['success' => true, 'message' => 'Image deleted successfully'];
        }

        return ['success' => false, 'error' => $result['error']['message'] ?? 'Delete failed'];

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
 */
function transformImageUrl($secureUrl, $options = []) {
    // Nếu không phải link cloudinary thì trả về nguyên gốc
    if (strpos($secureUrl, 'cloudinary.com') === false) return $secureUrl;

    $transformations = [];

    $options = array_merge([
        'width' => 600,
        'height' => 600,
        'crop' => 'fill',
        'format' => 'auto',
        'quality' => 'auto'
    ], $options);

    if ($options['width'] && $options['height']) {
        $transformations[] = sprintf('w_%s,h_%s,c_%s', $options['width'], $options['height'], $options['crop']);
    } else if ($options['width']) {
        $transformations[] = sprintf('w_%s,c_%s', $options['width'], $options['crop']);
    }

    if ($options['format']) {
        $transformations[] = 'f_' . $options['format'];
    }

    if ($options['quality']) {
        $transformations[] = 'q_' . $options['quality'];
    }

    $transString = implode(',', $transformations);

    // Chèn transformation vào url: https://res.cloudinary.com/<cloud>/image/upload/<trans>/v.../
    return preg_replace('/(\/upload\/)/', '$1' . $transString . '/', $secureUrl, 1);
}
