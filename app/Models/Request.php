<?php

namespace App\Models;

use App\Core\Database;

class Request {
    private static function statusToFlag(string $status): int {
        return match ($status) {
            'approved' => 1,
            'rejected' => 2,
            'storno_requested' => 3,
            'cancelled' => 4,
            default => 0, // pending
        };
    }

    private static function flagToStatus($flag): string {
        return match ((int) $flag) {
            1 => 'approved',
            2 => 'rejected',
            3 => 'storno_requested',
            4 => 'cancelled',
            default => 'pending',
        };
    }

    private static function mapVacationRow(array $row): array {
        $start = (string) ($row['beginn'] ?? '');
        $end = (string) ($row['ende'] ?? '');
        $days = (int) ($row['tageImUrlaub'] ?? 0);
        $status = self::flagToStatus($row['genemigt'] ?? 0);
        return [
            'id' => (int) ($row['idUrlaub'] ?? 0),
            'user_id' => (int) ($row['Mitarbeiter_idMitarbeiter'] ?? 0),
            'approver_id' => null,
            'start_date' => $start,
            'end_date' => $end,
            'net_days' => $days,
            'type' => 'vacation',
            'deducted_hours' => 0,
            'status' => $status,
            'admin_comment' => null,
            'created_at' => $start,
            'decided_at' => null,
            'firstname' => (string) ($row['vorname'] ?? ''),
            'lastname' => (string) ($row['nachname'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
        ];
    }

    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT u.*, m.vorname, m.nachname, m.email
            FROM urlaub u
            JOIN mitarbeiter m ON u.Mitarbeiter_idMitarbeiter = m.idMitarbeiter
            ORDER BY u.beginn DESC
        ");
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'mapVacationRow'], $rows);
    }

    public static function getByUserId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, m.vorname, m.nachname, m.email
            FROM urlaub u
            JOIN mitarbeiter m ON u.Mitarbeiter_idMitarbeiter = m.idMitarbeiter
            WHERE u.Mitarbeiter_idMitarbeiter = ?
            ORDER BY u.beginn DESC
        ");
        $stmt->execute([(int) $userId]);
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'mapVacationRow'], $rows);
    }

    public static function create($userId, $startDate, $endDate, $netDays, $type = 'vacation', $deductedHours = 0) {
        if (self::hasBlockedOverlap($startDate, $endDate)) {
            return false;
        }
        if (self::hasUserVacationOverlap($userId, $startDate, $endDate)) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO urlaub (genemigt, beginn, ende, tageImUrlaub, beginnsdatumInWorten, endedatumInWorten, idVertretung, buero, idBueroVertretung, Mitarbeiter_idMitarbeiter)
            VALUES (0, ?, ?, ?, NULL, NULL, NULL, 0, NULL, ?)
        ");
        return $stmt->execute([$startDate, $endDate, (int) $netDays, (int) $userId]);
    }

    public static function createAdminVacation($userId, $approverId, $startDate, $endDate, $netDays, $comment = null) {
        if (self::hasBlockedOverlap($startDate, $endDate)) {
            return false;
        }
        if (self::hasUserVacationOverlap($userId, $startDate, $endDate)) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO urlaub (genemigt, beginn, ende, tageImUrlaub, beginnsdatumInWorten, endedatumInWorten, idVertretung, buero, idBueroVertretung, Mitarbeiter_idMitarbeiter)
            VALUES (1, ?, ?, ?, NULL, NULL, NULL, 0, NULL, ?)
        ");
        if (!$stmt->execute([$startDate, $endDate, (int) $netDays, (int) $userId])) {
            return false;
        }
        return (int) $db->lastInsertId();
    }

    public static function decide($requestId, $approverId, $status, $comment = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE urlaub SET genemigt = ? WHERE idUrlaub = ?");
        return $stmt->execute([self::statusToFlag((string) $status), (int) $requestId]);
    }

    public static function getById($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, m.vorname, m.nachname, m.email
            FROM urlaub u
            JOIN mitarbeiter m ON m.idMitarbeiter = u.Mitarbeiter_idMitarbeiter
            WHERE u.idUrlaub = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $requestId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        return self::mapVacationRow($row);
    }

    public static function withdrawRequest($id, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM urlaub WHERE idUrlaub = ? AND Mitarbeiter_idMitarbeiter = ? AND COALESCE(genemigt, 0) = 0");
        return $stmt->execute([(int) $id, (int) $userId]);
    }

    public static function requestStorno($id, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE urlaub SET genemigt = 3 WHERE idUrlaub = ? AND Mitarbeiter_idMitarbeiter = ? AND COALESCE(genemigt, 0) = 1");
        return $stmt->execute([(int) $id, (int) $userId]);
    }

    public static function getBlockedPeriods() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM urlaubssperre ORDER BY von ASC");
        $rows = $stmt->fetchAll();
        return array_map(static function (array $row) {
            return [
                'id' => (int) ($row['idUrlaubssperre'] ?? 0),
                'start_date' => $row['von'] ?? null,
                'end_date' => $row['bis'] ?? null,
                'label' => null,
                'created_at' => $row['von'] ?? null,
            ];
        }, $rows);
    }

    public static function createBlockedPeriod($startDate, $endDate, $label = null, $createdBy = null) {
        if (self::hasBlockedOverlap($startDate, $endDate)) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO urlaubssperre (von, bis, ganzjaehrig) VALUES (?, ?, 0)");
        return $stmt->execute([$startDate, $endDate]);
    }

    public static function deleteBlockedPeriod($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM urlaubssperre WHERE idUrlaubssperre = ?");
        return $stmt->execute([(int) $id]);
    }

    public static function hasBlockedOverlap($startDate, $endDate) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 1
            FROM urlaubssperre
            WHERE von <= :end_date
              AND bis >= :start_date
            LIMIT 1
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public static function hasUserVacationOverlap($userId, $startDate, $endDate) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 1
            FROM urlaub
            WHERE Mitarbeiter_idMitarbeiter = :user_id
              AND COALESCE(genemigt, 0) NOT IN (2, 4)
              AND beginn <= :end_date
              AND ende >= :start_date
            LIMIT 1
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public static function calculateUserVacationStats($userId) {
        $db = Database::getConnection();
        $entitlementStmt = $db->prepare("SELECT urlaubsanspruch FROM mitarbeiter WHERE idMitarbeiter = ?");
        $entitlementStmt->execute([(int) $userId]);
        $entitlement = (int) ($entitlementStmt->fetchColumn() ?: 0);

        $approvedStmt = $db->prepare("SELECT COALESCE(SUM(tageImUrlaub), 0) FROM urlaub WHERE Mitarbeiter_idMitarbeiter = ? AND COALESCE(genemigt, 0) = 1");
        $approvedStmt->execute([(int) $userId]);
        $approvedDays = (int) $approvedStmt->fetchColumn();

        $plannedStmt = $db->prepare("SELECT COALESCE(SUM(tageImUrlaub), 0) FROM urlaub WHERE Mitarbeiter_idMitarbeiter = ? AND COALESCE(genemigt, 0) IN (0, 3)");
        $plannedStmt->execute([(int) $userId]);
        $plannedDays = (int) $plannedStmt->fetchColumn();

        return [
            'entitlement' => $entitlement,
            'approved' => $approvedDays,
            'planned' => $plannedDays,
            'remaining' => max(0, $entitlement - $approvedDays - $plannedDays)
        ];
    }

    public static function getCapacitySummary($startDate, $endDate) {
        $db = Database::getConnection();
        $employeesTotalStmt = $db->query("SELECT COUNT(*) FROM mitarbeiter WHERE LOWER(COALESCE(berechtigung, '')) != 'admin'");
        $employeesTotal = (int) $employeesTotalStmt->fetchColumn();

        $absentStmt = $db->prepare("
            SELECT COUNT(DISTINCT Mitarbeiter_idMitarbeiter)
            FROM urlaub
            WHERE COALESCE(genemigt, 0) = 1
              AND beginn <= :end_date
              AND ende >= :start_date
        ");
        $absentStmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        $absentApproved = (int) $absentStmt->fetchColumn();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'employees_total' => $employeesTotal,
            'absent_approved' => $absentApproved,
            'available' => max(0, $employeesTotal - $absentApproved)
        ];
    }

    public static function passesMinimumCoverage($requestUserId, $startDate, $endDate, $ignoreRequestId = null) {
        return true;
    }
}
