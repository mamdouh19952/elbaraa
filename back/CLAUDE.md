# Laravel API Backend — Engineering Standard

Reusable `CLAUDE.md` for **Laravel, API-only** projects (no Blade/web UI). This is a **standard**,
not a fixed template: it gives the non-negotiable baseline (FormRequests, Resources, the response
envelope, Sanctum) plus a **decision guide** for the things that should scale with project size
(Services, Repositories, Policies, exception handling). Drop it at the root of any such project and
fill in **§18 Project Specifics** — that's where a given project declares which of the optional
patterns it has actually turned on.

> If this backend lives in a monorepo alongside a frontend, this file is the `backend/CLAUDE.md`
> layer — a root `CLAUDE.md` can hold cross-cutting rules, and `frontend/CLAUDE.md` its own stack's
> conventions.

## 1. Role

Senior Laravel backend developer. Write clean, production-ready REST API code — consistent,
predictable, and boring in the good sense. Every endpoint should look like the same person wrote
it, and the complexity of the code should match the complexity of the problem — not more, not less.

## 2. Tech Stack

- **PHP ^8.2, Laravel ^12, MySQL + Eloquent.**
- **API-only, JSON.** No Blade views, no session auth — everything is a stateless JSON endpoint.
- **Auth:** Laravel Sanctum (token-based).
- Media uploads (when the project has any): `spatie/laravel-medialibrary`.

## 3. Engineering Philosophy

- Prefer rules phrased as **"use X when Y, skip X when Z"** over blanket bans or blanket mandates.
  Reason about the specific task, don't reflexively apply one architecture to every project.
- **Match complexity to the project.** A small CRUD API and a large multi-team API are not built
  the same way — §4 tells you how to tell them apart before choosing a pattern.
- Simple, readable code beats clever code. If a pattern makes the codebase harder for the project
  owner to read and reason about than the problem justifies, it's the wrong call even if it's
  "more correct" in the abstract.
- When genuinely unsure whether a task crosses a threshold in §4 (e.g. "is this logic complex
  enough for a Service?"), say what you're leaning toward and why, rather than silently picking one.

## 4. Architecture Decision Guide

This is the core of the file — read it before reaching for a pattern.

### Controllers vs. Services

```
Controller
   ↓
Service (only when justified below)
   ↓
Model / Eloquent
```

- **Default: keep CRUD logic in the controller.** A `store`/`update`/`destroy` that validates,
  writes, and returns a Resource doesn't need anywhere else to live.
- **Introduce a Service when at least one of these is true:**
  - the business logic is genuinely complex (multiple conditional branches, several dependent
    steps) — not just "more than 10 lines";
  - the same logic is reused by more than one controller/endpoint;
  - the operation is transactional and spans multiple models (e.g. booking + seat count + payment
    record together);
  - a domain has enough surface area that one controller has grown past what's readable in one
    sitting (a rough signal, not a hard line — a controller ballooning toward 300+ lines of mixed
    concerns is a sign, not a rule).
- **Don't create a Service just to have a Service layer.** A Service that only forwards one call to
  Eloquent (`return $this->repo->find($id)`-style indirection) is pure overhead — delete it.
- **Standing exception:** `MediaService` (§16) is worth having even in small projects, because
  wrapping Spatie's API keeps controllers from calling it directly — that one is not size-gated.

### Repository pattern

- **Skip it by default.** `Controller → Service → Repository → Eloquent` for an ordinary Laravel
  API is indirection for its own sake — Eloquent models already are the repository layer.
- Only introduce it for a large, long-lived project where the data source genuinely needs to be
  swappable (multiple storage backends, a planned migration off Eloquent). This is rare — don't
  add it because it "feels more Clean Architecture."

### Policies vs. simple role checks

- Use a **Policy** (`$this->authorize('update', $event)`) when authorization is per-record —
  "a user can only edit their *own* booking" — or when the rule has more than one condition.
- A simple **role middleware** (`role:admin`) is enough when the rule is flat — "only admins can
  hit this endpoint at all," no per-record ownership involved.
- Don't add a Policy for a public, read-only resource that needs no authorization.

### Exception handling

- **Default:** handle errors per method — guard, return early, respond with the standard envelope
  (§7). This is enough for a small-to-medium API and keeps each endpoint's failure modes visible
  at the call site.
- **Introduce a centralized handler** (via `bootstrap/app.php`'s `withExceptions()` in Laravel
  11/12) once the project has grown enough that ad hoc per-method guards are producing
  inconsistent error shapes, or you need *every* uncaught exception — not just the ones you
  explicitly guarded — to still come back in the standard envelope.
- If you add one, keep it thin: formatting exceptions into `{status, message}`, not business logic.

### Pagination

- Use `->paginate()` on any list endpoint whose collection can grow unbounded (events, bookings,
  products, orders).
- Skip it for small, genuinely bounded lists (a fixed set of categories, a project's own settings).

### API versioning

- Skip a `/v1` prefix by default for internal/first-party APIs.
- Adopt it when the API has external consumers you must avoid breaking, or a breaking v2 is
  already planned.

### Events / Listeners / Jobs / Queues

- Use them for real async work: outbound mail, SMS, notifications, heavy/slow processing.
- Not for ordinary synchronous CRUD — don't queue a database write just because "queues are best
  practice."

### Caching / Redis

- Only for a proven, repeated, expensive read (measured, not assumed). Not a default for every
  query.

### Broadcasting

- Only when the project has an actual real-time requirement (live seat availability, chat,
  live notifications). Otherwise skip it.

### Always skip, regardless of project size

- Don't install new packages, restructure folders, or change an existing endpoint's response
  contract without confirming with the project owner first — these affect the whole codebase no
  matter how small the project is.

## 5. Validation — FormRequest

**Always** a FormRequest — never inline `Validator::make()` or `$request->validate()` in a
controller, at any project size. All requests extend a shared `ApiFormRequest` base so validation
failures return the standard envelope automatically:

```php
// app/Http/Requests/ApiFormRequest.php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
```

## 6. API Resources

**Always** shape the response through a Resource — never return a raw model or raw collection.
Eager-load relations in the controller (`->with(...)`) and expose them via `whenLoaded('relation')`:

```php
// app/Http/Resources/EventResource.php
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
```

## 7. Response Envelope

The recommended default — **confirm/adjust it in §18 Project Specifics** if a given project already
has an established shape (e.g. an older codebase, or a different consumer's expectations).

```php
// success — single resource
return response()->json([
    'status'  => true,
    'message' => 'Event created successfully',
    'data'    => new EventResource($event),
], 201);

// success — collection
return response()->json([
    'status'  => true,
    'message' => 'Events retrieved successfully',
    'data'    => EventResource::collection($events),
]);

// error
return response()->json([
    'status'  => false,
    'message' => 'Event not found',
], 404);

// validation error — produced automatically by ApiFormRequest, don't hand-roll this
[
    'status'  => false,
    'message' => 'Validation error',
    'errors'  => $validator->errors(),
] // 422
```

**Status codes:** `200` ok · `201` created · `400` bad request · `401` unauthenticated ·
`403` forbidden · `404` not found · `422` validation. Keep them accurate — don't default
everything to `200`.

## 8. Canonical Endpoint Slice

The default shape for a simple CRUD endpoint — controller holds the logic directly (§4):

```php
// app/Http/Requests/StoreEventRequest.php
class StoreEventRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'start_time'  => 'required|date',
            'category_id' => 'required|integer|exists:categories,id',
        ];
    }
}

// app/Http/Controllers/Api/Event/EventController.php
class EventController extends Controller
{
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Event created successfully',
            'data'    => new EventResource($event),
        ], 201);
    }
}
```

When the logic crosses one of the thresholds in §4 (transactional across models, reused, genuinely
complex), move the body of the method into a Service and keep the controller as the thin
orchestration layer — request in, Service call, Resource out.

## 9. Auth & Authorization (Sanctum)

- Issue tokens with `$user->createToken('API TOKEN')->plainTextToken`.
- Protect routes with `auth:sanctum`.
- Flat role checks (`role:admin`) via route middleware; per-record checks via Policies — see §4.
- Logout: `$request->user()->currentAccessToken()->delete()`.
- Throttle sensitive routes: `throttle:x,y` (e.g. login, OTP resend) or a named limiter.

## 10. Concurrency & Transactions

Multi-step writes that touch stock / seats / balances: wrap the operation in `DB::transaction(...)`
and use `lockForUpdate()` on the row you decrement — the standard guard against race conditions.

```php
return DB::transaction(function () use ($eventId, $userId) {
    // lock the row before reading available_seats — prevents two requests
    // from both passing the check and overselling the last seat
    $event = Event::lockForUpdate()->findOrFail($eventId);

    if ($event->available_seats <= 0) {
        return response()->json(['status' => false, 'message' => 'No available seats'], 400);
    }

    $event->decrement('available_seats');
    // ...
});
```

## 11. Eloquent & Models

- `$fillable` whitelist — never `$guarded = []`.
- `casts()` for dates / `hashed` passwords / booleans.
- Explicit relationships defined on both sides (`hasMany` + `belongsTo`, not just one direction).
- Domain-grouped controllers: `App\Http\Controllers\Api\{Domain}\{Domain}Controller`
  (e.g. `Api\Booking\BookingController`).

## 12. Security Essentials

- Never mass-assign beyond `$fillable`; never trust client-supplied `id`/`user_id` on writes —
  derive ownership from the authenticated user.
- Hide sensitive fields (`password`, tokens) via `$hidden` on the model — never rely on the
  Resource alone to exclude them.
- Rate-limit auth and OTP endpoints (§9).
- Return `404` for "not found *or* not yours" on user-owned resources rather than `403`, unless the
  project needs to reveal existence explicitly — avoids leaking which records exist.

## 13. Testing

- Feature tests over unit tests for API endpoints — assert on the actual JSON envelope and status
  code, not internal method calls.
- Cover at minimum: the happy path, the validation-failure path, and the auth/ownership-failure
  path for each endpoint that has one.
- Don't write tests for a Service/Repository that only exists to be tested — test the endpoint
  behavior it produces.

## 14. Opt-in Feature Modules

Add these **only when the project actually has the feature** — don't scaffold them by default.

### Media / file uploads

Use `spatie/laravel-medialibrary`, wrapped in a service so controllers never call Spatie directly.

- `app/Http/Services/MediaService.php` with: `upload($model, $files, string $collection)`,
  `update($model, $files, string $collection)` (clears then re-uploads),
  `deleteMedia($model, string $collection)`, `deleteMediaItem($model, $mediaId, string $collection)`.
- Model implements `Spatie\MediaLibrary\HasMedia` and `use InteractsWithMedia`.
- Named collections, e.g. `event_cover` (single), `event_gallery` (multiple).
- Controller injects the service via constructor:
  ```php
  if ($request->hasFile('image')) {
      $this->media->upload($event, $request->file('image'), 'event_cover');
  }
  ```
- Resource exposes URLs, not raw media models:
  ```php
  'image' => $this->getFirstMediaUrl('event_cover') ?: null,
  ```

### OTP email verification

- On register: generate a 6-digit `otp`, store `otp`, `otp_expires_at` (`now()->addMinutes(10)`),
  `is_otp_verified = false`; mail the code; issue the Sanctum token.
- `resendOtp`: regenerate + re-mail, throttled (`throttle:2,1`).
- `verifyEmailOtp` (FormRequest): reject if already verified, wrong code, or expired (`422`,
  except "already verified" which can be `200`); on success set `is_otp_verified = true`, null out
  `otp` / `otp_expires_at`, return a fresh token.
- `login`: block with `403` until `is_otp_verified` is true.
- Add `otp`, `otp_expires_at`, `is_otp_verified` to the User model's `$fillable`, cast
  `otp_expires_at` to `datetime`.

### Localization (Arabic-first / bilingual projects)

- `LangMiddleware` reads a `lang` header or query param and calls
  `app()->setLocale('ar'|'en')`, default per project.
- Validation/error messages should be translatable (`resources/lang/{ar,en}`) if the project is
  bilingual — check whether one already exists before assuming scope.

## 15. RESTful Naming

- URIs: plural nouns, `products`, `products/{id}`.
- Controller methods stick to `index`, `show`, `store`, `update`, `destroy` (plus domain-specific
  verbs when REST verbs don't fit, e.g. `resendOtp`).
- Route naming may be resourceful (`apiResource`) or dot-style (`event.create`,
  `event.show/{id}`) — match whatever the project already uses; don't switch styles mid-project.

## 16. General Coding Rules

- Follow SOLID, DRY, KISS — but see §3: don't apply a pattern past what the problem needs.
- Reuse existing code — check for an existing FormRequest/Resource/Service before writing a new one.
- Meaningful variable and method names.
- **Comments:** default to none. Add a short comment only when the *why* isn't obvious from the
  code — a lock, a transaction boundary, a non-standard status code, a workaround, a tricky query.
  Never comment what the code already says.
- Don't add error handling, fallbacks, or validation for cases that can't happen. Validate at the
  boundary (FormRequest) and trust Eloquent/Laravel guarantees past that.
- Don't add abstractions, config flags, or "future-proofing" beyond what the current task needs.

---

## §18 Project Specifics — El Baraa Arabians (horse website MVP)

> §1–§16 are the standard; this section is what this codebase has actually turned on. Full
> functional detail (entities, requirements, success criteria) lives in the Spec-Kit spec at
> `../specs/001-horse-website-mvp/spec.md` (project root, not inside `back/` — see root
> `CLAUDE.md` §2) — this section only records the architectural choices from §4's decision guide.

**Architecture choices for this project:**

- Services: `enabled` — **Media only** (`MediaService`, §14). Everything else (Horse CRUD,
  Category assignment, auth) stays in controllers per §4's default — this is a small, single-admin
  MVP with no transactional multi-model writes yet.
- Repository pattern: `disabled` — Eloquent directly; no swappable data source need.
- Policies: `disabled` — single admin account, no per-record ownership to check. A flat
  `auth:sanctum` guard on the dashboard routes is enough (§9).
- Global exception handler: `disabled` — per-method guards are enough at this size.
- Pagination: `enabled` — on the public/admin horse listing endpoints (`GET /api/horses`), since
  the horse count can grow unbounded. Not needed on the (fixed, tiny) categories list.
- API versioning: `not used` — first-party API, single consumer (the SPA in `front/`).
- MediaLibrary: `enabled` — horse images (multiple, named collection e.g. `horse_gallery`) and
  optional horse video, both nullable/optional per horse. Video may be either an **uploaded file**
  (via MediaLibrary, same as images) or an **external link** (YouTube/Vimeo/etc. stored as a plain
  URL column) — the admin can use either. Wrap all Spatie calls in `MediaService` per the standard.
- OTP email verification: `disabled` — single pre-provisioned admin account, no self-registration
  flow to protect.
- Localization: `enabled` (`ar`/`en`) — Arabic default. Validation/error messages should be
  translatable once `resources/lang/{ar,en}` exists; check before assuming it's already there (it
  isn't yet as of this writing).

**Domain specifics:**

- **Domains / entities:**
  - `Horse` — name, breed, gender, date of birth, description, price (nullable), status
    (`available` / `reserved` / `sold` — see spec's Assumptions for why, this is a default pending
    owner confirmation), timestamps.
  - `Category` — `Sale` / `Mating` / `Breeding` today, more addable later without touching the
    `horses` table — **many-to-many** via a `category_horse` pivot, never a single `type` column
    on `Horse`.
  - Horse images/video — via MediaLibrary (`HasMedia` on `Horse`), see MediaLibrary row above.
  - `User` — the single admin/owner, already scaffolded by Jetstream (`app/Models/User.php`);
    reused as-is, not rebuilt.
  - Basic site content (About Us text, Contact details incl. WhatsApp number) — simple key/value
    settings, not a full CMS; exact shape TBD in `/plan`.
- **Roles:** none — single admin account, no role column/enum. Every `auth:sanctum`-protected route
  is implicitly "the owner's."
- **Key business rules:**
  - A horse can hold multiple categories simultaneously (e.g. Sale **and** Breeding at once).
  - `price` is nullable independent of category — a `Sale` horse with no price is valid; the
    frontend shows a "price on request" state rather than blank/zero.
  - `video` is nullable; when present it's either a MediaLibrary file or a URL, never required.
  - Deleting a `Horse` cascades to its category pivot rows and its media (images/video).
- **Non-standard status codes or response quirks specific to this project, if any:** none — the
  standard envelope (root `CLAUDE.md` §5) applies as-is.
