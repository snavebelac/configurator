# Admin redesign — work in progress

This file tracks the multi-session refactor of the Configurator admin panel
to the Epic Fox brand UI. Update it as work lands. Final, version-pinned
descriptions go in `CHANGELOG.md`; this file is the **mid-flight checkpoint**.

> **Pickup note (2026-08-19, unreleased — no version cut yet):** The
> client-facing proposal now works as a real share link at `/p/{uuid}` — no
> auth, optional passcode and expiry, and the client can accept or decline.
> Tenants can be created from `/signup` or `php artisan tenant:create`, and
> there is a settings screen with a workspace logo that brands the client
> proposal. Two long-standing settings bugs are fixed (see CHANGELOG
> [Unreleased]). The nav collapses on desktop and is an off-canvas drawer
> below `lg`; table row titles open their row. The legacy colour ramps,
> SweetAlert2 and axios are gone. Tests run on in-memory SQLite; dev stays on
> MySQL. All dependencies are current.
>
> **Versioned terms & conditions are done** (2026-08-19): named sets, draft →
> publish versioning, pinned to a proposal on delivery and recorded again on
> acceptance, rendered at the foot of the client proposal. **Percentage-based
> features** followed: priced as a share of the selected fixed lines, flat
> rather than compounding, in their own section on the client view. Proposal
> maths now lives in one place, `App\Helpers\ProposalPricing`. **Ongoing and
> recurring costs** followed: a third pricing type, billed monthly or annually,
> totalled per period and never mixed into the one-off figure.
>
> That completes everything that changes what a proposal *contains*. **Present
> mode is next** — and it now has the full picture to present: fixed work,
> nested features, percentages, recurring costs and terms. It was **fully
> scoped on 2026-08-20** (see item 2 below); no code written yet.
>
> Folding in the user's separate stand-alone proposal app is **cancelled** —
> the two have diverged too far to be worth porting anything; rebuild here
> instead.

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
- **Audience scope** — desktop / laptop first. The nav is responsive as of
  2026-08-19 (off-canvas drawer below `lg`); page content is not yet.
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

### 1. Extend the activity feed coverage

The Lately panel now reads from a real `Activity` event store
(tenant-scoped, polymorphic `subject`, action enum, JSON payload). Model
observers on `Proposal`, `Client` and `Package` write events for create
+ status-changed lifecycle moments. Headlines like "Caleb created Brand
identity system" / "Caleb moved X to Delivered" render in the dashboard
panel with subject-type-coloured icons. The dashboard also gained
"New feature" + "New package" quick-action buttons in the page header.

Things worth adding next:
- `feature.created` and `proposal.deleted` events for fuller coverage.
- A "View all activity" page if the 8-row panel feels insufficient.
- Retention is now its own item (7) rather than a bullet here.
- Note `proposal.accepted` / `proposal.rejected` already exist, added with
  the public client view.

### 2. Build "Present mode"

The live presentation experience for in-the-room and screen-shared demos: an
operator screen with full controls, and a client screen showing a clean view of
the same proposal that updates as the operator changes things. Static prototype
at `design-prototypes/present.html`.

Shape: route `dashboard.proposal.present` mounting a Livewire component on a
full-bleed `components.layouts.present` with no rail or topbar; required vs.
optional features, fox-yellow toggle, sticky live total in mono.

**Scoped and settled 2026-08-20 — these are decisions, not options.**

**The model is a shared spreadsheet.** That was literally the thing this
replaces: a sheet on a Zoom screen-share where ticking a row or changing a
quantity moved the total in front of everyone. So everything the operator does
takes effect immediately and visibly on both screens.

**Durable, but uncommitted.** Every toggle, quantity change and note writes to a
*working copy* the instant it happens — close the laptop, reopen, it is all
still there. What is deferred is not saving but **applying**: the proposal of
record keeps its own figures until the operator says so. Presenting can
therefore never silently rewrite a proposal, while nothing typed in the room is
ever at risk.

- **Storage** — a `presentations` row (proposal, operator, client-screen token,
  lifecycle timestamps, a version counter for cheap polling) plus a
  `presentation_lines` row per feature carrying `included`, `quantity` and
  `notes`. Per-line rows rather than one JSON blob, so a 4,000-character note
  being typed can't be clobbered by a toggle landing at the same moment.
- **In scope per line** — include/exclude, quantity, internal notes. **Price
  editing is deliberately out** for v1; renegotiating a rate mid-call is a
  bigger conversation than this feature.
- **Adding lines is in scope** — the operator can pull a feature out of the
  library mid-call via the existing `FeaturePicker`, which already handles
  parent auto-attach and de-dup. Packages are not (too much surface for a
  screen built to present).
- **Applying** — a pending-changes panel sits on the operator screen the whole
  time, not a modal on exit, so changes can be applied mid-call as often as
  wanted. If a presentation ends without applying, a banner on the proposal
  spells out what changed ("Photography turned off · Site refresh qty 1→2 · 2
  notes added") with Apply and Discard.
- **Transport** — `wire:poll` at roughly a second against the presentation's
  version counter. **No broadcasting**: nothing is installed (no Reverb, no
  Pusher, no laravel-echo; `BROADCAST_CONNECTION=log`), and a websocket server
  is a dependency plus a process plus a deployment story to buy latency nobody
  in the room can perceive. The state lives in a row either way, so this is a
  transport swap later, not a redesign.
- **The client screen is its own component** on `components.layouts.present`,
  sharing partials and `ProposalPricing` with `/p/{uuid}` rather than forking
  it. The share link is built for solo reading at desk distance with an accept
  CTA; a screen someone else is driving is a different job, and every
  presentation tweak would otherwise risk the share link. Reached by the
  presentation token so there is no passcode dance mid-demo.
- **Operator-only** — internal notes (`final_features.notes`, currently unused)
  and a "still on the table" figure for what is toggled off. **Not margin**:
  there is no cost data anywhere in the schema, so margin needs a `cost` column
  first and is its own feature.
- **Accepting in the room** — the operator *arms* the accept step and the
  **client** presses accept on the client screen, so the `ProposalResponse`
  records the client's own act rather than the operator's IP and click.

**Accepting is itself the commit — this is forced, not a preference.**
`ProposalResponse` recomputes the total server-side from `final_features`, so
accepting against an un-applied working copy would record the old quantities
and the wrong money. Accept therefore applies the working copy and records the
response in one transaction. Which means the review/apply banner only ever
appears for presentations that ended *without* an acceptance — exactly the case
where thinking before applying is worth something.

**Known work this lands on:**

- `ProposalView::accept()` has to come out into a shared action first — both
  paths must record responses through the same code, never a copy. It is the
  code that already refuses to trust client-submitted prices.
- The Alpine pricing mirror is buried inside the 530-line
  `livewire/public/proposal-view.blade.php` with only one partial extracted.
  Pulling it out is part of this job.
- `proposal_responses` needs to record how it was accepted (share link vs. in
  the room) and which presentation it came from.
- The client screen is unauthenticated, so the tenant scope is a no-op there:
  name the tenant explicitly with `Settings::forTenant()` and stamp `tenant_id`
  by hand, exactly as `ProposalView` does.
- Don't hard-code brand colours into the client screen — tenant-level branding
  of client-facing views is a wanted future feature, and every literal is work
  it will have to undo.

Pricing is already solved and must be reused rather than reimplemented:
`App\Helpers\ProposalPricing` in pence, covering fixed work, percentages and
recurring costs. Quantity changes flow through it, so percentage lines re-cut
themselves for free.

### 3. Wire the command palette

Topbar's `⌘K` search trigger is still purely visual.

- New Livewire component `App\Livewire\Admin\Shared\CommandPalette`
  rendered inside the admin layout, listening for `⌘K` / `Ctrl+K` via
  Alpine.
- Items: navigate (proposals, clients, features, packages, settings),
  create new (proposal, package, client, feature, user), and search
  across proposals + clients + features + packages by name.
- Match the visual pattern from `design-prototypes/dashboard.html`.

### 4. Activity retention

The `activities` table grows without bound. Promoted out of "smaller cleanups"
because a purge that silently deletes history is a product decision, not a
chore. `Prunable` plus the scheduler is the obvious mechanism; the questions to
settle first are the retention window, whether it's per-tenant configurable
(which would put it on the settings screen), and whether anything needs
exporting before deletion.

### 5. Responsive page content

The nav went responsive on 2026-08-19 — off-canvas drawer below `lg`, adaptive
topbar — but page *content* is still built desktop-first, so this is a feature
in its own right rather than a tidy-up. Best tackled once the feature set has
settled, since every screen added before then is another screen to make
responsive.

What needs attention:

- Wide admin tables (proposals, clients, features, packages, users) — the
  usual choices are horizontal scroll within the card, hiding low-value
  columns at narrow widths, or reflowing rows into stacked cards.
- The two-pane proposal builder (`ProposalCreate`) — library on the left,
  selection on the right. Needs to become one pane at a time on a small
  screen.
- The proposal edit screen's feature table, which carries per-row inline
  editing and drag-reorder.
- The client-facing proposal's summary rail, currently `hidden lg:block` — a
  client is *more* likely than an admin to open their proposal on a phone, so
  the running total needs a small-screen home rather than disappearing.
- The dashboard's four-column KPI strip and its `grid-cols-[1.3fr_1fr]` split.
- The proposal builder and modals generally, which assume pointer input.

### 6. Getting a real terms document into the editor

Terms are authored in the rich-text editor, and the assumption behind that is
that somebody types or pastes them. In practice a tenant's terms usually arrive
from a lawyer as a Word document or a PDF, and neither pastes well.

**What actually happens.** Pasting from a web page or from Word in the browser
is fine — real `<ol>`/`<li>` reach the clipboard and TipTap parses them. Pasting
from **desktop** Word is not: it puts no list on the clipboard at all. Each item
is a paragraph carrying `style='mso-list:l0 level1 lfo1'`, with the "1." as
literal text inside a span Word marks `mso-list:Ignore`. The `mso-list` style is
the only record that it was ever a list, and we strip attributes on save. So the
numbers freeze into the text: insert a clause and nothing renumbers. It looks
almost right, which is the dangerous part on a document with legal weight. PDF
is worse — plain text only, with hard line breaks mid-sentence.

Upstream won't fix this for us: [tiptap#1526] was closed as completed in July
2026 with "Word paste should work significantly better now", and re-tested two
weeks later by a user who found desktop Word still broken while browser Word
worked.

**Done (2026-08-20):** the editor carries a note telling authors to paste from
Word as plain text and re-apply structure. Cheap, honest, and because terms are
versioned it is a once-per-set chore rather than a recurring one.

**Worth building later, in order of appetite:**

- **Attach the document instead of retyping it.** Let a tenant either author
  terms in the editor *or* upload the lawyer's PDF/DOCX and have the proposal
  link to it. The smaller of the two, and it sidesteps fidelity entirely —
  nobody has to reproduce a legal document in an allowlist that has no tables
  and no `4.2.1` numbering. Needs thought about how an attachment pins to a
  version the way a body does, and what the client-facing page shows in place
  of rendered terms.
- **Import a .docx into the draft.** [mammoth.js] converts .docx to semantic
  HTML by reading the document's *styles* rather than the clipboard, so lists
  survive as lists. Actively maintained. This fits the existing flow exactly:
  import is just another way to fill a draft, and publishing stays a deliberate
  act with a human review in between.
- A paste transformer via `transformPastedHTML` is possible but not
  recommended. The order is load-bearing — read the `mso-list` markers, rebuild
  the list, *then* clean; clean first and the evidence is gone, and it fails
  silently with valid-but-wrong HTML. [Intevation's office-paste extension] is
  the established option, untouched since Sept 2025.
- Tiptap's own [Conversion API] does official DOCX import, needs a paid
  subscription, and sends the document to a third party — wrong for terms
  nobody wants leaving the building.

**The constraint behind all of it:** the allowlist has no tables and `<ol>`
gives 1./2./3., not `4.2.1`. Even a perfect import produces something that
doesn't look like the source. That is the real argument for the attachment
route, and it is adjacent to letting tenants brand their client-facing pages.

[tiptap#1526]: https://github.com/ueberdosis/tiptap/issues/1526
[mammoth.js]: https://github.com/mwilliamson/mammoth.js/
[Intevation's office-paste extension]: https://github.com/Intevation/tiptap-extension-office-paste
[Conversion API]: https://tiptap.dev/docs/conversion/import/docx/editor-extension

## Where things live

- Static design reference: `design-prototypes/` (HTML/CSS only — not served).
- Brand tokens: `resources/css/app.css` (top of `@theme` block).
- Layout shell: `resources/views/components/layouts/admin.blade.php`.
- Shared components: `resources/views/components/{logo,menu-item,pill,page-header,card,card-header,btn,money,th,field,checkbox-field,select-field,modal}.blade.php`.
- Done dashboard: `resources/views/livewire/admin/dashboard.blade.php` +
  `app/Livewire/Admin/Dashboard.php`.
- Coverage pattern to copy for new pages: `tests/Feature/DashboardTest.php`.
