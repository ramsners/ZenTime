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
        self::$instance->exec("PRAGMA foreign_keys = ON;");
    }

    private static function initializeSchema() {
        $db = self::$instance;
        $schema = "
        CREATE TABLE IF NOT EXISTS klassen (
            idKlassen INTEGER PRIMARY KEY AUTOINCREMENT,
            klasse TEXT,
            Mitarbeiter_idMitarbeiter INTEGER NOT NULL,
            FOREIGN KEY(Mitarbeiter_idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter)
        );

        CREATE TABLE IF NOT EXISTS mitarbeiter (
            idMitarbeiter INTEGER PRIMARY KEY AUTOINCREMENT,
            id TEXT,
            vorname TEXT,
            nachname TEXT,
            strasse TEXT,
            hausnummer TEXT,
            plz INTEGER,
            ort TEXT,
            gebDatum DATE,
            svNummer TEXT,
            geschlecht TEXT,
            email TEXT,
            firmenTelefonnummer TEXT,
            privateTelefonnummer TEXT,
            iban TEXT,
            bic TEXT,
            position TEXT,
            weiterePosition TEXT,
            bemerkung TEXT,
            status INTEGER,
            password TEXT,
            berechtigung TEXT,
            urlaubsanspruch INTEGER,
            aktWochenStd INTEGER
        );

        CREATE TABLE IF NOT EXISTS dokumente (
            idDokumente INTEGER PRIMARY KEY AUTOINCREMENT,
            url TEXT,
            uploadDatum DATE
        );

        CREATE TABLE IF NOT EXISTS standorte (
            idStandorte INTEGER PRIMARY KEY AUTOINCREMENT,
            bezeichnung TEXT,
            ort TEXT,
            kostenstelle INTEGER,
            strasse TEXT,
            hausnummer TEXT,
            plz INTEGER
        );

        CREATE TABLE IF NOT EXISTS mitarbeiter_has_dokumente (
            Mitarbeiter_idMitarbeiter INTEGER NOT NULL,
            Dokumente_idDokumente INTEGER NOT NULL,
            PRIMARY KEY (Mitarbeiter_idMitarbeiter, Dokumente_idDokumente),
            FOREIGN KEY(Mitarbeiter_idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter),
            FOREIGN KEY(Dokumente_idDokumente) REFERENCES dokumente(idDokumente)
        );

        CREATE TABLE IF NOT EXISTS mitarbeiter_has_standorte (
            Mitarbeiter_idMitarbeiter INTEGER NOT NULL,
            Standorte_idStandorte INTEGER NOT NULL,
            PRIMARY KEY (Mitarbeiter_idMitarbeiter, Standorte_idStandorte),
            FOREIGN KEY(Mitarbeiter_idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter),
            FOREIGN KEY(Standorte_idStandorte) REFERENCES standorte(idStandorte)
        );

        CREATE TABLE IF NOT EXISTS standort_vertritt_standort (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            idStandort INTEGER NOT NULL,
            idStandortVertreter INTEGER NOT NULL,
            prioritaet INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS eintritt (
            idEintritt INTEGER PRIMARY KEY AUTOINCREMENT,
            eintrittsdatum DATE,
            stdWoche INTEGER,
            berufsjahr INTEGER,
            einstufung TEXT,
            offenerUrlaub INTEGER,
            Mitarbeiter_idMitarbeiter INTEGER NOT NULL,
            FOREIGN KEY(Mitarbeiter_idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter)
        );

        CREATE TABLE IF NOT EXISTS abmeldung (
            idAbmeldung INTEGER PRIMARY KEY AUTOINCREMENT,
            datumAbmeldung DATE,
            Mitarbeiter_idMitarbeiter INTEGER NOT NULL,
            datumAnmeldung DATE,
            resturlaub INTEGER,
            FOREIGN KEY(Mitarbeiter_idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter)
        );

        CREATE TABLE IF NOT EXISTS aenderungsmeldung (
            idAenderungsmeldung INTEGER PRIMARY KEY AUTOINCREMENT,
            datum DATE,
            stdWoche INTEGER,
            Mitarbeiter_idMitarbeiter INTEGER NOT NULL,
            urlaubsanspruch INTEGER,
            bearbeitetVon INTEGER NOT NULL,
            FOREIGN KEY(Mitarbeiter_idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter),
            FOREIGN KEY(bearbeitetVon) REFERENCES mitarbeiter(idMitarbeiter)
        );

        CREATE TABLE IF NOT EXISTS taetigkeitsart (
            idTaetigkeitsart INTEGER PRIMARY KEY AUTOINCREMENT,
            bezeichnung TEXT
        );

        CREATE TABLE IF NOT EXISTS taetigkeit (
            idTaetigkeit INTEGER PRIMARY KEY AUTOINCREMENT,
            datum DATE,
            idMitarbeiter INTEGER NOT NULL,
            idTaetigkeitsart INTEGER NOT NULL,
            stunden NUMERIC,
            FOREIGN KEY(idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(idTaetigkeitsart) REFERENCES taetigkeitsart(idTaetigkeitsart) ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE (datum, idMitarbeiter, idTaetigkeitsart)
        );

        CREATE TABLE IF NOT EXISTS event (
            idEvent INTEGER PRIMARY KEY AUTOINCREMENT,
            start DATE,
            ende DATE,
            titel TEXT,
            bemerkung TEXT,
            klassen TEXT,
            urlaubAkzeptabel INTEGER,
            inUrlaub INTEGER NOT NULL,
            eventtyp TEXT NOT NULL,
            status INTEGER NOT NULL,
            Standorte_idStandorte INTEGER NOT NULL,
            FOREIGN KEY(Standorte_idStandorte) REFERENCES standorte(idStandorte)
        );

        CREATE TABLE IF NOT EXISTS urlaub (
            idUrlaub INTEGER PRIMARY KEY AUTOINCREMENT,
            genemigt INTEGER,
            beginn DATE,
            ende DATE,
            tageImUrlaub INTEGER,
            beginnsdatumInWorten TEXT,
            endedatumInWorten TEXT,
            idVertretung INTEGER,
            buero INTEGER,
            idBueroVertretung INTEGER,
            Mitarbeiter_idMitarbeiter INTEGER NOT NULL,
            FOREIGN KEY(Mitarbeiter_idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter)
        );

        CREATE TABLE IF NOT EXISTS urlaubssperre (
            idUrlaubssperre INTEGER PRIMARY KEY AUTOINCREMENT,
            von DATE,
            bis DATE,
            ganzjaehrig INTEGER
        );

        CREATE TABLE IF NOT EXISTS urlaub_event (
            idUrlaub_idEvent INTEGER PRIMARY KEY AUTOINCREMENT,
            idEvent INTEGER NOT NULL,
            idUrlaub INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS uebertrag (
            uebertragUrlaub NUMERIC,
            uebertragUeberstunden NUMERIC,
            idMitarbeiter INTEGER NOT NULL,
            datum DATE NOT NULL,
            angWochenStd NUMERIC,
            monatsSoll NUMERIC,
            PRIMARY KEY (idMitarbeiter, datum),
            FOREIGN KEY(idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter) ON DELETE CASCADE ON UPDATE CASCADE
        );

        CREATE TABLE IF NOT EXISTS vorlagen (
            idVorlagen INTEGER PRIMARY KEY AUTOINCREMENT,
            typ TEXT,
            url TEXT
        );

        CREATE TABLE IF NOT EXISTS zuschlag (
            idMitarbeiter INTEGER NOT NULL,
            datum DATE NOT NULL,
            gr10proTag NUMERIC,
            wochenende NUMERIC,
            nacht NUMERIC,
            A NUMERIC,
            C NUMERIC,
            E NUMERIC,
            F NUMERIC,
            D NUMERIC,
            THEORIE NUMERIC,
            PRIMARY KEY (idMitarbeiter, datum),
            FOREIGN KEY(idMitarbeiter) REFERENCES mitarbeiter(idMitarbeiter) ON DELETE CASCADE ON UPDATE CASCADE
        );

        CREATE TABLE IF NOT EXISTS viewformonthlyreport (
            idMitarbeiter INTEGER,
            datum DATE,
            A NUMERIC,
            C NUMERIC,
            E NUMERIC,
            F NUMERIC,
            D NUMERIC,
            THEORIE NUMERIC,
            nacht NUMERIC,
            wochenende NUMERIC,
            gr10proTag NUMERIC,
            Lektion NUMERIC,
            Regie NUMERIC,
            Pruefung NUMERIC,
            krank NUMERIC,
            Feiertag NUMERIC,
            Urlaub NUMERIC
        );
        ";
        $db->exec($schema);
    }

    private static function seedData() {
        $db = self::$instance;
        
        // Minimal test data (few rows only)
        $db->exec("
            INSERT INTO mitarbeiter (idMitarbeiter, id, vorname, nachname, email, position, status, password, berechtigung, urlaubsanspruch, aktWochenStd)
            VALUES
            (1, 'A001', 'Admin', 'User', 'admin@firma.at', 'Leitung', 0, 'admin', 'Admin', 240, 40),
            (2, 'M002', 'Lisa', 'Muster', 'lisa@firma.at', 'Mitarbeiter', 0, 'password', 'Mitarbeiter', 200, 38),
            (3, 'M003', 'Tom', 'Beispiel', 'tom@firma.at', 'Mitarbeiter', 0, 'password', 'Mitarbeiter', 200, 40)
        ");

        $db->exec("
            INSERT INTO klassen (idKlassen, klasse, Mitarbeiter_idMitarbeiter) VALUES
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
            INSERT INTO standorte (idStandorte, ort, kostenstelle, strasse, hausnummer, plz) VALUES
            (1, 'Ybbs an der Donau', 11, 'Gewerbestraße', '14', 3370),
            (2, 'Pöchlarn', 11, 'Regensburgerstraße', '14', 3380),
            (3, 'Wieselburg an der Erlauf', 11, 'Anton-Fahrner-Gasse ', '2', 3250),
            (4, 'Gmünd', 21, 'Bahnhofstraße', '21', 3950),
            (5, 'Horn', 31, 'Am Kuhberg ', '5', 3580),
            (6, 'Retz', 31, 'Höfleinerstraße ', '13', 2070),
            (7, 'Waidhofen an der Thaya', 41, 'Unterer Stadtplatz', '38', 3340),
            (8, 'Zwettl', 51, 'Kremserstraße', '52', 3910),
            (9, 'St. Pölten', 61, 'Hofstatt', '5', 3100)
        ");
        $db->exec("
            INSERT INTO standort_vertritt_standort (id, idStandort, idStandortVertreter, prioritaet) VALUES
            (1, 1, 2, 1), (2, 1, 3, 1), (3, 2, 1, 1), (4, 2, 3, 1), (5, 3, 1, 1), (6, 3, 2, 1),
            (7, 1, 9, 5), (8, 2, 9, 5), (9, 3, 9, 5), (10, 4, 8, 1), (11, 8, 4, 1), (12, 5, 6, 1),
            (13, 6, 5, 1), (14, 7, 4, 5), (15, 1, 1, 1), (16, 2, 2, 1), (17, 3, 3, 1), (18, 4, 4, 1),
            (19, 5, 5, 1), (20, 6, 6, 1), (21, 7, 7, 1), (22, 8, 8, 1), (23, 9, 9, 1), (24, 8, 7, 1),
            (25, 7, 8, 1), (26, 4, 7, 1)
        ");
        $db->exec("
            INSERT INTO taetigkeitsart (idTaetigkeitsart, bezeichnung) VALUES
            (1, 'lektion'),
            (3, 'regie'),
            (4, 'pruefung'),
            (5, 'krank'),
            (6, 'feiertag'),
            (7, 'urlaub')
        ");

        $db->exec("
            INSERT INTO eintritt (idEintritt, eintrittsdatum, stdWoche, berufsjahr, einstufung, offenerUrlaub, Mitarbeiter_idMitarbeiter)
            VALUES
            (1, '2020-01-01', 40, 6, 'KV Admin', 0, 1),
            (2, '2022-03-01', 38, 4, 'KV Büro', 10, 2),
            (3, '2021-06-15', 40, 5, 'KV Büro', 8, 3)
        ");

        $db->exec("
            INSERT INTO event (idEvent, start, ende, titel, bemerkung, klassen, urlaubAkzeptabel, inUrlaub, eventtyp, status, Standorte_idStandorte)
            VALUES
            (1, date('now','+12 day'), date('now','+12 day'), 'Team Event', 'Interne Abstimmung', 'B - Personenkraftwagen', 1, 0, 'Theorie', 0, 1)
        ");

        $db->exec("
            INSERT INTO urlaub (idUrlaub, genemigt, beginn, ende, tageImUrlaub, beginnsdatumInWorten, endedatumInWorten, idVertretung, buero, idBueroVertretung, Mitarbeiter_idMitarbeiter)
            VALUES
            (1, 0, date('now','+10 day'), date('now','+14 day'), 5, 'in 10 Tagen', 'in 14 Tagen', NULL, 1, NULL, 2),
            (2, 1, date('now','+20 day'), date('now','+22 day'), 3, 'in 20 Tagen', 'in 22 Tagen', 1, 1, 1, 3)
        ");

        $db->exec("
            INSERT INTO urlaubssperre (idUrlaubssperre, von, bis, ganzjaehrig)
            VALUES
            (1, '2016-12-27', '2017-01-05', 0)
        ");

        $db->exec("
            INSERT INTO urlaub_event (idUrlaub_idEvent, idEvent, idUrlaub)
            VALUES (1, 1, 2)
        ");

        $db->exec("
            INSERT INTO taetigkeit (idTaetigkeit, datum, idMitarbeiter, idTaetigkeitsart, stunden)
            VALUES
            (1, date('now','-1 day'), 2, 1, 7.5),
            (2, date('now','-1 day'), 3, 3, 8.0)
        ");

        $db->exec("
            INSERT INTO uebertrag (uebertragUrlaub, uebertragUeberstunden, idMitarbeiter, datum, angWochenStd, monatsSoll)
            VALUES (2.5, 4.0, 2, date('now','start of year'), 38.0, 152.0)
        ");

        $db->exec("
            INSERT INTO zuschlag (idMitarbeiter, datum, gr10proTag, wochenende, nacht, A, C, E, F, D, THEORIE)
            VALUES (2, date('now','-2 day'), 0.5, 0, 0, 0, 0, 0, 0, 0, 0.25)
        ");
    }
}
