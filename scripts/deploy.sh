#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="/var/www/thewinners"
BRANCH="${1:-main}"
LOCK_FILE="/tmp/thewinners_deploy.lock"
LOG_FILE="/var/log/thewinners-deploy.log"

exec > >(tee -a "$LOG_FILE") 2>&1

echo "============================================================"
echo "Deploy started: $(date -u +"%Y-%m-%d %H:%M:%S UTC")"
echo "Project: $PROJECT_DIR | Branch: $BRANCH"
echo "============================================================"

if [ -f "$LOCK_FILE" ]; then
  echo "❌ Deploy already running (lock exists: $LOCK_FILE)"
  exit 1
fi

touch "$LOCK_FILE"
trap 'rm -f "$LOCK_FILE"' EXIT

cd "$PROJECT_DIR"

echo "==> Ensure clean working tree before deploy"
if [ -n "$(git status --porcelain)" ]; then
  echo "❌ Working tree is not clean. Aborting deploy."
  git status --short
  exit 1
fi

echo "==> Fetch latest code"
git fetch origin "$BRANCH"

echo "==> Checkout branch"
git checkout "$BRANCH"

echo "==> Pull latest code"
git pull --ff-only origin "$BRANCH"

echo "==> Composer install (prod)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Ensure writable directories only where needed"
WRITABLE_DIRS=("var" "public/uploads")

for d in "${WRITABLE_DIRS[@]}"; do
  if [ -d "$d" ]; then
    sudo chown -R ubuntu:www-data "$d"
    sudo find "$d" -type d -exec chmod 2775 {} \;
    sudo find "$d" -type f -exec chmod 664 {} \;
  fi
done

echo "==> Cache prod"
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --env=prod
APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup --env=prod

echo "==> AssetMapper compile"
APP_ENV=prod APP_DEBUG=0 php bin/console asset-map:compile

echo "✅ Deploy finished: $(date -u +"%Y-%m-%d %H:%M:%S UTC")"
