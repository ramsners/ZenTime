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
                self::ensureSchemaUpToDate();
            } catch (PDOException $e) {
                die("Database Connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    private static function ensureSchemaUpToDate() {
        self::$instance->exec("PRAGMA foreign_keys = ON;");
        $db = self::$instance;
        $hasBaseTables = $db->query("
            SELECT COUNT(*)
            FROM sqlite_master
            WHERE type = 'table'
              AND name IN ('mitarbeiter', 'urlaub')
        ")->fetchColumn();
        if ((int) $hasBaseTables < 2) {
            return;
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS urlaub_kommentar (
                id             INTEGER PRIMARY KEY AUTOINCREMENT,
                urlaub_id      INTEGER NOT NULL,
                mitarbeiter_id INTEGER NOT NULL,
                kommentar      TEXT    NOT NULL,
                erstellt_am    DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(urlaub_id)      REFERENCES urlaub(id)       ON DELETE CASCADE,
                FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id)  ON DELETE CASCADE
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS app_settings (
                key   TEXT PRIMARY KEY,
                value TEXT NOT NULL DEFAULT ''
            )
        ");
        $db->exec("INSERT OR IGNORE INTO app_settings (key, value) VALUES ('min_staff_available', '1')");
        $db->exec("INSERT OR IGNORE INTO app_settings (key, value) VALUES ('max_fenstertage', '0')");
    }

    private static function initializeSchema() {
        $db = self::$instance;
        $schema = "
        CREATE TABLE IF NOT EXISTS mitarbeiter (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            personal_id     TEXT,
            vorname         TEXT,
            nachname        TEXT,
            strasse         TEXT,
            hausnummer      TEXT,
            plz             INTEGER,
            ort             TEXT,
            geb_datum       TEXT,
            sv_nummer       TEXT,
            geschlecht      TEXT,
            email           TEXT,
            firmen_telefon  TEXT,
            privat_telefon  TEXT,
            iban            TEXT,
            bic             TEXT,
            position        TEXT,
            weitere_position TEXT,
            bemerkung       TEXT,
            status          INTEGER,
            password        TEXT,
            berechtigung    TEXT,
            urlaubsanspruch INTEGER,
            akt_wochen_std  INTEGER
        );

        CREATE TABLE IF NOT EXISTS standorte (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            ort          TEXT,
            kostenstelle INTEGER,
            strasse      TEXT,
            hausnummer   TEXT,
            plz          INTEGER
        );

        CREATE TABLE IF NOT EXISTS klassen (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            klasse         TEXT,
            mitarbeiter_id INTEGER NOT NULL,
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id)
        );

        CREATE TABLE IF NOT EXISTS dokumente (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            url          TEXT,
            upload_datum TEXT
        );

        CREATE TABLE IF NOT EXISTS mitarbeiter_dokumente (
            mitarbeiter_id INTEGER NOT NULL,
            dokument_id    INTEGER NOT NULL,
            PRIMARY KEY (mitarbeiter_id, dokument_id),
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id),
            FOREIGN KEY(dokument_id)    REFERENCES dokumente(id)
        );

        CREATE TABLE IF NOT EXISTS mitarbeiter_standorte (
            mitarbeiter_id INTEGER NOT NULL,
            standort_id    INTEGER NOT NULL,
            basis          INTEGER NOT NULL,
            PRIMARY KEY (mitarbeiter_id, standort_id),
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id),
            FOREIGN KEY(standort_id)    REFERENCES standorte(id)
        );

        CREATE TABLE IF NOT EXISTS standort_vertretung (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            standort_id  INTEGER NOT NULL,
            vertreter_id INTEGER NOT NULL,
            prioritaet   INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS eintritt (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            mitarbeiter_id INTEGER NOT NULL,
            eintrittsdatum TEXT,
            std_woche      INTEGER,
            berufsjahr     INTEGER,
            einstufung     TEXT,
            offener_urlaub INTEGER,
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id)
        );

        CREATE TABLE IF NOT EXISTS abmeldung (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            mitarbeiter_id  INTEGER NOT NULL,
            datum_abmeldung TEXT,
            datum_anmeldung TEXT,
            resturlaub      INTEGER,
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id)
        );

        CREATE TABLE IF NOT EXISTS aenderungsmeldung (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            mitarbeiter_id  INTEGER NOT NULL,
            bearbeitet_von  INTEGER NOT NULL,
            datum           TEXT,
            std_woche       INTEGER,
            urlaubsanspruch INTEGER,
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id),
            FOREIGN KEY(bearbeitet_von) REFERENCES mitarbeiter(id)
        );

        CREATE TABLE IF NOT EXISTS taetigkeitsart (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            bezeichnung TEXT
        );

        CREATE TABLE IF NOT EXISTS taetigkeit (
            id                INTEGER PRIMARY KEY AUTOINCREMENT,
            datum             TEXT,
            mitarbeiter_id    INTEGER NOT NULL,
            taetigkeitsart_id INTEGER NOT NULL,
            stunden           REAL,
            FOREIGN KEY(mitarbeiter_id)    REFERENCES mitarbeiter(id)    ON DELETE CASCADE,
            FOREIGN KEY(taetigkeitsart_id) REFERENCES taetigkeitsart(id) ON DELETE CASCADE,
            UNIQUE (datum, mitarbeiter_id, taetigkeitsart_id)
        );

        CREATE TABLE IF NOT EXISTS event (
            id                INTEGER PRIMARY KEY AUTOINCREMENT,
            standort_id       INTEGER NOT NULL,
            start             TEXT,
            ende              TEXT,
            titel             TEXT,
            eventtyp          TEXT NOT NULL,
            klassen           TEXT,
            urlaub_akzeptabel INTEGER,
            in_urlaub         INTEGER NOT NULL,
            status            INTEGER NOT NULL,
            bemerkung         TEXT,
            FOREIGN KEY(standort_id) REFERENCES standorte(id)
        );

        CREATE TABLE IF NOT EXISTS urlaub (
            id                  INTEGER PRIMARY KEY AUTOINCREMENT,
            mitarbeiter_id      INTEGER NOT NULL,
            beginn              TEXT,
            ende                TEXT,
            tage_im_urlaub      INTEGER,
            beginn_in_worten    TEXT,
            ende_in_worten      TEXT,
            genehmigt           INTEGER,
            vertretung_id       INTEGER,
            buero               INTEGER,
            buero_vertretung_id INTEGER,
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id)
        );

        CREATE TABLE IF NOT EXISTS urlaubssperre (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            von         TEXT,
            bis         TEXT,
            ganzjaehrig INTEGER
        );

        CREATE TABLE IF NOT EXISTS urlaub_kommentar (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            urlaub_id      INTEGER NOT NULL,
            mitarbeiter_id INTEGER NOT NULL,
            kommentar      TEXT    NOT NULL,
            erstellt_am    DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(urlaub_id)      REFERENCES urlaub(id)      ON DELETE CASCADE,
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS urlaub_event (
            id        INTEGER PRIMARY KEY AUTOINCREMENT,
            event_id  INTEGER NOT NULL,
            urlaub_id INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS uebertrag (
            mitarbeiter_id         INTEGER NOT NULL,
            datum                  TEXT    NOT NULL,
            uebertrag_urlaub       REAL,
            uebertrag_ueberstunden REAL,
            ang_wochen_std         REAL,
            monats_soll            REAL,
            PRIMARY KEY (mitarbeiter_id, datum),
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS vorlagen (
            id  INTEGER PRIMARY KEY AUTOINCREMENT,
            typ TEXT,
            url TEXT
        );

        CREATE TABLE IF NOT EXISTS zuschlag (
            mitarbeiter_id INTEGER NOT NULL,
            datum          TEXT    NOT NULL,
            gr10_pro_tag   REAL,
            wochenende     REAL,
            nacht          REAL,
            A              REAL,
            C              REAL,
            E              REAL,
            F              REAL,
            D              REAL,
            theorie        REAL,
            PRIMARY KEY (mitarbeiter_id, datum),
            FOREIGN KEY(mitarbeiter_id) REFERENCES mitarbeiter(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS monatsbericht_view (
            mitarbeiter_id INTEGER,
            datum          TEXT,
            A              REAL,
            C              REAL,
            E              REAL,
            F              REAL,
            D              REAL,
            theorie        REAL,
            nacht          REAL,
            wochenende     REAL,
            gr10_pro_tag   REAL,
            lektion        REAL,
            regie          REAL,
            pruefung       REAL,
            krank          REAL,
            feiertag       REAL,
            urlaub_col     REAL
        );
        ";
        $db->exec($schema);
    }

    private static function seedData() {
        $db = self::$instance;

        $db->exec("
            INSERT INTO mitarbeiter (id, personal_id, vorname, nachname, email, position, status, password, berechtigung, urlaubsanspruch, akt_wochen_std)
            VALUES
            (1, 'A001', 'Admin', 'User', 'admin@firma.at', 'Leitung', 0, 'admin', 'Administrator', 240, 40),
            (2, 'M002', 'Lisa', 'Muster', 'lisa@firma.at', 'Mitarbeiter', 0, 'password', 'Mitarbeiter', 200, 38),
            (3, 'M003', 'Tom', 'Beispiel', 'tom@firma.at', 'Mitarbeiter', 0, 'password', 'Mitarbeiter', 200, 40)
        ");

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
            (9, 'Perfektionsfahrten', 1)
        ");

        $db->exec("
            INSERT INTO standorte (id, ort, kostenstelle, strasse, hausnummer, plz) VALUES
            (1, 'Ybbs an der Donau', 11, 'Gewerbestraße', '14', 3370),
            (2, 'Pöchlarn', 11, 'Regensburgerstraße', '14', 3380),
            (3, 'Wieselburg an der Erlauf', 11, 'Anton-Fahrner-Gasse', '2', 3250),
            (4, 'Gmünd', 21, 'Bahnhofstraße', '21', 3950),
            (5, 'Horn', 31, 'Am Kuhberg', '5', 3580),
            (6, 'Retz', 31, 'Höfleinerstraße', '13', 2070),
            (7, 'Waidhofen an der Thaya', 41, 'Unterer Stadtplatz', '38', 3340),
            (8, 'Zwettl', 51, 'Kremserstraße', '52', 3910),
            (9, 'St. Pölten', 61, 'Hofstatt', '5', 3100)
        ");

        $db->exec("
            INSERT INTO standort_vertretung (id, standort_id, vertreter_id, prioritaet) VALUES
            (1,1,2,1),(2,1,3,1),(3,2,1,1),(4,2,3,1),(5,3,1,1),(6,3,2,1),
            (7,1,9,5),(8,2,9,5),(9,3,9,5),(10,4,8,1),(11,8,4,1),(12,5,6,1),
            (13,6,5,1),(14,7,4,5),(15,1,1,1),(16,2,2,1),(17,3,3,1),(18,4,4,1),
            (19,5,5,1),(20,6,6,1),(21,7,7,1),(22,8,8,1),(23,9,9,1),(24,8,7,1),
            (25,7,8,1),(26,4,7,1)
        ");

        $db->exec("
            INSERT INTO taetigkeitsart (id, bezeichnung) VALUES
            (1, 'lektion'), (3, 'regie'), (4, 'pruefung'),
            (5, 'krank'),   (6, 'feiertag'), (7, 'urlaub')
        ");

        $db->exec("
            INSERT INTO eintritt (id, mitarbeiter_id, eintrittsdatum, std_woche, berufsjahr, einstufung, offener_urlaub)
            VALUES
            (1, 1, '2020-01-01', 40, 6, 'KV Admin', 0),
            (2, 2, '2022-03-01', 38, 4, 'KV Büro', 10),
            (3, 3, '2021-06-15', 40, 5, 'KV Büro', 8)
        ");

        $db->exec("
            INSERT INTO event (id, standort_id, start, ende, titel, bemerkung, klassen, urlaub_akzeptabel, in_urlaub, eventtyp, status)
            VALUES (1, 1, date('now','+12 day'), date('now','+12 day'), 'Team Event', 'Interne Abstimmung', 'B - Personenkraftwagen', 1, 0, 'Theorie', 0)
        ");

        $db->exec("
            INSERT INTO urlaub (id, mitarbeiter_id, beginn, ende, tage_im_urlaub, beginn_in_worten, ende_in_worten, vertretung_id, buero, buero_vertretung_id, genehmigt)
            VALUES
            (1, 2, date('now','+10 day'), date('now','+14 day'), 5, 'in 10 Tagen', 'in 14 Tagen', NULL, 1, NULL, 0),
            (2, 3, date('now','+20 day'), date('now','+22 day'), 3, 'in 20 Tagen', 'in 22 Tagen', 1,    1, 1,    1)
        ");

        $db->exec("
            INSERT INTO urlaubssperre (id, von, bis, ganzjaehrig) VALUES (1, '2016-12-27', '2017-01-05', 0)
        ");

        $db->exec("
            INSERT INTO urlaub_event (id, event_id, urlaub_id) VALUES (1, 1, 2)
        ");

        $db->exec("
            INSERT INTO taetigkeit (id, datum, mitarbeiter_id, taetigkeitsart_id, stunden)
            VALUES (1, date('now','-1 day'), 2, 1, 7.5), (2, date('now','-1 day'), 3, 3, 8.0)
        ");

        $db->exec("
            INSERT INTO uebertrag (mitarbeiter_id, datum, uebertrag_urlaub, uebertrag_ueberstunden, ang_wochen_std, monats_soll)
            VALUES (2, date('now','start of year'), 2.5, 4.0, 38.0, 152.0)
        ");

        $db->exec("
            INSERT INTO zuschlag (mitarbeiter_id, datum, gr10_pro_tag, wochenende, nacht, A, C, E, F, D, theorie)
            VALUES (2, date('now','-2 day'), 0.5, 0, 0, 0, 0, 0, 0, 0, 0.25)
        ");
    }
}
