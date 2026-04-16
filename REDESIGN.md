# Admin redesign — work in progress

This file tracks the multi-session refactor of the Configurator admin panel
to the Epic Fox brand UI. Update it as work lands. Final, version-pinned
descriptions go in `CHANGELOG.md`; this file is the **mid-flight checkpoint**.

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

## What's left, in rough priority

### 1. Wire the command palette

Today the topbar's search trigger is purely visual.

- New Livewire component `App\Livewire\Admin\Shared\CommandPalette` rendered
  inside the admin layout, listening for `⌘K` / `Ctrl+K` via Alpine.
- Items: navigate (proposals, clients, features, settings), create new
  (proposal, client, feature, user), and search across proposals + clients
  + features by name.
- Match the visual pattern from `design-prototypes/dashboard.html` (search
  input top, grouped sections, focused-item ink-on-fox highlight, footer
  hints).

### 2. Replace the "Lately" stand-in with a real activity feed

The dashboard currently shows the last eight updated proposals as a stand-in
for activity. Real activity needs an event store.

- Add an `Activity` model + migration: `id, tenant_id, user_id, subject_type,
  subject_id, action (enum), payload (json), created_at`. Use
  `BelongsToTenant` so it's automatically tenant-scoped.
- Hook `created` / `updated` (specifically status transitions) on `Proposal`
  to record events.
- Update the Dashboard component to load the latest 8 events instead of
  recently-updated proposals.

### 3. Build "Present mode"

Once the back-office is consistent, build the live presentation experience:

- New route `dashboard.proposal.present` mounting a Livewire component on a
  full-bleed layout (`components.layouts.present`, no rail/topbar).
- Implement against `design-prototypes/present.html`: required vs. optional
  features, fox-yellow toggle, sticky live total in mono.
- Phase 2: a "client mirror" view at a public UUID URL that subscribes to
  Livewire events from the operator — eventual real-time sync.

### 4. Mobile / tablet support

Deferred until the desktop pass is complete. The current rail will need a
collapsible/off-canvas treatment at narrow widths.

### 5. Drop the legacy `primary/*` palette

Once every admin page uses brand tokens, remove the `primary/*`,
`success/*`, `warning/*`, `gray/*` ramps from `resources/css/app.css` and
the `.button*` / `.toastify*` classes that depend on them. Also revisit
whether to keep Toastify + SweetAlert2 or replace with in-house components.

## Where things live

- Static design reference: `design-prototypes/` (HTML/CSS only — not served).
- Brand tokens: `resources/css/app.css` (top of `@theme` block).
- Layout shell: `resources/views/components/layouts/admin.blade.php`.
- Shared components: `resources/views/components/{logo,menu-item,pill,page-header,card,card-header,btn,money,th,field,checkbox-field,select-field,modal}.blade.php`.
- Done dashboard: `resources/views/livewire/admin/dashboard.blade.php` +
  `app/Livewire/Admin/Dashboard.php`.
- Coverage pattern to copy for new pages: `tests/Feature/DashboardTest.php`.
