# Security audit, 2026-10-28

## Result and scope

Two confirmed authorization defects are fixed with retained HTTP regressions. Dependency recommendations remain deferred for approval. Review is required. Nothing was merged or deployed.

Repository: `giantsoup/webportfolio`, public on GitHub. The reviewed base is refreshed `origin/main` at `d61cc0345ebb40c4b870a32433087d2d4c12bd52` on 2026-10-28. The PR identifies the final implementation commit.

Herd created the isolated `security-audit-20261028` branch and checkout. The original checkout started on `main` at `4ccd309feffbb6031d11c1c0205c4b0cd2ae0236`, with unrelated dependency, CI, README, local configuration, and infrastructure-test edits plus an untracked dependency plan. That work and all pre-existing checkouts were excluded.

Scope covers routes, Livewire actions, authentication, public rendering, validation, persistence, delivery, configuration, dependency advisories, tracked secret patterns, and deployment source. Production endpoints/data, cloud operations, credential validation/rotation, history rewriting, deployment, and browser interaction were excluded.

## Stack and isolation

PHP 8.5.12 executed Laravel 13.39.0, Fortify 1.41.0, Livewire 4.4.8, Flux 2.21.0, Pest 4, and PHPUnit 12. The frontend lockfile contains Tailwind CSS 4.3.3, Vite 8.0.2, Laravel Vite Plugin 3.2.0, and Axios 1.13.6. Views use Blade, Livewire, Flux, and bundled Alpine. `resources/js/app.js` imports the logo and has no Axios caller.

The Herd copy initially inherited older installed PHP packages. Installation from the unchanged lockfile with scripts and plugins disabled synchronized the isolated copy. All locked production package versions matched installed versions before reproductions. The final inventory supersedes the preliminary inventory.

Before application execution, startup providers, Composer hooks, test bootstrap, cached configuration, and all configured destinations were inspected. The copy uses a generated disposable key, SQLite `:memory:`, array cache/sessions/mail, sync queues, null logging/broadcasting, and its own local storage. No shared database, cache, storage symlink, or credential was retained. No cached config or routes were loaded. New HTTP tests block stray Laravel HTTP-client requests and fake mail where appropriate. Telemetry is disabled. Documentation and advisory requests were deliberate tooling calls, separate from application execution.

Configuration defines SQLite, MySQL, MariaDB, PostgreSQL, SQL Server, optional external caches/queues/mail providers, and local private/public plus S3 disks. Application callers do not explicitly select alternate database connections or remote disks. S3 is unused. The only application queued delivery is a configured-recipient inquiry mailable. These definitions do not prove deployed services or privileges.

## Fixed findings

### SEC-01: access changes were not enforced on Livewire updates

Confirmed and fixed. Severity is high because a previously privileged authenticated session could retain private inquiry access and administrative writes after privilege removal. Prior authorized component state is a prerequisite.

Rule sources are the private-back-office requirement in `README.md`, `routes/web.php`, `routes/settings.php`, `EnsureUserIsAdmin`, and `User` implementing `MustVerifyEmail`. Page middleware checked these rules, but the application had not registered those middleware for Livewire persistence.

The original HTTP reproducer used real page GETs and server-signed snapshots. Updates returned 200 after synthetic admin revocation or loss of email verification. Both denial assertions failed; the permitted control passed. Authorization middleware and queries were not mocked.

At `app/Providers/AppServiceProvider.php:36`, the patch persists `EnsureUserIsAdmin` and `EnsureEmailIsVerified` only where the originating route already requires them. Final HTTP tests cover all 12 private component pages after either status change, plus permitted controls. Inquiry writes assert unchanged fields and denial output. Logout replay and checksum tampering are denied.

Retained tests: `tests/Feature/Security/LivewireAccessBoundaryTest.php` and `RouteAccessTest.php`.

### SEC-02: expired password confirmation permitted two-factor changes

Confirmed and fixed. Severity is medium because an existing authenticated session could bypass the configured reauthentication requirement for sensitive account changes.

`config/fortify.php` enables `confirmPassword` and `routes/settings.php` requires it for security settings. The original HTTP action disabled synthetic two-factor authentication after confirmation expired. The denial regression expected 423 and received 200; the confirmed-session control passed.

Persisting Laravel's `RequirePassword` alone was insufficient for JSON requests. In this installed Livewire version, `Utils::applyMiddleware` aborts redirects but discards normal JSON denial responses.

The adapter at `app/Http/Middleware/EnsurePasswordIsConfirmed.php:20` delegates to Laravel's configured middleware and aborts with its 423 response. `routes/settings.php:21` applies it under the existing Fortify feature condition; `AppServiceProvider.php:39` persists it. Fortify's own HTTP endpoints are unchanged.

Final tests cover allowed and denied two-factor disable actions, nested recovery-code regeneration, unchanged database values after denial, custom 60-second and default 10,800-second timeouts, and HTML redirection to password confirmation. Existing account settings tests pass.

Retained test: `tests/Feature/Security/LivewireAccessBoundaryTest.php`.

## Deferred recommendations

### SEC-03: update Vite and review development-server exposure

The affected locked version is confirmed; live exploitation was not tested. Project severity is medium for development exposure. Upstream rates several advisories high.

`package-lock.json` pins Vite 8.0.2. npm audit reports 10 Vite advisory entries. The maintainer's [file-read advisory](https://github.com/vitejs/vite/security/advisories/GHSA-p9ff-h696-f583) includes this version. Other entries depend on platform and development-server conditions.

`vite.config.js` enables unrestricted CORS, without a documented need for every origin. Production deployment builds static assets and rejects `public/hot`; this is not evidence of a production Vite server.

Approve an update to a maintained patched Vite release, review compatibility with the existing plugin and Node constraints, and restrict CORS to required local origins or intended plugin defaults. Coordinate with ongoing dependency work. Acceptance requires a clean install, production asset build, applicable advisory scan, no release hot file, and browser verification of allowed and denied origins.

Deferred because project instructions require approval for dependency changes. No manifests, lockfiles, or Vite configuration were changed.

### SEC-04: remove or update unused Axios

The affected locked version is confirmed; no reachable application exploit was established. Project severity is low for this unused dependency.

npm audit reports 34 Axios advisory entries for 1.13.6. No Axios import or caller was found in application entry points. Several advisories concern Node HTTP adapters, absent from this PHP backend. The maintainer's [HTTP/2 advisory](https://github.com/axios/axios/security/advisories/GHSA-3pq3-5fj3-cg6v) illustrates a condition that does not match the discovered runtime.

Approve removal if unused, or update to a patched supported version if needed. Acceptance requires a clean install, source-use review, asset build, and fresh advisory scan. Affected dependency presence is not a confirmed production SSRF path. Deferred for dependency approval and coordination with ongoing maintenance.

## Route inventory and access rules

Final `route:list --json -vv` inventories contain 61 registrations and 113 explicit method entries in testing and production simulations. Local registration adds only the Boost browser-log POST, yielding 62 registrations and 114 method entries. Production simulation used safe local resources, not the live server.

All 113 production-shaped method entries were source-reviewed. 58 were exercised by at least one HTTP test; 55 remain source-only. Execution counts do not imply complete scenario coverage. Package assets and several Fortify management endpoints remain source-only. No route inventory counts as browser or production proof.

All route domains are unrestricted at Laravel's layer. Authenticated routes use the `web` session guard. Public/assets/signed-object routes have no route authentication guard. The Livewire update route derives authentication and authorization from its signed originating page.

The initial access matrix was prepared before reproductions and reconciled with the synchronized package inventory and middleware changes. Every registration is listed below. The HTTP column lists exercised methods; all other methods have source-only evidence.

Shared audience, scope, scenario, and rule-source definitions:

- P: public published portfolio fields. Drafts and future projects denied. Source: public controllers and `Project::published`. Contact accepts validated inquiry fields, forces new status, and sends to the configured owner. Contact POST has CSRF and a five-per-minute IP limit.
- A: current verified admin across portfolio records; account settings affect the current user. Guests redirect to login, non-admins receive 403, unverified users redirect to verification, and valid admins are allowed. Security adds current password confirmation. Source: private routes and middleware.
- F: guest login/recovery or the current authenticated user's own identity and credentials, as middleware specifies. Valid/invalid credentials, recovery codes, tokens, and signatures are relevant controls. Untested scenarios remain gaps. Source: Fortify routes/controllers/config.
- L: valid server-signed component state plus original page access rules. Role changes, verification changes, logout, tampering, and confirmation expiry are tested. Source: Livewire checksum/persistent middleware.
- U: signed bounded temporary upload or signed preview. Unsigned requests denied. Authorized multipart upload and preview rendering remain source-only. Source: Livewire upload controllers and project image validation.
- S: object-specific signed private local storage. Unsigned/expired reads and unsigned writes denied; signed read/write controls succeed. No application issuer for these private URLs was found. Source: filesystem handlers and disk config.
- X: public package assets, no application records. Paths are fixed or constrained to compiled components and country codes. Source: installed asset handlers.
- H: public health response, no application records. Source: `bootstrap/app.php`.


| Methods | Path | Route name | Action | Middleware | Rule | HTTP methods exercised |
| --- | --- | --- | --- | --- | --- | --- |
| GET, HEAD | `/` | `home` | `Controllers\HomeController` | M1 | P | GET, HEAD |
| GET, HEAD | `/contact` | `contact.create` | `Controllers\ContactController@create` | M1 | P | GET, HEAD |
| POST | `/contact` | `contact.store` | `Controllers\ContactController@store` | M2 | P | POST |
| GET, HEAD | `/dashboard` | `dashboard` | `AppLivewire\Admin\Dashboard` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/inquiries` | `admin.inquiries.index` | `AppLivewire\Admin\Inquiries\Index` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/projects` | `admin.projects.index` | `AppLivewire\Admin\Projects\Index` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/projects/create` | `admin.projects.create` | `AppLivewire\Admin\Projects\Form` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/projects/{project}/edit` | `admin.projects.edit` | `AppLivewire\Admin\Projects\Form` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/settings` | `admin.settings.edit` | `AppLivewire\Admin\Settings\Edit` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/skills` | `admin.skills.index` | `AppLivewire\Admin\Skills\Index` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/skills/create` | `admin.skills.create` | `AppLivewire\Admin\Skills\Form` | M3 | A | GET, HEAD |
| GET, HEAD | `/dashboard/skills/{skill}/edit` | `admin.skills.edit` | `AppLivewire\Admin\Skills\Form` | M3 | A | GET, HEAD |
| POST | `/email/verification-notification` | `verification.send` | `Fortify\EmailVerificationNotificationController@store` | M4 | F | source only |
| GET, HEAD | `/email/verify` | `verification.notice` | `Fortify\EmailVerificationPromptController@__invoke` | M5 | F | GET |
| GET, HEAD | `/email/verify/{id}/{hash}` | `verification.verify` | `Fortify\VerifyEmailController@__invoke` | M6 | F | GET |
| GET, HEAD | `/flux/editor.css` | `-` | `Flux\AssetManager@editorCss` | M7 | X | source only |
| GET, HEAD | `/flux/editor.js` | `-` | `Flux\AssetManager@editorJs` | M7 | X | source only |
| GET, HEAD | `/flux/editor.min.js` | `-` | `Flux\AssetManager@editorMinJs` | M7 | X | source only |
| GET, HEAD | `/flux/flags/{country}` | `__flux.flag` | `Flux\AssetManager@flag` | M7 | X | source only |
| GET, HEAD | `/flux/flux.js` | `-` | `Flux\AssetManager@fluxJs` | M7 | X | source only |
| GET, HEAD | `/flux/flux.min.js` | `-` | `Flux\AssetManager@fluxMinJs` | M7 | X | source only |
| GET, HEAD | `/flux/phone-utils.js` | `-` | `Flux\AssetManager@phoneUtilsJs` | M7 | X | source only |
| GET, HEAD | `/flux/phone.js` | `-` | `Flux\AssetManager@phoneJs` | M7 | X | source only |
| GET, HEAD | `/flux/phone.min.js` | `-` | `Flux\AssetManager@phoneMinJs` | M7 | X | source only |
| GET, HEAD | `/forgot-password` | `password.request` | `Fortify\PasswordResetLinkController@create` | M8 | F | GET |
| POST | `/forgot-password` | `password.email` | `Fortify\PasswordResetLinkController@store` | M8 | F | POST |
| GET, HEAD | `/livewire-43a2fa80/css/{component}.css` | `-` | `Closure` | M7 | X | source only |
| GET, HEAD | `/livewire-43a2fa80/css/{component}.global.css` | `-` | `Closure` | M7 | X | source only |
| GET, HEAD | `/livewire-43a2fa80/js/{component}.js` | `-` | `Closure` | M7 | X | source only |
| GET, HEAD | `/livewire-43a2fa80/livewire.csp.min.js.map` | `-` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@cspMaps` | M7 | X | source only |
| GET, HEAD | `/livewire-43a2fa80/livewire.min.js` | `-` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@returnJavaScriptAsFile` | M7 | X | source only |
| GET, HEAD | `/livewire-43a2fa80/livewire.min.js.map` | `-` | `Livewire\Mechanisms\FrontendAssets\FrontendAssets@maps` | M7 | X | source only |
| GET, HEAD | `/livewire-43a2fa80/preview-file/{filename}` | `livewire.preview-file` | `Livewire\Features\SupportFileUploads\FilePreviewController@handle` | M1 | U | GET |
| POST | `/livewire-43a2fa80/update` | `default-livewire.update` | `Livewire\Mechanisms\HandleRequests\HandleRequests@handleUpdate` | M9 | L | POST |
| POST | `/livewire-43a2fa80/upload-file` | `livewire.upload-file` | `Livewire\Features\SupportFileUploads\FileUploadController@handle` | M10 | U | POST |
| GET, HEAD | `/login` | `login` | `Fortify\AuthenticatedSessionController@create` | M8 | F | GET |
| POST | `/login` | `login.store` | `Fortify\AuthenticatedSessionController@store` | M11 | F | POST |
| POST | `/logout` | `logout` | `Fortify\AuthenticatedSessionController@destroy` | M5 | F | POST |
| POST | `/reset-password` | `password.update` | `Fortify\NewPasswordController@store` | M8 | F | POST |
| GET, HEAD | `/reset-password/{token}` | `password.reset` | `Fortify\NewPasswordController@create` | M8 | F | GET |
| GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS, QUERY | `/settings` | `-` | `Illuminate\Routing\RedirectController` | M3 | A | GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS, QUERY |
| GET, HEAD | `/settings/appearance` | `appearance.edit` | `Livewire\Mechanisms\HandleRouting\LivewirePageController` | M3 | A | GET, HEAD |
| GET, HEAD | `/settings/profile` | `profile.edit` | `Livewire\Mechanisms\HandleRouting\LivewirePageController` | M3 | A | GET, HEAD |
| GET, HEAD | `/settings/security` | `security.edit` | `Livewire\Mechanisms\HandleRouting\LivewirePageController` | M12 | A | GET, HEAD |
| GET, HEAD | `/storage/{path}` | `storage.local` | `Closure` | M7 | S | GET |
| PUT | `/storage/{path}` | `storage.local.upload` | `Closure` | M7 | S | PUT |
| GET, HEAD | `/two-factor-challenge` | `two-factor.login` | `Fortify\TwoFactorAuthenticatedSessionController@create` | M8 | F | GET |
| POST | `/two-factor-challenge` | `two-factor.login.store` | `Fortify\TwoFactorAuthenticatedSessionController@store` | M13 | F | POST |
| GET, HEAD | `/up` | `-` | `Illuminate\Foundation\Http\HealthCheck` | M7 | H | source only |
| GET, HEAD | `/user/confirm-password` | `password.confirm` | `Fortify\ConfirmablePasswordController@show` | M5 | F | GET |
| POST | `/user/confirm-password` | `password.confirm.store` | `Fortify\ConfirmablePasswordController@store` | M5 | F | source only |
| GET, HEAD | `/user/confirmed-password-status` | `password.confirmation` | `Fortify\ConfirmedPasswordStatusController@show` | M5 | F | source only |
| POST | `/user/confirmed-two-factor-authentication` | `two-factor.confirm` | `Fortify\ConfirmedTwoFactorAuthenticationController@store` | M14 | F | source only |
| POST | `/user/two-factor-authentication` | `two-factor.enable` | `Fortify\TwoFactorAuthenticationController@store` | M14 | F | source only |
| DELETE | `/user/two-factor-authentication` | `two-factor.disable` | `Fortify\TwoFactorAuthenticationController@destroy` | M14 | F | source only |
| GET, HEAD | `/user/two-factor-qr-code` | `two-factor.qr-code` | `Fortify\TwoFactorQrCodeController@show` | M14 | F | source only |
| GET, HEAD | `/user/two-factor-recovery-codes` | `two-factor.recovery-codes` | `Fortify\RecoveryCodeController@index` | M14 | F | source only |
| POST | `/user/two-factor-recovery-codes` | `two-factor.regenerate-recovery-codes` | `Fortify\RecoveryCodeController@store` | M14 | F | source only |
| GET, HEAD | `/user/two-factor-secret-key` | `two-factor.secret-key` | `Fortify\TwoFactorSecretKeyController@show` | M14 | F | source only |
| GET, HEAD | `/work` | `projects.index` | `Controllers\ProjectController@index` | M1 | P | GET, HEAD |
| GET, HEAD | `/work/{project:slug}` | `projects.show` | `Controllers\ProjectController@show` | M1 | P | GET, HEAD |

Action abbreviations: `Controllers` means `App\Http\Controllers`, `AppLivewire` means `App\Livewire`, and `Fortify` means `Laravel\Fortify\Http\Controllers`. Other namespaces are registered names. The Livewire prefix depends on checkout configuration.

### Expanded middleware groups

- M1: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Routing\Middleware\SubstituteBindings`.
- M2: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Routing\Middleware\ThrottleRequests:portfolio-contact`, `Illuminate\Routing\Middleware\SubstituteBindings`.
- M3: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Auth\Middleware\Authenticate`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Illuminate\Auth\Middleware\EnsureEmailIsVerified`, `App\Http\Middleware\EnsureUserIsAdmin`.
- M4: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Auth\Middleware\Authenticate:web`, `Illuminate\Routing\Middleware\ThrottleRequests:6,1`, `Illuminate\Routing\Middleware\SubstituteBindings`.
- M5: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Auth\Middleware\Authenticate:web`, `Illuminate\Routing\Middleware\SubstituteBindings`.
- M6: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Auth\Middleware\Authenticate:web`, `Illuminate\Routing\Middleware\ThrottleRequests:6,1`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Illuminate\Routing\Middleware\ValidateSignature`.
- M7: none; controller enforces the stated boundary.
- M8: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Illuminate\Auth\Middleware\RedirectIfAuthenticated:web`.
- M9: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders`.
- M10: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Routing\Middleware\ThrottleRequests:60,1`, `Illuminate\Routing\Middleware\SubstituteBindings`.
- M11: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Routing\Middleware\ThrottleRequests:login`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Illuminate\Auth\Middleware\RedirectIfAuthenticated:web`.
- M12: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Auth\Middleware\Authenticate`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Illuminate\Auth\Middleware\EnsureEmailIsVerified`, `App\Http\Middleware\EnsureUserIsAdmin`, `App\Http\Middleware\EnsurePasswordIsConfirmed`.
- M13: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Routing\Middleware\ThrottleRequests:two-factor`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Illuminate\Auth\Middleware\RedirectIfAuthenticated:web`.
- M14: `Illuminate\Cookie\Middleware\EncryptCookies`, `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse`, `Illuminate\Session\Middleware\StartSession`, `Illuminate\View\Middleware\ShareErrorsFromSession`, `Illuminate\Foundation\Http\Middleware\PreventRequestForgery`, `Illuminate\Auth\Middleware\Authenticate:web`, `Illuminate\Routing\Middleware\SubstituteBindings`, `Illuminate\Auth\Middleware\RequirePassword`.

## Non-route coverage

| Component | Disposition and evidence |
| --- | --- |
| Admin components and dashboard | Source-reviewed. All 12 page variants tested for GET/HEAD access and HTTP Livewire refresh after role/verification changes. Inquiry writes tested through HTTP. Existing project/skill/settings save component tests pass; those alone do not prove route permissions. |
| Account settings and logout | Source-reviewed. Existing component tests cover profile/password/deletion. HTTP replay covers expiry, nested recovery changes, logout, and tampering. Full TOTP setup and QR rendering not browser-tested. |
| Blade, mail templates, Alpine, JS | Source-reviewed. Stored project markup escaping tested in HTTP output. The only raw Blade HTML is generated QR SVG. URLs and skill colors have validation. Browser DOM behavior and external font delivery not exercised. |
| Local private/public storage and uploads | Source-reviewed. Signed private read/write controls and unsigned denials use isolated local filesystem fakes. Public images are intentionally public and project image validation is bounded. Provider ACLs, multipart upload acceptance, and web-server symlink behavior not exercised. |
| Database and models | Source-reviewed. SQLite tests cover visibility, forced inquiry fields, privileged user mass-assignment rejection, and denied writes. The inquiry raw ordering expression is constant; no user-controlled raw SQL found. Other engines, connection privileges, and production data not inspected. |
| Inquiry mail and queues | Source-reviewed. Existing HTTP test proves queued delivery intent with a fake. Recipient comes from config; templates escape input. Worker operation, provider acceptance, and receipt not tested. |
| Cache, sessions, logs, optional integrations | Config/callers source-reviewed; test stores isolated. Session defaults include HttpOnly, SameSite lax, and JSON serialization. Live Secure flags, TLS, trusted hosts/proxies, backend reachability, retention, and cross-environment separation unverified. |
| Admin bootstrap CLI and schedules | Source-reviewed. Existing command tests cover validation, promotion, verification, and non-interactive use. CLI is an operator boundary. Console routes contain the example inspire command; no application scheduled jobs found. |
| Static/public files and deployment | Source-reviewed. No tracked private database/upload/log/credential under public. Apache instructions disable indexing where honored. Deployment source uses static builds, main-only push deployment, read-only CI token permissions, known-host verification, external shared environment, and no-dev Composer install. Actual web server, cloud IAM, release artifacts, secrets, and branch protections unverified. |
| Secrets | Limited redacted current-tree token/key patterns and manual outgoing review found no matches. Gitleaks/TruffleHog unavailable. Full history, entropy scanning, and remote secret stores not scanned. |
| Absent application entry points | No API tokens, OAuth, impersonation, organization scoping, broadcast channels, GraphQL, webhooks, custom queued jobs beyond mail, exports/imports, search, parsers, archive extraction, or process-execution endpoints found. Not applicable to discovered source; infrastructure not inferred. |

Registration and Fortify profile/password-update features are disabled. Separate protected Livewire components implement account changes. Passkeys is installed but has no enabled application routes. Public registration-unavailable tests pass.

## Executed checks and limitations

- `herd php artisan test --compact`: 236 passed, 2 skipped, 961 assertions. The two skips require disabled public registration. JUnit has zero failures and zero errors.
- New security tests contain 185 executed scenarios across four files. Authorization claims use actual registered HTTP routes with the middleware stack enabled.
- Initial five-scenario reproducer: 3 failed, 2 passed before fixes. After registering password middleware alone, expiry still failed, exposing Livewire's JSON-response behavior. Final regressions and permitted controls pass.
- The CSRF test enables normal environment behavior and pairs tokenless denial with an accepted token control. Contact tests cover privileged field injection and rate limits. Authentication tests cover login throttling and one-time recovery-code use.
- `herd php vendor/bin/pint --dirty --format agent`: passed after final edits. `git diff --check`: passed.
- `herd composer --no-plugins --no-scripts audit --locked --format=json`: no advisories or abandoned packages in the queried feed.
- `npm audit --json --ignore-scripts`: exit 1, two affected direct packages at high upstream aggregate severity. The 44 Vite/Axios advisory entries are separated from confirmed reachable application defects above. No automatic fixes run.
- Laravel Boost MCP `search-docs` was called through the isolated stdio server. [Livewire 4 security](https://livewire.laravel.com/docs/4.x/security), [Laravel 13 HTTP tests](https://laravel.com/docs/13.x/http-tests), [Fortify](https://laravel.com/docs/13.x/fortify), and maintainer advisories informed the review.
- No browser, production, real cloud storage, remote database engine, queue worker, or mail-delivery proof is claimed. Production config caching and server aliases were not exercised. No frontend assets changed and no asset build ran. Remote CI is not claimed passed.

The branch and isolated checkout remain for review. Dependency work requires approval. Production configuration and normal release checks remain prerequisites for future activation. These findings and tests do not establish that the full application or deployment is secure.
