#!/usr/bin/env sh
set -eu

log() {
    printf '%s\n' "[white-mart] $*"
}

quote_env_value() {
    php -r '
        $value = $argv[1] ?? "";

        if ($value !== "" && preg_match("/^[A-Za-z0-9_:\\/.,@+-]+$/", $value)) {
            echo $value;
            exit;
        }

        echo "\"".str_replace(["\\", "\"", "\n", "\r"], ["\\\\", "\\\"", "\\n", ""], $value)."\"";
    ' "$1"
}

set_env_value() {
    key="$1"
    value="$(quote_env_value "${2:-}")"

    php -r '
        $path = ".env";
        $key = $argv[1];
        $value = $argv[2];
        $line = $key."=".$value;
        $contents = file_exists($path) ? file_get_contents($path) : "";

        if ($contents === false) {
            fwrite(STDERR, "Unable to read .env\n");
            exit(1);
        }

        if (preg_match("/^".preg_quote($key, "/")."=.*/m", $contents)) {
            $contents = preg_replace("/^".preg_quote($key, "/")."=.*/m", $line, $contents);
        } else {
            $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($path, $contents);
    ' "$key" "$value"
}

wait_for_database() {
    attempts="${WHITE_MART_DB_WAIT_ATTEMPTS:-60}"
    sleep_seconds="${WHITE_MART_DB_WAIT_SLEEP:-2}"

    log "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."

    i=1
    while [ "$i" -le "$attempts" ]; do
        if php -r '
            $host = getenv("DB_HOST") ?: "mysql";
            $port = getenv("DB_PORT") ?: "3306";
            $database = getenv("DB_DATABASE") ?: "white_mart";
            $username = getenv("DB_USERNAME") ?: "white_mart";
            $password = getenv("DB_PASSWORD") ?: "";

            try {
                new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
                    PDO::ATTR_TIMEOUT => 2,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                exit(0);
            } catch (Throwable) {
                exit(1);
            }
        '; then
            log "MySQL is ready."
            return 0
        fi

        i=$((i + 1))
        sleep "$sleep_seconds"
    done

    log "MySQL did not become ready in time."
    return 1
}

mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ -w storage ] && [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi

if [ ! -f .env ]; then
    log "Creating .env from .env.example."
    cp .env.example .env
fi

if [ ! -f storage/app/.app-key ]; then
    if [ -n "${APP_KEY:-}" ]; then
        log "Persisting provided Laravel APP_KEY."
        printf '%s\n' "$APP_KEY" > storage/app/.app-key
    else
        log "Generating persistent Laravel APP_KEY."
        php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;' > storage/app/.app-key
    fi
fi

APP_KEY="$(tr -d '\r\n' < storage/app/.app-key)"
export APP_KEY

set_env_value APP_NAME "${APP_NAME:-White-Mart}"
set_env_value APP_ENV "${APP_ENV:-production}"
set_env_value APP_KEY "$APP_KEY"
set_env_value APP_DEBUG "${APP_DEBUG:-false}"
set_env_value APP_URL "${APP_URL:-http://localhost}"
set_env_value LOG_CHANNEL "${LOG_CHANNEL:-stack}"
set_env_value LOG_STACK "${LOG_STACK:-single}"
set_env_value LOG_LEVEL "${LOG_LEVEL:-warning}"
set_env_value DB_CONNECTION "${DB_CONNECTION:-mysql}"
set_env_value DB_HOST "${DB_HOST:-mysql}"
set_env_value DB_PORT "${DB_PORT:-3306}"
set_env_value DB_DATABASE "${DB_DATABASE:-white_mart}"
set_env_value DB_USERNAME "${DB_USERNAME:-white_mart}"
set_env_value DB_PASSWORD "${DB_PASSWORD:-white_mart_change_me}"
set_env_value CACHE_STORE "${CACHE_STORE:-database}"
set_env_value QUEUE_CONNECTION "${QUEUE_CONNECTION:-database}"
set_env_value SESSION_DRIVER "${SESSION_DRIVER:-database}"
set_env_value SESSION_ENCRYPT "${SESSION_ENCRYPT:-true}"
set_env_value FILESYSTEM_DISK "${FILESYSTEM_DISK:-local}"
set_env_value MAIL_MAILER "${MAIL_MAILER:-log}"
set_env_value BARCODE_LOOKUP_PROVIDERS "${BARCODE_LOOKUP_PROVIDERS:-}"

if [ "${WHITE_MART_SKIP_INIT:-false}" != "true" ]; then
    wait_for_database

    log "Preparing Laravel runtime."
    php artisan config:clear --no-interaction
    
    if [ ! -f storage/app/.init_complete ]; then
        log "First time initialization: Running migrations and seeders."
        php artisan migrate --force --no-interaction
        php artisan db:seed --force --no-interaction
        
        log "Bootstrapping sudo user for local access."
        php artisan users:bootstrap-sudo admin@white-mart.local --name="System Admin" --password="password" --no-interaction || true
        
        touch storage/app/.init_complete
    else
        log "Database already initialized. Running any pending migrations."
        php artisan migrate --force --no-interaction
    fi

    php artisan storage:link --force --no-interaction
    php artisan optimize --no-interaction
    php artisan view:cache --no-interaction
    php artisan event:cache --no-interaction
    log "Laravel initialization complete."
fi

exec "$@"
