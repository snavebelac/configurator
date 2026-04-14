# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

While the major version is `0`, the API is considered unstable and breaking
changes may occur in any minor release.

## [Unreleased]

### Security

- Upgraded Livewire to 3.7.15, clearing
  [CVE-2025-54068](https://github.com/advisories/GHSA-29cq-5w36-x7w3) — a
  critical RCE vulnerability affecting Livewire versions prior to 3.6.4.

### Changed

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

### Known issues

- `vite-plugin-full-reload@1.2.0` (transitive via `laravel-vite-plugin`) pins
  `picomatch@2.3.1`, which has a dev-only ReDoS advisory. Will clear when the
  upstream plugin publishes a new release.

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
