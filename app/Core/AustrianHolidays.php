<?php

namespace App\Core;

class AustrianHolidays {
    private const API_URL = 'https://date.nager.at/api/v3/PublicHolidays/%d/AT';

    public static function getDatesForYear(int $year): array {
        $cacheKey = 'holidays_at_' . $year;
        $cached   = self::readCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $fromApi = self::fetchFromApi($year);
        if ($fromApi !== null) {
            self::writeCache($cacheKey, $fromApi);
            return $fromApi;
        }

        $fallback = self::calculateLocally($year);
        self::writeCache($cacheKey, $fallback);
        return $fallback;
    }

    /** @param int[] $years */
    public static function warmCache(array $years): void {
        foreach ($years as $year) {
            self::getDatesForYear((int) $year);
        }
    }

    private static function fetchFromApi(int $year): ?array {
        $url = sprintf(self::API_URL, $year);
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 8,
                'header'  => "Accept: application/json\r\nUser-Agent: EasyTime/1.0\r\n",
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return null;
        }

        $dates = [];
        foreach ($data as $item) {
            if (!empty($item['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $item['date'])) {
                $dates[] = $item['date'];
            }
        }

        return $dates !== [] ? array_values(array_unique($dates)) : null;
    }

    private static function calculateLocally(int $year): array {
        $fixed = [
            "$year-01-01",
            "$year-01-06",
            "$year-05-01",
            "$year-08-15",
            "$year-10-26",
            "$year-11-01",
            "$year-12-08",
            "$year-12-25",
            "$year-12-26",
        ];

        $easterTs = easter_date($year);
        $easter   = (new \DateTime())->setTimestamp($easterTs);

        $variable = [
            (clone $easter)->modify('+1 day')->format('Y-m-d'),
            (clone $easter)->modify('+39 days')->format('Y-m-d'),
            (clone $easter)->modify('+50 days')->format('Y-m-d'),
            (clone $easter)->modify('+60 days')->format('Y-m-d'),
        ];

        return array_merge($fixed, $variable);
    }

    private static function readCache(string $key): ?array {
        $db   = Database::getConnection();
        $keyCol = Database::isMysql() ? '`key`' : 'key';
        $stmt = $db->prepare("SELECT value FROM app_settings WHERE {$keyCol} = ? LIMIT 1");
        $stmt->execute([$key]);
        $raw = $stmt->fetchColumn();
        if ($raw === false || $raw === '') {
            return null;
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $dates = array_values(array_filter($decoded, static fn ($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)));
        return $dates !== [] ? $dates : null;
    }

    private static function writeCache(string $key, array $dates): void {
        $db   = Database::getConnection();
        Database::upsertAppSetting($key, json_encode(array_values(array_unique($dates))));
    }
}
