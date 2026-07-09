#!/usr/bin/env bash
# Backup harian REKA — §11.5. Simpan 14 hari. Uji PULIH secara berkala (restore test).
set -euo pipefail

APP_DIR="/var/www/reka"
BACKUP_DIR="/var/backups/reka"
KEEP_DAYS=14
STAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"

# 1. Dump pangkalan data (baca kredensial dari .env).
source <(grep -E '^DB_' "$APP_DIR/.env" | sed 's/^/export /')
mysqldump --single-transaction --quick \
  -h "${DB_HOST:-127.0.0.1}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  | gzip > "$BACKUP_DIR/db-$STAMP.sql.gz"

# 2. Rsync storage/app (aset, draf, handover).
rsync -a --delete "$APP_DIR/storage/app/" "$BACKUP_DIR/storage-latest/"
tar -czf "$BACKUP_DIR/storage-$STAMP.tar.gz" -C "$BACKUP_DIR" storage-latest

# 3. Buang backup lebih lama dari KEEP_DAYS.
find "$BACKUP_DIR" -name 'db-*.sql.gz' -mtime +$KEEP_DAYS -delete
find "$BACKUP_DIR" -name 'storage-*.tar.gz' -mtime +$KEEP_DAYS -delete

echo "Backup selesai: $STAMP"
# Cron: 0 2 * * *  /var/www/reka/deploy/backup.sh >> /var/log/reka-backup.log 2>&1
# schedule:run cron: * * * * *  php /var/www/reka/artisan schedule:run >> /dev/null 2>&1
