<?php

namespace App\Models;

use App\Core\Database;

class Department {
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT MIN(idKlassen) AS id, klasse
            FROM klassen
            WHERE klasse IS NOT NULL AND TRIM(klasse) != ''
            GROUP BY klasse
            ORDER BY klasse ASC
        ");
        $rows = $stmt->fetchAll();
        return array_map(static function (array $row) {
            return [
                'id' => (int) $row['id'],
                'name' => $row['klasse'],
                'color' => null,
            ];
        }, $rows);
    }

    public static function create(string $name, string $color): bool {
        return false;
    }

    public static function delete(int $id): bool {
        return false;
    }
}
