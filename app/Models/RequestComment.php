<?php

namespace App\Models;

use App\Core\Database;

class RequestComment {
    public static function create($requestId, $userId, $comment) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO request_comments (request_id, user_id, comment) VALUES (?, ?, ?)");
        return $stmt->execute([$requestId, $userId, $comment]);
    }

    public static function getByRequestId($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT rc.*, u.firstname, u.lastname, u.role
            FROM request_comments rc
            JOIN users u ON u.id = rc.user_id
            WHERE rc.request_id = ?
            ORDER BY rc.created_at ASC
        ");
        $stmt->execute([$requestId]);
        return $stmt->fetchAll();
    }
}

