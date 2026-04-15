# Admin redesign — work in progress

This file tracks the multi-session refactor of the Configurator admin panel
to the Epic Fox brand UI. Update it as work lands. Final, version-pinned
descriptions go in `CHANGELOG.md`; this file is the **mid-flight checkpoint**.

## Where we are

The dashboard (`/dashboard`) has been rebuilt against the new design system
and the admin layout shell now matches the agreed direction. Other admin
pages (proposals, clients, features, users, profile, settings) still use
the legacy `primary/*` blue palette and will look visually inconsistent
inside the new shell until they're migrated.

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
- `app/Livewire/Admin/Dashboard.php` — real KPI computation.
- `resources/views/livewire/admin/dashboard.blade.php` — KPI strip,
  attention feed, recent feed, segmented table.
- `tests/Feature/DashboardTest.php` — empty-state + populated-state
  coverage.

## What's left, in rough priority

### 1. Migrate the remaining admin pages to the new shell

Each page below currently renders in the new layout but with old `primary/*`
classes. Rewrite to use brand tokens (`bg-paper`, `border-rule`, `text-ink`,
`<x-pill>`, etc.) following the prototype patterns.

- `resources/views/livewire/admin/proposals/proposals-list.blade.php` →
  match the **`design-prototypes/proposals.html`** pattern: page header,
  segmented control by status, single clean table, search + filter
  toolbar. Component already loads what we need.
- `resources/views/livewire/admin/proposals/proposal-create.blade.php` and
  `proposal-edit.blade.php` — the proposal builder. The biggest piece
  here. Worth its own session.
- `resources/views/livewire/admin/clients/*` — list + create/edit modal.
- `resources/views/livewire/admin/features/*` — list + create/edit modal.
- `resources/views/livewire/admin/users/*` — list + create/edit modal.
- `resources/views/livewire/admin/profile.blade.php`.

For modals (`wire-elements/modal`), audit the modal patterns and decide
whether to keep the off-the-shelf component styling or restyle.

### 2. Wire the command palette

Today the topbar's search trigger is purely visual.

- New Livewire component `App\Livewire\Admin\Shared\CommandPalette` rendered
  inside the admin layout, listening for `⌘K` / `Ctrl+K` via Alpine.
- Items: navigate (proposals, clients, features, settings), create new
  (proposal, client, feature, user), and search across proposals + clients
  + features by name.
- Match the visual pattern from `design-prototypes/dashboard.html` (search
  input top, grouped sections, focused-item ink-on-fox highlight, footer
  hints).

### 3. Replace the "Lately" stand-in with a real activity feed

The dashboard currently shows the last eight updated proposals as a stand-in
for activity. Real activity needs an event store.

- Add an `Activity` model + migration: `id, tenant_id, user_id, subject_type,
  subject_id, action (enum), payload (json), created_at`. Use
  `BelongsToTenant` so it's automatically tenant-scoped.
- Hook `created` / `updated` (specifically status transitions) on `Proposal`
  to record events.
- Update the Dashboard component to load the latest 8 events instead of
  recently-updated proposals.

### 4. Build "Present mode"

Once the back-office is consistent, build the live presentation experience:

- New route `dashboard.proposal.present` mounting a Livewire component on a
  full-bleed layout (`components.layouts.present`, no rail/topbar).
- Implement against `design-prototypes/present.html`: required vs. optional
  features, fox-yellow toggle, sticky live total in mono.
- Phase 2: a "client mirror" view at a public UUID URL that subscribes to
  Livewire events from the operator — eventual real-time sync.

### 5. Mobile / tablet support

Deferred until the desktop pass is complete. The current rail will need a
collapsible/off-canvas treatment at narrow widths.

### 6. Drop the legacy `primary/*` palette

Once every admin page uses brand tokens, remove the `primary/*`,
`success/*`, `warning/*`, `gray/*` ramps from `resources/css/app.css` and
the `.button*` / `.toastify*` classes that depend on them. Also revisit
whether to keep Toastify + SweetAlert2 or replace with in-house components.

## Where things live

- Static design reference: `design-prototypes/` (HTML/CSS only — not served).
- Brand tokens: `resources/css/app.css` (top of `@theme` block).
- Layout shell: `resources/views/components/layouts/admin.blade.php`.
- Shared components: `resources/views/components/{logo,menu-item,pill}.blade.php`.
- Done dashboard: `resources/views/livewire/admin/dashboard.blade.php` +
  `app/Livewire/Admin/Dashboard.php`.
- Coverage pattern to copy for new pages: `tests/Feature/DashboardTest.php`.
