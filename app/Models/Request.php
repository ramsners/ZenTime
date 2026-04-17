<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Request {
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT r.*, u.firstname, u.lastname, u.email 
            FROM vacation_requests r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function getByUserId($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM vacation_requests WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function create($userId, $startDate, $endDate, $netDays, $type = 'vacation', $deductedHours = 0) {
        if (self::hasBlockedOverlap($startDate, $endDate)) {
            return false;
        }
        if (self::hasUserVacationOverlap($userId, $startDate, $endDate)) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO vacation_requests (user_id, start_date, end_date, net_days, type, deducted_hours) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $startDate, $endDate, $netDays, $type, $deductedHours]);
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
            INSERT INTO vacation_requests (user_id, approver_id, start_date, end_date, net_days, type, deducted_hours, status, admin_comment, decided_at)
            VALUES (?, ?, ?, ?, ?, 'vacation', 0, 'approved', ?, CURRENT_TIMESTAMP)
        ");
        return $stmt->execute([$userId, $approverId, $startDate, $endDate, $netDays, $comment]);
    }

    public static function decide($requestId, $approverId, $status, $comment = null) {
        if ($status === 'approved') {
            $request = self::getById($requestId);
            if ($request && !self::passesMinimumCoverage((int) $request['user_id'], $request['start_date'], $request['end_date'], (int) $request['id'])) {
                return false;
            }
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE vacation_requests SET status = ?, approver_id = ?, admin_comment = ?, decided_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$status, $approverId, $comment, $requestId]);
    }

    public static function getById($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, u.firstname, u.lastname, u.email
            FROM vacation_requests r
            JOIN users u ON u.id = r.user_id
            WHERE r.id = ?
            LIMIT 1
        ");
        $stmt->execute([$requestId]);
        return $stmt->fetch();
    }

    public static function withdrawRequest($id, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM vacation_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
        return $stmt->execute([$id, $userId]);
    }

    public static function requestStorno($id, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE vacation_requests SET status = 'storno_requested' WHERE id = ? AND user_id = ? AND status = 'approved'");
        return $stmt->execute([$id, $userId]);
    }

    public static function getBlockedPeriods() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM booking_blocked_periods ORDER BY start_date ASC");
        return $stmt->fetchAll();
    }

    public static function createBlockedPeriod($startDate, $endDate, $label = null, $createdBy = null) {
        if (self::hasBlockedOverlap($startDate, $endDate)) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO booking_blocked_periods (start_date, end_date, label, created_by) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$startDate, $endDate, $label, $createdBy]);
    }

    public static function deleteBlockedPeriod($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM booking_blocked_periods WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function hasBlockedOverlap($startDate, $endDate) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 1
            FROM booking_blocked_periods
            WHERE start_date <= :end_date
              AND end_date >= :start_date
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
            FROM vacation_requests
            WHERE user_id = :user_id
              AND status NOT IN ('rejected', 'cancelled')
              AND start_date <= :end_date
              AND end_date >= :start_date
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
        $entitlementStmt = $db->prepare("SELECT vacation_entitlement_days FROM users WHERE id = ?");
        $entitlementStmt->execute([$userId]);
        $entitlement = (int) ($entitlementStmt->fetchColumn() ?: 0);

        $approvedStmt = $db->prepare("SELECT COALESCE(SUM(net_days), 0) FROM vacation_requests WHERE user_id = ? AND status = 'approved'");
        $approvedStmt->execute([$userId]);
        $approvedDays = (int) $approvedStmt->fetchColumn();

        $plannedStmt = $db->prepare("SELECT COALESCE(SUM(net_days), 0) FROM vacation_requests WHERE user_id = ? AND status IN ('pending', 'storno_requested')");
        $plannedStmt->execute([$userId]);
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
        $employeesTotalStmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'Employee'");
        $employeesTotal = (int) $employeesTotalStmt->fetchColumn();

        $absentStmt = $db->prepare("
            SELECT COUNT(DISTINCT user_id)
            FROM vacation_requests
            WHERE status = 'approved'
              AND start_date <= :end_date
              AND end_date >= :start_date
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
        $db = Database::getConnection();
        $minimumStmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'min_staff_available' LIMIT 1");
        $minimumStmt->execute();
        $minimumAvailable = (int) ($minimumStmt->fetchColumn() ?: 1);

        $employeesTotalStmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'Employee'");
        $employeesTotal = (int) $employeesTotalStmt->fetchColumn();

        $sql = "
            SELECT COUNT(DISTINCT user_id)
            FROM vacation_requests
            WHERE status = 'approved'
              AND start_date <= :end_date
              AND end_date >= :start_date
        ";
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];
        if ($ignoreRequestId !== null) {
            $sql .= " AND id != :ignore_request_id";
            $params[':ignore_request_id'] = $ignoreRequestId;
        }
        $absentStmt = $db->prepare($sql);
        $absentStmt->execute($params);
        $absentApproved = (int) $absentStmt->fetchColumn();

        $alreadyAbsentStmt = $db->prepare("
            SELECT 1
            FROM vacation_requests
            WHERE user_id = :user_id
              AND status = 'approved'
              AND start_date <= :end_date
              AND end_date >= :start_date
            LIMIT 1
        ");
        $alreadyAbsentStmt->execute([
            ':user_id' => $requestUserId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        $isAlreadyAbsentInWindow = (bool) $alreadyAbsentStmt->fetchColumn();

        $newAbsentCount = $absentApproved + ($isAlreadyAbsentInWindow ? 0 : 1);
        $availableAfterApproval = $employeesTotal - $newAbsentCount;
        return $availableAfterApproval >= $minimumAvailable;
    }
}
