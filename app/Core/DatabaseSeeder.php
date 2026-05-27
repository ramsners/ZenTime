<?php

namespace App\Core;

use PDO;

/**
 * Demo-/Testdaten für lokale Entwicklung (SQLite).
 * Die drei Standard-Testuser (IDs 1–3) bleiben beim Reseed erhalten (Login-Daten unverändert).
 */
class DatabaseSeeder {
    /** @var list<int> */
    public const PRESERVED_USER_IDS = [1, 2, 3];

    /** @var list<array<string, mixed>> */
    private const PRESERVED_USERS = [
        [
            'id'              => 1,
            'personal_id'     => 'A001',
            'vorname'         => 'Admin',
            'nachname'        => 'User',
            'email'           => 'admin@firma.at',
            'position'        => 'Leitung',
            'password'        => 'admin',
            'berechtigung'    => 'Administrator',
            'urlaubsanspruch' => 240,
            'akt_wochen_std'  => 40,
        ],
        [
            'id'              => 2,
            'personal_id'     => 'M002',
            'vorname'         => 'Lisa',
            'nachname'        => 'Muster',
            'email'           => 'lisa@firma.at',
            'position'        => 'Mitarbeiter',
            'password'        => 'password',
            'berechtigung'    => 'Mitarbeiter',
            'urlaubsanspruch' => 200,
            'akt_wochen_std'  => 38,
        ],
        [
            'id'              => 3,
            'personal_id'     => 'M003',
            'vorname'         => 'Tom',
            'nachname'        => 'Beispiel',
            'email'           => 'tom@firma.at',
            'position'        => 'Mitarbeiter',
            'password'        => 'password',
            'berechtigung'    => 'Mitarbeiter',
            'urlaubsanspruch' => 200,
            'akt_wochen_std'  => 40,
        ],
    ];

    public static function credentialsHelp(): string {
        return <<<'TEXT'
Test-Zugänge (unverändert nach jedem Reseed):
  Administrator: admin@firma.at  /  admin   (auch Personal-ID: A001)
  Mitarbeiter:   lisa@firma.at   /  password (Personal-ID: M002)
  Mitarbeiter:   tom@firma.at    /  password (Personal-ID: M003)
TEXT;
    }

    public static function seedFreshDatabase(PDO $db): void {
        self::ensurePreservedUsers($db);
        self::seedDemoData($db);
    }

    public static function resetAndSeed(PDO $db): void {
        if (Database::isMysql()) {
            throw new \RuntimeException(
                'db:seed unterstützt nur SQLite (lokal ohne DB_DRIVER=mysql). Für MariaDB: migrate-Profil nutzen.'
            );
        }

        self::clearDemoData($db);
        self::ensurePreservedUsers($db);
        self::seedDemoData($db);
        self::resetSqliteSequences($db);
    }

    private static function clearDemoData(PDO $db): void {
        $db->exec('PRAGMA foreign_keys = OFF');

        $tables = [
            'urlaub_kommentar',
            'urlaub_event',
            'urlaub',
            'taetigkeit',
            'zuschlag',
            'uebertrag',
            'monatsbericht_view',
            'event',
            'urlaubssperre',
            'standort_vertretung',
            'klassen',
            'eintritt',
            'abmeldung',
            'aenderungsmeldung',
            'mitarbeiter_dokumente',
            'dokumente',
            'mitarbeiter_standorte',
            'vorlagen',
            'standorte',
            'taetigkeitsart',
            'app_settings',
        ];

        foreach ($tables as $table) {
            $db->exec("DELETE FROM {$table}");
        }

        $ids = implode(',', self::PRESERVED_USER_IDS);
        $db->exec("DELETE FROM mitarbeiter WHERE id NOT IN ({$ids})");

        $db->exec('PRAGMA foreign_keys = ON');
    }

    private static function ensurePreservedUsers(PDO $db): void {
        $stmt = $db->prepare("
            INSERT OR REPLACE INTO mitarbeiter (
                id, personal_id, vorname, nachname, email, position, status,
                password, berechtigung, urlaubsanspruch, akt_wochen_std
            ) VALUES (
                :id, :personal_id, :vorname, :nachname, :email, :position, 0,
                :password, :berechtigung, :urlaubsanspruch, :akt_wochen_std
            )
        ");

        foreach (self::PRESERVED_USERS as $user) {
            $stmt->execute($user);
        }
    }

    private static function seedDemoData(PDO $db): void {
        $today      = new \DateTimeImmutable('today');
        $in10       = $today->modify('+10 days')->format('Y-m-d');
        $in14       = $today->modify('+14 days')->format('Y-m-d');
        $in20       = $today->modify('+20 days')->format('Y-m-d');
        $in22       = $today->modify('+22 days')->format('Y-m-d');
        $in12       = $today->modify('+12 days')->format('Y-m-d');
        $yesterday  = $today->modify('-1 day')->format('Y-m-d');
        $twoDaysAgo = $today->modify('-2 days')->format('Y-m-d');
        $yearStart  = $today->format('Y') . '-01-01';
        $nextMonth  = $today->modify('+1 month')->format('Y-m-d');
        $nextMonthEnd = $today->modify('+1 month +2 days')->format('Y-m-d');

        $db->exec("
            INSERT INTO klassen (id, klasse, mitarbeiter_id) VALUES
            (1, 'A - Motorrad', 1),
            (2, 'B - Personenkraftwagen', 1),
            (3, 'C - Lastkraftwagen', 1),
            (4, 'CE - Lastkraftwagen Anhänger', 1),
            (5, 'D - Autobus', 1),
            (6, 'EzB - Personenkraftwagen Anhänger', 1),
            (7, 'F - Traktor', 1),
            (8, 'L17-Schulung', 1),
            (9, 'Perfektionsfahrten', 1),
            (10, 'B - Personenkraftwagen', 2),
            (11, 'A - Motorrad', 3)
        ");

        $db->exec("
            INSERT INTO standorte (id, ort, kostenstelle, strasse, hausnummer, plz) VALUES
            (1, 'Ybbs an der Donau', 11, 'Gewerbestraße', '14', 3370),
            (2, 'Pöchlarn', 11, 'Regensburgerstraße', '14', 3380),
            (3, 'Wieselburg an der Erlauf', 11, 'Anton-Fahrner-Gasse', '2', 3250),
            (4, 'Gmünd', 21, 'Bahnhofstraße', '21', 3950),
            (5, 'Horn', 31, 'Am Kuhberg', '5', 3580),
            (6, 'St. Pölten', 61, 'Hofstatt', '5', 3100)
        ");

        $db->exec("
            INSERT INTO standort_vertretung (id, standort_id, vertreter_id, prioritaet) VALUES
            (1, 1, 2, 1), (2, 1, 3, 2),
            (3, 2, 1, 1), (4, 2, 3, 2),
            (5, 3, 2, 1), (6, 3, 1, 2),
            (7, 4, 3, 1), (8, 5, 2, 1), (9, 6, 1, 1)
        ");

        $db->exec("
            INSERT INTO taetigkeitsart (id, bezeichnung) VALUES
            (1, 'lektion'), (3, 'regie'), (4, 'pruefung'),
            (5, 'krank'), (6, 'feiertag'), (7, 'urlaub')
        ");

        $db->exec("
            INSERT INTO eintritt (id, mitarbeiter_id, eintrittsdatum, std_woche, berufsjahr, einstufung, offener_urlaub)
            VALUES
            (1, 1, '2020-01-01', 40, 6, 'KV Admin', 0),
            (2, 2, '2022-03-01', 38, 4, 'KV Büro', 10),
            (3, 3, '2021-06-15', 40, 5, 'KV Büro', 8)
        ");

        $db->exec("
            INSERT INTO mitarbeiter_standorte (mitarbeiter_id, standort_id, basis) VALUES
            (1, 1, 1), (2, 1, 1), (2, 2, 0), (3, 3, 1), (3, 1, 0)
        ");

        $eventStmt = $db->prepare("
            INSERT INTO event (id, standort_id, start, ende, titel, bemerkung, klassen, urlaub_akzeptabel, in_urlaub, eventtyp, status)
            VALUES (1, 1, ?, ?, 'Team-Schulung', 'Interne Abstimmung', 'B - Personenkraftwagen', 1, 0, 'Theorie', 0)
        ");
        $eventStmt->execute([$in12, $in12]);

        $urlaubStmt = $db->prepare("
            INSERT INTO urlaub (id, mitarbeiter_id, beginn, ende, tage_im_urlaub, beginn_in_worten, ende_in_worten, vertretung_id, buero, buero_vertretung_id, genehmigt)
            VALUES
            (1, 2, ?, ?, 5, 'in Kürze', 'in Kürze', NULL, 1, NULL, 0),
            (2, 3, ?, ?, 3, 'in Kürze', 'in Kürze', 1, 1, 1, 1),
            (3, 2, ?, ?, 3, 'geplant', 'geplant', 3, 1, NULL, 3)
        ");
        $urlaubStmt->execute([$in10, $in14, $in20, $in22, $nextMonth, $nextMonthEnd]);

        $db->exec("
            INSERT INTO urlaubssperre (id, von, bis, ganzjaehrig) VALUES
            (1, '2025-12-24', '2026-01-06', 0)
        ");

        $db->exec("INSERT INTO urlaub_event (id, event_id, urlaub_id) VALUES (1, 1, 2)");

        $taetStmt = $db->prepare("
            INSERT INTO taetigkeit (id, datum, mitarbeiter_id, taetigkeitsart_id, stunden)
            VALUES
            (1, ?, 2, 1, 7.5),
            (2, ?, 3, 3, 8.0),
            (3, ?, 2, 1, 6.0)
        ");
        $taetStmt->execute([$yesterday, $yesterday, $twoDaysAgo]);

        $ueberStmt = $db->prepare("
            INSERT INTO uebertrag (mitarbeiter_id, datum, uebertrag_urlaub, uebertrag_ueberstunden, ang_wochen_std, monats_soll)
            VALUES (2, ?, 2.5, 4.0, 38.0, 152.0), (3, ?, 1.0, 2.0, 40.0, 160.0)
        ");
        $ueberStmt->execute([$yearStart, $yearStart]);

        $zuschStmt = $db->prepare("
            INSERT INTO zuschlag (mitarbeiter_id, datum, gr10_pro_tag, wochenende, nacht, A, C, E, F, D, theorie)
            VALUES (2, ?, 0.5, 0, 0, 0, 0, 0, 0, 0, 0.25)
        ");
        $zuschStmt->execute([$twoDaysAgo]);

        $db->exec("INSERT OR IGNORE INTO app_settings (key, value) VALUES ('min_staff_available', '1')");
        $db->exec("INSERT OR IGNORE INTO app_settings (key, value) VALUES ('max_fenstertage', '0')");
    }

    private static function resetSqliteSequences(PDO $db): void {
        $tables = [
            'klassen', 'standorte', 'standort_vertretung', 'eintritt', 'event',
            'urlaub', 'urlaubssperre', 'urlaub_event', 'taetigkeit', 'taetigkeitsart',
        ];
        foreach ($tables as $table) {
            $max = (int) $db->query("SELECT COALESCE(MAX(id), 0) FROM {$table}")->fetchColumn();
            $db->exec("DELETE FROM sqlite_sequence WHERE name = " . $db->quote($table));
            if ($max > 0) {
                $db->exec("INSERT INTO sqlite_sequence (name, seq) VALUES (" . $db->quote($table) . ", {$max})");
            }
        }
    }
}
