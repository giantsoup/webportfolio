# Web Portfolio

Taylor's personal portfolio and case-study site, built with Laravel and Livewire. Public pages present selected projects and skills. A private dashboard manages portfolio content and contact inquiries.

## Features

- Home page with portfolio copy, featured skills, and selected projects.
- Paginated project archive at `/work` and project details at `/work/{slug}`.
- Project publishing controls, featured images, skill associations, and repository, live-site, and case-study links.
- Contact form with validation, a limit of five submissions per minute per IP address, stored inquiries, and queued email notifications.
- Admin dashboard for projects, skills, inquiry statuses, portfolio copy, and social links.
- GitHub import: pick projects from the owner's public repositories, import them one at a time or all at once as drafts, and keep stars, language, topics, last push, and the README in sync. Published project pages show the GitHub stats.
- Fortify authentication with email verification and optional two-factor authentication. Public registration and password resets by email are disabled; reset a password from the server with `app:make-admin`.

## Requirements

| Tool | Version or requirement |
| --- | --- |
| PHP | 8.5, as required by `composer.json` and `.valetrc` |
| Composer | 2.x |
| Node.js | 26.13.0, as specified by `.nvmrc`, `.node-version`, and `package.json` |
| npm | 12.2.1, recorded in `packageManager`; use the committed `package-lock.json` with `npm ci` |
| Database | SQLite by default, with PHP's PDO SQLite extension enabled |

The application uses Laravel 13, Livewire 4, Flux UI 2 Free, Fortify 1, Tailwind CSS 4, and Vite 8. Tests use Pest 4 and PHPUnit 12. PHP formatting uses Laravel Pint.

The installed `livewire/flux` package does not require paid Flux Composer credentials.

## Set up locally

Run these steps from the repository root on a fresh local checkout. Keep an existing `.env` and application key when updating an existing installation.

1. Select the required runtimes. If you use nvm, run `nvm use`. Confirm that `php --version` reports PHP 8.5, `node --version` reports Node.js 26.13.0, and `npm --version` reports 12.2.1.

   If the selected Node installation still has its bundled npm, run `npm install --global npm@12.2.1` after `nvm use`. This installs npm into that selected Node version's prefix. The CI build jobs select the same Node and npm versions explicitly.

   Herd provides a `php85` binary if your default `php` points to another version. Composer and its scripts also need PHP 8.5 on their execution path.

2. Install the locked dependencies and create the local environment file.

   ```bash
   composer install
   cp .env.example .env
   npm ci
   ```

3. Set `APP_NAME` and `APP_URL` in `.env`. For this Herd checkout, use `APP_URL=http://webportfolio.test`. The example environment defaults to SQLite, database-backed sessions, cache, and queues, and the log mailer.

4. Initialize the local database and application key, then build the assets.

   ```bash
   touch database/database.sqlite
   php artisan key:generate --no-interaction
   php artisan migrate --seed --no-interaction
   php artisan storage:link --no-interaction
   npm run build
   ```

   `storage:link` exposes uploaded project images from the public disk. The seeders create portfolio settings, curated projects for Print for Me, Arbor XML Viewer, and dbgold, and the skill catalog. They do not create a user account.

   Reseeding updates curated content and project skill associations, deletes obsolete seeded projects, and removes skills outside the seeded catalog. Run seeders only when you intend those changes.

5. Create an administrator with the interactive command.

   ```bash
   php artisan app:make-admin
   ```

6. Open [the local portfolio](http://webportfolio.test). Sign in at `/login` and open `/dashboard` to manage content.

Herd serves this checkout directly. No separate application server is needed.

The `composer run setup` shortcut installs dependencies, creates `.env` if missing, generates a key, migrates, and builds assets. It does not seed content, create an admin, or create the public storage link. Use it only for initial setup because it generates an application key.

## Develop locally

With Herd serving the application, run Vite in one terminal:

```bash
npm run dev
```

Run the queue listener in another terminal to process contact notifications:

```bash
php artisan queue:listen --tries=1 --timeout=0 --no-interaction
```

Optionally, follow application logs with `php artisan pail --no-interaction`.

For development without Herd, `composer run dev` starts Laravel's development server, the queue listener, Pail, and Vite together.

## Manage admin access and content

`app:make-admin` creates a new administrator or promotes an existing user by email. It marks the account verified by default. For an existing user, it preserves the password unless you choose to replace it.

To require email verification and send the verification message, use:

```bash
php artisan app:make-admin --unverified --send-verification
```

Configure mail delivery before using that option. Enable two-factor authentication under **Settings > Security** after signing in. Dashboard and account settings routes require an authenticated, verified administrator.

To reset a forgotten password, run the command on the server from the live release with the account's email, answer **yes** when asked to set a new password, and enter it twice at the hidden prompts:

```bash
cd "$DEPLOY_PATH/current" && php artisan app:make-admin owner@example.com
```

In production, passwords need at least 12 characters with mixed case, a number, and a symbol, and must not appear in known breaches. Avoid `--password`, which leaves the password in shell history. Resetting the password does not turn off two-factor authentication.

Use the dashboard to manage projects, skills, inquiries, and portfolio settings. A project appears publicly only when it is published and its publication date has arrived. The home page shows up to three published projects, ordered by featured status, sort order, and publication date.

## Configure contact email

Set these values in `.env` to choose the notification recipient:

```dotenv
PORTFOLIO_CONTACT_RECIPIENT_ADDRESS=owner@example.com
PORTFOLIO_CONTACT_RECIPIENT_NAME="Portfolio owner"
```

These optional variables are read by [config/portfolio.php](config/portfolio.php) and are not included in `.env.example`. The recipient address falls back to `MAIL_FROM_ADDRESS`. The recipient name falls back to `MAIL_FROM_NAME`, although the current contact mail delivery uses only the address.

The default `MAIL_MAILER=log` writes mail to the configured application log instead of delivering it. For delivery, configure a supported mail transport and its credentials in `.env`, including a valid sender address. Set `APP_URL` to the site's actual origin so authentication emails contain the correct links.

Contact submissions are saved before notifications are queued. With the default `QUEUE_CONNECTION=database`, a running worker or listener is required to process notifications. If no recipient address is configured, the inquiry is stored without queuing mail.

## Connect GitHub

The dashboard's **Projects > Import from GitHub** page lists the public repositories owned by one GitHub account. Set the account and an optional token in `.env`:

```dotenv
GITHUB_USERNAME=giantsoup
GITHUB_TOKEN=
```

Public repositories can be listed without a token, but GitHub then allows only 60 requests an hour per IP address. A token raises that to 5,000. Create a fine-grained personal access token with **Public repositories (read-only)** access and no extra permissions. Private repositories are never listed.

- **Import** opens a new project prefilled from the repository: title, slug, summary, README as the body, links, language as the category, and skills matched from topics and languages. Nothing is saved until you save the form.
- **Import all** creates unpublished drafts for every listed repository that is not imported yet, and links existing projects whose repository URL already points at one.
- **Link** connects an existing project to its repository without changing its written copy.
- Each project form has a **GitHub** panel to link or unlink a repository, refresh its stats, preview the README, and copy the README into the body.

`php artisan app:sync-github-projects` refreshes every linked project and links projects whose repository URL matches. The scheduler runs it daily at 04:00, which needs Laravel's scheduler cron on the server:

```cron
* * * * * cd /path/to/deploy/current && php artisan schedule:run >> /dev/null 2>&1
```

The repository listing is cached for ten minutes; the import page's **Refresh** button clears it. If the token expires, the import page reports that GitHub could not be reached and the sync command fails until the token is replaced.

## Run checks

Run the application tests:

```bash
php artisan test --compact
```

Run an affected test file while developing, for example:

```bash
php artisan test --compact tests/Feature/Portfolio/ContactTest.php
```

[phpunit.xml](phpunit.xml) configures an in-memory SQLite database, array mail, synchronous queues, and empty GitHub settings for tests, and the base test case blocks real HTTP requests, so GitHub is always faked. The suite covers public pages, contact validation and throttling, admin access and content management, the GitHub integration, authentication, seeders, and workflow configuration.

Use `composer lint:check` to check PHP formatting without changes, or `composer lint` to apply formatting. `composer test` clears the configuration cache, checks formatting, and runs the test suite. Use `npm run build` to check the production asset build.

## CI and deployment

The [test workflow](.github/workflows/tests.yml) and [lint workflow](.github/workflows/lint.yml) run on pull requests targeting `develop`, `main`, `master`, and `workos`, and on manual dispatch.

The [deployment workflow](.github/workflows/deploy.yml) runs on pushes to `main` or manual dispatch. Its verification job checks formatting, builds assets, and runs tests before packaging a release. The deployment job uses the GitHub `production` environment and requires these secrets:

- `PROD_SSH_KEY`
- `PROD_SSH_KNOWN_HOSTS`
- `PROD_PORT`
- `PROD_USER`
- `PROD_HOST`
- `PROD_DEPLOY_PATH`

The [deployment script](.github/scripts/deploy.sh) expects PHP 8.5, Composer 2, a `www-data` group, and a preconfigured `$DEPLOY_PATH/shared/.env` on the server. Point the web server's document root at `$DEPLOY_PATH/current/public`. Configure production database and mail access, set `APP_ENV=production` and `APP_DEBUG=false`, supervise a queue worker separately, and add the scheduler cron described in [Connect GitHub](#connect-github).

Deployments cache configuration, so after editing the shared `.env` later, run `php artisan config:cache` from `$DEPLOY_PATH/current` for the change to take effect.

Each deployment installs production Composer dependencies, runs migrations, caches application configuration and icons, activates a release through the `current` symlink, and reloads Laravel services. It shares public uploads and logs between releases and retains five releases by default. It does not run database seeders or create an administrator. If using SQLite in production, configure an absolute database path outside the release directories so the database persists across deployments.

## Project structure

- `app/Http/Controllers/` and `resources/views/portfolio/`: public pages and contact handling.
- `app/Livewire/Admin/` and `resources/views/livewire/admin/`: dashboard components.
- `app/Models/`: projects, skills, inquiries, portfolio settings, and users.
- `app/Services/GitHub/`: the GitHub API client, repository data, and project importer.
- `config/portfolio.php`: contact recipient configuration. GitHub settings live under `github` in `config/services.php`.
- `database/seeders/`: curated portfolio content.
- `routes/web.php` and `routes/settings.php`: public, dashboard, and account settings routes.
- `tests/Feature/`: application behavior and infrastructure checks.

Source repository: [giantsoup/webportfolio](https://github.com/giantsoup/webportfolio).
