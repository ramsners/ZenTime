<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function getAll() {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM users")->fetchAll();
    }

    public static function getById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function authenticate($emailOrMnr, $password) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT u.*, c.password_hash FROM users u JOIN user_credentials c ON u.id = c.user_id WHERE u.email = ? OR u.mnr = ? LIMIT 1");
        $stmt->execute([$emailOrMnr, $emailOrMnr]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
        return false;
    }

    public static function createEmployee($firstname, $lastname, $email, $mnr, $password) {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO users (mnr, firstname, lastname, email, role) VALUES (?, ?, ?, ?, 'Employee')");
            $stmt->execute([$mnr, $firstname, $lastname, $email]);
            $userId = $db->lastInsertId();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO user_credentials (user_id, password_hash, password_salt) VALUES (?, ?, '')");
            $stmt->execute([$userId, $hash]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
