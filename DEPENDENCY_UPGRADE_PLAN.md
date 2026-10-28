# Dependency and Node.js 26 upgrade implementation plan

Status: the local Node.js 26/npm 12 runtime switch is implemented and verified. Other dependency migration phases remain proposed for Taylor's review.

Prepared on 2026-10-28 for `/Users/Taylor/Sites/webportfolio`. This document was the only repository file created during planning. Taylor subsequently authorized the local Node.js 26/npm 12 switch and Herd verification. That authorization does not start the other dependency migration phases, publish a PR update, merge or deploy.

## Outcome and boundaries

Resolve the remaining npm audit findings, move the project to Node.js 26 with explicitly pinned npm 12, and complete the previously deferred upgrades where their owning packages support them. Preserve application behavior, PHP 8.5, Laravel 13, authentication settings, and the existing deployment model.

The approved implementation would be a dependency migration with explicit manifest and transitive-version changes. It would not be described as a lockfile-only point-release run. Every dependency in the previous deferred catalog has a disposition below. A registry's latest version is a candidate, not evidence that every parent accepts it.

Do not merge, enable auto-merge, deploy, change shared databases, remove existing runtimes, change global runtime defaults, or overwrite unrelated work as part of implementation. Release activation remains a separate decision.

## Verified starting point

- [Existing Draft PR #1](https://github.com/giantsoup/webportfolio/pull/1) has head `19e817b7149f30abab2ca716e9036d88051039e8`, based on main at `4ccd309feffbb6031d11c1c0205c4b0cd2ae0236`.
- That PR contains the compatible Composer and npm maintenance updates. Its GitHub quality and test checks passed on that exact head. It remains Draft, with browser verification and npm findings outstanding.
- The maintenance baseline has 51 passing tests, 195 assertions, and two registration tests skipped because registration is disabled. Composer audit is clear; npm reports Axios and Vite as two high-severity vulnerable-package entries.
- Both earlier isolated frontend proposals cleared npm audit and passed the build, peer-tree check, clean reinstall, and 51 application tests on Node 24. Those results are preliminary evidence. They do not establish Node 26 compatibility or candidate CI results.
- The working checkout contains unrelated changes in `.codex/config.toml`, `README.md`, and `tests/Feature/Infrastructure/WorkflowConfigurationTest.php`. Preserve those files' existing edits. Runtime work will eventually overlap the latter two paths, so final integration must reconcile their intent explicitly.
- Production receives an archive containing built assets. `.github/scripts/deploy.sh` does not invoke Node or npm; the Linux GitHub Actions verification job builds assets before uploading the release. Node parity is required on build machines, not automatically on the production PHP host.

## Target versions and decisions

Targets below were checked against official release metadata on the preparation date. Refresh availability, support status, requirements, and advisories before implementation. If the refreshed graph needs additional majors or unrelated changes, report that difference before expanding scope.

| Area | Proposed target | Intended change and constraint |
| --- | --- | --- |
| Node.js and npm | Node **26.13.0 LTS**, npm **12.2.1** | Select this pair through Herd's existing version manager for this project. It is already installed locally. Preserve other version prefixes and global defaults. |
| Axios | Remove unused root dependency | Reconfirm no application usage, remove its manifest entry, and let npm remove only unneeded transitives. Keep application code unchanged. |
| Vite | **8.3.5** | Retain the existing `^8.0.2` manifest constraint, which already admits the target. |
| Vite compiler chain | Rolldown **1.2.15**, pluginutils **1.0.1**, Oxc types **0.155.0** | Allow the recorded prerelease-to-stable and 0.x minor transitions, with matching native bindings and required graph changes. Keep Oxc at the version selected by Rolldown, rather than independently moving to 0.156.0. |
| Concurrently | **10.0.6** | Change `^9.2.1` to `^10.0.6`; accept its supported parent-owned CLI dependency graph. |
| Pest | **5.3.4** | Change the direct requirement from `^4.4.2` to `^5.3.4`. Upgrade its complete plugin family together. |
| Pest Laravel plugin | **5.0.1** | Change `^4.1.0` to `^5.0.1`. Requires Laravel `^13.23.0`; PR #1's Laravel 13.39.0 satisfies this prerequisite. |
| PHPUnit and parallel runner | PHPUnit **13.4.5**, ParaTest **7.27.4** | Resolve through Pest 5, including compatible coverage, PHPUnit utility, and Sebastian packages. Do not introduce an unnecessary direct PHPUnit requirement. |
| Hamcrest | **3.0.0** | Mockery 1.6.15 permits both 2.x and 3.x. Resolve this transitive migration with the test-tooling group. |
| Boost integration | Boost **2.10.3**, MCP **1.1.2**, Roster **1.0.0** | Existing Boost `^2.3.4` admits the target. Select MCP and Roster through their owning dependency graph; do not add direct root requirements solely to force transitives. |
| HTTP dependency family | Guzzle **8.3.1**, Promises **3.1.0**, PSR-7 **3.2.0**, URI Template **2.0.1** | Upgrade as one compatible group after Boost. Current Boost 2.4.8 restricts Guzzle to 7.x; Boost 2.10.3 and Laravel 13.39.0 permit the new family. |
| Numeric dependency | Brick Math **1.0.0** | Laravel 13.39.0 and Ramsey UUID 4.9.4 permit 1.x. Verify behavior through their integrations and any discovered direct use. |
| Optional/native npm packages | Versions selected by their supported parents | Preserve complete macOS, Linux, other-platform and duplicate lock instances. Keep Tailwind's required Lightning CSS 1.32 binding while permitting Vite's separate 1.33 graph. |

The Node release index records 26.13.0 LTS with bundled npm 11.20.0. Taylor subsequently selected npm 12 explicitly, so the project now targets npm 12.2.1 separately. npm 12 supports Node 26. Its install-script and remote-source security defaults remain enabled; review necessary script approvals individually and never use blanket security bypasses. Sources: [Node release index](https://nodejs.org/dist/index.json), [Node release policy](https://nodejs.org/en/about/previous-releases), [npm 12 changes](https://github.com/npm/cli/blob/v12.2.1/CHANGELOG.md).

The package-group prerequisites come from the [Pest upgrade guide](https://pestphp.com/docs/upgrade-guide), [Pest Laravel plugin requirements](https://raw.githubusercontent.com/pestphp/pest-plugin-laravel/v5.0.1/composer.json), [PHPUnit requirements](https://raw.githubusercontent.com/sebastianbergmann/phpunit/13.4.5/composer.json), [Laravel requirements](https://raw.githubusercontent.com/laravel/framework/v13.39.0/composer.json), [Boost requirements](https://github.com/laravel/boost/blob/v2.10.3/composer.json), [Ramsey UUID requirements](https://raw.githubusercontent.com/ramsey/uuid/4.9.4/composer.json), and [Mockery requirements](https://raw.githubusercontent.com/mockery/mockery/1.6.15/composer.json).

## Implementation sequence after approval

### 1. Preserve the baseline and isolate the work

1. Re-read current project instructions and activate the relevant Laravel, Pest, UI and agent-documentation skills for the files actually being changed.
2. Refresh remote state and verify PR #1's actual head and merge status. Start from its verified dependency baseline in an isolated checkout. If it remains open, keep its existing head as an ancestor and append the approved migration commits through normal, non-force pushes. If it has merged, verify ancestry before choosing a fresh branch from main.
3. Capture status, diffs, lockfiles, complete inventories, manifest hashes, runtime paths, and the selected versions. Use a separate checkout. Do not stash, copy, stage or commit the user's unrelated work.
4. Verify Herd's PHP isolation in that checkout. Use the original site's PHP 8.5 isolation, or the explicit existing Herd PHP 8.5 binary if necessary. Verify Composer's actual PHP runtime too. Keep Composer and PHP versions unchanged.
5. Install the committed locks with development dependencies using inspected hooks and safe test services. Repeat the existing complete baseline checks. Inspect CLI help before choosing flags. Do not use setup scripts that migrate or seed a shared database.

Gate: the exact baseline is recorded, required checks have known results, and unrelated changes remain intact.

### 2. Move build tooling to Node 26 and npm 12

1. Select the existing Herd-managed Node 26.13.0 installation, which already contains npm 12.2.1. For a fresh environment, install the official Node version and then pin npm 12.2.1 within its selected runtime. Preserve Node 24, other runtime prefixes and global defaults. Verify executable paths and both versions before dependency resolution.
2. Set `.nvmrc` and `.node-version` to the same exact `26.13.0` value. Change `package.json`'s Node engine to `>=26 <27`. Add the npm engine `^12.2.1`, record `packageManager: npm@12.2.1` and verify the actual version in CI; that metadata is not itself an installer or enforcement mechanism.
3. Update the tests and deploy-verification workflows to use `actions/setup-node` with `node-version-file: '.nvmrc'`. Keep the existing workflow action major unless a separately established compatibility issue requires changing it. Install npm 12.2.1 explicitly inside the isolated CI runner and verify the selected Node/npm versions before `npm ci`.
4. Update the existing infrastructure test to verify agreement among `.nvmrc`, `.node-version`, the engine range, package-manager metadata, workflows, and README. Follow the existing test conventions and preserve its unrelated current edits at integration time.
5. Update README's runtime and setup instructions. Refresh root lock metadata through npm without changing locked dependency versions in this runtime commit; compare all locked versions, integrity values and source references to enforce that boundary.
6. Perform fresh `npm ci`, production build, asset-manifest checks, platform/peer validation, and existing tests on Node 26 locally and in Linux CI. Inspect install-script warnings and native package availability. Do not enable all scripts, disable audits, ignore engines/platforms, or skip optional dependencies to conceal failures.
7. Keep production's existing built-artifact deployment model. Install Node 26 on a remote host only if inspection proves that host actually builds assets and the change is authorized. No production runtime installation is assumed.

Gate: exact Node/npm selection, clean install, unchanged package graph for this commit, successful builds and Linux verification. Node 24 remains available for rollback.

### 3. Resolve frontend findings and upgrade the dev-process launcher

1. Recheck Axios usage across application code, frontend entry points, tests, build configuration and package parents. Apply the reviewed removal only if it is still unused. Remove its dependency entry and unneeded transitive packages through npm. If a real call site has appeared, stop the removal and report the alternative Axios upgrade and its proxy major separately.
2. Update Vite to 8.3.5 within its existing direct constraint. Resolve the normal Rolldown/native-binding/Oxc graph. Do not force the old compiler into new Vite through overrides.
3. Read every release note across the Oxc 0.122.0 to 0.155.0 gap and review the declaration diff. Confirm the package's actual application and parent usage. Complete the original 0.x rubric with real behavior, compatibility, baseline/final, security and reinstall evidence. Require at least 95/100; do not fill missing evidence with assumptions.
4. Review every graph addition, removal, relocated duplicate and native binding, including platforms unavailable locally. Ensure the optional direct Linux packages remain compatible with the parents that consume them. Do not deduplicate incompatible Lightning CSS versions by force.
5. Upgrade Concurrently to 10.0.6 in its own commit and review the v10 migration notes. Let it select its supported Chalk, Yargs, supports-color and related packages. Do not independently force those transitives to registry-latest majors.
6. Verify Vite development asset transformation, actual HMR, production entry points, emitted image/CSS/JS and manifest paths. Use Herd for Laravel; do not launch a second Laravel web server. Test Concurrently with harmless child processes for names/colors, exit codes, interruption and `--kill-others`, avoiding the existing dev command's real queue worker against shared services.
7. Exercise the rendered public pages, contact form, authentication views, settings and admin controls in Chrome with isolated local fixtures. Check desktop/mobile layouts, console/network errors, Livewire updates, navigation and modal/validation interactions. Compare asset size and CSS changes and investigate unexplained regressions.

Gate: zero npm findings, successful clean reinstall and peer checks, completed Oxc evidence, functional Vite/HMR checks, dev-process lifecycle checks, and rendered behavior evidence on the final frontend set.

### 4. Migrate the PHP test stack together

1. Keep Laravel at the verified 13.39 baseline or a separately reviewed compatible 13.x refresh before Pest migration. Do not try the Pest Laravel plugin against main's older Laravel 13.1.1 lock.
2. Update the two direct Pest requirements to the targets above. Resolve Pest's core plugin, Laravel plugin, architecture, mutation and profanity plugins, PHPUnit 13, ParaTest, coverage and Sebastian packages together.
3. Select Hamcrest 3 through Mockery's supported range. Read relevant migration notes, identify actual matcher/mock use, and verify that integration even if the application has no direct Hamcrest imports.
4. Review PHPUnit 13's assertion, mock, configuration and deprecation changes before adapting tests. Preserve existing assertions, test intent, authentication behavior, and the two intentional registration skips. Do not delete tests, add broad skips or weaken assertions to make the migration pass.
5. Change only tests/configuration that the migration actually requires. Run the full suite, focused affected tests, and the parallel runner if it remains part of the selected stack. Inspect any coverage/configuration mode the project actually uses.
6. Run Pint as required for changed PHP files and the existing check-only lint command. Have quality CI report formatting drift with `lint:check` instead of silently formatting it, if that workflow adjustment is included in the approved migration.

Gate: all existing tests retain their intent and pass under the new runner, no unexplained assertion-count change or additional skips, no unsupported test configuration, and successful PHP platform checks on PHP 8.5.

### 5. Update developer integrations, then HTTP and math dependencies

1. Upgrade Boost to 2.10.3, MCP to 1.1.2 and Roster to 1.0.0 as a coherent developer-tooling group. Confirm constraints and inspect migration notes before changing any app/provider integration.
2. Verify package discovery, roster/package detection, MCP initialization, tool enumeration, and representative tool calls. Specifically verify Boost documentation search and project URL resolution where available. Package discovery alone does not prove the tools work. If connector tooling is unavailable, leave this group's acceptance incomplete and report the gap.
3. Inspect Composer's update hooks before resolution. Prevent `boost:update` from overwriting curated AGENTS instructions, project skills or `.codex/config.toml`. Generate any proposed context refresh separately, review it, and apply only approved, accurate package-version/context changes with the agent-documentation skill.
4. After Boost supports the new HTTP dependencies, update Guzzle, Promises, PSR-7 and URI Template together. Confirm every parent accepts the selected versions. Review HTTP request/response/stream, redirect, exception and promise behavior. Exercise the real Laravel HTTP-client integration against controlled local endpoints; avoid external writes or live credentials.
5. Update Brick Math to 1.0.0 independently. Review migration notes and verify relevant casts, numeric/decimal handling and Ramsey UUID integration. Use application behavior where it exists and meaningful parent integration where the package is only transitive.
6. Repeat the full application suite and audits on the combined Composer set. Do not remove or replace existing libraries just to force a solver result.

Gate: working developer tools, complete HTTP/math behavior checks, compatible parent requirements, no new advisories, and unchanged application/authentication behavior.

### 6. Reconcile every remaining transitive candidate

Compare the final complete lock inventory against the catalog below. For each entry, record one of: upgraded through its supported parent; removed as unused; or retained with the exact parent constraint and reason. Inspect added and removed duplicate paths so a major change cannot be hidden as a graph move.

A clean outcome does not require every transitive package to equal its registry-latest major. Retain a parent-required older major when no supported parent update is available. Examples include Tailwind's Magic String/Lightning CSS pairing, PostCSS's Nano ID range, platform/WASM support ranges, and the full-reload plugin's Picomatch range. Recheck each actual final parent rather than relying on this example list as a substitute for inspection.

Do not add transitive packages as direct requirements, widen third-party constraints through overrides, change registries, suppress security reports, or introduce prereleases to empty the outdated list.

Gate: no deferred package is silently omitted. Every change or justified retention is reviewable, and all configured manifests and optional/platform packages are represented.

## Planned change surface

Expected files are `package.json`, `package-lock.json`, `composer.json`, `composer.lock`, `.nvmrc`, `.node-version`, README, the existing infrastructure test, and the test/deploy workflows. The lint workflow may receive the explicitly described check-only adjustment. Test configuration and affected test/application call sites change only when a reviewed migration requires it. Curated agent files receive only separately reviewed context/version updates if needed; unrelated `.codex/config.toml` work remains untouched.

Do not add a new framework, package manager, base application directory, browser-test dependency, generic validation script or database migration for this work. Prefer the existing test suites and commands. Add focused regression tests only for demonstrated coverage gaps or actual compatibility fixes.

## Final acceptance and review package

- Run the existing complete backend suite with PHP 8.5, plus relevant focused/parallel checks. Report passes, assertions and skips accurately.
- Run check-only PHP lint, Composer strict validation and platform checks, npm peer validation, production build and manifest/hot-file checks.
- Run successful Composer and npm audits against the final locks. Target zero findings. An unavailable audit or unresolved new advisory blocks ready-for-merge status.
- Prove final Composer install and npm ci reproduce without changing tracked files, first locally and then in the Linux CI build environment on Node 26 with npm 12.2.1.
- Complete Chrome interaction and responsive checks with safe local data. Do not claim browser proof from compilation, HTTP response tests, fixtures or screenshots alone.
- Capture full before/after inventories, source references, manifest digests, additions/removals and approved exceptions. Run the original maintenance checker unchanged. Its expected rejections for authorized majors/manifest edits must match an explicit approval ledger; do not claim a strict-policy pass by hiding entries or changing evidence. Any rejection outside the approved scope blocks completion. The original 0.x evidence requirements remain in force unless Taylor explicitly changes them.
- Review the full diff for unrelated work, leaked credentials, unexpected generated assets and dependency changes outside the approved groups.
- After implementation is authorized, publish reviewable commits/PRs with exact tested versions, evidence, migration notes, remaining parent constraints and rollback steps. Keep any incomplete group Draft. Do not merge or deploy automatically.

Prefer retaining the existing Draft PR #1 for the coordinated migration, with separate verified commits for Node runtime/CI, npm security/compiler, Concurrently, the PHP test stack, Boost/MCP/Roster, and HTTP/math upgrades. Its base remains main, so the existing PR workflows continue to run. Update its title and description to describe the complete approved migration after implementation, rather than retaining the old lockfile-only description.

If a group must be split out, keep each review unit independently verifiable and make its prerequisite explicit. Current PR workflow branch filters do not cover arbitrary dependency-branch targets; do not create a dependent PR whose base silently prevents CI from running. A split PR must target a covered base or receive a separately reviewed workflow-filter adjustment.

The deploy workflow automatically runs on pushes to main. Merging any part of the migration can therefore trigger production deployment. Keep the PR Draft until the final acceptance gates pass, and leave merge/release activation to Taylor's separate decision. Do not merge the preliminary baseline merely to unblock a later migration phase.

## Rollback and stop conditions

Each group ends in a verified commit. Roll back by reverting that group and any dependent groups, restoring the matching manifests/locks, selecting the previous project runtime and reinstalling/rebuilding from those locks. Preserve the previous built release artifact. Dependency rollback does not authorize changing production or undoing database migrations.

Pause the affected group for missing migration evidence, an unsupported native platform, unreviewed script/source changes, a parent constraint conflict, missing developer-tool proof, unexplained test skips or asset regressions. Continue only independent work already covered by the approved plan. Unexpected application redesign, database changes, new direct packages or additional runtime majors require a revised scope.

## Approval scope

Reviewing this document does not start implementation. Approval to proceed would cover the specified Node 26/npm 12 move, Axios removal, listed direct constraint edits, reviewed parent-owned major and prerelease transitions, Oxc evidence work, required tests/configuration/docs updates, and review PR publication. It would not authorize merging, deployment, modifying shared data, changing global runtime defaults, moving beyond the pinned npm 12 major, or forcing incompatible transitive versions.

## Complete prior deferred-major catalog

This appendix accounts for all 53 transitive major candidates recorded in the previous review, plus the direct decisions in the target matrix. Versions are the earlier discovery candidates, with major groups refreshed above; implementation must refresh the complete inventory. A listed latest version is not a promise to override its parents.

| Locked package or instance | Prior locked version | Discovered latest major | Planned disposition |
| --- | --- | --- | --- |
| brick/math | 0.14.8 | 1.0.0 | Phase 5: upgrade to 1.0 with Laravel/UUID behavior checks. |
| guzzlehttp/guzzle | 7.16.1 | 8.3.1 | Phase 5: upgrade the HTTP family together after Boost. |
| guzzlehttp/promises | 2.6.0 | 3.1.0 | Phase 5: upgrade the HTTP family together after Boost. |
| guzzlehttp/psr7 | 2.13.2 | 3.2.0 | Phase 5: upgrade the HTTP family together after Boost. |
| guzzlehttp/uri-template | v1.0.11 | v2.0.1 | Phase 5: upgrade the HTTP family together after Boost. |
| hamcrest/hamcrest-php | v2.1.1 | v3.0.0 | Phase 4: migrate through Mockery and verify matchers/mocks. |
| laravel/mcp | v0.6.7 | v1.1.2 | Phase 5: upgrade with Boost and verify real tools. |
| laravel/roster | v0.5.1 | v1.0.0 | Phase 5: upgrade with Boost and verify real tools. |
| pestphp/pest-plugin | v4.0.0 | v5.0.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| pestphp/pest-plugin-arch | v4.0.2 | v5.0.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| pestphp/pest-plugin-mutate | v4.0.1 | v5.0.2 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| pestphp/pest-plugin-profanity | v4.2.1 | v5.0.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| phpunit/php-code-coverage | 12.5.8 | 14.4.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| phpunit/php-file-iterator | 6.0.2 | 7.0.2 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| phpunit/php-invoker | 6.0.0 | 7.0.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| phpunit/php-text-template | 5.0.1 | 6.0.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| phpunit/php-timer | 8.0.0 | 9.0.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| phpunit/phpunit | 12.5.33 | 13.4.5 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/cli-parser | 4.2.1 | 5.0.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/comparator | 7.1.8 | 8.4.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/complexity | 5.0.0 | 6.0.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/diff | 7.0.1 | 9.0.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/environment | 8.1.2 | 9.3.3 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/exporter | 7.0.3 | 8.2.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/global-state | 8.0.3 | 9.0.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/lines-of-code | 4.0.1 | 5.0.2 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/object-enumerator | 7.0.0 | 8.1.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/object-reflector | 5.0.0 | 6.1.0 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/recursion-context | 7.0.1 | 8.0.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/type | 6.0.4 | 7.0.2 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| sebastian/version | 6.0.1 | 7.0.1 | Phase 4: let Pest 5/PHPUnit 13 select the compatible utility/plugin major; record any retained parent pin. |
| node_modules/@emnapi/wasi-threads | 1.2.3 | 2.2.0 | Phase 6: follow the platform/WASM parent range; keep 1.x if required. |
| node_modules/ansi-regex | 5.0.1 | 6.4.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/ansi-styles | 4.3.0 | 7.0.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/chalk | 4.1.2 | 6.0.1 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/chalk/node_modules/supports-color | 7.2.0 | 11.0.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/cliui | 8.0.1 | 9.0.1 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/color-convert | 2.0.1 | 3.1.3 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/color-name | 1.1.4 | 2.1.1 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/emoji-regex | 8.0.0 | 11.0.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/has-flag | 4.0.0 | 5.0.1 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/is-fullwidth-code-point | 3.0.0 | 5.1.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/magic-string | 0.30.21 | 1.4.5 | Phase 6: retain Tailwind/compiler-required major unless an official parent release permits 1.x. |
| node_modules/mime-types | 2.1.35 | 3.0.2 | Phase 3: remove with unused Axios if no other parent requires it; verify the final graph. |
| node_modules/nanoid | 3.3.20 | 6.0.2 | Phase 6: retain the supported PostCSS range; no forced 6.x override. |
| node_modules/proxy-from-env | 1.1.0 | 2.1.0 | Phase 3: remove with unused Axios if no other parent requires it; verify the final graph. |
| node_modules/string-width | 4.2.3 | 8.3.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/strip-ansi | 6.0.1 | 7.2.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/supports-color | 8.1.1 | 11.0.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/vite-plugin-full-reload/node_modules/picomatch | 2.3.2 | 4.0.7 | Phase 6: follow the supported full-reload parent range; retain 2.x if required. |
| node_modules/wrap-ansi | 7.0.0 | 10.0.2 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/yargs | 17.7.2 | 18.2.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |
| node_modules/yargs-parser | 21.1.1 | 22.0.0 | Phases 3/6: resolve through Concurrently 10 and other owning parents; retain any still-pinned major with its reason. |

Additional compatible candidates previously held by their parents, including newer ParaTest, native Lightning CSS bindings and optional WASM support packages, are covered by phases 3, 4 and 6. Previously applied Laravel/Livewire/Flux updates remain part of the prerequisite PR and must receive the final combined-set verification; they are not lost from the release scope.

## Additional migration references

- [Vite 8 migration notes](https://vite.dev/guide/migration) and [Vite 8.3.5 release](https://github.com/vitejs/vite/releases/tag/v8.3.5).
- [Rolldown 1.2.15 release](https://github.com/rolldown/rolldown/releases/tag/v1.2.15) and [Oxc release history](https://github.com/oxc-project/oxc/releases).
- [Concurrently 10 release](https://github.com/open-cli-tools/concurrently/releases/tag/v10.0.0).
- [MCP 1 release](https://github.com/laravel/mcp/releases/tag/v1.0.0) and [Roster 1 release](https://github.com/laravel/roster/releases/tag/v1.0.0).
- [Guzzle 8 release](https://github.com/guzzle/guzzle/releases/tag/8.0.0), [Brick Math 1 release](https://github.com/brick/math/releases/tag/1.0.0), and [PHPUnit 13 migration context](https://phpunit.de/announcements/phpunit-13.html).
- [Original dependency-maintenance policy](/Users/Taylor/.codex/skills/laravel-dependency-updates/SKILL.md). Approved migration changes must remain explicit exceptions to this routine-maintenance policy.

## Local runtime implementation status

Taylor authorized the local runtime switch after reviewing the npm version distinction. The project selects Herd-managed Node 26.13.0 with npm 12.2.1, records those requirements in its runtime files and manifest, and aligns the CI configuration and infrastructure tests. All 132 dependency lock instances remain unchanged. A fresh npm 12 install retains the default blocking of unapproved install scripts. Local verification passed: npm ci, production build, peer validation, PHP lint, and all 51 application tests with 205 assertions and two existing skips. Herd serves the site with PHP 8.5 and its managed Vite process uses the verified Node 26.13.0 binary. The homepage, built assets and live Vite assets return HTTP 200 over trusted local HTTPS. Linux CI and the remaining dependency migrations are still pending. No PR update or production activation is included in this local task.
