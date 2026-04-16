# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

While the major version is `0`, the API is considered unstable and breaking
changes may occur in any minor release.

## [Unreleased]

## [0.2.0] - 2026-04-16

This release closes out the Epic Fox brand redesign, adds two substantial
domain features (nested features and packages), turns the dashboard's
"Lately" panel into a real activity feed, redesigns the client-facing
proposal preview as an editorial document, and consolidates icons onto
Phosphor.

### Added

- Auth screens (`Login`, `ForgottenPassword`, `PasswordReset`) migrated to
  the Epic Fox brand UI via a new shared `<x-auth-shell>` wrapper that
  carries the fox-yellow logo mark, italic-serif masthead, lede, bordered
  card, and optional footer slot. Inputs use the same `<x-field>` /
  `<x-btn>` primitives as the back-office, so any future style change
  reaches every form in the app. The `components.layouts.app` layout was
  rewired to load Libre Baskerville + Inter and apply
  `bg-paper text-ink` to the body. New `tests/Feature/{LoginTest,
  PasswordResetTest}.php` cover render + brand copy, validation, invalid /
  inactive sign-in, happy-path redirect, reset-link dispatch, and password
  update. Small refactor on `Login.php`: swapped the
  `Request::session()` facade for the `session()` helper so the
  authenticate path is testable through `Livewire::test` without binding
  a session to a fake request.
- Nested features (parent → child, one level deep). Activated the
  long-dormant `parent_id` columns on `features` + `final_features`
  (originally schema'd but never wired into models, UI, or snapshotting)
  with proper indexes. Parents have their own base price; children stack
  on top. In the proposal builder, selecting a child auto-attaches its
  parent (with a toast for visibility); removing a parent cascade-removes
  any of its selected children (also toasted). A standalone feature can
  be reparented freely; only a feature that already has children is
  locked (it would create grandchildren). The library itself is strictly
  alphabetical — customer-facing ordering lives exclusively on
  `FinalFeature.order`, drag-reorderable on the proposal-edit page via
  `@alpinejs/sort` (parents only; children stay alpha within their
  parent). Added `source_feature_id` to `final_features` plus a one-time
  backfill migration that matches existing snapshots to their source
  Features by `(tenant_id, name)`, so the new "already on proposal"
  de-dup logic works across both fresh and historical proposals.
  Snapshot creation preserves the parent link so historical proposals
  keep their structure even as the library evolves. 16 new tests in
  `tests/Feature/NestedFeaturesTest.php` cover relationships, the
  `roots()` scope, library cascade-on-delete, modal-level reparenting
  rules, list grouping, auto-attach-on-pick, cascade-on-remove,
  snapshot ordering, drag-to-reorder via `reorderParents`, and snapshot
  cascade.
- Reusable `FeaturePicker` Livewire component that owns the
  library-browse side only — search, paginated grouped display, pick-row
  rendering. Accepts a `disabledIds` prop (marked `#[Reactive]` so it
  updates as the parent re-renders) and emits `feature-picked` events.
  `ProposalCreate` now mounts the picker as its left pane (replacing the
  inline implementation), and a new `AddFeaturesModal` embeds it on the
  proposal-edit page so features can be added to an existing proposal
  without starting over. The modal applies the same auto-attach-parent
  rules as the create flow and reuses an existing parent snapshot when
  one is already on the proposal. Dropped the Finalise button (a
  reversible ceremony the system didn't actually need) in favour of an
  elevated "Preview (client view)" CTA. 9 new tests in
  `tests/Feature/{FeaturePickerTest,AddFeaturesModalTest}.php`.
- Editorial redesign of the client preview at
  `/dashboard/proposal/preview/{uuid}`. Rebuilt as a typeset document
  rather than a data table — large italic-serif masthead with For /
  Prepared by / Dated, parent features as serif section dividers with
  children grouped beneath, and a sticky summary rail with a live total
  in Office Code Pro tabular. Optional features toggle via Alpine; the
  subtotal, optional sum, and VAT recompute in front of the client. All
  toggling is ephemeral (client-side only) — no DB writes — so the page
  works as a share-link preview without any session layer. Subtle
  paper-grain noise texture (SVG-as-data-URL) sits under everything for
  tactility. Fox-yellow is reserved for the "Live" pill on the summary
  card. 3 new tests in `tests/Feature/ProposalPreviewTest.php`.
- Packages — pre-curated sets of features that can be dropped onto a
  proposal in bulk. New `Package` model (tenant-scoped via
  `BelongsToTenant`, UUID, soft-deleted), many-to-many to `Feature` via
  the `feature_package` pivot. Custom `FeaturePackage` Pivot model also
  uses `BelongsToTenant`, so pivot rows are globally filtered by tenant
  and `tenant_id` is auto-populated on attach. The pivot carries
  per-package overrides on `quantity`, `optional`, and `price` — all
  nullable; null means "inherit the feature's default." Price is stored
  in integer-pence to match the `Feature` accessor convention, so
  attaching with `'price' => 25.00` is treated as pounds and stored as
  2500 pence. Full admin CRUD at `/dashboard/packages` with a list page
  mirroring `FeaturesList` and a two-pane edit page that pairs the
  shared `<x-feature-picker>` (left) with an editable members table
  (right) carrying inline override inputs and a three-state "Inherit /
  Force optional / Force required" select. On proposal-edit, a new
  `AddPackageModal` bulk-snapshots a package's features with overrides
  applied (auto-attach-parent + de-dup against `source_feature_id`
  reused from the existing infrastructure). On proposal-create, a
  separate event-based `PackagePickerModal` emits `package-picked`;
  `ProposalCreate::handlePackagePicked` expands features into
  `selectedFeatureIds` and stashes overrides in a `packageOverrides`
  array that `snapshotFeature` consults at create time. New "Packages"
  icon in the admin nav rail. 13 new tests in
  `tests/Feature/PackagesTest.php` covering relationships, CRUD,
  override persistence, bulk-snapshot with/without overrides,
  auto-attach-parent on package-add, dedup, and tenant-scope on both
  `Package` and the `FeaturePackage` pivot.
- Real activity feed on the dashboard, replacing the previous
  last-8-updated-proposals stand-in. New tenant-scoped `Activity` model
  with a polymorphic `subject`, JSON `payload`, immutable storage (no
  `updated_at`), and a `static log()` helper. New `ActivityAction` enum
  covers `proposal.created`, `proposal.status_changed`,
  `client.created`, `package.created`. Three model observers (Proposal,
  Client, Package) write events on the relevant lifecycle moments —
  the Proposal observer fires status-changed only on actual
  `wasChanged('status')`, so a name-only edit doesn't pollute the
  feed. Subject names are stashed into the payload at log-time so the
  feed reads coherently even if the subject is later soft-deleted.
  Headlines like "Caleb created Brand identity system" / "Caleb moved
  X to Delivered" render in the dashboard panel with subject-type-
  coloured icons (sage for client, fox-soft for package, status-
  coloured for proposal status changes). 8 new tests in
  `tests/Feature/ActivityTest.php`.
- Quick-action buttons in the dashboard header: "New feature" (opens
  the existing modal) and "New package" (links to the create page)
  added alongside the existing "New proposal" accent button.
- Swapped the admin typography stack from Fraunces + Geist + JetBrains
  Mono to Libre Baskerville (display) + Inter (body) + Office Code Pro
  (numerals), after prototyping the change in `design-prototypes/`.
  Libre Baskerville and Inter load from Google Fonts; Office Code Pro is
  self-hosted from `public/fonts/` (OFL-licensed webfont kit from Font
  Squirrel — the original `nathco/Office-Code-Pro` GitHub repo currently
  404s). Display h1s are now italic with `-0.04em` tracking. Dropped all
  inline Fraunces-specific `font-variation-settings` and the synthetic
  half-step `font-[450]` weights from component templates.
- Toast notifications restyled to the brand UI: ink background, paper
  text, colored status dot on the left (`status-accepted-dot` for success,
  `status-rejected-dot` for the `.warning` variant). The JS dispatch
  contract (`$this->dispatch('toast', ...)`) is unchanged.
- Fixed wire-elements modal widths under Tailwind v4. The vendor's
  responsive `sm:max-w-*` / `md:max-w-*` / `xl:max-w-*` classes only exist
  as strings in `vendor/wire-elements/modal/src/ModalComponent.php`, which
  Tailwind v4's content scanner doesn't traverse by default. Added an
  `@source` directive for that file in `resources/css/app.css` so the
  classes are preserved in the built CSS. Prior to this the modals
  rendered full-width on wider screens — noticeable on the feature modal,
  which was effectively unusable. Also picked per-modal widths (`2xl` for
  client, `3xl` for feature and user) via `modalMaxWidth()` overrides,
  instead of the blanket `5xl` default.
- Reworked the feature modal layout so Name and Description each get
  their own full-width row, Price and Quantity share a row, and the
  Optional checkbox lives on its own full-width row under a rule — with
  room for its description copy to stretch out.
- Whimsical placeholders across modal + builder inputs. Tiny homage to a
  certain 90s space-station show — swap them back to plain examples any
  time if you'd rather they weren't there.
- Proposal builder rebuilt against the Epic Fox brand UI:
    - `/dashboard/proposal/create` is now a two-pane builder — a searchable
      feature library on the left, a Selected features table with a
      running total on the right — on top of a client selector and
      proposal-name meta strip. Uses the shared `<x-card>`, `<x-field>`,
      `<x-select-field>`, `<x-money>`, `<x-btn>` components throughout.
    - Fixed a long-standing bug where `ProposalCreate::render()` filtered
      the library with `$this->search` but the property is named
      `$this->featureSearch`, so the library search box never actually
      filtered anything. The eager-loaded query now uses the correct
      property and resets pagination when the term changes.
    - `createProposal()` now redirects to the newly created proposal's
      edit page (previously it stayed on the create page with no
      feedback), and emits a toast.
    - `/dashboard/proposal/edit/{proposal}` has a meta strip
      (status pill / client / owner / last-updated) over an editable
      features table. Each row is the existing `ProposalFeatureForm`
      Livewire component restyled with brand tokens — name / quantity /
      unit price / optional toggle / line total, all editable in place.
    - Implemented the missing `removeFinalFeature()` method on
      `ProposalFeatureForm` — the Delete button in the builder row was
      wired to a method that didn't exist, silently throwing a JS error
      on click. Also added the in-row Optional toggle so you no longer
      need to go back to the feature library to change it.
    - `ProposalTotalOnTheFly` is restyled as the builder's footer figure
      (serif display type, `refreshFeatureProposalEdit` event contract
      unchanged so the live update continues to work).
- All four modal flows (`client-modal`, `feature-modal`, `user-modal`, and
  the inline `proposal-feature-form` row) rewritten against brand tokens,
  inside a rebuilt `<x-modal>` that has a titled header, ink-on-paper
  body, and a rule-soft footer for the cancel/save row. Modal inputs use
  the same `<x-field>` / `<x-checkbox-field>` / `<x-select-field>` as
  full-page forms, so one style change now reaches every form in the app.
- Shared Blade form primitives to round out the component set:
  `<x-field>` (text / email / tel / password / number, with optional
  `prefix` for £, `hint`, status-rejected error state, and automatic
  `@error('name')` handling); `<x-checkbox-field>` (ink-accent checkbox
  with label + optional description); `<x-select-field>` (native select
  styled to match, takes an `$options` map).
- Feature tests for the builder and modals:
  `tests/Feature/ProposalBuilderTest.php` covers the empty create render,
  select/remove library features, required-field validation,
  create-and-redirect with snapshot feature copying, edit-page render
  (with the new meta strip assertions), in-place feature edit, and
  feature removal. `tests/Feature/ModalTest.php` covers the create, edit
  mount, and required-field validation paths for each of the client,
  feature, and user modals. Suite is now 45 tests / 147 assertions.
- Shared Blade primitives for the admin UI so the brand look only lives in
  one place: `<x-page-header>` (eyebrow + serif h1 + lede + actions slot),
  `<x-card>` + `<x-card-header>`, `<x-btn>` (`accent` / `ghost` / `quiet` /
  `row` / `destructive` variants), `<x-money>` (`kpi` / `kpi-fox` / `row` /
  `mono` / `body` sizes, optional 2dp precision), and `<x-th>`. The
  dashboard and proposals list were retrofitted onto them as part of the
  same change.
- Brand UI migration for the remaining back-office list pages:
    - Clients list (`/dashboard/clients`) — brand header, single search
      toolbar (name / contact / email via a proper `whereHas` query, fixing
      the same silent `orWhere('client.name', ...)` bug the proposals list
      had), URL-backed search.
    - Features list (`/dashboard/features`) — brand header with
      `{total} · {optional} optional` eyebrow, per-row Optional badge in
      fox-soft tones, prices via `<x-money :precise="true">`.
    - Users list (`/dashboard/users`) — brand header, inline
      Active/Inactive pill, "You" marker on the signed-in row, delete
      action hidden on the current user.
    - Profile form (`/dashboard/profile`) — rebuilt as a single bordered
      card: ink-on-paper inputs with focus ring, cancel/save footer using
      `<x-btn>` variants, and a status-accepted banner when the update
      succeeds.
- Feature tests for each migrated page:
  `tests/Feature/{ClientListTest,FeaturesListTest,UserListTest,ProfileTest}.php`
  cover the empty state, the populated list, the search filter (where the
  page has one), and the delete / update paths. 30 tests, 84 assertions.
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

- Replaced 50+ hand-rolled inline SVGs across the admin views with
  `<x-phosphor-*>` components from `codeat3/blade-phosphor-icons` (built
  on `blade-ui-kit/blade-icons`). Icons are still inline SVG at
  template-render time, so client-side payload is unchanged — only the
  icons referenced in templates ever reach the browser. The bespoke
  `<x-logo>` brand mark and the SVG-as-data-URL paper-grain texture in
  the client preview are kept as-is. The nav rail's `<x-menu-item>`
  was refactored to take an `icon="..."` prop and render via
  `<x-dynamic-component>`, replacing the previous slot-of-paths
  pattern. Settled icon vocabulary: `phosphor-plus` for "add" CTAs,
  `phosphor-x` for close/remove, `phosphor-check` for confirmations,
  `phosphor-magnifying-glass` for search inputs, `phosphor-arrow-right`
  for forward affordances, `phosphor-arrow-square-out` for "open in
  new tab", `phosphor-arrow-elbow-down-right` for child-feature
  indents, `phosphor-dots-six-vertical` for drag handles,
  `phosphor-lock-simple` for required markers, plus subject-type icons
  (cube, stack, file-text, user, user-circle, users-three, envelope,
  squares-four, bell, sign-out).
- Proposal-edit features list converted from `<table>` to a CSS-grid
  layout so each parent+children group can be wrapped together as a
  single sortable item. Children move with their parent visually
  during drag. `proposal-feature-form` renders a div-based grid row
  instead of a `<tr>`. The drag handle (`x-sort:handle`) lives only
  on parent rows, so children can't be independently dragged.
- `<x-select-field>` gained `modelLive`, `disabled`, and `hint` props
  for parity with `<x-field>`.
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

- `FeaturePicker`'s `disabledIds` prop is marked `#[Reactive]`, so when
  `AddFeaturesModal` adds a feature the picker shows it as
  greyed-out / ticked immediately. Without the attribute, Livewire 4
  only sets nested-component props at mount time, leaving the picker
  visually stale until a full reload — a UX wart that would otherwise
  let the user attempt to re-add a feature already on the proposal.
- Wired the previously-orphaned `refreshFeatureProposalEdit` event:
  `ProposalEdit` now listens for it and reloads features. Before this,
  removing a parent FinalFeature would soft-delete its children at the
  DB level (correct) but leave their stale Livewire row components in
  the DOM until the page was reloaded.
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

[Unreleased]: https://github.com/snavebelac/configurator/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/snavebelac/configurator/releases/tag/v0.2.0
[0.1.0]: https://github.com/snavebelac/configurator/releases/tag/v0.1.0
