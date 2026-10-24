#!/usr/bin/env bash
# Doggys: one-time first install on o2switch (cPanel), run from the cPanel Terminal:
#   git clone https://github.com/wf464967fv-oss/doggys.git ~/doggys && bash ~/doggys/scripts/first-install.sh
# Creates the MySQL database/user and the contact@doggys.be mailbox, writes .env with generated
# passwords (never printed), installs dependencies, migrates, seeds, imports data, adds the cron.
# Safe to re-run: if .env already exists, accounts and passwords are left untouched.
# Still done in the cPanel UI: PHP 8.4 selector + extensions, doggys.be document root -> ~/doggys/public.
set -euo pipefail
cd "$(dirname "$0")/.."

ACCOUNT="$(whoami)"
DB_NAME="${ACCOUNT}_doggys"
DOMAIN="doggys.be"

for candidate in php /opt/alt/php84/usr/bin/php /usr/local/bin/php8.4 php8.4; do
    if command -v "$candidate" >/dev/null 2>&1 && "$candidate" -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);'; then
        PHP="$(command -v "$candidate")"
        break
    fi
done
[ -n "${PHP:-}" ] || { echo "ERROR: PHP 8.4 not found. Select PHP 8.4 in cPanel first."; exit 1; }
COMPOSER="$PHP $(command -v composer)"
echo "PHP: $PHP"

uapi_ok() {
    local response
    response="$(uapi --output=json "$@")" || return 1
    [[ "$response" == *'"status":1'* ]]
}

set_env() {
    if grep -qE "^#? ?$1=" .env; then
        sed -i -E "s|^#? ?$1=.*|$1=$2|" .env
    else
        echo "$1=$2" >> .env
    fi
}

if [ ! -f .env ]; then
    DB_PASSWORD="$(openssl rand -hex 24)"
    MAIL_PASSWORD="$(openssl rand -hex 24)"

    uapi_ok Mysql create_database name="$DB_NAME" || { echo "ERROR: could not create database $DB_NAME"; exit 1; }
    uapi_ok Mysql create_user name="$DB_NAME" password="$DB_PASSWORD" || { echo "ERROR: could not create MySQL user $DB_NAME"; exit 1; }
    uapi_ok Mysql set_privileges_on_database user="$DB_NAME" database="$DB_NAME" privileges=ALL%20PRIVILEGES || { echo "ERROR: could not grant privileges"; exit 1; }
    uapi_ok Email add_pop email=contact domain="$DOMAIN" password="$MAIL_PASSWORD" quota=1024 || echo "WARNING: mailbox contact@$DOMAIN not created (already exists?). Set MAIL_PASSWORD in .env by hand."

    cp .env.example .env
    set_env APP_ENV production
    set_env APP_DEBUG false
    set_env APP_URL "https://$DOMAIN"
    set_env LOG_LEVEL warning
    set_env DB_CONNECTION mysql
    set_env DB_HOST 127.0.0.1
    set_env DB_PORT 3306
    set_env DB_DATABASE "$DB_NAME"
    set_env DB_USERNAME "$DB_NAME"
    set_env DB_PASSWORD "$DB_PASSWORD"
    set_env SESSION_SECURE_COOKIE true
    set_env QUEUE_CONNECTION sync
    set_env MAIL_MAILER smtp
    set_env MAIL_SCHEME smtps
    set_env MAIL_HOST "mail.$DOMAIN"
    set_env MAIL_PORT 465
    set_env MAIL_USERNAME "contact@$DOMAIN"
    set_env MAIL_PASSWORD "$MAIL_PASSWORD"
    chmod 600 .env
    unset DB_PASSWORD MAIL_PASSWORD
    echo "Created database, MySQL user, mailbox and .env (passwords stored in .env only)."
else
    echo ".env already exists: accounts and passwords left untouched."
fi

$COMPOSER install --no-dev --optimize-autoloader --no-interaction
grep -qE '^APP_KEY=.+' .env || $PHP artisan key:generate --force
$PHP artisan migrate --force
$PHP artisan db:seed --force
$PHP artisan storage:link 2>/dev/null || true
$PHP artisan doggys:import-providers
$PHP artisan doggys:import-guides
chmod -R 775 storage bootstrap/cache

$PHP artisan optimize:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan event:cache

CRON_LINE="* * * * * cd $PWD && $PHP artisan schedule:run >> /dev/null 2>&1"
( crontab -l 2>/dev/null | grep -vF "artisan schedule:run" || true; echo "$CRON_LINE" ) | crontab -
echo "Cron installed: $CRON_LINE"

uapi_ok SSL start_autossl_check || echo "WARNING: AutoSSL check not started, run it from SSL/TLS Status."

echo "First install finished. Remaining: point $DOMAIN document root to $PWD/public (cPanel > Domains), then sign up and run: $PHP artisan doggys:make-admin YOUR_EMAIL"
