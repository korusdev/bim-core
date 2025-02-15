<?php


namespace Bim\Util;

/**
 * Class Config
 * @package Bim\Util
 */
class Config
{

    protected $data = array();
    protected $cache = array();

    public function __construct($type = "migrations") {

        switch ($type) {
            case "commands":
                $defaultPath = __DIR__ . "/../config/commands.json";
                break;
            default:
                $defaultPath = __DIR__ . "/../config/bim.json";
        }

        $config = \Bitrix\Main\Config\Configuration::getInstance();
        $configMigrations = $config->get($type);

        if ($configMigrations) {
            $this->data = $configMigrations;
        } else {
            $this->data = $this->parse($defaultPath);
        }
    }

    public function get($key, $default = null) {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $segs = explode('.', $key);
        $root = $this->data;

        foreach ($segs as $part) {
            if (isset($root[$part])) {
                $root = $root[$part];
                continue;
            } else {
                $root = $default;
                break;
            }
        }

        return ($this->cache[$key] = $root);
    }

    protected function parse($path) {        
        $data = json_decode(file_get_contents($path), true);
        
        if (function_exists('json_last_error_msg')) {
            $error_message = json_last_error_msg();
        } else {
            $error_message = 'Syntax error';
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = array(
                'message' => $error_message,
                'type' => json_last_error(),
                'file' => $path,
            );
            throw new \Exception($error);
        }

        return $data;
    }
}