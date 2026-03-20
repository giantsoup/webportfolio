# Web Portfolio

Personal portfolio and case-study site built with Laravel, Livewire, Flux UI, and Tailwind CSS.

This application combines a public-facing portfolio with a private admin area for managing featured work, skills, portfolio copy, and inbound contact inquiries.

## Features

- Public landing page with a structured hero, featured skills, and featured projects
- Project archive and individual project detail pages
- Contact form with validation, throttling, queued mail delivery, and inquiry persistence
- Private admin dashboard for projects, skills, inquiries, and portfolio settings
- Fortify-powered authentication with email verification and two-factor support
- Seeded demo content for local development

## Stack

- PHP 8.5
- Laravel 13
- Livewire 4
- Flux UI 2
- Tailwind CSS 4
- Pest for automated testing

## Local Setup

This repository is pinned to PHP 8.5. If you use Laravel Herd or Valet, the included `.valetrc` requests PHP 8.5 for the project.

Flux UI is a paid package. Before your first `composer install`, make sure your machine has valid Flux Composer credentials configured.

```bash
composer config http-basic.composer.fluxui.dev <flux-username> <flux-license-key>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

If you want the full local development stack running together:

```bash
composer run dev
```

That starts the Laravel server, queue listener, log tailing, and Vite dev server in one command.

## Seeded Admin Login

Local development seeding creates an admin account:

- Email: `test@example.com`
- Password: `password`

## Contact Workflow Notes

- Contact submissions are stored in the database as inquiries
- Outbound inquiry mail is queued
- Set `PORTFOLIO_CONTACT_RECIPIENT_NAME` and `PORTFOLIO_CONTACT_RECIPIENT_ADDRESS` in your environment if you want contact emails delivered

## Quality Checks

Run the minimum checks you need while working:

```bash
php artisan test --compact
composer lint
```

GitHub Actions are included for both linting and automated tests on push and pull request events.

## Repository

- GitHub: [giantsoup/webportfolio](https://github.com/giantsoup/webportfolio)
