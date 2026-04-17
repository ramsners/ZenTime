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

                self::ensureSchemaUpToDate();

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

    private static function ensureSchemaUpToDate() {
        $db = self::$instance;
        $db->exec("
            CREATE TABLE IF NOT EXISTS booking_blocked_periods (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                label VARCHAR,
                created_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
            );
        ");
    }

    private static function initializeSchema() {
        $db = self::$instance;
        $schema = "
        CREATE TABLE IF NOT EXISTS departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR NOT NULL,
            color VARCHAR NOT NULL DEFAULT '#3b82f6'
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mnr VARCHAR UNIQUE NOT NULL,
            firstname VARCHAR NOT NULL,
            lastname VARCHAR NOT NULL,
            email VARCHAR UNIQUE NOT NULL,
            role VARCHAR NOT NULL CHECK(role IN ('CEO', 'Employee')),
            department_id INTEGER,
            custom_color VARCHAR,
            vacation_entitlement_days INTEGER NOT NULL DEFAULT 25,
            overtime_hours DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(department_id) REFERENCES departments(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS user_credentials (
            user_id INTEGER PRIMARY KEY,
            password_hash VARCHAR NOT NULL,
            password_salt VARCHAR NOT NULL,
            last_login TIMESTAMP,
            must_change_password BOOLEAN DEFAULT 0,
            reset_token VARCHAR,
            reset_expires_at TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS vacation_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            approver_id INTEGER,
            type VARCHAR NOT NULL DEFAULT 'vacation' CHECK(type IN ('vacation', 'overtime')),
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            net_days INTEGER NOT NULL DEFAULT 0,
            deducted_hours DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status VARCHAR NOT NULL DEFAULT 'pending' CHECK(status IN ('pending', 'approved', 'rejected', 'storno_requested', 'cancelled')),
            admin_comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            decided_at TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(approver_id) REFERENCES users(id) ON DELETE SET NULL
        );
        ";
        $db->exec($schema);
    }

    private static function seedData() {
        $db = self::$instance;
        
        // Seed initial departments
        $db->exec("INSERT INTO departments (name, color) VALUES ('Sales', '#f43f5e')"); // Rose
        $db->exec("INSERT INTO departments (name, color) VALUES ('Engineering', '#3b82f6')"); // Blue
        $db->exec("INSERT INTO departments (name, color) VALUES ('Marketing', '#8b5cf6')"); // Violet

        // Insert dummy Admin (CEO)
        $stmt = $db->prepare("INSERT INTO users (mnr, firstname, lastname, email, role) VALUES ('admin', 'Admin', 'User', 'admin', 'CEO')");
        $stmt->execute();
        $adminId = $db->lastInsertId();

        $adminHash = password_hash('admin', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO user_credentials (user_id, password_hash, password_salt) VALUES ($adminId, '$adminHash', '')");

        // Insert dummy Employee
        $stmt = $db->prepare("INSERT INTO users (mnr, firstname, lastname, email, role, department_id, vacation_entitlement_days, overtime_hours) VALUES ('E001', 'John', 'Doe', 'john.doe@zentime.com', 'Employee', 2, 30, 15.5)");
        $stmt->execute();
        $employeeId = $db->lastInsertId();

        $employeeHash = password_hash('password123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO user_credentials (user_id, password_hash, password_salt) VALUES ($employeeId, '$employeeHash', '')");

        // Insert some initial requests
        $db->exec("INSERT INTO vacation_requests (user_id, type, start_date, end_date, net_days, deducted_hours, status) VALUES ($employeeId, 'vacation', date('now', '+7 days'), date('now', '+14 days'), 5, 0, 'pending')");
        $db->exec("INSERT INTO vacation_requests (user_id, approver_id, type, start_date, end_date, net_days, deducted_hours, status, decided_at) VALUES ($employeeId, $adminId, 'vacation', date('now', '-30 days'), date('now', '-25 days'), 4, 0, 'approved', CURRENT_TIMESTAMP)");
        
        // Sample Overtime request
        $db->exec("INSERT INTO vacation_requests (user_id, approver_id, type, start_date, end_date, net_days, deducted_hours, status, decided_at) VALUES ($employeeId, $adminId, 'overtime', date('now', '-10 days'), date('now', '-10 days'), 0, 4.5, 'approved', CURRENT_TIMESTAMP)");
    }
}
