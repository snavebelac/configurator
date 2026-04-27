# Admin redesign — work in progress

This file tracks the multi-session refactor of the Configurator admin panel
to the Epic Fox brand UI. Update it as work lands. Final, version-pinned
descriptions go in `CHANGELOG.md`; this file is the **mid-flight checkpoint**.

> **Pickup note (after v0.2.0, 2026-04-16):** The brand redesign is
> functionally complete and the back-office now has nested features,
> packages, a real activity feed, and an editorial client preview.
> Since the v0.2.0 cut, the ⌘K command palette, a tenant settings
> admin page, and a fully public proposal share link (with optional
> expiry + 6-digit access code gating) have all landed. The next
> big-rock item is **Present mode**.

## Where we are

The entire back-office is now on the new design system: dashboard,
proposals list, proposal builder (create + edit), clients, features,
users, and the profile form. All four `wire-elements/modal` modals
(client, feature, user, and the inline proposal-feature editor) have also
been restyled. The three public auth screens (login, forgotten-password,
password-reset) share a brand `<x-auth-shell>` wrapper and use the same
`<x-field>` / `<x-btn>` primitives.

Everything is built on a shared set of Blade primitives —
`<x-page-header>`, `<x-card>`, `<x-card-header>`, `<x-btn>`, `<x-money>`,
`<x-th>`, `<x-pill>`, `<x-field>`, `<x-checkbox-field>`, `<x-select-field>`,
`<x-modal>` — so every form field, button, table, and card has a single
source of truth.

The feature library now supports **one level of parent/child nesting**:
parents have their own base price and can own any number of children;
children stack on top of the parent. Selecting a child in the proposal
builder silently auto-attaches its parent; removing a parent
cascade-removes its selected children. A standalone feature can be
reparented freely; only a parent that already has children is locked (it
would create grandchildren). The library itself is strictly alphabetical
— customer-facing ordering lives on the `FinalFeature` snapshot, where
parents can be drag-reordered on the proposal-edit screen via
`@alpinejs/sort`. `FinalFeature` snapshots preserve the parent link and
now carry a `source_feature_id` pointer so the "Add features" picker can
de-dup against what's already on a proposal.

A reusable `FeaturePicker` Livewire component drives the library-browse
UI: `ProposalCreate` mounts it as its left pane, and `AddFeaturesModal`
embeds it too so features can be added to an existing proposal without
starting over. The old "Finalise" button has been dropped (a reversible
ceremony we didn't need) in favour of an elevated "Preview (client
view)" CTA.

**Packages** — pre-curated sets of features that can be dropped onto a
proposal in bulk. A `Package` has a many-to-many relationship to
`Feature` via the `feature_package` pivot (a custom tenant-scoped
`FeaturePackage` Pivot model), with optional per-package overrides on
quantity, price, and required/optional. `AddPackageModal` snapshots a
package's features onto an existing proposal (with the same auto-attach
parent + de-dup logic), and `PackagePickerModal` covers the
proposal-create path. Full admin CRUD at `/dashboard/packages`.

The static reference for the whole direction lives in `design-prototypes/`
(`dashboard.html`, `proposals.html`, `present.html`). Open
`design-prototypes/dashboard.html` in a browser before continuing.

## Decisions locked in (don't re-litigate)

- **Palette** — Epic Fox: `#242423` ink, `#CFDBD5` sage, `#F5CB5C` fox-yellow,
  `#5F6463` slate. Yellow is reserved for primary CTA, active nav indicator,
  the "open pipeline" KPI tile, the live-presentation total, and the
  "live" indicator. Used at most twice per screen.
- **Type pairing** — Libre Baskerville (display; h1 in italic with
  `-0.04em` tracking, regular upright elsewhere) + Inter (body) + Office
  Code Pro (numerals, self-hosted from `public/fonts/`). Libre
  Baskerville only ships weight 400 and 700, so don't reach for
  `font-medium` on display elements.
- **Shell** — 64px slim icon rail (ink, fox-yellow active accent, tooltip on
  hover) + 60px sticky topbar with workspace breadcrumb and a `⌘K` search
  trigger.
- **Audience scope** — desktop / laptop only for now. Mobile responsiveness
  is deferred.
- **Command palette** — agreed feature, not yet wired (visual trigger only).
- **Present mode** — agreed direction, prototype only. Real implementation
  comes after the back-office is consistent.

## What's done

- `resources/css/app.css` — brand tokens, font families, status hues, body
  baseline. Legacy `primary/*` palette retained as a compatibility shim.
- `resources/views/components/layouts/admin.blade.php` — slim rail + sticky
  topbar.
- `resources/views/components/logo.blade.php` — fox mark.
- `resources/views/components/menu-item.blade.php` — icon-only rail item
  with active accent and tooltip.
- `resources/views/components/pill.blade.php` — status pill component.
- `resources/views/components/page-header.blade.php` — eyebrow + serif h1
  + lede + actions slot. Used by every migrated admin page.
- `resources/views/components/card.blade.php` +
  `resources/views/components/card-header.blade.php` — rounded panel
  wrapper and its titled header row.
- `resources/views/components/btn.blade.php` — button / link with
  `accent` / `ghost` / `quiet` / `row` / `destructive` variants.
- `resources/views/components/money.blade.php` — `£` + tnum figure with
  `kpi` / `kpi-fox` / `row` / `mono` / `body` sizes. `:precise` toggles
  2dp vs. whole-pound.
- `resources/views/components/th.blade.php` — uppercase table header cell.
- `resources/views/components/field.blade.php` — labelled text-like input
  (text / email / tel / password / number) with ink focus, status-rejected
  error state, optional `prefix` slot (e.g. `£` for prices), optional
  `hint`. Respects `@error('field-name')`.
- `resources/views/components/checkbox-field.blade.php` — ink-accent
  checkbox with label + optional description.
- `resources/views/components/select-field.blade.php` — native select
  styled like `<x-field>`, takes an `$options` map.
- `resources/views/components/modal.blade.php` — rebuilt as a brand panel:
  titled header, border, rule-soft footer seam. Everything published by
  `wire-elements/modal` now drops into this consistently.
- `app/Livewire/Admin/Dashboard.php` — real KPI computation.
- `resources/views/livewire/admin/dashboard.blade.php` — KPI strip,
  attention feed, recent feed, segmented table.
- `tests/Feature/DashboardTest.php` — empty-state + populated-state
  coverage.
- `app/Livewire/Admin/Proposals/ProposalsList.php` — URL-backed status
  filter + debounced search across name/client fields, per-status counts,
  eager-loaded totals.
- `resources/views/livewire/admin/proposals/proposals-list.blade.php` —
  brand header, segmented status control, clean table with `<x-pill>`,
  owner column, value column, preserved delete action.
- `tests/Feature/ProposalsListTest.php` — empty state, multi-status list,
  status filter, name/client search, delete.
- `app/Livewire/Admin/Clients/ClientList.php` +
  `resources/views/livewire/admin/clients/client-list.blade.php` — brand
  header, single search toolbar, clean table. URL-backed search via
  `#[Url]`, `whereHas`-style search across client fields.
- `app/Livewire/Admin/Features/FeaturesList.php` +
  `resources/views/livewire/admin/features/features-list.blade.php` —
  brand header with `{total} · {optional} optional` eyebrow, per-row
  Optional badge, prices via `<x-money :precise="true">`.
- `resources/views/livewire/admin/users/user-list.blade.php` — brand
  header, inline Active/Inactive pill, "You" badge on the signed-in row,
  delete hidden on the current user.
- `resources/views/livewire/admin/profile.blade.php` — brand form:
  bordered card, ink focus ring, cancel/save footer using `<x-btn>`.
- `tests/Feature/{ClientListTest,FeaturesListTest,UserListTest,ProfileTest}.php`
  covering empty state, listing, search (where applicable), and
  delete / update paths.
- `app/Livewire/Admin/Proposals/ProposalCreate.php` — fixed an existing
  `$this->search` / `$this->featureSearch` variable-name bug in the
  feature filter query, added a post-create redirect to the edit page,
  and improved validation messages.
- `app/Livewire/Admin/Proposals/ProposalFeatureForm.php` — implemented
  the missing `removeFinalFeature()` method (the button was wired to a
  method that didn't exist), added in-place `optional` toggle.
- `resources/views/livewire/admin/proposals/proposal-create.blade.php` —
  two-pane builder (feature library + selected features) with running
  total, serif headings, uses `<x-card>`, `<x-field>`, `<x-select-field>`,
  `<x-money>`.
- `resources/views/livewire/admin/proposals/proposal-edit.blade.php` —
  meta strip (status pill / client / owner / updated) over an editable
  features table using the in-place `ProposalFeatureForm` row component
  and the restyled `ProposalTotalOnTheFly` footer.
- `resources/views/livewire/admin/{clients,features,users}/{client,feature,user}-modal.blade.php`
  — rewritten against `<x-modal>` + `<x-field>` + `<x-btn>` so every
  create/edit flow has the same ink/paper/fox look.
- `tests/Feature/ProposalBuilderTest.php` — 7 tests covering empty
  render, select/remove features, validation, create+redirect, edit
  render, in-place feature edit, feature removal.
- `tests/Feature/ModalTest.php` — 8 tests covering create, edit-load,
  validation for each of client / feature / user modals.
- `resources/views/components/auth-shell.blade.php` — shared wrapper for
  the public auth pages (logo mark, eyebrow, italic serif heading, lede,
  bordered card, optional footer slot).
- `resources/views/components/layouts/app.blade.php` — rewired to load
  Libre Baskerville + Inter and set `bg-paper text-ink` on the body so
  auth pages inherit brand typography.
- `resources/views/livewire/{login,forgotten-password,password-reset}.blade.php`
  — rebuilt against `<x-auth-shell>` + `<x-field>` + `<x-btn>` with
  editorial status messages, `wire:loading` submit states, and
  brand-consistent links.
- `app/Livewire/Login.php` — swapped the `Request::session()` facade
  call for the `session()` helper so the authenticate path is
  exercisable through `Livewire::test` without request-session gymnastics.
- `tests/Feature/{LoginTest,PasswordResetTest}.php` — 10 tests covering
  render + brand copy, validation, invalid/inactive login,
  happy-path redirect, reset-link dispatch, and password update.
- `app/Livewire/Admin/Shared/CommandPalette.php` +
  `resources/views/livewire/admin/shared/command-palette.blade.php` —
  ⌘K / Ctrl+K command palette mounted globally in the admin layout.
  Suggested actions (new proposal / feature / package / teammate),
  recent proposals on empty query, and live-search across proposals,
  clients, features and packages via Laravel Scout. Tenant-scoped via
  Scout `where('tenant_id', ...)` plus the existing global scope.
  Shipping with `SCOUT_DRIVER=database` so there's no external
  dependency; the same code runs against Algolia once credentials are
  in `.env`. Algolia was chosen over Meilisearch / Typesense because
  it's the only one of the three with a permanent free Cloud tier
  ("Build" — 1M records + 10k searches/mo, no credit card). Index
  settings live in `config/scout.php` with `filterOnly(tenant_id)` in
  `attributesForFaceting` for each model so the where-clause filter
  applies. 8 Pest tests in `tests/Feature/CommandPaletteTest.php`
  cover open/close, suggested actions, search across all four models,
  and tenant isolation.
- `app/Livewire/Admin/Settings.php` +
  `resources/views/livewire/admin/settings.blade.php` — full tenant
  settings admin page (currency, tax name/rate, tax-inclusive toggle,
  default share-link expiry in days). Reachable from the nav rail via
  the `gear-six` icon at `/dashboard/settings`. The new
  `default_share_expiry_days` column on `settings` (nullable — null
  means "never") is used by the share modal to pre-populate the
  expiry date picker. `SettingsHelper` was extended to take an
  optional `?int $tenantId` so the public preview can resolve a
  proposal's settings from its own tenant rather than the session.
- `app/Livewire/Public/ProposalPreview.php` + new public route
  `/p/{uuid}` (named `proposal.share`) — the editorial client preview
  is now genuinely public, with no `auth` middleware. The route is
  rate-limited to 120 requests/minute and emits `X-Robots-Tag:
  noindex, nofollow` via `App\Http\Middleware\NoIndex`. Both views
  (admin and public) render the **same** Blade template
  (`livewire/admin/proposals/preview.blade.php`) so design changes
  flow to both surfaces without duplication. The component bypasses
  the tenant scope explicitly (`Proposal::withoutGlobalScope('tenant')`)
  and rebinds the `Settings` singleton to the proposal's own tenant
  so currency / tax / etc. resolve correctly without a session
  tenant. `proposals` gained `expires_at` and `access_code_hash`
  columns: when set, the public preview renders an expired notice or
  a 6-digit code-entry gate instead of the proposal. The unlock
  cookie is an HMAC of `proposal_id|access_code_hash` keyed by
  `app.key`, so regenerating the code changes the hash and
  immediately invalidates every existing cookie on the next visit.
  Code submissions are rate-limited to 5 attempts per
  IP+proposal per 15 min. The admin in-app preview at
  `/dashboard/proposal/preview/{uuid}` ignores expiry + code gates so
  admins always see the proposal unfiltered.
- `app/Livewire/Admin/Proposals/ShareModal.php` +
  `resources/views/livewire/admin/proposals/share-modal.blade.php` —
  a "Share" button on the proposal-edit header opens a modal with
  the public URL (one-click copy), an expiry date picker (defaulting
  to today + the tenant's `default_share_expiry_days` if set), and
  an optional access-code section. Generating a code shows the plain
  6-digit value once, and the bcrypt hash is only persisted on save.
  Tests across `tests/Feature/{PublicProposalPreviewTest,ShareModalTest}.php`
  (15 cases) cover unauth render, tenant isolation, the expiry / code
  gates, code regeneration invalidating cookies, admin bypassing both
  gates, and modal flows for expiry-only and code-only configurations.

## What's left, in rough priority

### 1. Build "Present mode"

The live presentation experience for in-the-room demos. This is the
next big-rock item.

- New route `dashboard.proposal.present` mounting a Livewire component
  on a full-bleed layout (`components.layouts.present`, no rail/topbar).
- Implement against `design-prototypes/present.html`: required vs.
  optional features, fox-yellow toggle, sticky live total in mono.
- Phase 2: a "client mirror" view at a public UUID URL that subscribes
  to Livewire events from the operator — eventual real-time sync.

### 2. Accept / Reject + persisted client toggles

On the editorial client preview, optional toggles are currently
ephemeral (client-side only). Two natural follow-ups:

- An accept/reject affordance that posts the toggled-on optional IDs and
  transitions the proposal to ACCEPTED/REJECTED.
- A signed-URL or session-token flavour of the preview URL that lets
  clients return and see their saved configuration.

### 3. View tracking on shared proposals

Today the public preview at `/p/{uuid}` is read-only — there's no
record of who has actually opened a share link or when. A simple "has
the client seen this?" answer would be high-value on the
proposal-edit page and the dashboard's "needs attention" feed.

- New `proposal_views` table — `proposal_id`, `viewed_at`, hashed IP +
  user-agent fingerprint (so we can roughly distinguish the client
  from the salesperson clicking their own preview link).
- `App\Livewire\Public\ProposalPreview::mount()` records a view when
  it renders the unlocked preview state (not the gate / expired
  states). Dedupe within a short window (e.g. 30 min) per
  fingerprint to avoid re-counting refreshes.
- Surface on the proposal-edit meta strip: "Last seen 2 days ago · 4
  views" or "Not yet viewed".
- Surface on the dashboard: "Stuck delivered" rows could show "Seen
  X days ago" or "Never opened" — that's the truly stuck signal.
- Open question: how granular do we want the dedupe window, and do
  we surface raw view counts or just last-seen-at?

### 4. Per-tenant identity on the customer preview

The public proposal preview at `/p/{uuid}` currently uses the Epic Fox
brand palette and typography, and shows nothing about the issuing
tenant beyond the salesperson's name. For a multi-tenant SaaS that's
sub-optimal — every tenant's clients see the same look and the same
generic footer. v1 scope is **fonts, colours, logo, and contact
details** (no full theme system).

Schema is already partway there: `settings.logo` and
`settings.company_name` columns exist in the migration but neither is
read or written anywhere yet. Fill in the rest:

- **Branding fields on `settings`**:
  - Brand colour columns (e.g. `brand_ink`, `brand_accent`) — hex
    strings, validated as `/#[0-9a-f]{6}/i`.
  - Font selection — most realistic shape is a small curated
    enum/list (Libre Baskerville, Inter, Playfair Display, IBM
    Plex…) rather than letting tenants paste arbitrary font names.
    Two slots: display and body, or just display.
  - Logo upload — single image, stored on Laravel's `public` disk.
    Display preview + replace + remove in the settings page.
    Reasonable size cap (e.g. 1MB) and content-type allowlist.
- **Contact fields on `settings`** (none of these exist yet):
  - `company_email`, `company_phone`, `company_address` (single
    multi-line string is fine for v1; can split into structured
    fields later if needed). The user-account email belongs to the
    salesperson and isn't the right thing to render in a customer
    proposal — the company contact is a tenant-level fact.
- **Settings form**: a new "Brand & company" section on the settings
  page with the logo uploader, colour pickers, font selector, and
  the four contact fields. Existing tax/currency section stays as it
  is.
- **Render path**: the customer preview layout (and only the customer
  preview — not the admin chrome) emits an inline `<style>` block
  that overrides the relevant CSS custom properties on `<body>`:
  `--color-ink`, `--color-fox`, `--font-display`, `--font-sans`.
  Logo replaces the existing fox mark in the masthead. Contact
  details render in the document footer or sidebar (replacing the
  current generic "All prices in GBP…" copy).
- **Tenant resolution**: the existing `Setting::forTenant()` helper
  already covers fetching the proposal's tenant settings without a
  session — extend `SettingsHelper` to expose the new fields the
  same way it currently exposes tax/currency.

Open questions:
- Curated font list vs. free-text? Curated is safer (consistent
  rendering, no third-party leak through `<link rel="preconnect">`).
- Do we offer a "preview your customer view" toggle on the settings
  page so admins can sanity-check the palette before saving?
- How does the access-code gate look when the tenant has rebranded —
  does the gate inherit the brand, or stay neutral so it reads as a
  Configurator-issued auth screen?
- Address: single textarea (good enough for footer rendering) or
  structured (line 1, line 2, city, postcode, country)? Structured
  scales better but is more form to fill in.

### 5. Attachments on proposals (documents + images)

Allow proposals to carry uploaded documents and images. Primary use
case is **site maps** — sketches or diagrams that contextualise the
proposed work — but the same primitive could carry contracts, mood
boards, screenshots, signed agreements, etc. The shape is
deliberately deferred until this is picked up.

Open questions worth banging out before any code:

- Where do attachments surface in the customer preview? Inline
  within feature sections, a dedicated "Documents" group at the
  bottom, or a side rail?
- Allowed file types — images + PDF only, or broader (DWG, etc.)?
  Size cap?
- Does an attachment belong to the whole proposal, or can it be
  attached to a specific feature?
- Storage — Laravel `public` disk vs. a private disk with signed
  URLs that respect the same expiry / access-code gates as the
  share link itself? (Sensitive site maps probably want the same
  gate as the proposal text.)
- Admin UX — a single drop-zone on the proposal-edit page, or a
  modal flow? Reorder / caption?

### 6. Smaller cleanups

- Description / additional-notes editing in the proposal admin (both
  fields render beautifully on the client preview if set, but there's
  no admin UI to edit them).
- `feature.created` and `proposal.deleted` events for fuller activity
  feed coverage.
- A "View all activity" page if the 8-row dashboard panel feels
  insufficient.
- Periodic purge of activities older than ~12 months once volume
  warrants it.
- Bell icon in the topbar is purely visual — no notifications system
  behind it.
- Mobile / tablet support — the rail will need a collapsible/off-canvas
  treatment. Deferred until desktop is locked in.
- Drop the legacy `primary/*` / `success/*` / `warning/*` / `gray/*`
  ramps from `resources/css/app.css` and the `.button*` / `.toastify*`
  classes that depend on them, now that everything renders against
  brand tokens. Also revisit whether to keep Toastify + SweetAlert2 or
  replace with in-house components.
- Three pre-redesign legacy components are still on disk but
  unreferenced anywhere in views/PHP: `livewire/admin/shared/{progress,
  select}.blade.php`, `components/{info,alert}.blade.php` (plus their
  `App\Livewire\Admin\Shared\*` PHP). Safe to delete in a tidy-up pass.
- Flip `SCOUT_DRIVER` to `algolia` once the Algolia "Build" project is
  provisioned. Set `ALGOLIA_APP_ID` / `ALGOLIA_SECRET` in `.env`, then
  `php artisan scout:sync-index-settings` (pushes the index settings
  declared in `config/scout.php`, including `filterOnly(tenant_id)`),
  followed by `php artisan scout:import "App\\Models\\Proposal"`
  (repeat for `Client`, `Feature`, `Package`) to seed the indexes.

## Where things live

- Static design reference: `design-prototypes/` (HTML/CSS only — not served).
- Brand tokens: `resources/css/app.css` (top of `@theme` block).
- Layout shell: `resources/views/components/layouts/admin.blade.php`.
- Shared components: `resources/views/components/{logo,menu-item,pill,page-header,card,card-header,btn,money,th,field,checkbox-field,select-field,modal}.blade.php`.
- Done dashboard: `resources/views/livewire/admin/dashboard.blade.php` +
  `app/Livewire/Admin/Dashboard.php`.
- Coverage pattern to copy for new pages: `tests/Feature/DashboardTest.php`.
