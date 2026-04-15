<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static string $dbPath = __DIR__ . '/../../database/database.sqlite';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $isNew = !file_exists(self::$dbPath);
            
            // Ensure the directory exists
            if (!is_dir(dirname(self::$dbPath))) {
                mkdir(dirname(self::$dbPath), 0777, true);
            }

            try {
                self::$instance = new PDO("sqlite:" . self::$dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                if ($isNew) {
                    self::initializeSchema();
                    self::seedData();
                }
            } catch (PDOException $e) {
                die("Database Connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    private static function initializeSchema() {
        $db = self::$instance;
        $schema = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mnr VARCHAR UNIQUE NOT NULL,
            firstname VARCHAR NOT NULL,
            lastname VARCHAR NOT NULL,
            email VARCHAR UNIQUE NOT NULL,
            role VARCHAR NOT NULL CHECK(role IN ('CEO', 'Employee')),
            vacation_entitlement_days INTEGER NOT NULL DEFAULT 25,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS user_credentials (
            user_id INTEGER PRIMARY KEY,
            password_hash VARCHAR NOT NULL,
            password_salt VARCHAR NOT NULL,
            last_login TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS vacation_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            approver_id INTEGER,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            net_days INTEGER NOT NULL,
            status VARCHAR NOT NULL DEFAULT 'pending' CHECK(status IN ('pending', 'approved', 'rejected')),
            admin_comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            decided_at TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id),
            FOREIGN KEY(approver_id) REFERENCES users(id)
        );
        ";
        $db->exec($schema);
    }

    private static function seedData() {
        $db = self::$instance;
        
        // Insert dummy Admin (CEO) with username "admin" and password "admin"
        $stmt = $db->prepare("INSERT INTO users (mnr, firstname, lastname, email, role) VALUES ('admin', 'Admin', 'User', 'admin', 'CEO')");
        $stmt->execute();
        $adminId = $db->lastInsertId();

        $adminHash = password_hash('admin', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO user_credentials (user_id, password_hash, password_salt) VALUES ($adminId, '$adminHash', '')");

        // Insert dummy Employee
        $stmt = $db->prepare("INSERT INTO users (mnr, firstname, lastname, email, role) VALUES ('E001', 'John', 'Doe', 'john.doe@zentime.com', 'Employee')");
        $stmt->execute();
        $employeeId = $db->lastInsertId();

        $employeeHash = password_hash('password123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO user_credentials (user_id, password_hash, password_salt) VALUES ($employeeId, '$employeeHash', '')");

        // Insert some initial requests
        $db->exec("INSERT INTO vacation_requests (user_id, start_date, end_date, net_days, status) VALUES ($employeeId, date('now', '+7 days'), date('now', '+14 days'), 5, 'pending')");
        $db->exec("INSERT INTO vacation_requests (user_id, approver_id, start_date, end_date, net_days, status, decided_at) VALUES ($employeeId, $adminId, date('now', '-30 days'), date('now', '-25 days'), 4, 'approved', CURRENT_TIMESTAMP)");
        $db->exec("INSERT INTO vacation_requests (user_id, approver_id, start_date, end_date, net_days, status, admin_comment, decided_at) VALUES ($employeeId, $adminId, date('now', '-60 days'), date('now', '-50 days'), 7, 'rejected', 'Too busy these days', CURRENT_TIMESTAMP)");
    }
}
