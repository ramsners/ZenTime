<?php

namespace App\Models;

use App\Core\Database;

class Notification {
    public static function create($userId, $title, $message, $category = 'info') {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, category) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $message, $category]);
    }

    public static function getByUserId($userId, $limit = 20) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, (int) $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countUnread($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function markAllAsRead($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}

