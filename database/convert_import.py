#!/usr/bin/env python3
"""
Konvertiert den MySQL-Dump (ahaappup) in SQLite mit den neuen Spaltennamen.
Führt das Skript einmal aus – bestehende DB wird überschrieben.
"""

import re
import sqlite3
import os

SRC = os.path.join(os.path.dirname(__file__), "..", "ahaappup (1).sql")
DB  = os.path.join(os.path.dirname(__file__), "database.sqlite")

# ── Tabellennamen-Mapping ────────────────────────────────────────────────────
TABLE_RENAME = {
    "mitarbeiter_has_dokumente":  "mitarbeiter_dokumente",
    "mitarbeiter_has_standorte":  "mitarbeiter_standorte",
    "standort_vertritt_standort": "standort_vertretung",
    "viewformonthlyreport":       "monatsbericht_view",
}

# ── Spaltenname-Mapping pro Tabelle (alter Name → neuer Name) ────────────────
COL = {
    "abmeldung": {
        "idAbmeldung":             "id",
        "datumAbmeldung":          "datum_abmeldung",
        "Mitarbeiter_idMitarbeiter": "mitarbeiter_id",
        "datumAnmeldung":          "datum_anmeldung",
    },
    "aenderungsmeldung": {
        "idAenderungsmeldung":       "id",
        "stdWoche":                  "std_woche",
        "Mitarbeiter_idMitarbeiter": "mitarbeiter_id",
        "bearbeitetVon":             "bearbeitet_von",
    },
    "dokumente": {
        "idDokumente":  "id",
        "uploadDatum":  "upload_datum",
    },
    "eintritt": {
        "idEintritt":                "id",
        "stdWoche":                  "std_woche",
        "offenerUrlaub":             "offener_urlaub",
        "Mitarbeiter_idMitarbeiter": "mitarbeiter_id",
    },
    "event": {
        "idEvent":               "id",
        "urlaubAkzeptabel":      "urlaub_akzeptabel",
        "inUrlaub":              "in_urlaub",
        "Standorte_idStandorte": "standort_id",
    },
    "klassen": {
        "idKlassen":                 "id",
        "Mitarbeiter_idMitarbeiter": "mitarbeiter_id",
    },
    "mitarbeiter": {
        "idMitarbeiter":        "id",
        # "id" (varchar personal-ID) → "personal_id" — handled specially below
        "gebDatum":             "geb_datum",
        "svNummer":             "sv_nummer",
        "firmenTelefonnummer":  "firmen_telefon",
        "privateTelefonnummer": "privat_telefon",
        "weiterePosition":      "weitere_position",
        "aktWochenStd":         "akt_wochen_std",
    },
    "mitarbeiter_dokumente": {
        "Mitarbeiter_idMitarbeiter": "mitarbeiter_id",
        "Dokumente_idDokumente":     "dokument_id",
    },
    "mitarbeiter_standorte": {
        "Mitarbeiter_idMitarbeiter": "mitarbeiter_id",
        "Standorte_idStandorte":     "standort_id",
    },
    "standorte": {
        "idStandorte": "id",
    },
    "standort_vertretung": {
        "idStandort":          "standort_id",
        "idStandortVertreter": "vertreter_id",
    },
    "taetigkeit": {
        "idTaetigkeit":    "id",
        "idMitarbeiter":   "mitarbeiter_id",
        "idTaetigkeitsart": "taetigkeitsart_id",
    },
    "taetigkeitsart": {
        "idTaetigkeitsart": "id",
    },
    "uebertrag": {
        "uebertragUrlaub":       "uebertrag_urlaub",
        "uebertragUeberstunden": "uebertrag_ueberstunden",
        "idMitarbeiter":         "mitarbeiter_id",
        "angWochenStd":          "ang_wochen_std",
        "monatsSoll":            "monats_soll",
    },
    "urlaub": {
        "idUrlaub":                  "id",
        "genemigt":                  "genehmigt",
        "tageImUrlaub":              "tage_im_urlaub",
        "beginnsdatumInWorten":      "beginn_in_worten",
        "endedatumInWorten":         "ende_in_worten",
        "idVertretung":              "vertretung_id",
        "idBueroVertretung":         "buero_vertretung_id",
        "Mitarbeiter_idMitarbeiter": "mitarbeiter_id",
    },
    "urlaub_kommentar": {
        "request_id": "urlaub_id",
        "user_id":    "mitarbeiter_id",
        "comment":    "kommentar",
        "created_at": "erstellt_am",
    },
    "urlaubssperre": {
        "idUrlaubssperre": "id",
    },
    "urlaub_event": {
        "idUrlaub_idEvent": "id",
        "idEvent":          "event_id",
        "idUrlaub":         "urlaub_id",
    },
    "monatsbericht_view": {
        "idMitarbeiter": "mitarbeiter_id",
        "gr10proTag":    "gr10_pro_tag",
        "THEORIE":       "theorie",
        "Lektion":       "lektion",
        "Regie":         "regie",
        "Pruefung":      "pruefung",
        "Feiertag":      "feiertag",
        "Urlaub":        "urlaub_col",  # avoid conflict with table name "urlaub"
    },
    "vorlagen": {
        "idVorlagen": "id",
    },
    "zuschlag": {
        "idMitarbeiter": "mitarbeiter_id",
        "gr10proTag":    "gr10_pro_tag",
        "THEORIE":       "theorie",
    },
}

# ── SQLite Schema ────────────────────────────────────────────────────────────
SCHEMA = """
PRAGMA foreign_keys = OFF;

CREATE TABLE mitarbeiter (
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

CREATE TABLE standorte (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  ort          TEXT,
  kostenstelle INTEGER,
  strasse      TEXT,
  hausnummer   TEXT,
  plz          INTEGER
);

CREATE TABLE mitarbeiter_standorte (
  mitarbeiter_id INTEGER NOT NULL,
  standort_id    INTEGER NOT NULL,
  basis          INTEGER NOT NULL,
  PRIMARY KEY (mitarbeiter_id, standort_id)
);

CREATE TABLE standort_vertretung (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  standort_id  INTEGER NOT NULL,
  vertreter_id INTEGER NOT NULL,
  prioritaet   INTEGER NOT NULL
);

CREATE TABLE klassen (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  klasse         TEXT,
  mitarbeiter_id INTEGER NOT NULL
);

CREATE TABLE taetigkeitsart (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  bezeichnung TEXT
);

CREATE TABLE taetigkeit (
  id                INTEGER PRIMARY KEY AUTOINCREMENT,
  datum             TEXT,
  mitarbeiter_id    INTEGER NOT NULL,
  taetigkeitsart_id INTEGER NOT NULL,
  stunden           REAL,
  UNIQUE (datum, mitarbeiter_id, taetigkeitsart_id)
);

CREATE TABLE zuschlag (
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
  PRIMARY KEY (mitarbeiter_id, datum)
);

CREATE TABLE uebertrag (
  mitarbeiter_id         INTEGER NOT NULL,
  datum                  TEXT    NOT NULL,
  uebertrag_urlaub       REAL,
  uebertrag_ueberstunden REAL,
  ang_wochen_std         REAL,
  monats_soll            REAL,
  PRIMARY KEY (mitarbeiter_id, datum)
);

CREATE TABLE urlaub (
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
  buero_vertretung_id INTEGER
);

CREATE TABLE urlaub_kommentar (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  urlaub_id      INTEGER NOT NULL,
  mitarbeiter_id INTEGER NOT NULL,
  kommentar      TEXT    NOT NULL,
  erstellt_am    TEXT
);

CREATE TABLE urlaubssperre (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  von         TEXT,
  bis         TEXT,
  ganzjaehrig INTEGER
);

CREATE TABLE event (
  id                INTEGER PRIMARY KEY AUTOINCREMENT,
  standort_id       INTEGER NOT NULL,
  start             TEXT,
  ende              TEXT,
  titel             TEXT,
  eventtyp          TEXT,
  klassen           TEXT,
  urlaub_akzeptabel INTEGER,
  in_urlaub         INTEGER,
  status            INTEGER,
  bemerkung         TEXT
);

CREATE TABLE urlaub_event (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  urlaub_id INTEGER NOT NULL,
  event_id  INTEGER NOT NULL
);

CREATE TABLE eintritt (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  mitarbeiter_id INTEGER NOT NULL,
  eintrittsdatum TEXT,
  std_woche      INTEGER,
  berufsjahr     INTEGER,
  einstufung     TEXT,
  offener_urlaub INTEGER
);

CREATE TABLE abmeldung (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  mitarbeiter_id  INTEGER NOT NULL,
  datum_abmeldung TEXT,
  datum_anmeldung TEXT,
  resturlaub      INTEGER
);

CREATE TABLE aenderungsmeldung (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  mitarbeiter_id  INTEGER NOT NULL,
  bearbeitet_von  INTEGER NOT NULL,
  datum           TEXT,
  std_woche       INTEGER,
  urlaubsanspruch INTEGER
);

CREATE TABLE dokumente (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  url          TEXT,
  upload_datum TEXT
);

CREATE TABLE mitarbeiter_dokumente (
  mitarbeiter_id INTEGER NOT NULL,
  dokument_id    INTEGER NOT NULL,
  PRIMARY KEY (mitarbeiter_id, dokument_id)
);

CREATE TABLE vorlagen (
  id  INTEGER PRIMARY KEY AUTOINCREMENT,
  typ TEXT,
  url TEXT
);

CREATE TABLE monatsbericht_view (
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
"""

# ── Hilfsfunktionen ──────────────────────────────────────────────────────────

def rename_table(name):
    return TABLE_RENAME.get(name, name)

def rename_col(table, col):
    """Gibt den neuen Spaltennamen zurück."""
    mapping = COL.get(table, {})
    # Sonderfall mitarbeiter: 'id' (varchar) → 'personal_id'
    if table == "mitarbeiter" and col == "id":
        return "personal_id"
    return mapping.get(col, col)

def map_columns(table, cols):
    return [rename_col(table, c) for c in cols]

# ── Parser ───────────────────────────────────────────────────────────────────

INSERT_RE = re.compile(
    r"^INSERT INTO `([^`]+)`\s+\(([^)]+)\)\s+VALUES\s*$|"
    r"^INSERT INTO `([^`]+)`\s+\(([^)]+)\)\s+VALUES\s+(.+);$",
    re.IGNORECASE
)

def parse_col_list(raw):
    """Parsed backtick-quoted Spaltenliste → Liste von Strings."""
    return [c.strip().strip("`") for c in raw.split(",")]

def process_dump(src_path):
    """
    Liest den MySQL-Dump und gibt (table_name, col_list, values_sql_fragment)
    als Generator zurück.
    Gibt auch CREATE TABLE Blöcke zurück (als None, None, raw_sql Tuple).
    """
    inserts = []  # list of (new_table, new_cols, values_fragment)

    with open(src_path, encoding="utf-8", errors="replace") as f:
        lines = f.readlines()

    i = 0
    current_insert_table = None
    current_insert_cols  = None

    while i < len(lines):
        line = lines[i].rstrip("\n")

        # Multi-line INSERT: "INSERT INTO `t` (cols) VALUES"  dann nächste Zeile Werte
        m = re.match(r"^INSERT INTO `([^`]+)`\s+\(([^)]+)\)\s+VALUES$", line, re.IGNORECASE)
        if m:
            old_table = m.group(1)
            new_table = rename_table(old_table)
            cols = parse_col_list(m.group(2))
            new_cols = map_columns(new_table, cols)
            # Nächste Zeilen bis Semikolon sammeln
            values_lines = []
            i += 1
            while i < len(lines):
                vline = lines[i].rstrip("\n")
                values_lines.append(vline)
                if vline.rstrip().endswith(";"):
                    break
                i += 1
            values_sql = " ".join(values_lines)
            inserts.append((new_table, new_cols, values_sql))
            i += 1
            continue

        # Single-line INSERT
        m2 = re.match(r"^INSERT INTO `([^`]+)`\s+\(([^)]+)\)\s+VALUES\s+(.+);$", line, re.IGNORECASE)
        if m2:
            old_table = m2.group(1)
            new_table = rename_table(old_table)
            cols = parse_col_list(m2.group(2))
            new_cols = map_columns(new_table, cols)
            values_sql = m2.group(3) + ";"
            inserts.append((new_table, new_cols, values_sql))
            i += 1
            continue

        i += 1

    return inserts

# ── Hauptprogramm ────────────────────────────────────────────────────────────

def main():
    print("Lese MySQL-Dump …")
    inserts = process_dump(SRC)
    print(f"  {len(inserts)} INSERT-Blöcke gefunden.")

    print("Erstelle neue SQLite-Datenbank …")
    if os.path.exists(DB):
        os.remove(DB)

    con = sqlite3.connect(DB)
    cur = con.cursor()
    cur.executescript(SCHEMA)
    con.commit()

    print("Importiere Daten …")
    errors = 0
    for new_table, new_cols, values_sql in inserts:
        col_str = ", ".join(new_cols)
        # Werte-Teil: alles bis auf abschließendes Semikolon
        vals = values_sql.rstrip(";").strip()
        sql = f"INSERT OR IGNORE INTO {new_table} ({col_str}) VALUES {vals};"
        try:
            cur.executescript(sql)
        except Exception as e:
            print(f"  FEHLER in {new_table}: {e}")
            print(f"    SQL (Anfang): {sql[:200]}")
            errors += 1

    con.commit()
    con.close()

    print(f"Fertig. Fehler: {errors}")
    print(f"Datenbank: {DB}")

    # Kurze Statistik
    con2 = sqlite3.connect(DB)
    cur2 = con2.cursor()
    tables = [r[0] for r in cur2.execute("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")]
    print("\nZeilenzahlen:")
    for t in tables:
        n = cur2.execute(f"SELECT COUNT(*) FROM {t}").fetchone()[0]
        print(f"  {t:<30} {n:>8}")
    con2.close()

if __name__ == "__main__":
    main()
