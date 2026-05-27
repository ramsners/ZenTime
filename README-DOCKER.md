# EasyTime – Docker

Projektordner: `EasyTime` · Container: `easytime-web`, `easytime-db` · DB: `easytime`

## Voraussetzungen

- Docker Desktop (lokal installiert)
- Optional: Legacy-Daten aus `ahaappup (1).sql`

## Schnellstart

```bash
cp .env.example .env
docker compose up -d --build
```

App: **http://localhost:8080**

## Daten aus Legacy-SQL importieren

`ahaappup (1).sql` ist **nicht** direkt kompatibel. Zuerst nach SQLite konvertieren:

```bash
python3 database/convert_import.py
```

Dann in MariaDB (Docker) migrieren:

```bash
docker compose up -d --build
docker compose --profile migrate run --rm migrate
```

## Produktion auf Server

```bash
cp .env.example .env
# Passwörter in .env anpassen

docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Datenbank bleibt im Volume `db_data`. Images können in eine Registry gepusht und auf dem Server gezogen werden.

## Services

| Service | Beschreibung |
|---------|--------------|
| `web` | PHP 8.2 + Apache, Port `APP_PORT` (Standard 8080) |
| `db` | MariaDB 11, Schema aus `database/mariadb/` |
| `migrate` | Einmal-Job: `database.sqlite` → MariaDB (Profil `migrate`) |

## Umgebungsvariablen

Siehe [`.env.example`](.env.example). Wichtig:

- `DB_HOST=db` (Service-Name in Docker)
- `DB_DRIVER=mysql`

Ohne Docker (nur lokal): `DB_DRIVER` weglassen → SQLite unter `database/database.sqlite`.
