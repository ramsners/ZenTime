<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    public static function getAll() {
        $db = Database::getConnection();
        return $db->query("SELECT u.*, d.name as department_name, d.color as department_color FROM users u LEFT JOIN departments d ON u.department_id = d.id")->fetchAll();
    }

    public static function getById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT u.*, d.name as department_name, d.color as department_color FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function authenticate($emailOrMnr, $password) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT u.*, c.password_hash, c.must_change_password FROM users u JOIN user_credentials c ON u.id = c.user_id WHERE u.email = ? OR u.mnr = ? LIMIT 1");
        $stmt->execute([$emailOrMnr, $emailOrMnr]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
        return false;
    }

    public static function createEmployee($firstname, $lastname, $email, $mnr, $password, $role = 'Employee', $departmentId = null, $customColor = null, $vacationDays = 25, $overtimeHours = 0) {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            if ($role === 'Admin') {
                $role = 'CEO';
            }
            $safeRole = in_array($role, ['CEO', 'Employee'], true) ? $role : 'Employee';
            $stmt = $db->prepare("INSERT INTO users (mnr, firstname, lastname, email, role, department_id, custom_color, vacation_entitlement_days, overtime_hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$mnr, $firstname, $lastname, $email, $safeRole, $departmentId, $customColor, $vacationDays, $overtimeHours]);
            $userId = $db->lastInsertId();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO user_credentials (user_id, password_hash, password_salt, must_change_password) VALUES (?, ?, '', 1)");
            $stmt->execute([$userId, $hash]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    public static function updateEmployee($id, $firstname, $lastname, $email, $mnr, $password = null, $role = 'Employee', $departmentId = null, $customColor = null, $vacationDays = 25, $overtimeHours = 0) {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            if ($role === 'Admin') {
                $role = 'CEO';
            }
            $safeRole = in_array($role, ['CEO', 'Employee'], true) ? $role : 'Employee';
            $stmt = $db->prepare("UPDATE users SET mnr = ?, firstname = ?, lastname = ?, email = ?, role = ?, department_id = ?, custom_color = ?, vacation_entitlement_days = ?, overtime_hours = ? WHERE id = ?");
            $stmt->execute([$mnr, $firstname, $lastname, $email, $safeRole, $departmentId, $customColor, $vacationDays, $overtimeHours, $id]);

            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE user_credentials SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$hash, $id]);
            }

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    public static function deleteEmployee($id) {
        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // Set approver_id to NULL if this user approved any requests
            $stmt = $db->prepare("UPDATE vacation_requests SET approver_id = NULL WHERE approver_id = ?");
            $stmt->execute([$id]);

            // Delete user's vacation requests
            $stmt = $db->prepare("DELETE FROM vacation_requests WHERE user_id = ?");
            $stmt->execute([$id]);

            // Delete user's credentials
            $stmt = $db->prepare("DELETE FROM user_credentials WHERE user_id = ?");
            $stmt->execute([$id]);

            // Delete the user
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    public static function updatePassword($userId, $newPassword, $clearMustChange = false) {
        $db = Database::getConnection();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $mustChange = $clearMustChange ? 0 : 1;
        $stmt = $db->prepare("UPDATE user_credentials SET password_hash = ?, must_change_password = ? WHERE user_id = ?");
        return $stmt->execute([$hash, $mustChange, $userId]);
    }

    public static function generateResetToken($email) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) return false;

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $db->prepare("UPDATE user_credentials SET reset_token = ?, reset_expires_at = ? WHERE user_id = ?");
        $stmt->execute([$token, $expires, $user['id']]);

        return $token;
    }

    public static function verifyResetToken($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT user_id FROM user_credentials WHERE reset_token = ? AND reset_expires_at > CURRENT_TIMESTAMP LIMIT 1");
        $stmt->execute([$token]);
        $cred = $stmt->fetch();

        if ($cred) {
            return $cred['user_id'];
        }
        return false;
    }

    public static function clearResetToken($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE user_credentials SET reset_token = NULL, reset_expires_at = NULL WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
