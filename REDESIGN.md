# Admin redesign — work in progress

This file tracks the multi-session refactor of the Configurator admin panel
to the Epic Fox brand UI. Update it as work lands. Final, version-pinned
descriptions go in `CHANGELOG.md`; this file is the **mid-flight checkpoint**.

## Where we are

All list-style admin pages (dashboard, proposals, clients, features, users)
and the profile form have been rebuilt against the new design system, on
top of a shared set of Blade primitives (`<x-page-header>`, `<x-card>`,
`<x-card-header>`, `<x-btn>`, `<x-money>`, `<x-th>`, `<x-pill>`). The only
un-migrated back-office surface is the proposal builder (create/edit) —
that's flagged below as its own session because of its size.

The wire-elements modals (client / feature / user / proposal-feature)
still render with their default styling inside the new shell. Restyling
those is also pending.

The static reference for the whole direction lives in `design-prototypes/`
(`dashboard.html`, `proposals.html`, `present.html`). Open
`design-prototypes/dashboard.html` in a browser before continuing.

## Decisions locked in (don't re-litigate)

- **Palette** — Epic Fox: `#242423` ink, `#CFDBD5` sage, `#F5CB5C` fox-yellow,
  `#5F6463` slate. Yellow is reserved for primary CTA, active nav indicator,
  the "open pipeline" KPI tile, the live-presentation total, and the
  "live" indicator. Used at most twice per screen.
- **Type pairing** — Fraunces (display, with `SOFT 50` on headlines and
  `SOFT 80` on the giant present-mode total) + Geist (body) + JetBrains
  Mono (numerals).
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

## What's left, in rough priority

### 1. Migrate the proposal builder

- `resources/views/livewire/admin/proposals/proposal-create.blade.php` and
  `proposal-edit.blade.php` — the biggest remaining piece, and worth its
  own session. Rewrite to use brand tokens and the shared components
  (`<x-page-header>`, `<x-card>`, `<x-btn>`, `<x-money>`), matching the
  proposal-edit flow implied by the dashboard and proposals-list patterns.
- `resources/views/livewire/admin/proposals/proposal-feature-form.blade.php`
  — the feature-add modal that sits inside the builder.

### 2. Restyle the wire-elements modals

The modals (`client-modal`, `feature-modal`, `user-modal`,
`proposal-feature-form`) all still use the old `primary/*` form styling.
Audit the `wire-elements/modal` skin and decide whether to publish the
vendor views and restyle, or wrap our form controls in a shared form-field
component first so the styling is consistent regardless of whether a form
renders on a page or inside a modal.

### 3. Wire the command palette

Today the topbar's search trigger is purely visual.

- New Livewire component `App\Livewire\Admin\Shared\CommandPalette` rendered
  inside the admin layout, listening for `⌘K` / `Ctrl+K` via Alpine.
- Items: navigate (proposals, clients, features, settings), create new
  (proposal, client, feature, user), and search across proposals + clients
  + features by name.
- Match the visual pattern from `design-prototypes/dashboard.html` (search
  input top, grouped sections, focused-item ink-on-fox highlight, footer
  hints).

### 4. Replace the "Lately" stand-in with a real activity feed

The dashboard currently shows the last eight updated proposals as a stand-in
for activity. Real activity needs an event store.

- Add an `Activity` model + migration: `id, tenant_id, user_id, subject_type,
  subject_id, action (enum), payload (json), created_at`. Use
  `BelongsToTenant` so it's automatically tenant-scoped.
- Hook `created` / `updated` (specifically status transitions) on `Proposal`
  to record events.
- Update the Dashboard component to load the latest 8 events instead of
  recently-updated proposals.

### 5. Build "Present mode"

Once the back-office is consistent, build the live presentation experience:

- New route `dashboard.proposal.present` mounting a Livewire component on a
  full-bleed layout (`components.layouts.present`, no rail/topbar).
- Implement against `design-prototypes/present.html`: required vs. optional
  features, fox-yellow toggle, sticky live total in mono.
- Phase 2: a "client mirror" view at a public UUID URL that subscribes to
  Livewire events from the operator — eventual real-time sync.

### 6. Mobile / tablet support

Deferred until the desktop pass is complete. The current rail will need a
collapsible/off-canvas treatment at narrow widths.

### 7. Drop the legacy `primary/*` palette

Once every admin page uses brand tokens, remove the `primary/*`,
`success/*`, `warning/*`, `gray/*` ramps from `resources/css/app.css` and
the `.button*` / `.toastify*` classes that depend on them. Also revisit
whether to keep Toastify + SweetAlert2 or replace with in-house components.

## Where things live

- Static design reference: `design-prototypes/` (HTML/CSS only — not served).
- Brand tokens: `resources/css/app.css` (top of `@theme` block).
- Layout shell: `resources/views/components/layouts/admin.blade.php`.
- Shared components: `resources/views/components/{logo,menu-item,pill,page-header,card,card-header,btn,money,th}.blade.php`.
- Done dashboard: `resources/views/livewire/admin/dashboard.blade.php` +
  `app/Livewire/Admin/Dashboard.php`.
- Coverage pattern to copy for new pages: `tests/Feature/DashboardTest.php`.
