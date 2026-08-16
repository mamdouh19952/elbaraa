<!--
SYNC IMPACT REPORT
==================
Version change: 1.0.0 → 2.0.0

This amendment broadens the product from bilingual (Arabic/English) to trilingual (Arabic/English/
Chinese), replaces "Arabic always default on first visit" with browser-language auto-detection
(falling back to Arabic when inconclusive), and adds a fallback/completeness-indicator contract for
translatable content. Treated as MAJOR because it redefines what "constitution-compliant" means for
existing behavior: an AR/EN-only, Arabic-always-default implementation was compliant under 1.0.0
and is not under 2.0.0.

Principles changed:
  - VI. Bilingual & RTL/LTR Correctness → RENAMED "Trilingual & RTL/LTR Correctness"; broadened to
    three languages; added the current-locale→English→Arabic public fallback requirement and the
    admin-facing missing-translation-indicator requirement.

Sections changed:
  - Tech Stack Constraints → added `spatie/laravel-translatable` as an approved package exception,
    with a note that `site_settings` deliberately stays a plain key-value table extended via `_zh`
    suffixes rather than adopting the package.

Cross-references updated:
  - specs/001-horse-website-mvp/spec.md — FR-019/020/021 broadened to three languages plus
    auto-detect; new FR-024 for the fallback/completeness-indicator contract; Key Entities and Out
    of Scope updated to match.

Templates reviewed:
  ✅ .specify/templates/plan-template.md — no constitution-specific references requiring changes
  ✅ .specify/templates/spec-template.md — no constitution-specific references requiring changes
  ✅ .specify/templates/tasks-template.md — no constitution-specific references requiring changes

Deferred TODOs: None.
-->

<!--
PRIOR SYNC IMPACT REPORT (1.0.0 initial ratification for this project)
==================
Version change: 1.0.0 ("Medical Platform Constitution") → 1.0.0 (El Baraa Arabians — Horse
Website Constitution)

This is a full content replacement, not an amendment. The prior "Medical Platform Constitution"
found in this file was a mismatched artifact — its domain (a medical platform), its thin-controller
principle, and its Blade/Bootstrap tech-stack line never applied to this project (a horse
breeder's showcase site + admin dashboard, API-only Laravel backend, fat-controller convention
per `back/CLAUDE.md` and the user's global `laravel-api-style` skill). Rather than "amend" content
that was never actually ratified for this codebase, this rewrite is treated as the effective first
ratification for this project — version reset to 1.0.0 with today's date, not bumped to 2.0.0.

Principles filled:
  - I. Laravel Conventions First (kept — domain-neutral, still correct)
  - II. Thin Controllers, Rich Domain Layer → REDEFINED as "Fat Controllers, Media Is the Only
    Service" — this project's actual convention (back/CLAUDE.md §4) is the opposite of the prior
    text
  - III. Security by Default (NON-NEGOTIABLE) → kept, tightened for single-admin/Sanctum specifics
  - IV. RESTful API Design → kept, added the API-only/no-DB-access-from-frontend contract
  - V. Simplicity & Testability → kept, reframed around the horse MVP's "don't add pedigree/
    bloodline/height/color" scope discipline
  - NEW: VI. Bilingual & RTL/LTR Correctness — added, this project is Arabic+English with real
    layout mirroring, the prior constitution had no localization principle at all

Sections filled:
  - Tech Stack Constraints → domain corrected (horses, not medical records); Blade/Bootstrap line
    replaced with API-only + Vue/Tailwind SPA; MediaLibrary added
  - Development Workflow → added Spec-Kit workflow order, kept migration/FormRequest/Resource rules

Templates reviewed:
  ✅ .specify/templates/plan-template.md  — Constitution Check section is generic and compatible
  ✅ .specify/templates/spec-template.md  — no constitution-specific references; fully compatible
  ✅ .specify/templates/tasks-template.md — task structure aligns with principle-driven phases
  ✅ .specify/templates/constitution-template.md — source template; no update needed

Deferred TODOs:
  None — all fields resolved from repo context (root/back/front CLAUDE.md files) and the
  001-horse-website-mvp spec.
-->

# El Baraa Arabians — Horse Website Constitution

## Core Principles

### I. Laravel Conventions First

Every implementation MUST follow Laravel's established idioms before reaching for custom
solutions. Eloquent ORM MUST be used for all database interactions — raw SQL is only permitted for
queries Eloquent cannot express. Route-Model Binding MUST be used where applicable. Configuration
MUST live in `config/` files, not hard-coded in application logic. Artisan commands, migrations,
and seeders are the authoritative mechanism for schema and data management.

**Rationale**: Laravel's conventions are battle-tested, well-documented, and ensure the codebase
remains approachable to any Laravel developer without tribal knowledge.

### II. Fat Controllers, Media Is the Only Service

Controllers hold CRUD logic directly by default — a `store`/`update`/`destroy` that validates,
writes via Eloquent, and returns a Resource does not need a Service layer in between. A Service
MUST only be introduced when logic is genuinely complex (multiple conditional branches or
dependent steps), reused across more than one endpoint, or transactional across multiple models —
not by default and not "to have a Service layer." The **one standing exception** is media handling:
all horse image/video uploads MUST go through `MediaService`, which wraps
`spatie/laravel-medialibrary` so controllers never call Spatie's API directly.

**Rationale**: This is a small, single-admin MVP (one owner, one write path per resource). An
extra layer between the controller and Eloquent adds indirection without a concrete duplication or
complexity problem to justify it. Media is the exception because Spatie's API is verbose enough
that hiding it behind one seam is worth it even at this size — see `back/CLAUDE.md` §4 and §14.

### III. Security by Default (NON-NEGOTIABLE)

All admin-dashboard routes (horse create/update/delete, media management, category management,
site-content management) MUST be protected by `auth:sanctum`. Every Model MUST define an explicit
`$fillable` array — wildcard `$guarded = []` is prohibited. User-supplied input MUST be validated
through a Form Request (extending the shared `ApiFormRequest`) before reaching the database.
Sensitive configuration values (DB credentials, API keys) MUST live exclusively in `.env` and MUST
NOT be committed to version control. There is a single admin account for this project — no public
registration endpoint may be exposed by the API.

**Rationale**: Security failures in small CRUD APIs stem almost entirely from missing
authentication guards, mass-assignment vulnerabilities, and exposed credentials. A single-admin
system additionally has no reason to expose account creation — every such endpoint is pure attack
surface with no product value.

### IV. RESTful, API-Only Contract

All resource endpoints MUST follow REST conventions: standard HTTP verbs (GET, POST, PUT/PATCH,
DELETE) with resource-named URLs (`/api/horses`, `/api/horses/{id}`). HTTP status codes MUST
accurately reflect the operation result (200, 201, 401, 403, 404, 422). API responses MUST be
formatted through Laravel API Resources — never a raw model or hand-built array — using the
standard envelope (`{status, message, data}`, see root `CLAUDE.md` §5). Collection endpoints that
can grow unbounded (the horses list) MUST be paginated. The Vue frontend (`front/`) MUST reach all
data exclusively through this API — it MUST NOT read the database directly, by design or by
accident (e.g. no shared DB credentials handed to the frontend).

**Rationale**: Consistent REST design and a fixed envelope keep the SPA's assumptions valid across
every endpoint. The API-only boundary is the project's core architectural contract (see root
`CLAUDE.md` §1) — without it, "Laravel API backend + Vue SPA frontend" stops being true.

### V. MVP Simplicity — Build What's Specified, Not What's Possible

Every feature MUST start with the simplest implementation that satisfies the current spec.
Abstractions (Repositories, Policies, a global exception handler, caching) MUST be introduced only
when a concrete need exists — not preemptively (see `back/CLAUDE.md` §4's per-pattern thresholds).
The horse data model specifically MUST stay to what `specs/001-horse-website-mvp/spec.md`
defines (name, breed, gender, date of birth, description, nullable price, status, categories,
images, optional video) — fields like height, color, pedigree, bloodline, or competition history
MUST NOT be added speculatively; they're explicitly deferred to a future spec. PHPUnit feature
tests MUST cover the happy path and at least one error path for each CRUD endpoint and the
authentication flow.

**Rationale**: This is an MVP for a single owner, not a livestock-registry platform. Every
speculative field or layer added now is something the owner has to work around later if the real
requirement differs. Feature tests at the HTTP layer validate real behavior without brittle
mocking of internals.

### VI. Trilingual & RTL/LTR Correctness

The product supports Arabic, English, and Chinese as first-class, equally-complete experiences —
not English-with-translated-strings. Arabic responses/content MUST support right-to-left
presentation correctly on the frontend; English and Chinese both render left-to-right, so only
Arabic requires RTL handling. This is a frontend layout concern primarily, but the backend MUST NOT
assume LTR-only text (e.g. no fixed-width truncation that breaks Arabic or Chinese script, no
locale hard-coded to `en`). Public-facing translatable content MUST fall back current locale →
English → Arabic when a translation is missing for a given field, rather than rendering blank;
admin-facing editors for translatable content MUST surface which of the three languages are missing
per field rather than silently hiding the gap. Validation and error messages returned by the API
SHOULD be translatable (`resources/lang/{ar,en}`) once that structure exists, rather than
hard-coded English strings baked into controllers or Form Requests.

**Rationale**: Arabic remains the fallback language for this product's primary audience (root
`CLAUDE.md` §8) when browser-language auto-detection is inconclusive. A backend that silently
assumes English-only text is a correctness bug for this project, not a nice-to-have. The
fallback/completeness-indicator behavior exists because Chinese translations are added
incrementally by the owner — the product must degrade gracefully, not blankly, while a field is
only partially translated.

## Tech Stack Constraints

- **Language/Runtime**: PHP ^8.2
- **Framework**: Laravel ^12.0
- **Database**: MySQL — Eloquent ORM only; raw queries require documented justification
- **API**: JSON-only, `routes/api.php` — no Blade views and no session-based web auth are part of
  the product. The Jetstream/Livewire starter-kit scaffolding present in `resources/views` and
  `routes/web.php` is unused starter boilerplate from the `laravel/laravel` skeleton, not an
  active dual-track UI (see root `CLAUDE.md` §3) — whether to strip it is a `/plan`-phase decision.
- **UI (Vue)**: Tailwind CSS, in `front/` — the only UI this backend serves data to
- **Media**: `spatie/laravel-medialibrary`, wrapped by `MediaService` (Principle II)
- **Translatable content**: `spatie/laravel-translatable` — approved 2026-08-13 as an explicit
  exception to "no new packages without owner approval," used for `Horse` (`name`/`breed`/
  `description`) and `Category` (`name`) JSON-translatable attributes (Principle VI). `site_settings`
  remains a plain key-value table and is NOT migrated to this package — it is extended via `_zh` key
  suffixes (`about_zh`, `address_zh`) following its existing `_en`/`_ar` convention.
- **Auth**: Laravel Sanctum, Bearer token (not cookie-based SPA auth — see root `CLAUDE.md` §6)
- **Build Tool**: Vite (already configured via `vite.config.js`)
- **Testing**: PHPUnit ^11 — configured in `phpunit.xml`
- **Code Style**: Laravel Pint
- **Package policy**: No new Composer or npm packages without explicit owner approval

## Development Workflow

1. **Spec-driven** — features go through Spec-Kit in order: `/speckit-specify` →
   `/speckit-clarify` → `/speckit-plan` → `/speckit-tasks` → `/speckit-implement`. Don't jump to
   implementation without a spec and plan the owner has seen.
2. **Migration-first schema changes** — database schema changes MUST be introduced via Artisan
   migrations; manual SQL alterations to the database are prohibited.
3. **Form Request validation** — every controller action that accepts user input MUST use a
   dedicated Form Request class extending `ApiFormRequest`; inline `$request->validate()` calls
   are not permitted.
4. **Resource responses** — all API JSON responses MUST pass through a Laravel Resource or
   ResourceCollection; raw `response()->json()` with hand-built arrays is prohibited (except the
   fixed envelope wrapper itself).
5. **Eloquent relationships** — all foreign-key relationships (Horse↔Category, Horse↔Media) MUST
   be declared as Eloquent relationship methods on both sides; queries MUST use `with()`
   eager-loading to prevent N+1s, especially on the horse listing endpoint.
6. **Branch per feature** once this directory is a git repository — direct commits to the default
   branch are discouraged except for trivial fixes.

## Governance

This constitution supersedes all conflicting ad-hoc conventions in the project. Amendments MUST
be documented as an update to this file, incrementing the version following semantic versioning
rules (MAJOR: principle removal/redefinition; MINOR: new principle or section added; PATCH:
clarification or wording fix), and updating the Sync Impact Report comment at the top.

All work MUST verify compliance with the principles above before being considered done.
Complexity violations (e.g., a Service or abstraction added without meeting Principle II's or V's
bar) MUST be documented in the plan's Complexity Tracking table when `/speckit-plan` is run.

Runtime development guidance is maintained in `CLAUDE.md` at the project root, `back/CLAUDE.md`,
and `front/CLAUDE.md`. The functional specification is maintained in
`specs/001-horse-website-mvp/spec.md`.

**Version**: 2.0.0 | **Ratified**: 2026-08-11 | **Last Amended**: 2026-08-13
