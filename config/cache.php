<?php
/**
 * Advanced Cache System (APCu + File fallback)
 */
class Cache {
    private $cacheDir;
    private static $instance = null;
    private $useApcu = false;

    private function __construct() {
        $this->useApcu = function_exists('apcu_fetch') && ini_get('apc.enabled');
        if (!$this->useApcu) {
            $this->cacheDir = __DIR__ . '/../storage/cache/';
            if (!is_dir($this->cacheDir)) {
                @mkdir($this->cacheDir, 0777, true);
            }
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
        if ($this->useApcu) {
            $success = false;
            $data = apcu_fetch($key, $success);
            return $success ? $data : null;
        }

        $file = $this->getCacheFilePath($key);
        if (file_exists($file)) {
            $data = @file_get_contents($file);
            if ($data) {
                $cache = @unserialize($data);
                if ($cache !== false && $cache['expires'] > time()) {
                    return $cache['data'];
                }
            }
            // Expired or corrupt
            @unlink($file);
        }
        return null;
    }

    public function set($key, $data, $ttl = 3600) {
        if ($this->useApcu) {
            apcu_store($key, $data, $ttl);
            return;
        }

        $file = $this->getCacheFilePath($key);
        $cache = [
            'data' => $data,
            'expires' => time() + $ttl
        ];
        @file_put_contents($file, serialize($cache));
    }

    public function delete($key) {
        if ($this->useApcu) {
            apcu_delete($key);
            return;
        }

        $file = $this->getCacheFilePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public function clear() {
        if ($this->useApcu) {
            apcu_clear_cache();
            return;
        }

        $files = glob($this->cacheDir . '*.cache');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}

function cache() {
    return Cache::getInstance();
}
