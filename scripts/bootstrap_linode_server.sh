#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'

SCRIPT_NAME="$(basename "$0")"
SCRIPT_VERSION="1.0.0"
SUMMARY_LINES=()
CERTBOT_STATUS="skipped"
CHANGE_COUNT=0

RED="$(printf '\033[31m')"
GREEN="$(printf '\033[32m')"
YELLOW="$(printf '\033[33m')"
BLUE="$(printf '\033[34m')"
BOLD="$(printf '\033[1m')"
RESET="$(printf '\033[0m')"

info() {
    printf '%b\n' "${BLUE}${BOLD}==>${RESET} $*"
}

success() {
    printf '%b\n' "${GREEN}${BOLD}✔${RESET} $*"
}

warn() {
    printf '%b\n' "${YELLOW}${BOLD}!${RESET} $*"
}

error() {
    printf '%b\n' "${RED}${BOLD}x${RESET} $*" >&2
}

die() {
    error "$*"
    exit 1
}

on_error() {
    local line_number="$1"
    error "Setup failed on line ${line_number}. Review the output above."
}

trap 'on_error "$LINENO"' ERR

append_summary() {
    SUMMARY_LINES+=("$1")
}

mark_changed() {
    CHANGE_COUNT=$((CHANGE_COUNT + 1))
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

prompt_default() {
    local prompt="$1"
    local default_value="$2"
    local answer

    read -r -p "${prompt} [${default_value}]: " answer
    printf '%s' "${answer:-$default_value}"
}

prompt_yes_no() {
    local prompt="$1"
    local default_value="${2:-y}"
    local answer

    while true; do
        if [[ "${default_value}" == "y" ]]; then
            read -r -p "${prompt} [Y/n]: " answer
            answer="${answer:-y}"
        else
            read -r -p "${prompt} [y/N]: " answer
            answer="${answer:-n}"
        fi

        case "${answer}" in
            y|Y|yes|YES)
                return 0
                ;;
            n|N|no|NO)
                return 1
                ;;
            *)
                warn "Please answer yes or no."
                ;;
        esac
    done
}

prompt_secret() {
    local prompt="$1"
    local answer

    read -r -s -p "${prompt}: " answer
    printf '\n' >&2
    printf '%s' "${answer}"
}

prompt_optional_secret_with_default() {
    local prompt="$1"
    local default_value="$2"
    local answer

    read -r -s -p "${prompt} [press Enter to use generated value]: " answer
    printf '\n' >&2
    printf '%s' "${answer:-$default_value}"
}

sanitize_slug() {
    printf '%s' "$1" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9]/-/g; s/-\{2,\}/-/g; s/^-//; s/-$//'
}

sql_escape() {
    printf '%s' "$1" | sed "s/'/''/g"
}

validate_identifier() {
    local label="$1"
    local value="$2"

    if [[ ! "${value}" =~ ^[a-zA-Z0-9_]+$ ]]; then
        die "${label} contains invalid characters. Only letters, numbers, and underscores are allowed."
    fi
}

generate_password() {
    hexdump -vn 16 -e '16/1 "%02x"' /dev/urandom
}

dotenv_escape() {
    local value="$1"
    value="${value//\\/\\\\}"
    value="${value//\"/\\\"}"
    value="${value//\$/\\$}"
    value="${value//$'\n'/\\n}"
    value="${value//$'\r'/\\r}"
    printf '"%s"' "${value}"
}

dotenv_unquote() {
    local value="$1"

    if [[ "${value}" == \"*\" && "${value}" == *\" ]]; then
        value="${value#\"}"
        value="${value%\"}"
    fi

    value="${value//\\n/$'\n'}"
    value="${value//\\r/$'\r'}"
    value="${value//\\\$/\$}"
    value="${value//\\\"/\"}"
    value="${value//\\\\/\\}"

    printf '%s' "${value}"
}

env_get() {
    local file="$1"
    local key="$2"
    local raw_value=""

    if [[ ! -f "${file}" ]]; then
        return 1
    fi

    raw_value="$(grep -E "^${key}=" "${file}" | head -n 1 | cut -d '=' -f 2- || true)"

    if [[ -z "${raw_value}" ]]; then
        return 1
    fi

    dotenv_unquote "${raw_value}"
}

upsert_env_var() {
    local file="$1"
    local key="$2"
    local value="$3"
    local escaped_value
    local current_value
    escaped_value="$(dotenv_escape "${value}")"
    current_value="$(grep -E "^${key}=" "${file}" | head -n 1 | cut -d '=' -f 2- || true)"

    if [[ -n "${current_value}" ]] && [[ "${current_value}" == "${escaped_value}" ]]; then
        return 0
    fi

    if grep -qE "^${key}=" "${file}" 2>/dev/null; then
        sed -i.bak "s|^${key}=.*$|${key}=${escaped_value}|" "${file}"
        rm -f "${file}.bak"
    else
        printf '%s=%s\n' "${key}" "${escaped_value}" >>"${file}"
    fi

    mark_changed
}

write_if_changed() {
    local target_path="$1"
    local owner="$2"
    local permissions="$3"
    local temp_file

    temp_file="$(mktemp)"
    cat >"${temp_file}"

    if [[ -f "${target_path}" ]] && cmp -s "${temp_file}" "${target_path}"; then
        rm -f "${temp_file}"
        success "${target_path} is already up to date."
        return 0
    fi

    install -m "${permissions}" -o "${owner%%:*}" -g "${owner##*:}" "${temp_file}" "${target_path}"
    rm -f "${temp_file}"
    mark_changed
    success "Updated ${target_path}."
    return 0
}

determine_app_key() {
    local env_file="${APP_PATH}/shared/.env"
    local existing_key=""

    if [[ -f "${env_file}" ]]; then
        existing_key="$(grep -E '^APP_KEY=' "${env_file}" | head -n 1 | cut -d '=' -f 2- || true)"
        existing_key="${existing_key%\"}"
        existing_key="${existing_key#\"}"
    fi

    if [[ -n "${existing_key}" ]]; then
        APP_KEY_VALUE="${existing_key}"
        success "Preserving existing APP_KEY from shared environment."
        return
    fi

    APP_KEY_VALUE="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
    success "Generated a new APP_KEY for the shared environment."
}

require_root() {
    if [[ "${EUID}" -ne 0 ]]; then
        die "Run this script as root."
    fi
}

check_operating_system() {
    if [[ ! -f /etc/os-release ]]; then
        die "Unable to detect the operating system."
    fi

    # shellcheck disable=SC1091
    source /etc/os-release

    if [[ "${ID:-}" != "ubuntu" ]]; then
        die "This script is intended for Ubuntu servers."
    fi

    if [[ "${VERSION_ID:-}" != "24.04" ]]; then
        warn "This script was written for Ubuntu 24.04 LTS. Continuing on ${PRETTY_NAME:-Ubuntu}."
    else
        success "Detected ${PRETTY_NAME}."
    fi
}

collect_inputs() {
    info "Collecting setup details."

    APP_PATH="$(prompt_default "Application path" "/var/www/webportfolio")"
    DEPLOY_USER="$(prompt_default "Deploy user" "deploy")"
    APP_SLUG="$(prompt_default "Nginx site slug" "$(basename "${APP_PATH}")")"
    EXISTING_ENV_FILE="${APP_PATH}/shared/.env"
    EXISTING_SETUP_HINT="no"

    if [[ -f "${EXISTING_ENV_FILE}" || -f "/etc/nginx/sites-available/${APP_SLUG}" || -d "/home/${DEPLOY_USER}" ]]; then
        EXISTING_SETUP_HINT="yes"
    fi

    EXISTING_APP_NAME="$(env_get "${EXISTING_ENV_FILE}" "APP_NAME" || true)"
    EXISTING_APP_URL="$(env_get "${EXISTING_ENV_FILE}" "APP_URL" || true)"
    EXISTING_DB_NAME="$(env_get "${EXISTING_ENV_FILE}" "DB_DATABASE" || true)"
    EXISTING_DB_USER="$(env_get "${EXISTING_ENV_FILE}" "DB_USERNAME" || true)"
    EXISTING_DB_PASS="$(env_get "${EXISTING_ENV_FILE}" "DB_PASSWORD" || true)"

    APP_NAME="$(prompt_default "Application name" "${EXISTING_APP_NAME:-Web Portfolio}")"

    if [[ -n "${EXISTING_APP_URL}" ]]; then
        EXISTING_PRIMARY_DOMAIN="${EXISTING_APP_URL#http://}"
        EXISTING_PRIMARY_DOMAIN="${EXISTING_PRIMARY_DOMAIN#https://}"
        EXISTING_PRIMARY_DOMAIN="${EXISTING_PRIMARY_DOMAIN%%/*}"
    else
        EXISTING_PRIMARY_DOMAIN="your-domain.com"
    fi

    APP_DOMAIN="$(prompt_default "Primary domain" "${EXISTING_PRIMARY_DOMAIN}")"
    APP_WWW_DOMAIN="$(prompt_default "WWW domain" "www.${APP_DOMAIN}")"
    APP_DB="$(prompt_default "MariaDB database name" "${EXISTING_DB_NAME:-webportfolio}")"
    validate_identifier "Database name" "${APP_DB}"
    APP_DB_USER="$(prompt_default "MariaDB database user" "${EXISTING_DB_USER:-webportfolio}")"
    validate_identifier "Database user" "${APP_DB_USER}"

    GENERATED_DB_PASSWORD="$(generate_password)"
    APP_DB_PASS="$(prompt_optional_secret_with_default "MariaDB database password" "${EXISTING_DB_PASS:-$GENERATED_DB_PASSWORD}")"

    read -r -p "Paste the GitHub Actions deploy public SSH key now (leave blank to skip): " GITHUB_ACTIONS_PUBLIC_KEY

    if prompt_yes_no "Enable UFW firewall rules for SSH and Nginx?" "y"; then
        ENABLE_UFW="yes"
    else
        ENABLE_UFW="no"
    fi

    if prompt_yes_no "Enable fail2ban for SSH brute-force protection?" "y"; then
        ENABLE_FAIL2BAN="yes"
    else
        ENABLE_FAIL2BAN="no"
    fi

    if prompt_yes_no "Attempt to request an SSL certificate with Certbot now?" "n"; then
        ENABLE_CERTBOT="yes"
        CERTBOT_EMAIL="$(prompt_default "Certbot email address" "admin@${APP_DOMAIN}")"
    else
        ENABLE_CERTBOT="no"
        CERTBOT_EMAIL=""
    fi

    if [[ "${EXISTING_SETUP_HINT}" == "yes" ]]; then
        UPGRADE_DEFAULT="n"
    else
        UPGRADE_DEFAULT="y"
    fi

    if prompt_yes_no "Run apt-get upgrade during this run?" "${UPGRADE_DEFAULT}"; then
        RUN_SYSTEM_UPGRADE="yes"
    else
        RUN_SYSTEM_UPGRADE="no"
    fi

    PLACEHOLDER_MESSAGE="${APP_NAME} server bootstrap complete. Ready for first deployment."
    SUMMARY_FILE="/root/$(sanitize_slug "${APP_SLUG}")-server-setup-summary.txt"

    printf '\n'
    info "Review the values below."
    printf '%s\n' \
        "App name: ${APP_NAME}" \
        "Primary domain: ${APP_DOMAIN}" \
        "WWW domain: ${APP_WWW_DOMAIN}" \
        "Deploy user: ${DEPLOY_USER}" \
        "App path: ${APP_PATH}" \
        "Nginx site slug: ${APP_SLUG}" \
        "Database: ${APP_DB}" \
        "Database user: ${APP_DB_USER}" \
        "Enable UFW: ${ENABLE_UFW}" \
        "Enable fail2ban: ${ENABLE_FAIL2BAN}" \
        "Attempt Certbot now: ${ENABLE_CERTBOT}" \
        "Run apt-get upgrade: ${RUN_SYSTEM_UPGRADE}"

    if ! prompt_yes_no "Continue with these settings?" "y"; then
        die "Aborted by user."
    fi
}

ensure_apt_dependencies() {
    info "Updating apt metadata and installing base packages."
    apt-get update

    if [[ "${RUN_SYSTEM_UPGRADE}" == "yes" ]]; then
        apt-get upgrade -y
        success "System packages upgraded."
    else
        success "Skipped apt-get upgrade for validation-friendly rerun."
    fi

    apt-get install -y software-properties-common ca-certificates curl git unzip nginx supervisor mariadb-server certbot python3-certbot-nginx acl

    if [[ "${ENABLE_FAIL2BAN}" == "yes" ]]; then
        apt-get install -y fail2ban
        success "fail2ban installed."
    else
        success "Skipping fail2ban installation."
    fi

    success "Base packages installed."
}

ensure_php_85() {
    info "Checking PHP 8.5 package availability."

    if ! apt-cache show php8.5 >/dev/null 2>&1; then
        info "Adding Ondrej PHP repository."
        add-apt-repository ppa:ondrej/php -y
        apt-get update
    fi

    info "Installing PHP 8.5 and required extensions."
    apt-get install -y php8.5 php8.5-cli php8.5-fpm php8.5-common php8.5-mysql php8.5-mbstring php8.5-xml php8.5-curl php8.5-zip php8.5-bcmath php8.5-intl php8.5-gd php8.5-sqlite3

    if command -v update-alternatives >/dev/null 2>&1 && [[ -x /usr/bin/php8.5 ]]; then
        update-alternatives --set php /usr/bin/php8.5
    fi

    success "PHP 8.5 installed."
}

ensure_composer() {
    if command_exists composer; then
        success "Composer is already installed."
        return
    fi

    info "Installing Composer."
    local installer
    installer="$(mktemp)"
    curl -fsSL https://getcomposer.org/installer -o "${installer}"
    php "${installer}" --install-dir=/usr/local/bin --filename=composer
    rm -f "${installer}"
    success "Composer installed."
}

ensure_services_enabled() {
    info "Enabling and starting required services."
    systemctl enable --now mariadb
    systemctl enable --now php8.5-fpm
    systemctl enable --now nginx
    systemctl enable --now supervisor

    if [[ "${ENABLE_FAIL2BAN}" == "yes" ]]; then
        systemctl enable --now fail2ban
        success "fail2ban enabled."
    elif systemctl list-unit-files | grep -q '^fail2ban\.service'; then
        systemctl disable --now fail2ban >/dev/null 2>&1 || true
        success "fail2ban disabled."
    fi

    success "System services enabled."
}

configure_php_fpm() {
    info "Applying low-memory PHP-FPM tuning for a 1 GB server."

    write_if_changed "/etc/php/8.5/fpm/pool.d/zz-webportfolio-tuning.conf" "root:root" "0644" <<EOF
; Web portfolio tuning for small servers
[www]
pm = ondemand
pm.max_children = 5
pm.process_idle_timeout = 10s
pm.max_requests = 250
EOF

    php-fpm8.5 -t
    systemctl reload php8.5-fpm
    success "PHP-FPM tuned for low-memory operation."
}

configure_mariadb_for_small_server() {
    info "Applying low-memory MariaDB tuning for a 1 GB server."

    write_if_changed "/etc/mysql/mariadb.conf.d/60-webportfolio-tuning.cnf" "root:root" "0644" <<'EOF'
[mysqld]
innodb_buffer_pool_size = 128M
innodb_log_file_size = 64M
max_connections = 30
thread_cache_size = 8
table_open_cache = 256
performance_schema = OFF
tmp_table_size = 16M
max_heap_table_size = 16M
EOF

    systemctl restart mariadb
    success "MariaDB tuned for low-memory operation."
}

ensure_deploy_user() {
    info "Ensuring deploy user exists."

    if id -u "${DEPLOY_USER}" >/dev/null 2>&1; then
        success "User ${DEPLOY_USER} already exists."
    else
        adduser --disabled-password --gecos "" "${DEPLOY_USER}"
        mark_changed
        success "Created user ${DEPLOY_USER}."
    fi

    usermod -aG www-data "${DEPLOY_USER}"

    mkdir -p "/home/${DEPLOY_USER}/.ssh"
    chmod 700 "/home/${DEPLOY_USER}/.ssh"
    touch "/home/${DEPLOY_USER}/.ssh/authorized_keys"
    chmod 600 "/home/${DEPLOY_USER}/.ssh/authorized_keys"
    chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "/home/${DEPLOY_USER}/.ssh"

    if [[ -n "${GITHUB_ACTIONS_PUBLIC_KEY}" ]]; then
        if ! grep -Fqx "${GITHUB_ACTIONS_PUBLIC_KEY}" "/home/${DEPLOY_USER}/.ssh/authorized_keys"; then
            printf '%s\n' "${GITHUB_ACTIONS_PUBLIC_KEY}" >>"/home/${DEPLOY_USER}/.ssh/authorized_keys"
            mark_changed
            success "Added GitHub Actions SSH public key for ${DEPLOY_USER}."
        else
            success "GitHub Actions SSH public key is already present."
        fi
    else
        warn "No GitHub Actions public key was provided. Add it later before enabling CI/CD deploys."
    fi
}

ensure_app_directories() {
    info "Creating application directories."

    mkdir -p "${APP_PATH}/releases"
    mkdir -p "${APP_PATH}/shared/storage/app/public"
    mkdir -p "${APP_PATH}/shared/storage/framework/cache"
    mkdir -p "${APP_PATH}/shared/storage/framework/sessions"
    mkdir -p "${APP_PATH}/shared/storage/framework/views"
    mkdir -p "${APP_PATH}/shared/storage/logs"
    mkdir -p "${APP_PATH}/shared/bootstrap/cache"
    mkdir -p "${APP_PATH}/shared/database"
    mkdir -p "${APP_PATH}/bootstrap-placeholder/public"

    local placeholder_file="${APP_PATH}/bootstrap-placeholder/public/index.html"

    write_if_changed "${placeholder_file}" "${DEPLOY_USER}:www-data" "0644" <<EOF
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>${APP_NAME}</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #0f172a;
            color: #e2e8f0;
        }
        main {
            width: min(42rem, calc(100vw - 2rem));
            padding: 2rem;
            border: 1px solid #334155;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.85);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }
        h1 {
            margin-top: 0;
        }
        p {
            line-height: 1.6;
        }
        code {
            color: #93c5fd;
        }
    </style>
</head>
<body>
    <main>
        <h1>${APP_NAME}</h1>
        <p>${PLACEHOLDER_MESSAGE}</p>
        <p>The server is configured and waiting for the first release into <code>${APP_PATH}</code>.</p>
    </main>
</body>
</html>
EOF

    if [[ ! -e "${APP_PATH}/current" ]]; then
        ln -s "${APP_PATH}/bootstrap-placeholder" "${APP_PATH}/current"
        chown -h "${DEPLOY_USER}:www-data" "${APP_PATH}/current"
        mark_changed
        success "Created placeholder current symlink."
    elif [[ "$(readlink -f "${APP_PATH}/current")" == "$(readlink -f "${APP_PATH}/bootstrap-placeholder")" ]]; then
        success "Current symlink already points to the bootstrap placeholder."
    else
        success "Current release already exists. Preserving ${APP_PATH}/current."
    fi

    chown -R "${DEPLOY_USER}:www-data" "${APP_PATH}"
    find "${APP_PATH}/shared" -type d -exec chmod 2775 {} \;
    find "${APP_PATH}/shared" -type f -exec chmod 664 {} \;
    setfacl -R -m "u:${DEPLOY_USER}:rwx" -m "u:www-data:rwx" "${APP_PATH}/shared"
    setfacl -dR -m "u:${DEPLOY_USER}:rwx" -m "u:www-data:rwx" "${APP_PATH}/shared"

    success "Application directories are ready."
}

configure_mariadb_database() {
    info "Configuring MariaDB database and user."

    local escaped_db
    local escaped_user
    local escaped_password
    escaped_db="$(sql_escape "${APP_DB}")"
    escaped_user="$(sql_escape "${APP_DB_USER}")"
    escaped_password="$(sql_escape "${APP_DB_PASS}")"

    mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`${escaped_db}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${escaped_user}'@'localhost' IDENTIFIED BY '${escaped_password}';
ALTER USER '${escaped_user}'@'localhost' IDENTIFIED BY '${escaped_password}';
GRANT ALL PRIVILEGES ON \`${escaped_db}\`.* TO '${escaped_user}'@'localhost';
FLUSH PRIVILEGES;
SQL

    success "MariaDB database and user configured."
}

validate_existing_environment() {
    local env_file="${APP_PATH}/shared/.env"
    local existing_app_url=""
    local existing_db_name=""
    local existing_db_user=""

    existing_app_url="$(env_get "${env_file}" "APP_URL" || true)"
    existing_db_name="$(env_get "${env_file}" "DB_DATABASE" || true)"
    existing_db_user="$(env_get "${env_file}" "DB_USERNAME" || true)"

    if [[ -n "${existing_app_url}" && "${existing_app_url}" != "https://${APP_DOMAIN}" ]]; then
        warn "Existing APP_URL (${existing_app_url}) differs from the input domain. Preserving the existing shared .env."
    fi

    if [[ -n "${existing_db_name}" && "${existing_db_name}" != "${APP_DB}" ]]; then
        warn "Existing DB_DATABASE (${existing_db_name}) differs from the input value. Preserving the existing shared .env."
    fi

    if [[ -n "${existing_db_user}" && "${existing_db_user}" != "${APP_DB_USER}" ]]; then
        warn "Existing DB_USERNAME (${existing_db_user}) differs from the input value. Preserving the existing shared .env."
    fi
}

write_environment_file() {
    info "Writing shared production environment file."

    if [[ -f "${APP_PATH}/shared/.env" ]]; then
        determine_app_key
        upsert_env_var "${APP_PATH}/shared/.env" "APP_KEY" "${APP_KEY_VALUE}"
        validate_existing_environment
        success "Shared environment file already exists. Preserved existing values."
        return
    fi

    determine_app_key

    cat >"${APP_PATH}/shared/.env" <<EOF
APP_NAME=$(dotenv_escape "${APP_NAME}")
APP_ENV=production
APP_KEY=$(dotenv_escape "${APP_KEY_VALUE}")
APP_DEBUG=false
APP_URL=$(dotenv_escape "https://${APP_DOMAIN}")

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${APP_DB}
DB_USERNAME=${APP_DB_USER}
DB_PASSWORD=$(dotenv_escape "${APP_DB_PASS}")

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=$(dotenv_escape "hello@${APP_DOMAIN}")
MAIL_FROM_NAME=$(dotenv_escape "${APP_NAME}")

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME=$(dotenv_escape "${APP_NAME}")
EOF

    chown "${DEPLOY_USER}:www-data" "${APP_PATH}/shared/.env"
    chmod 640 "${APP_PATH}/shared/.env"
    mark_changed

    success "Shared environment file created."
}

configure_nginx() {
    info "Configuring Nginx site."

    write_if_changed "/etc/nginx/sites-available/${APP_SLUG}" "root:root" "0644" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${APP_DOMAIN} ${APP_WWW_DOMAIN};

    root ${APP_PATH}/current/public;
    index index.php index.html;

    client_max_body_size 20m;

    access_log /var/log/nginx/${APP_SLUG}_access.log;
    error_log /var/log/nginx/${APP_SLUG}_error.log;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    gzip_min_length 256;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

    if [[ ! -L "/etc/nginx/sites-enabled/${APP_SLUG}" ]] || [[ "$(readlink -f "/etc/nginx/sites-enabled/${APP_SLUG}")" != "/etc/nginx/sites-available/${APP_SLUG}" ]]; then
        ln -sfn "/etc/nginx/sites-available/${APP_SLUG}" "/etc/nginx/sites-enabled/${APP_SLUG}"
        mark_changed
        success "Enabled Nginx site ${APP_SLUG}."
    else
        success "Nginx site ${APP_SLUG} is already enabled."
    fi

    rm -f /etc/nginx/sites-enabled/default
    nginx -t
    systemctl reload nginx

    success "Nginx site ${APP_SLUG} configured."
}

configure_supervisor() {
    info "Configuring Supervisor worker."

    write_if_changed "/etc/supervisor/conf.d/${APP_SLUG}-worker.conf" "root:root" "0644" <<EOF
[program:${APP_SLUG}-worker]
command=/bin/bash -lc 'until [ -f ${APP_PATH}/current/artisan ]; do sleep 5; done; exec php ${APP_PATH}/current/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=128'
directory=${APP_PATH}/current
user=${DEPLOY_USER}
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=${APP_PATH}/shared/storage/logs/worker.log
stopwaitsecs=3600
EOF

    supervisorctl reread
    supervisorctl update
    success "Supervisor worker configured."
}

configure_cron() {
    info "Configuring Laravel scheduler cron."

    local cron_line
    local current_crontab
    cron_line="* * * * * if [ -f ${APP_PATH}/current/artisan ]; then cd ${APP_PATH}/current && php artisan schedule:run >> /dev/null 2>&1; fi"
    current_crontab="$(runuser -u "${DEPLOY_USER}" -- crontab -l 2>/dev/null || true)"

    if ! grep -Fqx "${cron_line}" <<<"${current_crontab}"; then
        printf '%s\n%s\n' "${current_crontab}" "${cron_line}" | sed '/^$/N;/^\n$/D' | runuser -u "${DEPLOY_USER}" -- crontab -
        mark_changed
        success "Scheduler cron added for ${DEPLOY_USER}."
    else
        success "Scheduler cron already exists for ${DEPLOY_USER}."
    fi
}

configure_firewall() {
    if [[ "${ENABLE_UFW}" != "yes" ]]; then
        warn "Skipping UFW firewall configuration."
        return
    fi

    info "Configuring UFW."
    ufw allow OpenSSH
    ufw allow 'Nginx Full'
    ufw --force enable
    success "UFW configured."
}

configure_certbot() {
    if [[ "${ENABLE_CERTBOT}" != "yes" ]]; then
        warn "Skipping Certbot setup for now."
        CERTBOT_STATUS="skipped"
        return
    fi

    info "Requesting SSL certificate with Certbot."

    local certbot_args=(
        --nginx
        --non-interactive
        --agree-tos
        --redirect
        -m "${CERTBOT_EMAIL}"
        -d "${APP_DOMAIN}"
    )

    if [[ -n "${APP_WWW_DOMAIN}" ]]; then
        certbot_args+=(-d "${APP_WWW_DOMAIN}")
    fi

    if [[ -d "/etc/letsencrypt/live/${APP_DOMAIN}" ]]; then
        CERTBOT_STATUS="already-issued"
        success "An SSL certificate already exists for ${APP_DOMAIN}."
    elif certbot "${certbot_args[@]}"; then
        CERTBOT_STATUS="issued"
        success "Certbot completed successfully."
    else
        CERTBOT_STATUS="failed"
        warn "Certbot did not complete. Check DNS and rerun later with: certbot --nginx -d ${APP_DOMAIN} -d ${APP_WWW_DOMAIN}"
    fi
}

write_summary_file() {
    info "Writing setup summary."

    append_summary "Server bootstrap summary"
    append_summary "Script: ${SCRIPT_NAME} ${SCRIPT_VERSION}"
    append_summary "App name: ${APP_NAME}"
    append_summary "Primary domain: ${APP_DOMAIN}"
    append_summary "WWW domain: ${APP_WWW_DOMAIN}"
    append_summary "Deploy user: ${DEPLOY_USER}"
    append_summary "App path: ${APP_PATH}"
    append_summary "Nginx site: /etc/nginx/sites-available/${APP_SLUG}"
    append_summary "Supervisor config: /etc/supervisor/conf.d/${APP_SLUG}-worker.conf"
    append_summary "Shared env file: ${APP_PATH}/shared/.env"
    append_summary "Database: ${APP_DB}"
    append_summary "Database user: ${APP_DB_USER}"
    append_summary "PHP CLI: $(php -v | head -n 1)"
    append_summary "Composer: $(composer --version)"
    append_summary "Nginx status: $(systemctl is-active nginx)"
    append_summary "PHP-FPM status: $(systemctl is-active php8.5-fpm)"
    append_summary "MariaDB status: $(systemctl is-active mariadb)"
    append_summary "Supervisor status: $(systemctl is-active supervisor)"
    append_summary "fail2ban status: $(systemctl is-active fail2ban 2>/dev/null || echo 'not installed')"
    append_summary "PHP-FPM tuning: ondemand, max_children=5, idle_timeout=10s, max_requests=250"
    append_summary "MariaDB tuning: innodb_buffer_pool_size=128M, max_connections=30, performance_schema=OFF"
    append_summary "Queue worker memory limit: 128 MB"
    append_summary "Worker status: $(supervisorctl status "${APP_SLUG}-worker" 2>/dev/null || true)"
    append_summary "UFW enabled: $(ufw status 2>/dev/null | head -n 1 || echo 'not configured')"
    append_summary "Certbot status: ${CERTBOT_STATUS}"
    append_summary "Current symlink target: $(readlink -f "${APP_PATH}/current")"
    append_summary "Placeholder page: http://${APP_DOMAIN}"
    append_summary "Changes applied: ${CHANGE_COUNT}"

    if [[ "${CHANGE_COUNT}" -eq 0 ]]; then
        append_summary "No configuration changes were required. The server already matched the expected setup."
    fi

    append_summary "Next steps:"
    append_summary "1. Add the GitHub production secrets: PROD_HOST, PROD_PORT, PROD_USER, PROD_DEPLOY_PATH, PROD_SSH_KEY, PROD_SSH_KNOWN_HOSTS."
    append_summary "2. Verify DNS resolves to this server."
    append_summary "3. Run the GitHub deploy workflow manually after secrets are ready."
    append_summary "4. APP_KEY is already generated and stored in ${APP_PATH}/shared/.env."
    append_summary "5. If Certbot was skipped or failed, rerun it after DNS is live."

    printf '%s\n' "${SUMMARY_LINES[@]}" >"${SUMMARY_FILE}"
    chmod 600 "${SUMMARY_FILE}"

    success "Summary written to ${SUMMARY_FILE}."
    printf '\n'
    cat "${SUMMARY_FILE}"
}

ensure_swap() {
    if swapon --show | grep -q '/swapfile'; then
        success "Swap file already exists."
        return
    fi

    info "Creating 2 GB swap file."

    if ! fallocate -l 2G /swapfile; then
        warn "fallocate failed, falling back to dd for swap creation."
        dd if=/dev/zero of=/swapfile bs=1M count=2048 status=progress
    fi

    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile

    if ! grep -Fq '/swapfile' /etc/fstab; then
        printf '%s\n' '/swapfile none swap sw 0 0' >>/etc/fstab
    fi

    mark_changed
    success "Swap file configured."
}

main() {
    require_root
    check_operating_system
    collect_inputs
    ensure_swap
    ensure_apt_dependencies
    ensure_php_85
    ensure_composer
    ensure_services_enabled
    configure_php_fpm
    configure_mariadb_for_small_server
    ensure_deploy_user
    ensure_app_directories
    configure_mariadb_database
    write_environment_file
    configure_nginx
    configure_supervisor
    configure_cron
    configure_firewall
    configure_certbot
    write_summary_file
}

main "$@"
