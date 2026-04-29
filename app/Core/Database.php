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

        $db->exec("INSERT INTO klassen (idKlassen, klasse, Mitarbeiter_idMitarbeiter) VALUES (1, 'Verwaltung', 1)");
        $db->exec("INSERT INTO standorte (idStandorte, ort, kostenstelle, strasse, hausnummer, plz) VALUES (1, 'Wien', 1001, 'Hauptstrasse', '1', 1010)");
        $db->exec("INSERT INTO taetigkeitsart (idTaetigkeitsart, bezeichnung) VALUES (1, 'Büro'), (2, 'Homeoffice')");

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
            (1, date('now','+12 day'), date('now','+12 day'), 'Team Event', 'Interne Abstimmung', 'Verwaltung', 1, 0, 'Theorie', 0, 1)
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
            (1, date('now','+30 day'), date('now','+35 day'), 0)
        ");

        $db->exec("
            INSERT INTO urlaub_event (idUrlaub_idEvent, idEvent, idUrlaub)
            VALUES (1, 1, 2)
        ");

        $db->exec("
            INSERT INTO taetigkeit (idTaetigkeit, datum, idMitarbeiter, idTaetigkeitsart, stunden)
            VALUES
            (1, date('now','-1 day'), 2, 1, 7.5),
            (2, date('now','-1 day'), 3, 2, 8.0)
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
