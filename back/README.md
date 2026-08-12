# Backend — Laravel API

JSON REST API for the El Baraa Arabians site. No Blade views, no session auth — the Vue SPA in
[`../front`](../front/README.md) is the only consumer.

- **Laravel 12**, PHP 8.2+, MySQL
- **Sanctum** token auth
- **spatie/laravel-medialibrary** for horse photos and video

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set your database in `.env`, then:

```bash
php artisan migrate --seed
php artisan storage:link     # required — uploaded images 404 without it
php artisan serve            # http://localhost:8000
```

Two `.env` values matter beyond the defaults:

```dotenv
APP_URL=http://localhost:8000        # must include the port, or image URLs come back wrong
FRONTEND_URL=http://localhost:5173   # allowed CORS origin (see config/cors.php)
```

`php artisan migrate --seed` creates:

- 3 categories — For Sale / Mating (للزواج) / Our Breeding (إنتاجنا)
- 6 demo horses using the owner's real photos from `../front/public/images`
- the admin user `admin@elbaraa-arabians.com` / `password`
- default site settings (about text, phone, WhatsApp, email)

---

## Project layout

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── Auth/AuthController.php          login · logout · me
│   │   ├── Category/CategoryController.php  index
│   │   ├── Horse/HorseController.php        index · show · store · update · destroy
│   │   │                                    · deleteImage · deleteVideo
│   │   └── Setting/SettingController.php    index · update
│   ├── Requests/
│   │   ├── ApiFormRequest.php               base — makes 422s use the standard envelope
│   │   ├── Auth/LoginRequest.php
│   │   ├── Horse/{Store,Update}HorseRequest.php
│   │   └── Setting/UpdateSettingsRequest.php
│   ├── Resources/                           HorseResource · CategoryResource
│   └── Services/MediaService.php            the only service — wraps Spatie
├── Models/                                  Horse · Category · SiteSetting · User
bootstrap/app.php                            folds 401/404 into the standard envelope
routes/api.php                               every endpoint, public and protected
database/
├── migrations/                              horses · categories · category_horse · site_settings
└── seeders/                                 CategorySeeder · HorseSeeder · SiteSettingSeeder
```

**Controllers hold the CRUD logic directly.** That is deliberate (see `CLAUDE.md` §4): this is a
small single-admin API with no transactional multi-model writes. `MediaService` is the one service,
so no controller ever calls Spatie's API directly.

---

## Data model

```
horses ──< category_horse >── categories        (many-to-many)
   │
   └──< media                                   (Spatie — images + optional video file)

site_settings                                   (flat key/value)
users                                           (single admin)
```

### `horses`

| Column | Notes |
|---|---|
| `name_en`, `name_ar` | both required |
| `breed_en`, `breed_ar` | optional |
| `gender` | `male` \| `female` |
| `date_of_birth` | optional, not in the future |
| `description_en`, `description_ar` | optional |
| `price` | **nullable** — null means "price on request" |
| `currency` | 3-letter code, defaults to `USD` |
| `status` | `available` \| `reserved` \| `sold` |
| `video_url` | optional external link (YouTube/Vimeo) |
| `is_featured` | shown on the home page |

Deleting a horse cascades to its pivot rows and its media.

### Why many-to-many for categories

A horse is frequently *both* For Sale and part of the breeding programme. A single `type` column
would force a false choice and would need a migration every time a category is added. The pivot
means new categories are just new rows.

---

## API reference

Base URL: `http://localhost:8000/api`

Every response uses the envelope described in the [root README](../README.md#the-response-contract).

### Public — no auth

| Method | Endpoint | Notes |
|---|---|---|
| `GET` | `/categories` | Small fixed list, not paginated |
| `GET` | `/horses` | Paginated. Query: `per_page`, `category`, `status`, `featured` |
| `GET` | `/horses/{id}` | `404` if missing |
| `GET` | `/settings` | Flat object of site content |
| `POST` | `/login` | Rate limited to 6/min. Returns `data.token` |

`GET /horses` returns the Laravel paginator inside `data`, so the array is at **`data.data`** and
page info at **`data.meta`**.

### Protected — `Authorization: Bearer <token>`

| Method | Endpoint | Notes |
|---|---|---|
| `GET` | `/me` | Current admin |
| `POST` | `/logout` | Revokes only the token used |
| `POST` | `/horses` | Create. `multipart/form-data` |
| `POST` | `/horses/{id}` | **Update — POST, not PUT** (see below) |
| `DELETE` | `/horses/{id}` | Cascades to media + categories |
| `DELETE` | `/horses/{id}/images/{mediaId}` | `mediaId` comes from `data.images[].id` |
| `DELETE` | `/horses/{id}/video` | Clears both the uploaded file and `video_url` |
| `PUT` | `/settings` | Only the keys you send are updated |

> **Why update is POST:** PHP does not parse `multipart/form-data` on `PUT` requests, and this
> endpoint accepts image uploads. Using POST is the standard Laravel workaround.

### Writing a horse

Send as `multipart/form-data`:

| Field | Notes |
|---|---|
| `name_en`, `name_ar`, `gender`, `status` | required on create |
| `price` | omit or leave empty for "price on request" |
| `categories[]` | repeat the key per category id. On update this **replaces** the whole set |
| `images[]` | repeat per file. jpg/png/webp, 5 MB each. On update these are **appended** |
| `removed_media_ids[]` | media ids to delete during an update |
| `video` | one file, mp4/mov/avi, 50 MB — replaces any existing uploaded video |
| `video_url` | or an external link instead of a file |

Validation lives in the FormRequests, never in the controller, so failures always return the
`422` envelope automatically.

---

## Media handling

Photos go through `MediaService`, which wraps Spatie so controllers stay clean:

```php
$this->media->upload($horse, $request->file('images'), Horse::GALLERY_COLLECTION);
```

- Two collections: `horse_gallery` (many) and `horse_video` (one)
- A `thumb` conversion (600×400) is generated for every image
- `HorseResource` exposes URLs only — never raw media models

**The seeder uses `preservingOriginal()`.** Spatie's `addMedia()` *moves* the source file by
default, which would delete the owner's originals in `../front/public/images`. Keep that flag if
you touch the seeder.

---

## Testing

Import [`El-Baraa-Arabians.postman_collection.json`](El-Baraa-Arabians.postman_collection.json)
into Postman. Run **Auth → Login** first — a test script saves the token into a collection
variable, so every other request is authenticated automatically. The whole collection can be run
top-to-bottom; Logout is intentionally the last item.

There is no PHPUnit suite committed yet. The API was verified end-to-end during development
(48 assertions covering the envelope, auth gating, validation, CRUD, media, and cascade deletes).

---

## Known issue: unused starter scaffolding

The app still carries the full **Jetstream + Livewire + Fortify** starter kit — Blade auth views,
`app/Actions/Fortify/*`, `app/Actions/Jetstream/*`, `app/View/Components/*`, the `/dashboard` route
in `routes/web.php`, and the `HasProfilePhoto` / `TwoFactorAuthenticatable` traits on `User`.

**None of it is used.** This project is API-only and authenticates with Sanctum tokens.

Removing it is worthwhile but touches the auth stack, so it should be done deliberately:
keep `laravel/sanctum` and `HasApiTokens`, drop the rest, then re-verify login/logout still work.
