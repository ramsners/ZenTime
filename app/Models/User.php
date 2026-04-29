<?php

namespace App\Models;

use App\Core\Database;

class User {
    private static function normalizeRole(?string $role): string {
        $normalized = strtolower(trim((string) $role));
        if ($normalized === 'admin' || $normalized === 'ceo') {
            return 'CEO';
        }
        return 'Employee';
    }

    private static function mapEmployeeRow(array $row): array {
        return [
            'id' => (int) ($row['idMitarbeiter'] ?? 0),
            'mnr' => (string) ($row['id'] ?? ''),
            'firstname' => (string) ($row['vorname'] ?? ''),
            'lastname' => (string) ($row['nachname'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'role' => self::normalizeRole($row['berechtigung'] ?? null),
            'vacation_entitlement_days' => (int) ($row['urlaubsanspruch'] ?? 0),
            'overtime_hours' => isset($row['overtime_hours']) ? (float) $row['overtime_hours'] : 0.0,
            'department_id' => isset($row['department_id']) ? (int) $row['department_id'] : null,
            'department_name' => $row['department_name'] ?? null,
            'department_color' => null,
        ];
    }

    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT
                m.*,
                k.idKlassen AS department_id,
                k.klasse AS department_name,
                (
                    SELECT u.uebertragUeberstunden
                    FROM uebertrag u
                    WHERE u.idMitarbeiter = m.idMitarbeiter
                    ORDER BY u.datum DESC
                    LIMIT 1
                ) AS overtime_hours
            FROM mitarbeiter m
            LEFT JOIN klassen k ON k.Mitarbeiter_idMitarbeiter = m.idMitarbeiter
            ORDER BY m.nachname ASC, m.vorname ASC
        ");
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'mapEmployeeRow'], $rows);
    }

    public static function getById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT
                m.*,
                k.idKlassen AS department_id,
                k.klasse AS department_name,
                (
                    SELECT u.uebertragUeberstunden
                    FROM uebertrag u
                    WHERE u.idMitarbeiter = m.idMitarbeiter
                    ORDER BY u.datum DESC
                    LIMIT 1
                ) AS overtime_hours
            FROM mitarbeiter m
            LEFT JOIN klassen k ON k.Mitarbeiter_idMitarbeiter = m.idMitarbeiter
            WHERE m.idMitarbeiter = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        return self::mapEmployeeRow($row);
    }

    public static function authenticate($emailOrMnr, $password) {
        $db = Database::getConnection();
        $normalizedStaffId = self::normalizeStaffIdentifier((string) $emailOrMnr);
        $stmt = $db->prepare("
            SELECT *
            FROM mitarbeiter
            WHERE email = ? OR id = ? OR id = ?
            LIMIT 1
        ");
        $stmt->execute([$emailOrMnr, $emailOrMnr, $normalizedStaffId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        $storedPassword = (string) ($row['password'] ?? '');
        $isValid = $storedPassword !== '' && (
            hash_equals($storedPassword, $password) ||
            password_verify($password, $storedPassword)
        );
        if (!$isValid) {
            return false;
        }

        return self::mapEmployeeRow($row);
    }

    private static function mapRoleToSchemaValue(string $role): string {
        return strtolower($role) === 'admin' || strtolower($role) === 'ceo'
            ? 'Admin'
            : 'Mitarbeiter';
    }

    private static function normalizeStaffIdentifier(string $mnr): string {
        $trimmed = trim($mnr);
        if ($trimmed === '') {
            return '';
        }
        return ctype_digit($trimmed) ? ('M' . str_pad($trimmed, 3, '0', STR_PAD_LEFT)) : $trimmed;
    }

    private static function upsertClassForEmployee(int $employeeId, ?string $className): void {
        $trimmed = trim((string) $className);
        if ($trimmed === '') {
            return;
        }

        $db = Database::getConnection();
        $existingStmt = $db->prepare("SELECT idKlassen FROM klassen WHERE Mitarbeiter_idMitarbeiter = ? LIMIT 1");
        $existingStmt->execute([$employeeId]);
        $existingId = $existingStmt->fetchColumn();
        if ($existingId) {
            $updateStmt = $db->prepare("UPDATE klassen SET klasse = ? WHERE idKlassen = ?");
            $updateStmt->execute([$trimmed, $existingId]);
            return;
        }

        $insertStmt = $db->prepare("INSERT INTO klassen (klasse, Mitarbeiter_idMitarbeiter) VALUES (?, ?)");
        $insertStmt->execute([$trimmed, $employeeId]);
    }

    private static function upsertOvertime(int $employeeId, float $overtimeHours): void {
        $db = Database::getConnection();
        $today = date('Y-m-d');
        $stmt = $db->prepare("
            INSERT INTO uebertrag (uebertragUrlaub, uebertragUeberstunden, idMitarbeiter, datum, angWochenStd, monatsSoll)
            VALUES (0, ?, ?, ?, NULL, NULL)
            ON CONFLICT(idMitarbeiter, datum)
            DO UPDATE SET uebertragUeberstunden = excluded.uebertragUeberstunden
        ");
        $stmt->execute([$overtimeHours, $employeeId, $today]);
    }

    public static function createEmployee($firstname, $lastname, $email, $mnr, $password, $role = 'Employee', $departmentId = null, $customColor = null, $vacationDays = 25, $overtimeHours = 0) {
        $db = Database::getConnection();
        $staffId = self::normalizeStaffIdentifier((string) $mnr);
        if ($staffId === '') {
            return false;
        }

        $className = null;
        if ($departmentId !== null && $departmentId !== '') {
            $classStmt = $db->prepare("SELECT klasse FROM klassen WHERE idKlassen = ? LIMIT 1");
            $classStmt->execute([(int) $departmentId]);
            $className = $classStmt->fetchColumn() ?: null;
        }

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("
                INSERT INTO mitarbeiter (id, vorname, nachname, email, status, password, berechtigung, urlaubsanspruch, aktWochenStd)
                VALUES (?, ?, ?, ?, 0, ?, ?, ?, 40)
            ");
            $stmt->execute([
                $staffId,
                $firstname,
                $lastname,
                $email,
                $password,
                self::mapRoleToSchemaValue((string) $role),
                (int) $vacationDays
            ]);
            $employeeId = (int) $db->lastInsertId();

            self::upsertClassForEmployee($employeeId, $className);
            self::upsertOvertime($employeeId, (float) $overtimeHours);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }

    public static function updateEmployee($id, $firstname, $lastname, $email, $mnr, $password = null, $role = 'Employee', $departmentId = null, $customColor = null, $vacationDays = 25, $overtimeHours = 0) {
        $db = Database::getConnection();
        $staffId = self::normalizeStaffIdentifier((string) $mnr);
        if ($staffId === '') {
            return false;
        }

        $className = null;
        if ($departmentId !== null && $departmentId !== '') {
            $classStmt = $db->prepare("SELECT klasse FROM klassen WHERE idKlassen = ? LIMIT 1");
            $classStmt->execute([(int) $departmentId]);
            $className = $classStmt->fetchColumn() ?: null;
        }

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("
                UPDATE mitarbeiter
                SET id = ?, vorname = ?, nachname = ?, email = ?, berechtigung = ?, urlaubsanspruch = ?
                WHERE idMitarbeiter = ?
            ");
            $stmt->execute([
                $staffId,
                $firstname,
                $lastname,
                $email,
                self::mapRoleToSchemaValue((string) $role),
                (int) $vacationDays,
                (int) $id
            ]);

            if (!empty($password)) {
                $pwStmt = $db->prepare("UPDATE mitarbeiter SET password = ? WHERE idMitarbeiter = ?");
                $pwStmt->execute([$password, (int) $id]);
            }

            self::upsertClassForEmployee((int) $id, $className);
            self::upsertOvertime((int) $id, (float) $overtimeHours);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }

    public static function deleteEmployee($id) {
        $db = Database::getConnection();
        $employeeId = (int) $id;
        try {
            $db->beginTransaction();

            $db->prepare("DELETE FROM urlaub WHERE Mitarbeiter_idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM klassen WHERE Mitarbeiter_idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM mitarbeiter_has_dokumente WHERE Mitarbeiter_idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM mitarbeiter_has_standorte WHERE Mitarbeiter_idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM eintritt WHERE Mitarbeiter_idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM abmeldung WHERE Mitarbeiter_idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM aenderungsmeldung WHERE Mitarbeiter_idMitarbeiter = ? OR bearbeitetVon = ?")->execute([$employeeId, $employeeId]);
            $db->prepare("DELETE FROM taetigkeit WHERE idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM uebertrag WHERE idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM zuschlag WHERE idMitarbeiter = ?")->execute([$employeeId]);
            $db->prepare("DELETE FROM mitarbeiter WHERE idMitarbeiter = ?")->execute([$employeeId]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return false;
        }
    }

    public static function updatePassword($userId, $newPassword, $clearMustChange = false) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE mitarbeiter SET password = ? WHERE idMitarbeiter = ?");
        return $stmt->execute([$newPassword, (int) $userId]);
    }

    public static function generateResetToken($email) {
        return false;
    }

    public static function verifyResetToken($token) {
        return false;
    }

    public static function clearResetToken($userId) {
        return true;
    }
}
