#!/bin/sh
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DRIVER="${DB_DRIVER:-mysql}"

if [ "$DB_DRIVER" = "mysql" ]; then
  echo "Waiting for MariaDB at ${DB_HOST}:${DB_PORT}..."
  for i in $(seq 1 60); do
    if php -r "
      try {
        new PDO(
          'mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}',
          '${DB_USERNAME}',
          '${DB_PASSWORD}'
        );
        exit(0);
      } catch (Throwable \$e) {
        exit(1);
      }
    " 2>/dev/null; then
      echo "Database is ready."
      break
    fi
    if [ "$i" -eq 60 ]; then
      echo "Database not reachable after 60 attempts." >&2
      exit 1
    fi
    sleep 2
  done
fi

exec "$@"
