<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Request as VacationRequest;
use App\Models\User;

class NotificationService {
    public static function notifyUser(int $userId, string $title, string $message, string $category = 'info'): void {
        Notification::create($userId, $title, $message, $category);
    }

    public static function notifyAdmins(string $title, string $message, string $category = 'approval'): void {
        foreach (User::getAdminUserIds() as $adminId) {
            Notification::create($adminId, $title, $message, $category);
        }
    }

    public static function onVacationRequested(int $requestId, int $employeeId): void {
        $req = VacationRequest::getById($requestId);
        $emp = User::getById($employeeId);
        if (!$req || !$emp) {
            return;
        }
        $name = trim($emp['firstname'] . ' ' . $emp['lastname']);
        $range = $req['start_date'] . ' – ' . $req['end_date'];
        self::notifyAdmins(
            'Neuer Urlaubsantrag',
            "{$name} hat Urlaub beantragt ({$range}, {$req['net_days']} Tage). Antrag #{$requestId}.",
            'approval'
        );
    }

    public static function onVacationDecided(int $requestId, string $status): void {
        $req = VacationRequest::getById($requestId);
        if (!$req) {
            return;
        }
        $range = $req['start_date'] . ' – ' . $req['end_date'];
        $messages = [
            'approved'  => ['Urlaub genehmigt', "Dein Antrag #{$requestId} ({$range}) wurde genehmigt.", 'success'],
            'rejected'  => ['Urlaub abgelehnt', "Dein Antrag #{$requestId} ({$range}) wurde abgelehnt.", 'rejected'],
            'cancelled' => ['Urlaub storniert', "Dein Antrag #{$requestId} ({$range}) wurde storniert.", 'info'],
        ];
        if (!isset($messages[$status])) {
            return;
        }
        [$title, $message, $category] = $messages[$status];
        self::notifyUser((int) $req['user_id'], $title, $message, $category);
    }

    public static function onStornoRequested(int $requestId, int $employeeId): void {
        $req = VacationRequest::getById($requestId);
        $emp = User::getById($employeeId);
        if (!$req || !$emp) {
            return;
        }
        $name = trim($emp['firstname'] . ' ' . $emp['lastname']);
        $range = $req['start_date'] . ' – ' . $req['end_date'];
        self::notifyAdmins(
            'Storno angefragt',
            "{$name} möchte den genehmigten Urlaub ({$range}) stornieren. Antrag #{$requestId}.",
            'approval'
        );
    }
}
