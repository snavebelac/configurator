# Configurator

> Working title — the product name is not finalised. "ConfiguPro" appears in
> earlier copy but is unlikely to be what we ship under.

Configurator is a multi-tenant web app for building **interactive pricing
proposals**. Instead of sending a flat PDF and waiting for the client to come
back with questions, you sit with them — in the room or on a call — and shape
the proposal together in real time.

## What it does

Each subscriber gets their own tenancy with their own data. Inside that tenancy
you build up:

- **A feature library** — the building blocks of what you sell, each with its
  own price.
- **Clients** — the people you're quoting for.
- **Proposals** — a curated selection of features attached to a client.

When you walk a client through a proposal, every feature can be ticked or
unticked. Features you mark as **optional** are the ones the client is allowed
to drop; the rest are core to the work and stay in. As toggles change, the
proposal total recalculates instantly.

## The presentation experience (planned)

The longer-term shape of the product is a two-screen setup designed for live
walkthroughs — typically over a screen share on a Zoom call:

- **Operator screen** — what you see. The full controls: tick/untick features,
  adjust quantities, see margins and notes.
- **Client screen** — what they see. A clean, presentation-grade view of the
  same proposal that updates live as you make changes on the operator side.

The goal is a quoting conversation that feels collaborative rather than
transactional, with no "I'll send you a revised version" round-trips.

## Where we are today

The foundations are in place and usable internally:

- Sign in, multi-tenant data isolation, role-based permissions.
- Manage users, clients, and a feature library inside a tenancy.
- Build proposals from features, preview them by shareable link, and walk
  through proposal status (draft → delivered → accepted/rejected → archived).
- A separate, simpler proposal output exists too — a lightweight HTML/CSS
  document with aims, scopes, prices and a summary — that we use for client
  reads. This will eventually merge into the interactive proposal flow so there
  is one tool, not two.

## What's coming

- **Self-serve sign-up.** Today tenancies are provisioned by us; the plan is
  for new customers to register themselves and land in their own workspace
  immediately.
- **Live two-screen presentation mode** as described above.
- **Unifying the two proposal formats** so the live, interactive proposal and
  the polished read-only document are the same thing seen two ways.

## Status

Configurator started as an internal tool for our own client work. We are
building it with the intent to offer it more widely once the self-serve and
live-presentation pieces land. Expect the product, name, and pricing to evolve
between now and then.
