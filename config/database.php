<?php
/**
 * Database Configuration - Axeron Sports Shop
 * Kết nối MySQL sử dụng MySQLi
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sports_shop');
define('DB_PORT', '3306');

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            $this->connection->set_charset('utf8mb4');
            $this->connection->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
        } catch (mysqli_sql_exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            // Return JSON for API requests instead of HTML
            if (php_sapi_name() === 'cli' || !headers_sent()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Không thể kết nối database. Vui lòng thử lại sau.']);
            }
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }

    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function delete($sql, $params = []) {
        return $this->update($sql, $params);
    }

    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    public function beginTransaction() {
        $this->connection->begin_transaction();
    }

    public function commit() {
        $this->connection->commit();
    }

    public function rollback() {
        $this->connection->rollback();
    }

    // Ngăn không cho clone
    private function __clone() {}

    // Ngăn không cho unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function để sử dụng nhanh
function db() {
    return Database::getInstance();
}

// Helper function format tiền VND
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

// Helper function tạo slug từ text
function createSlug($string) {
    $string = preg_replace('/[^a-zA-Z0-9\s-]/u', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return strtolower($string);
}

// Helper function sanitize input
function sanitize($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

// Helper function redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper function JSON response
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Helper function to resolve image url
function getImageUrl($url, $default = null) {
    if (empty($url)) {
        return $default;
    }
    // Check if it is an absolute URL (starting with http:// or https://)
    if (preg_match('/^https?:\/\//i', $url)) {
        return $url;
    }
    // Determine the base URL
    $baseUrl = '';
    if (defined('BASE_URL')) {
        $baseUrl = BASE_URL;
    }
    
    // Ensure relative path starts with a slash
    $path = $url;
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }
    
    // Normalize base URL
    if (!empty($baseUrl)) {
        $baseUrl = rtrim($baseUrl, '/');
    }
    
    return $baseUrl . $path;
}
