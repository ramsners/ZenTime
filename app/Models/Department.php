<?php

namespace App\Models;

use App\Core\Database;

class Department {
    public static function getAll(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM departments ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function create(string $name, string $color): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO departments (name, color) VALUES (?, ?)");
        return $stmt->execute([$name, $color]);
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        // Constraints ON DELETE SET NULL on users will automatically detach employees
        $stmt = $db->prepare("DELETE FROM departments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
