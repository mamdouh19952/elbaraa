# Fullstack (Laravel API + Vue SPA) — Root CLAUDE.md

Root convention file for a **monorepo fullstack project**: a Laravel API backend + a Vue 3 SPA
frontend living side by side. This file is read **first** and holds only the rules that are
cross-cutting — the contract between the two sides, the monorepo layout, and how to run/commit
across both. It does **not** duplicate the full Laravel or Vue standards; those live one level
down and take over once you're working inside their folder. It also does **not** duplicate the
full functional specification — the detailed, up-to-date requirements live in the Spec-Kit
specification (§9), not here.

## 1. Project Overview

**El Baraa Arabians (مربط البراء)** — a showcase website for a single Arabian horse owner/breeder
(Tamer Eladawy). One Vue application serves both:

- A **public website**: Home, Horses (listing), Horse Details, About Us, Contact.
- An **admin dashboard** (same Vue app, auth-gated): the owner manages horses, their images/video,
  categories, status, and basic site content.

There is no multi-tenant, multi-breeder, or multi-admin concept — this is a single-owner MVP.
Full functional detail (user stories, requirements, entities, success criteria) lives in the
Spec-Kit spec — see §9. This file only holds the rules that must stay true regardless of which
part of the spec is being implemented.

## 2. Layering — which file governs what

```
project/
├── CLAUDE.md        ← you are here: cross-cutting rules, read first
├── .specify/         ← one Spec-Kit installation for the whole project (§9)
├── .claude/skills/   ← speckit-* skills (specify/plan/tasks/implement/...)
├── specs/            ← feature specs, e.g. specs/001-horse-website-mvp/
├── back/
│   └── CLAUDE.md      ← full Laravel API standard, filled in (§18 Project Specifics)
└── front/
    ├── CLAUDE.md      ← full Vue standard, filled in (Project Overview)
    └── .claude/
        └── CLAUDE.md  ← duplicate of front/CLAUDE.md — keep both in sync until consolidated
```

> `.specify/`, `.claude/skills/`, and `specs/` live at the **project root**, as siblings of
> `back/` and `front/` — not nested inside either. Spec-Kit resolves its project root by walking
> *upward* from wherever a command runs looking for a `.specify/` directory; if it lived inside
> `back/`, running a Spec-Kit command from `front/` would never find it (they're siblings, not
> ancestor/descendant). Root placement also matches reality: a feature spec here covers both the
> API and the SPA together, not one side.

- **Working anywhere in `back/`**: this file's contract (§5–§7 below) + `back/CLAUDE.md`
  (the full Laravel API standard) + the `laravel-api-style` skill. Controllers hold the logic by
  default — no Service layer except Media — per that standard.
- **Working anywhere in `front/`**: this file's contract (§5–§7 below) + `front/CLAUDE.md`
  (the full Vue standard) + the `vue-composition-patterns` skill.
- **This root file wins** on anything that touches both sides (the envelope shape, auth strategy,
  API base URL, CORS, monorepo git conventions). If `back/CLAUDE.md` or `front/CLAUDE.md`
  ever conflicts with §5–§7 here, this file is the tie-breaker — fix the stack file instead of
  silently picking one.

> **Note on `front/`'s duplicate CLAUDE.md**: `front/CLAUDE.md` and `front/.claude/CLAUDE.md` are
> currently near-duplicates (same template, minor drift). Both have been updated with this
> project's Project Overview so neither is stale. Consider asking to consolidate to one later —
> not done here since removing a file wasn't part of this pass.

## 3. Tech Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 12, PHP ^8.2, MySQL + Eloquent, API-only (`routes/api.php`) |
| Frontend | Vue 3, Composition API + `<script setup>`, Pinia, Vue Router, Tailwind CSS |
| Auth | Laravel Sanctum — token-based (see §6) |
| Media | `spatie/laravel-medialibrary` for horse images/video, wrapped in a `MediaService` |
| i18n | Arabic + English, Arabic default, full RTL/LTR (see §8) — exact library TBD in `/plan` |
| Build | Vite |

Since the Vue app is a **standalone SPA in its own folder**, the backend is always API-only —
no Blade dual-track (that's a different project shape, see `back/CLAUDE.md`'s note on Blade
projects). The Laravel side currently ships with the default **Jetstream + Livewire** starter-kit
scaffolding (Blade auth views, `routes/web.php` dashboard route) from `laravel/laravel` — this is
unused starter boilerplate, not part of the product. Whether to strip it or leave it dormant in
favor of a pure Sanctum API login is a decision for `/plan`, not this file.

## 4. Setting up a new fullstack project

*(General recipe this project itself was bootstrapped from — kept here for the next one.)*

1. Scaffold `back/` (Laravel) and `front/` (Vue + Vite) as siblings.
2. Copy `laravel/CLAUDE.md` → `back/CLAUDE.md`, fill in its §18 Project Specifics.
3. Copy `vue/CLAUDE.md` → `front/CLAUDE.md`, fill in its Project Overview.
4. Fill in §7 Project Specifics below (API base URL, auth strategy, domains).
5. Confirm the response envelope (§5) and auth flow (§6) match what this specific project actually
   needs before writing the first endpoint — don't assume, ask if unclear.

## 5. The Contract — response envelope

**Non-negotiable, identical on both sides.** The backend returns this shape on every endpoint; the
frontend never assumes anything else:

```php
// success
['status' => true,  'message' => '...', 'data' => ...]
// error
['status' => false, 'message' => '...']
// validation (422, from ApiFormRequest)
['status' => false, 'message' => 'Validation error', 'errors' => [...]]
```

Frontend composables/stores read `response.data.data` for payload and
`response.data.message` / `error.response?.data?.message` for user-facing text — never invent a
different fallback shape. This project has no established prior envelope to reconcile with (fresh
scaffold) — this is simply the shape to build to from the start.

## 6. Auth flow (Sanctum, cross-origin SPA)

Because frontend and backend are **separate apps** (different dev servers, likely different
deploy origins), this project uses **Sanctum token auth**, not cookie-based SPA auth:

- Backend: `POST /api/login` → `$user->createToken('API TOKEN')->plainTextToken`. Protect routes
  with `auth:sanctum`. Logout: `currentAccessToken()->delete()`.
- Frontend: store the token in the Pinia auth store + `localStorage`; attach it as
  `Authorization: Bearer <token>` on every Axios request; `getTokens()` runs in `App.vue`'s
  `onMounted` to restore session on reload.
- Single admin account (the owner) — there is no public self-registration flow. The `User` model
  and Sanctum's personal-access-tokens table already exist from the Jetstream scaffold (§3); reuse
  them rather than building a parallel auth system.

## 7. Dev environment & CORS

- Backend serves the API (default `http://localhost:8000`), frontend runs its own Vite dev server
  (default `http://localhost:5173`) — two processes, not one.
- Frontend reads the API origin from an env var (`VITE_API_BASE_URL`), never hardcodes it.
- Backend `config/cors.php` must allow the frontend's dev + prod origins explicitly — no `*`
  wildcard once credentials/tokens are involved.
- `.env` files stay per-app (`back/.env`, `front/.env`) and are never committed.
- `back/public/storage` is currently a **stale symlink** pointing at a different, unrelated
  project's path (leftover from copying this Laravel app from another local project). It will need
  `php artisan storage:link` re-run once media upload work starts — noted here so it isn't
  mistaken for an intentional config.

## 8. Localization & RTL/LTR

- **Arabic + English**, both fully supported — not just translated strings, but a real direction
  switch (navigation order, alignment, icon mirroring) for Arabic (`dir="rtl"`) vs. English
  (`dir="ltr"`).
- **Arabic is the default** language on first load, matching the owner's primary audience; the
  visitor can switch to English at any time and the choice should persist across the visit.
- This applies to **both** the public website and the admin dashboard.
- The exact i18n mechanism (`vue-i18n` vs. a static dictionary) is a `/plan`-phase decision, not
  decided here — see the Spec-Kit spec (§9) for the behavioral requirement, and `front/CLAUDE.md`
  → Language & RTL for the frontend conventions to apply once a library is chosen.

## 9. Spec-Kit — spec-driven development

This project uses **Spec-Kit** for feature specification, planning, and task breakdown. The
Spec-Kit installation (`.specify/`, `.claude/skills/speckit-*`, `specs/`) lives at the **project
root**, as a sibling of `back/` and `front/` — not nested inside either — since specs here cover
the whole product (API + SPA together), not just one side (see §2).

- **Constitution**: `.specify/memory/constitution.md` — the non-negotiable engineering
  principles for this project (kept in sync with `back/CLAUDE.md`).
- **Specs**: `specs/NNN-feature-name/spec.md` — one feature spec per numbered directory. The
  MVP spec is `specs/001-horse-website-mvp/spec.md`.
- **Workflow**: `/speckit-specify` (spec) → `/speckit-clarify` (resolve ambiguities) →
  `/speckit-plan` (design/tech plan) → `/speckit-tasks` (task breakdown) → `/speckit-implement`
  (build). Don't skip straight to `/speckit-implement` without a plan the owner has seen.
- Detailed functional requirements, user stories, entities, and success criteria belong in the
  spec file, **not** in any CLAUDE.md — if this file and the spec ever disagree on a business rule,
  the spec is source of truth for *what* to build; the CLAUDE.md files are source of truth for
  *how* (coding conventions, architecture).

## 10. Existing Brand & Assets — reuse, don't regenerate

The project already has real branding and photography. **Do not invent a new logo, generate
placeholder horse images, or pick an unrelated color palette** — inspect and reuse what's here:

- **Logo**: `front/public/images/logo.png` — "El Baraa Arabians" gold/bronze gradient horse mark
  on white, with an Arabic watermark variant (مربط البراء) visible on the horse photos. This is
  the one and only logo file in the project — use it as-is.
- **Horse photography**: `front/public/images/*.jpg` / `*.jpeg` (14 photos) — real horses from the
  stable, already professionally shot and watermarked. Reuse these; don't replace or delete them.
  Filenames are CDN export IDs (not horse names) — matching a specific photo to a specific horse
  record requires opening the image (the horse's name is watermarked in-frame) or asking the owner.
- **Color palette**: derived from the logo — gold/bronze gradient (`#8a6d1f` → `#f0c04a`-ish) as
  the primary/accent color, black and white/off-white as the neutral base. Treat this as the
  foundation; exact hex tokens get finalized during UI/UX design, not invented from scratch.
- **Fonts/icons**: none bundled in the project yet — a font pairing (Latin + Arabic) and icon set
  are open choices for the UI/UX design pass, informed by the logo's elegant script style.

## 11. Constraints (apply to both sides)

- Don't install new packages, on either side, unless explicitly asked.
- Don't change an existing endpoint's response contract (§5) without confirming — it breaks the
  other side silently.
- Don't duplicate business logic between backend and frontend — validation lives in FormRequests
  on the backend; the frontend's VeeValidate/Yup schemas (if installed) are UX-only, not the
  source of truth.
- Keep the horse data model simple for the MVP — name, breed, gender, date of birth, description,
  optional price, status, multiple categories, multiple images, optional video. Don't add fields
  like height, color, pedigree, bloodline, or competition history unless explicitly requested later
  (see the spec's Out of Scope section).
- A horse can belong to **multiple categories at once** (Sale, Mating, Breeding, more later) via a
  many-to-many relationship — never a single `type` column.
- **Price** and **video** are both nullable — never make either required at the model, validation,
  or UI level.
- Keep backward compatibility; follow existing naming conventions on each side.

## 12. Monorepo git conventions

*(This directory is not yet a git repository — apply these once it is initialized.)*

- Scope commit messages by app when a change is single-sided: `feat(back): ...`,
  `fix(front): ...`; no scope when a change genuinely spans both (e.g. a new endpoint +
  its consumer added together).
- One `.gitignore` at root covering both `back/vendor`, `back/.env`, `front/node_modules`,
  `front/.env`, `front/dist` — currently `back/.gitignore` and `front/.gitignore` exist
  independently and there's no root one yet.
- Don't restructure `back/` or `front/` internals from the root — that's each stack file's
  call, following its own "don't change project structure" rule.

## 13. Project Specifics

- **API base URL:** dev `http://localhost:8000/api`, prod _(not yet deployed — TBD)_
- **Auth strategy:** Bearer token (§6) — single admin account, no public registration/roles system.
- **Domains / entities:** Horse, Category (Sale/Mating/Breeding — extensible), horse images/video
  (via MediaLibrary), Admin `User` (single, from existing Jetstream scaffold), basic site content
  (About Us text, Contact details). Full detail in the Spec-Kit spec (§9).
- **Roles:** none beyond the single admin — no role column/enum needed in the MVP.
- **Language scope:** Arabic + English, **Arabic default** (§8).
- **Non-standard envelope or status-code quirks:** none — the standard envelope (§5) applies as-is.
