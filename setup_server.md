# Web Portfolio Server Setup

This guide is the exact step-by-step process to get the new portfolio live on a Linode server with GitHub Actions CI/CD.

It assumes:

- The repo is already pushed to GitHub.
- The app now targets PHP 8.5.
- The deployment workflow is already in the repo.
- You are using Ubuntu 24.04 on Linode.
- You want a straightforward single-server production setup.

If a `deploy` GitHub Actions run already triggered and failed, that is expected. Finish this setup, then rerun the workflow manually.

## Automated Bootstrap Option

If you want the server to do almost all of this interactively for you, use:

- [bootstrap_linode_server.sh](/Users/Taylor/Sites/webportfolio/scripts/bootstrap_linode_server.sh)

From your Mac:

```bash
scp /Users/Taylor/Sites/webportfolio/scripts/bootstrap_linode_server.sh root@your-server-ip:/root/
ssh root@your-server-ip 'bash /root/bootstrap_linode_server.sh'
```

The script:

- prompts for the important values
- installs the required packages
- creates the deploy user
- configures MariaDB, Nginx, Supervisor, cron, firewall, and the shared `.env`
- creates a placeholder page so Nginx has something valid to serve before the first app deploy
- writes a summary file at the end

You can still use the manual commands below if you prefer full control.

## What You Are Building

The final setup works like this:

1. You push code to GitHub.
2. GitHub Actions runs linting, tests, and a production asset build.
3. If that succeeds, GitHub packages the app and uploads a release artifact.
4. GitHub connects to your Linode over SSH.
5. The server creates a new release directory, links the shared `.env` and `storage`, installs Composer dependencies, runs migrations, caches Laravel, switches the `current` symlink, and reloads workers.
6. Nginx serves the site, Supervisor keeps the queue worker alive, and cron runs the Laravel scheduler every minute.

## One-Pass Checklist

Follow this exact order:

1. Create the Linode and point your domain DNS to it.
2. Create the GitHub environments and secrets.
3. SSH into the Linode and install system packages.
4. Install PHP 8.5, Composer, MariaDB, Nginx, and Supervisor.
5. Create the `deploy` user and app directories.
6. Create the production database and the shared `.env`.
7. Configure Nginx, Supervisor, cron, and firewall.
8. Issue the SSL certificate with Certbot.
9. Manually run the `deploy` workflow in GitHub Actions.
10. Verify the site, queue worker, logs, and HTTPS.

## Variables Used In This Guide

Replace these values with your real ones:

- `your-domain.com`
- `www.your-domain.com`
- `CHANGE_THIS_TO_A_LONG_RANDOM_PASSWORD`

## Part 1: GitHub Environments And Secrets

Open your GitHub repo and create two environments:

- `Testing`
- `production`

### Production Secrets

Add these to the `production` environment:

- `PROD_HOST`
- `PROD_PORT`
- `PROD_USER`
- `PROD_DEPLOY_PATH`
- `PROD_SSH_KEY`
- `PROD_SSH_KNOWN_HOSTS`

### Exact Secret Formats

Use these exact formats:

`PROD_HOST`

```text
your-domain.com
```

or if you prefer:

```text
123.123.123.123
```

`PROD_PORT`

```text
22
```

`PROD_USER`

```text
deploy
```

`PROD_DEPLOY_PATH`

```text
/var/www/webportfolio
```

`PROD_SSH_KEY`

Paste the full private key, including the first and last lines:

```text
-----BEGIN OPENSSH PRIVATE KEY-----
...
-----END OPENSSH PRIVATE KEY-----
```

`PROD_SSH_KNOWN_HOSTS`

Paste the raw `ssh-keyscan` output, for example:

```text
your-domain.com ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAA...
www.your-domain.com ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAA...
```

### Branch Protection

In GitHub branch protection for `main`, require:

- `tests`
- `linter`

Do not require `deploy` as a merge check.

## Part 2: Local Mac Commands

Run these on your Mac, not on the Linode.

### Create The GitHub Actions Deploy Key

```bash
ssh-keygen -t ed25519 -C "github-actions-webportfolio" -f ~/.ssh/webportfolio_actions
```

Show the public key:

```bash
cat ~/.ssh/webportfolio_actions.pub
```

Show the private key:

```bash
cat ~/.ssh/webportfolio_actions
```

You will:

- Put the public key into `/home/deploy/.ssh/authorized_keys` on the server.
- Paste the private key into GitHub as `PROD_SSH_KEY`.

### Generate Known Hosts

After your DNS is pointing at the Linode, run:

```bash
ssh-keyscan -H your-domain.com
ssh-keyscan -H www.your-domain.com
```

Paste that output into `PROD_SSH_KNOWN_HOSTS`.

## Part 3: Linode Server Commands

SSH into the new Linode as `root`.

### Set Reusable Variables

```bash
export APP_DOMAIN="your-domain.com"
export APP_WWW_DOMAIN="www.your-domain.com"
export APP_PATH="/var/www/webportfolio"
export APP_DB="webportfolio"
export APP_DB_USER="webportfolio"
export APP_DB_PASS="CHANGE_THIS_TO_A_LONG_RANDOM_PASSWORD"
```

### Update The Server

```bash
apt update && apt upgrade -y
apt install -y software-properties-common ca-certificates curl git unzip nginx supervisor mariadb-server certbot python3-certbot-nginx acl
```

### Install PHP 8.5

Check whether Ubuntu already sees `php8.5`:

```bash
apt-cache policy php8.5
```

If no candidate is shown, add Ondrej Surý's PHP PPA:

```bash
add-apt-repository ppa:ondrej/php -y
apt update
```

Install PHP 8.5 and Laravel extensions:

```bash
apt install -y php8.5 php8.5-cli php8.5-fpm php8.5-common php8.5-mysql php8.5-mbstring php8.5-xml php8.5-curl php8.5-zip php8.5-bcmath php8.5-intl php8.5-gd php8.5-sqlite3
```

Verify:

```bash
php -v
```

You want PHP 8.5 here.

### Install Composer

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
composer --version
```

### Create The Deploy User

```bash
adduser --disabled-password --gecos "" deploy
usermod -aG www-data deploy
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
touch /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys
chown -R deploy:deploy /home/deploy/.ssh
```

Open the authorized keys file:

```bash
nano /home/deploy/.ssh/authorized_keys
```

Paste in the contents of `~/.ssh/webportfolio_actions.pub`.

### Create App Directories

```bash
mkdir -p "$APP_PATH"/{releases,shared/storage/app/public,shared/storage/framework/cache,shared/storage/framework/sessions,shared/storage/framework/views,shared/storage/logs,shared/bootstrap/cache}
chown -R deploy:www-data "$APP_PATH"
find "$APP_PATH/shared" -type d -exec chmod 2775 {} \;
find "$APP_PATH/shared" -type f -exec chmod 664 {} \;
setfacl -R -m u:deploy:rwx -m u:www-data:rwx "$APP_PATH/shared"
setfacl -dR -m u:deploy:rwx -m u:www-data:rwx "$APP_PATH/shared"
```

## Part 4: Database Setup

Create the MariaDB database and user:

```bash
mariadb -e "CREATE DATABASE ${APP_DB} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mariadb -e "CREATE USER '${APP_DB_USER}'@'localhost' IDENTIFIED BY '${APP_DB_PASS}';"
mariadb -e "GRANT ALL PRIVILEGES ON ${APP_DB}.* TO '${APP_DB_USER}'@'localhost';"
mariadb -e "FLUSH PRIVILEGES;"
```

## Part 5: Production Environment File

Create the shared production `.env`:

```bash
APP_KEY_VALUE="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"

cat > "$APP_PATH/shared/.env" <<EOF
APP_NAME="Web Portfolio"
APP_ENV=production
APP_KEY="${APP_KEY_VALUE}"
APP_DEBUG=false
APP_URL=https://${APP_DOMAIN}

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
DB_PASSWORD="${APP_DB_PASS}"

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
MAIL_FROM_ADDRESS="hello@${APP_DOMAIN}"
MAIL_FROM_NAME="Web Portfolio"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="Web Portfolio"
EOF
chown deploy:www-data "$APP_PATH/shared/.env"
chmod 640 "$APP_PATH/shared/.env"
```

If you are following the bootstrap script instead of the manual setup, the script now generates and preserves `APP_KEY` for you automatically.

## Part 6: Nginx Configuration

Create the Nginx site:

```bash
cat > /etc/nginx/sites-available/webportfolio <<EOF
server {
    listen 80;
    server_name ${APP_DOMAIN} ${APP_WWW_DOMAIN};

    root ${APP_PATH}/current/public;
    index index.php index.html;

    access_log /var/log/nginx/webportfolio_access.log;
    error_log /var/log/nginx/webportfolio_error.log;

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
ln -sf /etc/nginx/sites-available/webportfolio /etc/nginx/sites-enabled/webportfolio
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now nginx php8.5-fpm mariadb supervisor
systemctl reload nginx
```

## Part 7: Queue Worker

Create the Supervisor worker config:

```bash
cat > /etc/supervisor/conf.d/webportfolio-worker.conf <<EOF
[program:webportfolio-worker]
command=php ${APP_PATH}/current/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
directory=${APP_PATH}/current
user=deploy
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
```

Check status:

```bash
supervisorctl status
```

It may not fully start until after the first successful deploy creates `${APP_PATH}/current`.

## Part 8: Laravel Scheduler

Set up the cron entry:

```bash
( sudo -u deploy crontab -l 2>/dev/null; echo "* * * * * cd ${APP_PATH}/current && php artisan schedule:run >> /dev/null 2>&1" ) | sudo -u deploy crontab -
```

Verify:

```bash
sudo -u deploy crontab -l
```

## Part 9: Firewall

```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable
ufw status
```

## Part 10: SSL Certificate

Once DNS is pointed correctly:

```bash
certbot --nginx -d "$APP_DOMAIN" -d "$APP_WWW_DOMAIN"
```

After this finishes, your Nginx config should be updated automatically for HTTPS.

## Part 11: First Deployment

Now go to GitHub Actions and run the `deploy` workflow manually using `workflow_dispatch`.

If it succeeds, check:

```bash
ls -la "$APP_PATH"
ls -la "$APP_PATH/current"
supervisorctl status
systemctl status nginx php8.5-fpm --no-pager
tail -n 100 "$APP_PATH/shared/storage/logs/worker.log"
tail -n 100 /var/log/nginx/webportfolio_error.log
```

Then test the site:

```bash
curl -I "https://${APP_DOMAIN}"
```

## Part 12: Good Final Verification Commands

Run these after the first deploy:

```bash
sudo -u deploy test -f "$APP_PATH/shared/.env" && echo ok
sudo -u deploy php "$APP_PATH/current/artisan" about
curl -I "https://${APP_DOMAIN}"
```

## Troubleshooting

### `Host key verification failed`

Cause:

- `PROD_SSH_KNOWN_HOSTS` is wrong or empty.

Fix:

```bash
ssh-keyscan -H your-domain.com
ssh-keyscan -H www.your-domain.com
```

Replace the secret value in GitHub.

### `Permission denied (publickey)`

Cause:

- Wrong private key in `PROD_SSH_KEY`
- Public key missing from `/home/deploy/.ssh/authorized_keys`
- Wrong permissions on `.ssh` files

Fix:

```bash
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys
chown -R deploy:deploy /home/deploy/.ssh
```

### `Missing shared environment file`

Cause:

- `${APP_PATH}/shared/.env` was not created

Fix:

Create it with the exact command from Part 5.

### Database `SQLSTATE` Errors On Deploy

Cause:

- Wrong credentials in `.env`
- Database/user not created
- Wrong DB driver

Fix:

Check:

```bash
grep '^DB_' "$APP_PATH/shared/.env"
mariadb -u "${APP_DB_USER}" -p"${APP_DB_PASS}" -e "SHOW DATABASES;"
```

### `502 Bad Gateway`

Cause:

- `php8.5-fpm` is not running
- Nginx socket path is wrong
- PHP crashed during boot

Fix:

```bash
systemctl status php8.5-fpm --no-pager
journalctl -u php8.5-fpm -n 100 --no-pager
nginx -t
tail -n 100 /var/log/nginx/webportfolio_error.log
```

### Site Loads Without CSS Or JS

Cause:

- The deploy workflow failed before `npm run build`
- Or the asset build step failed

Fix:

Check the GitHub Actions logs for the `Build Assets` step.

### Contact Form Queues But Does Not Send

Cause:

- Supervisor worker is not running

Fix:

```bash
supervisorctl status
tail -n 100 "$APP_PATH/shared/storage/logs/worker.log"
```

### Storage Write Failures

Cause:

- Permissions or ACLs were skipped

Fix:

```bash
chown -R deploy:www-data "$APP_PATH"
setfacl -R -m u:deploy:rwx -m u:www-data:rwx "$APP_PATH/shared"
setfacl -dR -m u:deploy:rwx -m u:www-data:rwx "$APP_PATH/shared"
```

### The Deploy Workflow Started Too Early

Cause:

- You pushed the workflow before secrets/server were ready

Fix:

- Let it fail or cancel it
- Finish this setup
- Rerun the workflow manually in GitHub Actions

## Optional Hardening After Go-Live

After everything is working, consider:

- Switching mail from `log` to a real provider
- Disabling password SSH auth in `/etc/ssh/sshd_config`
- Adding Fail2ban
- Adding backups for MariaDB and the shared `.env`
- Moving queue/cache/session to Redis later if traffic grows

## Current Repo Behavior

The repository now expects:

- PHP 8.5
- GitHub Actions to test and lint on pull requests
- GitHub Actions to deploy from `main`
- A release-based deployment into `/var/www/webportfolio`

## If You Get Stuck

The fastest things to inspect are:

```bash
systemctl status nginx php8.5-fpm mariadb supervisor --no-pager
supervisorctl status
tail -n 100 /var/log/nginx/webportfolio_error.log
tail -n 100 "$APP_PATH/shared/storage/logs/worker.log"
```

And in GitHub Actions:

- `Install Dependencies`
- `Build Assets`
- `Run Tests`
- `Upload release artifact to server`
- `Deploy release on server`
