<?php

namespace App\Models;

use App\Core\Database;

class AuditLog {
    public static function create($actorUserId, $action, $entityType, $entityId = null, $details = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, details)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$actorUserId, $action, $entityType, $entityId, $details]);
    }

    public static function getRecent($limit = 100) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT al.*, u.firstname, u.lastname
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.actor_user_id
            ORDER BY al.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

