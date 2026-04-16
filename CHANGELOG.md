# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

While the major version is `0`, the API is considered unstable and breaking
changes may occur in any minor release.

## [Unreleased]

### Added

- Proposals list page (`/dashboard/proposals`) rebuilt against the new
  design system, following the `design-prototypes/proposals.html` pattern:
  serif page header with in-flight count, segmented status control (All /
  Drafts / Delivered / Accepted / Rejected / Archived) with per-status
  counts, debounced search across proposal name and client name / contact /
  email, a single clean table with `<x-pill>` status badges, owner and
  value columns, and row actions for Edit / Preview / Delete. Status and
  search are persisted to the URL so filtered views can be shared.
  `ProposalsList` now correctly scopes client-field search via `whereHas`
  (the previous `orWhere('client.name', ...)` dot-notation was a silent
  no-op) and eager-loads `client`, `user`, and `features` to avoid N+1
  lookups when rendering totals. `tests/Feature/ProposalsListTest.php`
  covers the empty state, multi-status render, status filter, search
  filter, and delete action.
- New brand UI baseline applied to the admin shell and dashboard, in line with
  the static reference in `design-prototypes/`:
    - Epic Fox brand palette and typography wired into `resources/css/app.css`
      as additive Tailwind v4 tokens (`bg-ink`, `text-fox`, `text-sage`,
      `text-slate`, `bg-paper`, `border-rule`, status hues, plus
      `font-display` / `font-sans` / `font-mono`). The legacy `primary/*`
      palette is retained so non-dashboard pages continue to render until
      they migrate.
    - Refactored `components/layouts/admin.blade.php` to a 64px ink rail with
      icon-only nav (fox-yellow active accent + tooltip on hover) and a
      sticky 60px topbar with workspace breadcrumb and a visual
      `⌘K` search trigger (palette wiring still to come).
    - New `<x-pill>` Blade component for status badges; `<x-logo>` rebuilt as
      the geometric fox mark; `<x-menu-item>` rewritten for the slim rail.
    - `Dashboard` Livewire component now computes real KPIs (open pipeline
      value, won this month, conversion %, average closed value) plus a
      "needs your attention" feed (delivered > 14d, drafts untouched > 7d)
      and the eight most recent updates.
    - `dashboard.blade.php` rebuilt: serif greeting, four-tile KPI strip
      (first tile in ink + fox-yellow), attention panel, recent-activity
      feed, and a segmented proposals table. Numbers use tabular figures.
    - `tests/Feature/DashboardTest.php` covers the empty state and the
      populated state across all proposal statuses.
- Static `design-prototypes/` reference folder containing the agreed direction
  for the admin refactor: a dashboard, a proposals list, and a "present mode"
  for live client walkthroughs. Built around the Epic Fox brand palette
  (`#242423` ink, `#CFDBD5` sage, `#F5CB5C` fox-yellow, `#5F6463` slate),
  with Fraunces (display, SOFT 50) + Geist (body) + JetBrains Mono (numerals).
  Frozen reference only — not served by the app. Open
  `design-prototypes/dashboard.html` to view.

### Security

- Upgraded Livewire to 3.7.15, clearing
  [CVE-2025-54068](https://github.com/advisories/GHSA-29cq-5w36-x7w3) — a
  critical RCE vulnerability affecting Livewire versions prior to 3.6.4.
- Cleared all outstanding Composer security advisories: Livewire RCE above,
  `psy/psysh` local privilege escalation (via Tinker 3), and the PHPUnit
  PHPT deserialization advisory (via PHPUnit 12.5.8+). `composer audit` is
  now clean.

### Changed

- Rewrote `README.md` for prospective users: dropped the original marketing
  copy in favour of a plain-English overview of the product concept (feature
  library → clients → interactive proposals with optional toggles and live
  totals), the planned operator/client two-screen presentation mode, and an
  honest split of what works today versus what is still to come (self-serve
  sign-up, screen mirroring, unifying the interactive proposal with the
  simpler HTML proposal output). Flagged that the "ConfiguPro" name is not
  final.
- Upgraded frontend toolchain: Vite 6 → 8, `laravel-vite-plugin` 1.2 → 3,
  Tailwind CSS v4 plugin and runtime to 4.2, SweetAlert2 to 11.26, axios to
  1.15, concurrently to 9.2.
- Resolved the Vite 6.0.9+ CORS regression that blocked the dev server from
  serving assets to the Herd/Valet `.test` host by upgrading
  `laravel-vite-plugin` past its CORS-aware release.
- Refreshed safe-range Composer dependencies: `laravel/framework` 12.14 →
  12.56, `laravel/telescope` 5.7 → 5.20, `laravel/pail` 1.2.2 → 1.2.6,
  `laravel/sail` 1.43 → 1.57, `laravel/pint` 1.22 → 1.29,
  `fruitcake/laravel-telescope-toolbar` 1.3.6 → 1.3.7,
  `nunomaduro/collision` 8.8.0 → 8.8.3, `fakerphp/faker` 1.23 → 1.24.
- Upgraded `spatie/laravel-permission` 6 → 7. The existing `HasRoles` usage,
  `assignRole` / `syncRoles` calls, and the `permission_tables` migration all
  continue to work unchanged; v7 only added type declarations to the public
  API and renamed internal event and command classes (neither of which are
  referenced in this project).
- Upgraded `wire-elements/modal` 2 → 3. v3 targets Tailwind v4 and also adds
  Livewire 4 compatibility, clearing the way for the Livewire upgrade. No
  code changes are required — the admin layout already uses the renamed
  `@livewire('wire-elements-modal')` directive, and no modal views have been
  published to `resources/views/vendor/`.
- Upgraded `laravel/tinker` 2 → 3, which also clears the `psy/psysh`
  CVE-2026-25129 advisory.
- Upgraded `laravel/boost` 1 → 2. The `.mcp.json` `boost:mcp` command is
  unchanged; consider running `php artisan boost:install` once to pick up v2's
  refreshed guidelines and skills sync.
- Upgraded testing stack: Pest 3 → 4 (4.6.0) and PHPUnit 11 → 12.5. PHPUnit
  13 is not yet reachable because Pest 4 pins PHPUnit to `^12.5` — we'll
  revisit when Pest publishes a PHPUnit-13-compatible release. PHPUnit 12.5.8+
  clears [CVE-2026-24765](https://github.com/advisories/GHSA-vvj3-c3rp-c85p).
  No changes were required to `phpunit.xml`, test files, or the base TestCase.
- Upgraded `laravel/framework` 12 → 13 (13.5.0). Added
  `serializable_classes => false` to `config/cache.php` per the Laravel 13
  hardening guidance. No other code changes were needed — the CSRF
  middleware rename (`VerifyCsrfToken` → `PreventRequestForgery`) is not
  referenced anywhere in this project, and the new Eloquent restriction
  against creating models during boot does not apply to the `BelongsToTenant`
  or `Uuid` traits, which only register closures.
- Upgraded Livewire 3 → 4 (4.2.4). All existing directives (`wire:model`,
  `.live`, `.lazy`, `.live.debounce`, `wire:click`/`submit`/`loading`/
  `key`/`confirm`), lifecycle hooks (`mount`, `render`, `updated*`), and
  attributes (`#[Layout]`, `#[On]`) carried over without changes. No
  non-self-closed `<livewire:…>` tags existed to rewrite.

### Fixed

- Added explicit `#[Layout('components.layouts.app')]` to the four full-page
  Livewire components that previously relied on the Livewire 3 default
  (`Login`, `ForgottenPassword`, `PasswordReset`, and the public
  `Admin\Proposals\Preview`). Livewire 4 changed the default to a
  `layouts::app` namespace, producing `No hint path defined for [layouts]`
  on any component without an explicit layout attribute.
- Resolved the long-standing `TenantScopeTest` failure carried over from
  v0.1.0. The test was written against an earlier pivot-based design; the
  application has since adopted snapshotting features into a separate
  `final_features` table on proposal creation. The test now verifies tenant
  auto-fill and cross-tenant isolation across `Proposal` and `FinalFeature`.

### Removed

- Dead pivot-based feature/proposal code left over from the earlier design:
  `App\Models\FeatureProposal`, the `Feature::proposals()` relationship, the
  commented-out `BelongsToMany` version of `Proposal::features()`, and the
  unused `feature_proposal` migration / table. Proposals now exclusively use
  `final_features` snapshots.

### Known issues

- `vite-plugin-full-reload@1.2.0` (transitive via `laravel-vite-plugin`) pins
  `picomatch@2.3.1`, which has a dev-only ReDoS advisory. Will clear when the
  upstream plugin publishes a new release.
- Upgrade verification so far is test-suite-only; a full browser pass across
  the admin UI is still outstanding and should precede any new feature work.

## [0.1.0] - 2026-04-14

Initial baseline of ConfiguPro — a multi-tenant SaaS for building custom price
configurators and generating digital proposals.

### Added

- Session-scoped multi-tenancy via `BelongsToTenant` trait and `TenantScope`
  global scope, populated on `Login` and cleared on `Logout`.
- UUID v7 primary lookup via `Uuid` trait with `findByUuid()` and route-model
  binding for public-facing URLs.
- Livewire 3 admin panel covering dashboard, users, clients, features,
  proposals, and settings.
- Proposal lifecycle: draft → delivered → accepted/rejected → archived, with
  status scopes on `HasStatus` trait.
- Feature snapshots via `FinalFeature` when a proposal is finalised.
- Role and permission management via `spatie/laravel-permission`.
- Authentication screens (login, forgotten password, password reset) with IP
  rate limiting and an `active` user flag enforced by `RequireActiveUser`
  middleware.
- `Settings` and `Formatter` facades for tenant-scoped configuration and
  currency/number formatting.
- Modal-based CRUD via `wire-elements/modal` for clients, users, features, and
  proposal features.
- Toast notifications via Toastify and confirmation dialogs via SweetAlert2.
- Tailwind CSS v4 styling with Vite-powered asset pipeline.
- Pest feature tests covering tenant scoping and UUID generation.

### Known issues

- `Proposal::features()` is defined as `HasMany` but `Feature::proposals()` is
  `BelongsToMany`; one feature-pivot-sharing test in `TenantScopeTest` fails
  because of this relationship mismatch. To be addressed after the dependency
  upgrade pass.

[Unreleased]: https://github.com/snavebelac/configurator/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/snavebelac/configurator/releases/tag/v0.1.0
