<?php

namespace App\Models;

use App\Core\Database;

class RequestComment {
    public static function create($requestId, $userId, $comment) {
        $trimmed = trim((string) $comment);
        if ($trimmed === '') {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO request_comments (request_id, user_id, comment) VALUES (?, ?, ?)");
        return $stmt->execute([(int) $requestId, (int) $userId, $trimmed]);
    }

    public static function getByRequestId($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT
                rc.*,
                m.vorname AS firstname,
                m.nachname AS lastname,
                m.berechtigung AS role
            FROM request_comments rc
            JOIN mitarbeiter m ON m.idMitarbeiter = rc.user_id
            WHERE rc.request_id = ?
            ORDER BY rc.created_at ASC
        ");
        $stmt->execute([(int) $requestId]);
        return $stmt->fetchAll();
    }

    public static function getByRequestIds(array $requestIds): array {
        $ids = array_values(array_unique(array_map('intval', $requestIds)));
        $ids = array_filter($ids, static fn($id) => $id > 0);
        if (empty($ids)) {
            return [];
        }

        $db = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("
            SELECT
                rc.*,
                m.vorname AS firstname,
                m.nachname AS lastname,
                m.berechtigung AS role
            FROM request_comments rc
            JOIN mitarbeiter m ON m.idMitarbeiter = rc.user_id
            WHERE rc.request_id IN ($placeholders)
            ORDER BY rc.created_at ASC, rc.id ASC
        ");
        $stmt->execute($ids);

        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['request_id']][] = $row;
        }
        return $grouped;
    }
}
