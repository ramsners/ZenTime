<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Request {
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT r.*, u.firstname, u.lastname, u.email 
            FROM vacation_requests r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function getByUserId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM vacation_requests WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create($userId, $startDate, $endDate, $netDays) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO vacation_requests (user_id, start_date, end_date, net_days) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $startDate, $endDate, $netDays]);
    }

    public static function decide($requestId, $approverId, $status, $comment = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE vacation_requests SET status = ?, approver_id = ?, admin_comment = ?, decided_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$status, $approverId, $comment, $requestId]);
    }
}
