#!/usr/bin/env bash
# Doggys: idempotent update on o2switch, run over SSH from the project folder:  bash scripts/deploy.sh
# Safe to run twice. public/build (compiled CSS/JS) is NOT in Git: upload it from your computer first.
set -euo pipefail
cd "$(dirname "$0")/.."

PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-composer}"

[ -f .env ] || { echo "ERROR: .env is missing (see docs/DEPLOIEMENT.md, step 7)"; exit 1; }
[ -f public/build/manifest.json ] || echo "WARNING: public/build is missing: upload it (npm run build locally), otherwise the site has no CSS/JS."

$PHP artisan down --retry=60 || true
trap '$PHP artisan up' EXIT

git pull --ff-only
$COMPOSER install --no-dev --optimize-autoloader --no-interaction
$PHP artisan migrate --force
$PHP artisan db:seed --force          # reference data only, idempotent
$PHP artisan storage:link 2>/dev/null || true

$PHP artisan optimize:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan event:cache

echo "Update finished."
