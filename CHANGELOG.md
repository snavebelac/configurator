# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

While the major version is `0`, the API is considered unstable and breaking
changes may occur in any minor release.

## [Unreleased]

### Changed

- Upgraded frontend toolchain: Vite 6 → 8, `laravel-vite-plugin` 1.2 → 3,
  Tailwind CSS v4 plugin and runtime to 4.2, SweetAlert2 to 11.26, axios to
  1.15, concurrently to 9.2.
- Resolved the Vite 6.0.9+ CORS regression that blocked the dev server from
  serving assets to the Herd/Valet `.test` host by upgrading
  `laravel-vite-plugin` past its CORS-aware release.

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
