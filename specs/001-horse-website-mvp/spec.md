# Feature Specification: Horse Website MVP (Public Showcase + Admin Dashboard)

**Feature Branch**: `001-horse-website-mvp`

**Created**: 2026-08-11

**Status**: Draft

**Input**: User description: "A website for a single horse owner/breeder (El Baraa Arabians /
مربط البراء) to showcase his horses. Laravel API backend + Vue SPA frontend (public website and
admin dashboard in the same app). Horses have simple MVP data (name, breed, gender, DOB,
description, optional price, status, multiple images, optional video) and can belong to multiple
categories at once (Sale, Mating, Breeding — extensible). Arabic + English with correct RTL/LTR.
Must reuse the existing logo and horse photography already in the project."

## Overview

**Goal**: Give a single Arabian horse owner/breeder a premium, image-focused website that
showcases his horses to potential buyers and breeding partners, and a simple dashboard to keep it
up to date himself without developer involvement.

**Target audience**:
- **Public visitors** — prospective buyers, breeders, and people interested in Arabian horses,
  primarily Arabic-speaking with English-speaking visitors also expected.
- **The owner** (single admin) — manages all content through the dashboard; not technical.

**Platforms**: One Vue 3 SPA containing both the public website and an authentication-gated admin
dashboard, talking to a Laravel REST API backend. The frontend never accesses the database
directly (see root `CLAUDE.md` §1, §5).

**Out of scope for this spec**: everything under "Out of Scope / Future Extensibility" below.

## Existing Assets & Visual Identity *(informational — see root `CLAUDE.md` §10 for the reuse rule)*

- **Logo**: `front/public/images/logo.png` — "El Baraa Arabians" gold/bronze gradient wordmark and
  horse-and-crescent mark on a white background. This is the site's logo; it MUST be reused as-is,
  not redesigned or replaced.
- **Horse photography**: 14 existing photos in `front/public/images/*.{jpg,jpeg}`, real horses from
  the stable, already professionally shot (several with an in-frame "مربط البراء / El Baraa
  Arabians / Tamer Eladawy" watermark and the horse's name burned into the image). These MUST be
  reused for horse records rather than generating placeholder imagery. Filenames are CDN export IDs
  and do not indicate which horse is in which photo — assigning existing photos to the correct
  horse record is a manual step for the owner/admin (matching by the name visible in each photo),
  not something the system can infer automatically.
- **Color palette**: gold/bronze gradient (from the logo) as the primary/accent color; black and
  white/off-white as the neutral base; horse-photography earth tones (hay, dirt, greenery) as
  natural supporting tones in imagery. Exact design tokens are a UI/UX design decision, not part of
  this spec.
- **Typography & icons**: not yet chosen — no font files or icon sets exist in the project yet.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Browse and discover horses (Priority: P1)

A visitor lands on the site, browses the horse listing, and opens a horse's detail page to see its
photos, description, categories, status, and price (or a "contact us" indicator when no price is
set).

**Why this priority**: This is the core value of the site — showcasing horses to potential buyers
and breeding partners. Without it, there is no product.

**Independent Test**: With at least one horse published (e.g. via seeded data), open the site and
confirm the horse's listing card and detail page render with real content — deliverable and
demoable before any admin tooling exists.

**Acceptance Scenarios**:

1. **Given** at least one horse exists, **When** a visitor opens the Horses page, **Then** they see
   a card for each horse showing its cover image, name, category badge(s), and a price-or-contact
   indicator.
2. **Given** a visitor is on the Horses page, **When** they select a horse, **Then** they see its
   full detail page: all images, name, breed, gender, date of birth, description, categories,
   status, price (or "contact us" indicator), and video (if one exists).
3. **Given** a horse has no price set, **When** a visitor views its listing card or detail page,
   **Then** a clear "price on request" style indicator is shown instead of a blank or zero price.
4. **Given** a horse belongs to more than one category (e.g. Sale and Breeding), **When** a visitor
   views it, **Then** all of its categories are visible, and the horse appears when browsing by
   any one of them.

---

### User Story 2 - Switch between Arabic and English (Priority: P1)

A visitor switches the site language at any point and the entire layout — text, direction,
navigation, alignment — adapts correctly, not just the words.

**Why this priority**: The owner's audience is bilingual, and a premium brand cannot ship a
half-translated or direction-broken experience — this is as core to the product as the horse data
itself.

**Independent Test**: Load any page (default Arabic), switch to English via the language switcher,
and confirm both text and layout direction flip correctly. Testable independently of any specific
horse content.

**Acceptance Scenarios**:

1. **Given** a visitor opens the site for the first time, **When** the page loads, **Then** it is
   shown in Arabic with right-to-left layout (navigation, alignment, mirrored icons where
   directional).
2. **Given** a visitor is viewing any page, **When** they use the language switcher to select
   English, **Then** all visible text switches to English and the layout switches to left-to-right.
3. **Given** a visitor has switched language, **When** they navigate to another page or reload,
   **Then** their chosen language persists for the rest of the visit.
4. **Given** the admin dashboard, **When** an authenticated admin views it, **Then** the same
   language/direction rules apply there too — bilingual support is not public-site-only.

---

### User Story 3 - Contact the owner about a specific horse (Priority: P2)

A visitor interested in a specific horse (typically one listed for Sale) contacts the owner
directly about that horse.

**Why this priority**: Converting interest into a lead is the business reason the site exists;
this is the payoff of Story 1.

**Independent Test**: Open any horse's detail page and confirm the WhatsApp contact action opens a
chat referencing that specific horse — testable independently of the admin dashboard.

**Acceptance Scenarios**:

1. **Given** a visitor is on a horse's detail page, **When** they tap/click the WhatsApp contact
   action, **Then** a WhatsApp chat opens to the owner's number with a message pre-filled that
   references that horse (e.g. its name).
2. **Given** a visitor wants general information not tied to one horse, **When** they visit the
   Contact page, **Then** they find the owner's contact details (phone/WhatsApp/email/social, as
   provided by the owner).

---

### User Story 4 - Manage horses from the dashboard (Priority: P2)

The owner/admin logs into the dashboard and adds, edits, or removes horses, their images and
video, categories, and status — keeping the public site current without developer involvement.

**Why this priority**: The public site has no content without this. It's what makes the site
self-serve for a non-technical single owner.

**Independent Test**: Log in as admin, create a horse with images and at least one category,
confirm it appears correctly on the public site — testable independently of the public browsing
experience (dashboard + API alone).

**Acceptance Scenarios**:

1. **Given** the admin is logged in, **When** they create a new horse with its required fields, at
   least one image, and one or more categories, **Then** the horse becomes visible on the public
   site with that data.
2. **Given** the admin is logged in, **When** they edit a horse's price, status, or categories,
   **Then** the public site reflects the change.
3. **Given** the admin is logged in, **When** they add, replace, or remove an image or the video on
   a horse, **Then** the public site reflects the updated media.
4. **Given** the admin is logged in, **When** they delete a horse, **Then** it and its images,
   video, and category associations no longer appear anywhere on the public site.
5. **Given** a visitor who is not logged in, **When** they navigate to a dashboard URL, **Then**
   they are redirected to a login screen and cannot view or change any data.

---

### User Story 5 - Learn about the breeder (Priority: P3)

A visitor reads About Us to learn about the owner/breeder and the stable's story, independent of
any specific horse.

**Why this priority**: Builds credibility for a premium brand; expected on any breeder site, but
lower priority than the transactional flows above.

**Independent Test**: Open the About Us page directly and confirm it renders the stable/owner bio
content in the current language, independent of horse data.

**Acceptance Scenarios**:

1. **Given** a visitor opens About Us, **When** the page loads, **Then** they see the stable's
   story/owner bio in the currently selected language.

---

### Edge Cases

- A horse has zero images (still being set up by the admin). Since this MVP has no separate
  draft/published visibility state (see Assumptions), such a horse would be publicly visible with
  no photos — **flagged as an open question for the owner before `/plan`**, not silently resolved
  here (see the project's decision log).
- A horse is marked **Sold**: it remains visible (breeders commonly showcase past results/sales for
  credibility) but is clearly badged as sold; the WhatsApp CTA still works (a visitor may be asking
  about similar available horses).
- A horse's optional video is an external link that later becomes private/removed: the page must
  not break — it simply fails to play; no special handling beyond not crashing.
- Very long description text, or long horse/breed names, in either language must not break the
  layout (card truncation, detail-page wrapping).
- Two visitors browsing concurrently in different languages: language choice is per-visitor
  (local to their device/session), never global/shared state.
- An admin removes a category that one or more horses currently use: those horses simply lose that
  category tag; they keep any other categories they have.
- A horse belongs to zero categories (e.g. accidentally unchecked): it's a valid but likely-mistake
  state — the UI should make current categories obvious enough that this is easy to notice and fix,
  but the system does not forbid it.

## Requirements *(mandatory)*

### Functional Requirements

**Public website**

- **FR-001**: System MUST display a public listing of horses, each showing at least a cover image,
  name, category badge(s), and a price-or-contact indicator.
- **FR-002**: System MUST provide a Horse Details page showing name, breed, gender, date of birth,
  description, all images, categories, status, price (or a "contact us" indicator if unset), and
  video (if provided).
- **FR-003**: Visitors MUST be able to browse/filter horses by category.
- **FR-004**: System MUST provide a Home page showcasing the brand (existing logo) and a selection
  of horses.
- **FR-005**: System MUST provide an About Us page and a general Contact page, independent of any
  specific horse.
- **FR-006**: System MUST provide a WhatsApp contact action on each horse (at minimum on its
  Details page) that opens a chat pre-filled with a reference to that horse.

**Horse data & categories**

- **FR-007**: A horse's `price` MUST be optional; when absent, every place price would be shown
  MUST instead show a non-blank "price on request" style indicator.
- **FR-008**: A horse's `video` MUST be optional, and MAY be supplied either as an external link
  (e.g. YouTube/Vimeo) or as an uploaded video file — the admin may use either per horse.
- **FR-009**: A horse MUST support multiple images.
- **FR-010**: A horse MUST be able to belong to multiple categories simultaneously (starting set:
  Sale, Mating, Breeding) via a many-to-many relationship — never a single "type" field.
- **FR-011**: Adding a new category in the future MUST NOT require restructuring existing horses or
  their data.
- **FR-012**: Each horse MUST have a status distinct from its categories. Default for this spec:
  **Available / Reserved / Sold**, visible to visitors as a badge (see Assumptions — this default
  is pending explicit owner confirmation before `/plan`).
- **FR-013**: The horse data model for this MVP MUST be limited to: name, breed, gender, date of
  birth, description, price (nullable), status, categories (multi), images (multi), video
  (nullable). Fields such as height, color, pedigree, sire/dam, bloodline, lineage, or competition
  history MUST NOT be added unless requested in a future spec.

**Admin dashboard**

- **FR-014**: System MUST require authentication for every horse-management action (create, edit,
  delete, manage images/video, manage categories, manage status) and any site-content management.
- **FR-015**: Unauthenticated visitors attempting to reach the dashboard MUST be redirected to a
  login screen and MUST NOT be able to view or change any managed data.
- **FR-016**: The admin MUST be able to create, edit, and delete horses, including assigning and
  removing categories per horse.
- **FR-017**: The admin MUST be able to add, replace, and remove a horse's images and video.
- **FR-018**: The admin MUST be able to edit basic site content required by the public pages (About
  Us text; Contact details including a WhatsApp number).

**Bilingual / RTL-LTR**

- **FR-019**: System MUST present all visitor- and admin-facing content in both Arabic and English,
  with a control to switch language at any time.
- **FR-020**: Arabic MUST render right-to-left (mirrored layout, navigation, and alignment — not
  translated text inside an unchanged left-to-right layout); English MUST render left-to-right.
- **FR-021**: Arabic MUST be the language shown on first visit by default.

**Architecture contract**

- **FR-022**: The Laravel backend MUST expose all data the Vue frontend needs through a REST API;
  the frontend MUST NOT access the database directly.
- **FR-023**: System MUST reuse the existing logo and existing horse photography already present in
  the project rather than placeholder or newly generated imagery.

### Key Entities *(include if feature involves data)*

- **Horse**: name, breed, gender, date of birth, description, price (nullable), status
  (Available/Reserved/Sold — see Assumptions), one or more categories, one or more images, an
  optional video (link or uploaded file).
- **Category**: a label a horse can carry (Sale, Mating, Breeding to start); many-to-many with
  Horse; the set is extensible without restructuring Horse data.
- **Horse Image**: one of a horse's photos; a horse has one or more.
- **Horse Video** *(optional, at most one per horse in this MVP)*: either an external link or an
  uploaded file reference.
- **Admin (Owner)**: the single account that can authenticate to manage all of the above; no
  multi-admin or role system in this MVP.
- **Site Content**: the small set of editable text/settings the public pages need beyond horse data
  — About Us bio, and Contact details (phone/WhatsApp/email/social/address as applicable).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A visitor can find and open any given horse's detail page within 3 clicks/taps from
  the Home page.
- **SC-002**: A visitor can switch between Arabic and English from any page, and the layout
  direction updates correctly with no overlapping or broken UI, in under 1 second perceived delay.
- **SC-003**: A horse listed without a price still shows a clear, non-blank pricing indicator on
  both its listing card and its detail page — 100% of the time, never blank or "0".
- **SC-004**: A visitor interested in a specific horse can start a WhatsApp conversation referencing
  that horse in one tap/click from its detail page.
- **SC-005**: The admin can publish a new horse (core fields, at least one image, at least one
  category) in under 5 minutes without developer help.
- **SC-006**: The public site is fully usable on a mobile phone screen — no horizontal scrolling,
  no overlapping elements — in both languages.
- **SC-007**: Adding a new horse category in the future requires no changes to existing horses'
  data or manual data migration.

## Assumptions

- **Single admin/owner account** — no multi-admin roles or permission tiers in the MVP. The
  existing Jetstream-scaffolded `User` model is reused for this one account rather than building a
  parallel auth system; the public self-registration flow is not exposed by the API.
- **Horse status semantics** — the spec defaults "status" to a sales-lifecycle indicator
  (Available/Reserved/Sold), since the answer given when this was raised ("admin will control it
  via CRUD") confirmed *who* manages it but not *what it means*. This default is called out
  explicitly as something to confirm with the owner before `/plan`, because it has a real
  consequence the alternative interpretation (a Draft/Published visibility flag) would not: **in
  this MVP, there is no way to hide a horse the admin hasn't finished setting up yet** — every
  saved horse is immediately public. If that's not acceptable, a lightweight visibility flag should
  be added to the plan.
- **Video**: admin may use either an external link or a direct upload, per horse (confirmed).
  Multiple videos per horse, and richer video handling (transcoding, thumbnails), are out of scope.
- **Per-horse contact channel**: WhatsApp click-to-chat (confirmed) is the primary CTA on a horse's
  detail page; the WhatsApp number itself is owner-supplied content, configured via the admin
  dashboard's site-content settings, not hard-coded.
- **"Manage basic website content"** (from the original brief) is interpreted as editable About Us
  text and Contact details — not a general-purpose page builder or CMS.
- Locale choice persists for the visitor's device/session but does not require an account.
- No existing filename→horse mapping exists for the current photos; associating them with the
  correct horse records is a manual data-entry step for the owner/admin once the dashboard exists,
  not something this feature builds automatically.
- The existing Jetstream/Livewire scaffolding in the Laravel backend (default auth Blade views,
  `routes/web.php` dashboard route) is treated as inert starter boilerplate for the purposes of this
  spec; whether to remove it or leave it dormant is a technical decision for `/plan`, not a
  behavioral requirement here.

## Out of Scope / Future Extensibility

Explicitly deferred, per the original brief's "start simple, expand later" direction — the data
model and architecture should not block adding these later, but none are built now:

- Additional horse attributes: height, color, pedigree, sire/dam, bloodline, lineage, competition
  history/achievements.
- Multiple admin accounts, roles, or permission levels.
- A general-purpose CMS/page builder beyond the specific About/Contact content fields.
- Multiple videos per horse; video transcoding/streaming infrastructure.
- Online payments, deposits, or any money movement through the site.
- Favorites/wishlist, saved searches, or visitor accounts.
- Blog/news, testimonials, or a public inquiry/lead-management system beyond WhatsApp contact.
- Multi-language content beyond Arabic/English.
- Search-engine metadata strategy, analytics, and marketing integrations (may be addressed in
  `/plan` as non-functional concerns, not user-facing requirements here).
