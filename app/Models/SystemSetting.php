<?php

namespace App\Models;

use App\Core\Database;

class SystemSetting {
    public static function get($key, $default = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    public static function set($key, $value) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value
        ");
        return $stmt->execute([$key, (string) $value]);
    }
}

