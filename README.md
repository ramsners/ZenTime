# EasyTime

## Lokale Entwicklung (SQLite)

```bash
pnpm install   # optional
pnpm dev       # http://localhost:8000
```

Datenbank: `database/database.sqlite` (wird beim ersten Start automatisch angelegt).

### Demo-Daten neu laden

Alle **Demo-Inhalte** (Standorte, Urlaube, Events, Tätigkeiten, …) werden zurückgesetzt und neu erzeugt.  
**Nicht** betroffen sind nur die drei festen Test-Accounts (Admin, Lisa, Tom) mit den Logins unten.

**Wichtig:** Eigene Änderungen in der App (neue Mitarbeiter, geänderte Urlaubsanträge, App-Einstellungen, Kommentare, …) gehen beim Reseed **verloren** und werden nicht gespeichert.

```bash
pnpm db:seed
# oder: php scripts/seed-database.php
```

### Test-Zugänge

| Rolle | Login (E-Mail oder Personal-ID) | Passwort |
|--------|----------------------------------|----------|
| **Administrator** | `admin@firma.at` oder `A001` | `admin` |
| **Mitarbeiter** | `lisa@firma.at` oder `M002` | `password` |
| **Mitarbeiter** | `tom@firma.at` oder `M003` | `password` |

Für Admin-Funktionen: **Administrator**-Account. Für die normale Mitarbeiter-Ansicht: **Lisa** oder **Tom**.

---

## Docker (empfohlen)

Siehe **[README-DOCKER.md](README-DOCKER.md)** für lokales Starten und Server-Deploy mit MariaDB.

```bash
cp .env.example .env
docker compose up -d --build
# http://localhost:8080
```

Legacy-Daten: `python3 database/convert_import.py` → `docker compose --profile migrate run --rm migrate`

---

## HTL-Projekt-Vorlage (ApexTime)

Alles klar, ich packe euch jetzt das **Komplettpaket** für euer HTL-Projekt zusammen. Wenn ihr das so umsetzt, habt ihr die Architektur (MVC), die Datenbank (Relational), die Logik (Login/Check) und die Anforderungen (Austauschbarkeit) komplett abgedeckt.

### 🚀 Projekt-Name: **ApexTime**
(Professionell, modern, kein Schulbezug.)

---

## 1. Das Datenbank-Modell (MariaDB)
Erstellt eine Datenbank (z. B. `apextime_db`) und führt dieses SQL-Script aus. Es enthält die Tabellen und einen Test-CEO sowie einen Mitarbeiter.

```sql
CREATE TABLE mitarbeiter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mnr VARCHAR(10) UNIQUE NOT NULL,
    vorname VARCHAR(50),
    nachname VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    passwort VARCHAR(255) NOT NULL, -- Gehashte Passwörter!
    urlaubsanspruch INT DEFAULT 25,
    ist_ceo BOOLEAN DEFAULT FALSE
);

CREATE TABLE urlaubsantraege (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mitarbeiter_id INT,
    start_datum DATE NOT NULL,
    end_datum DATE NOT NULL,
    status ENUM('offen', 'genehmigt', 'abgelehnt') DEFAULT 'offen',
    erstellt_am TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mitarbeiter_id) REFERENCES mitarbeiter(id) ON DELETE CASCADE
);

-- Test-User (Passwort ist jeweils '12345')
INSERT INTO mitarbeiter (mnr, vorname, nachname, email, passwort, ist_ceo) 
VALUES ('CEO01', 'Chef', 'Oberhaupt', 'ceo@company.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeS2W5K2.mQ.rS8D.mO', TRUE);

INSERT INTO mitarbeiter (mnr, vorname, nachname, email, passwort, ist_ceo) 
VALUES ('M001', 'Max', 'Mustermann', 'max@company.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeS2W5K2.mQ.rS8D.mO', FALSE);
```

---

## 2. Die Git-Struktur (Dateisystem)
Legt diese Ordner und Dateien genau so an:

* **/app**
    * `/Core/DatabaseInterface.php` (Das Interface für die Austauschbarkeit)
    * `/Core/MariaDBStorage.php` (Die echte Datenbank-Logik)
    * `/Models/User.php` & `Vacation.php`
    * `/Controllers/AuthController.php` & `VacationController.php`
* **/config**
    * `db.php` (Host, User, Pass)
* **/public**
    * `index.php` (Einstiegspunkt)
    * `/css/style.css`
* **/views**
    * `login.php`, `dashboard_employee.php`, `dashboard_ceo.php`

---

## 3. Kern-Logik: Der Login-Check (Pseudocode / Logik)
Dieser Ablauf muss in eurem `AuthController.php` programmiert werden:

1.  **Formular-Submit:** User sendet `mnr` und `passwort`.
2.  **DB-Abfrage:** `SELECT * FROM mitarbeiter WHERE mnr = :mnr`
3.  **Verify:** `if (password_verify($eingabe, $db_hash))`
4.  **Session:** * `$_SESSION['user_id'] = $db_id;`
    * `$_SESSION['role'] = $db_ist_ceo;`
5.  **Redirect:** * `if ($_SESSION['role'] == true) header("Location: /ceo_dashboard");`
    * `else header("Location: /mitarbeiter_dashboard");`

---

## 4. Die "Austauschbarkeit" (OOP Anforderung)
Damit ihr die volle Punktzahl bekommt, muss der Controller das **Interface** benutzen, nicht direkt die MariaDB-Klasse.

**Interface (`DatabaseInterface.php`):**
```php
interface DatabaseInterface {
    public function findUserByMNr($mnr);
    public function saveRequest($data);
    public function getAllRequests(); // Für den CEO-Kalender
}
```

Wenn ihr später auf JSON umstellt, schreibt ihr einfach eine `JsonStorage.php`, die das gleiche Interface nutzt. Das Programm merkt den Unterschied nicht!

---

## 5. Workflow für den Urlaubsantrag (Optional-Punkte inklusive)
1.  **Mitarbeiter:** Füllt Formular aus (Start/Ende).
2.  **Controller:** Rechnet Tage aus und prüft gegen `urlaubsanspruch` in der DB.
3.  **Speichern:** Status wird auf `offen` gesetzt.
4.  **CEO-Kalender:** Zeigt alle `offen` Anträge farbig an (z. B. Gelb).
5.  **Entscheidung:** CEO klickt "Genehmigen".
6.  **Update & Mail:** * Status in DB -> `genehmigt`.
    * PHP triggert `mail()` an die Mitarbeiter-Email.
    * Urlaubstage werden vom Konto des Mitarbeiters abgezogen.

---

### Zusammenfassung für die Abgabe:
* **Architektur:** MVC (Model-View-Controller).
* **Technik:** PHP (PDO für SQL), Sessions für Login, Mail-Funktion.
* **Flexibilität:** Interface-Lösung für das Backend.
* **Sicherheit:** Passwort-Hashing (`password_hash`) und Session-Checks auf jeder Seite.

Das ist der komplette Bauplan. Ihr müsst jetzt "nur noch" den PHP-Code in die entsprechenden Dateien schreiben. Viel Erfolg bei eurem HTL-Projekt!

Hier ist die detaillierte Aufstellung, was **genau** in jede Datei und jeden Ordner kommt. Das ist dein technisches Drehbuch für die Umsetzung in PHP.

---

### 📂 /app (Das Gehirn)
Hier liegt der PHP-Code, den der User niemals direkt sieht.

* **`/Core/DatabaseInterface.php`**: Hier definierst du nur die Regeln (z. B. `public function checkLogin($mnr, $pw);`). Das ist der Beweis für den Lehrer, dass euer Backend "austauschbar" ist.
* **`/Core/MariaDBStorage.php`**: Hier schreibst du die echten SQL-Befehle (PDO), um Daten aus MariaDB zu holen. Diese Klasse "gehorcht" dem Interface.
* **`/Models/User.php`**: Eine Klasse, die einen Mitarbeiter repräsentiert (Eigenschaften wie Name, Urlaubstage).
* **`/Models/VacationRequest.php`**: Eine Klasse für einen Urlaubsantrag (Start, Ende, Status).
* **`/Controllers/AuthController.php`**: Enthält die Logik für Login und Logout. Er prüft das Passwort und startet die `$_SESSION`.
* **`/Controllers/VacationController.php`**: Berechnet, ob ein Mitarbeiter noch genug Urlaubstage hat, speichert neue Anträge und erlaubt dem CEO das Genehmigen.

---

### 📂 /public (Die Haustür)
Das ist der einzige Ordner, den der Webserver nach außen zeigt.

* **`index.php`**: Der "Front Controller". Jede Anfrage landet hier. Sie lädt die Autoload-Funktionen und entscheidet: "User will Login sehen -> lade AuthController".
* **`/css/style.css`**: Hier kommt euer Design rein (Farben, Layout, Abstände).
* **`/js/main.js`**: (Optional) Falls ihr kleine Effekte wollt oder den Kalender interaktiv macht.

---

### 📂 /views (Das Gesicht)
Hier liegt reines HTML mit ganz wenig PHP (nur für `echo`).

* **`/auth/login.php`**: Das Formular für MNr und Passwort.
* **`/employee/dashboard.php`**: Die Seite, auf der der Mitarbeiter seine eigenen Anträge sieht und das Formular für neue Anträge findet.
* **`/admin/dashboard.php`**: Die CEO-Ansicht mit der Tabelle oder dem Kalender aller Mitarbeiter-Anträge.
* **`/admin/calendar.php`**: Die visuelle Übersicht (eventuell mit einer Tabelle gelöst).

---

### 📂 /config (Die Geheimnisse)
* **`db.php`**: Hier stehen `$db_host`, `$db_name`, `$db_user` und `$db_pass`. 
    * **WICHTIG:** Diese Datei darf **nicht** in Git hochgeladen werden (Sicherheitsrisiko!).

---

### 📂 /database (Das Fundament)
* **`schema.sql`**: Hier kopierst du alle `CREATE TABLE` Befehle hinein. So kann jeder aus deinem Team die Datenbank mit einem Klick bei sich lokal erstellen.

---

### 📂 /vendor (Die Werkzeuge)
* Dieser Ordner wird von **Composer** automatisch erstellt. Hier landet zum Beispiel der `PHPMailer` für die E-Mail-Benachrichtigungen. Ihr rührt diesen Ordner händisch nicht an.

---

### 📄 Dateien im Hauptverzeichnis (Root)
* **`.gitignore`**: Hier schreibst du `/vendor/` und `config/db.php` rein. Git ignoriert diese dann beim Hochladen.
* **`README.md`**: Eure Projektdokumentation (Name: **ApexTime**, Anleitung zur Installation, Teammitglieder).
* **`composer.json`**: Eine kleine Datei, die sagt: "Ich brauche PHPMailer".

---

### Zusammenfassung der Logik-Kette:
1.  User gibt Daten in **`/views/auth/login.php`** ein.
2.  Daten gehen an den **`AuthController.php`**.
3.  Controller nutzt **`MariaDBStorage.php`**, um User zu finden.
4.  Wenn okay, schreibt der Controller die Rolle in die **Session** und schickt den User zum **Dashboard**.

Mit dieser Struktur erfüllt ihr alle Anforderungen: **MVC**, **OOP**, **Austauschbarkeit** und **Sauberkeit**. Viel Erfolg beim Coden!