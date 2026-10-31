#!/usr/bin/env bash
set -euo pipefail
umask 027
SOURCE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ACCOUNT_HOME="$(getent passwd "$(id -un)" | cut -d: -f6)"
APP_DIR="$ACCOUNT_HOME/apps/rangmahal"
PUBLIC_DIR="$ACCOUNT_HOME/public_html"
BACKUP_DIR="$ACCOUNT_HOME/backups/rangmahal"
if [[ -z "$ACCOUNT_HOME" || "$SOURCE_DIR" != "$ACCOUNT_HOME"/repositories/rangmahal ]]; then
  echo 'Run this script from the registered Rang Mahal repository.' >&2; exit 1
fi
if [[ ! -f "$APP_DIR/.env" ]]; then echo 'Configure private production .env before deployment.' >&2; exit 1; fi
if [[ ! -f "$SOURCE_DIR/public/build/manifest.json" ]]; then echo 'Frontend assets are missing. Build locally before pushing.' >&2; exit 1; fi
mkdir -p "$APP_DIR" "$BACKUP_DIR"
STAMP="$(date +%Y%m%d-%H%M%S)"
tar -czf "$BACKUP_DIR/public-$STAMP.tar.gz" -C "$PUBLIC_DIR" .
if [[ -f "$APP_DIR/artisan" ]]; then
  tar -czf "$BACKUP_DIR/app-$STAMP.tar.gz" --exclude=vendor --exclude=storage/logs --exclude=.env -C "$APP_DIR" .
  # Avoid serving new PHP against the old schema during an additive migration.
  (cd "$APP_DIR" && php artisan down --retry=30)
fi
rsync -a --exclude=.git --exclude=.github --exclude=.env --exclude='.env.*' --exclude=.local-tools --exclude=node_modules --exclude=vendor --exclude=storage --exclude=tests --exclude=previews --exclude=laravel-conversion "$SOURCE_DIR/" "$APP_DIR/"
mkdir -p "$APP_DIR/storage/framework/"{cache/data,sessions,views} "$APP_DIR/storage/logs" "$APP_DIR/storage/app/public" "$APP_DIR/bootstrap/cache"
cd "$APP_DIR"
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
php artisan config:clear
php deploy/backup-database.php "$BACKUP_DIR/database-$STAMP.sql"
php artisan migrate --force
rsync -a --exclude=index.php --exclude=hot "$APP_DIR/public/" "$PUBLIC_DIR/"
cp "$APP_DIR/deploy/cpanel-public-index.php" "$PUBLIC_DIR/index.php"
cp "$APP_DIR/deploy/cpanel-public.htaccess" "$PUBLIC_DIR/.htaccess"
chmod 600 "$APP_DIR/.env"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 750 {} +
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache
php artisan up
printf 'Deployed commit %s at %s\n' "$(git -C "$SOURCE_DIR" rev-parse HEAD)" "$STAMP"
