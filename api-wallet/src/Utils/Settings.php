<?php

namespace App\Utils;

class Settings
{
    public static function getSettings()
    {
        $file = __DIR__ . '/../configs.php';
        if (is_file($file) && file_exists($file)) {
            $result = require($file);

            return $result;
        }

        return [];
    }
}