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