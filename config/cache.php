<?php
/**
 * Simple File-Based Cache System
 */
class Cache {
    private $cacheDir;
    private static $instance = null;

    private function __construct() {
        $this->cacheDir = __DIR__ . '/../storage/cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getCacheFilePath($key) {
        return $this->cacheDir . md5($key) . '.cache';
    }

    public function get($key) {
        $file = $this->getCacheFilePath($key);
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $cache = unserialize($data);
            if ($cache !== false && $cache['expires'] > time()) {
                return $cache['data'];
            }
            // Expired
            unlink($file);
        }
        return null;
    }

    public function set($key, $data, $ttl = 3600) {
        $file = $this->getCacheFilePath($key);
        $cache = [
            'data' => $data,
            'expires' => time() + $ttl
        ];
        file_put_contents($file, serialize($cache));
    }

    public function delete($key) {
        $file = $this->getCacheFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

function cache() {
    return Cache::getInstance();
}
