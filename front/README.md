# Frontend — Vue 3 SPA

One Vue application serving both the **public website** and the **admin dashboard**, split by route
guards. Bilingual Arabic / English with a real RTL ⇄ LTR layout switch.

- **Vue 3** — Composition API, `<script setup>`, **plain JavaScript** (no TypeScript)
- **Pinia** state · **Vue Router** · **Axios**
- **Tailwind CSS v4** (CSS-first config — there is no `tailwind.config.js`)
- **vue-i18n** · **vue3-toastify**

---

## Setup

```bash
npm install
cp .env.example .env      # VITE_API_BASE_URL=http://localhost:8000/api
npm run dev               # http://localhost:5173
```

The backend must be running first — see [`../back/README.md`](../back/README.md).

```bash
npm run build             # production build into dist/
npm run preview           # serve the built output
npx prettier --write "src/**/*.{vue,js,css}"
```

---

## Structure

```
src/
├── assets/main.css       Tailwind entry — design tokens (@theme) + component classes
├── components/
│   ├── horses/           HorseCard.vue · HorsePrice.vue
│   └── shared/           BrandLogo · LanguageSwitcher · StatusBadge
│                         · AppSpinner · ConfirmDialog
├── composables/          useLocalized · useWhatsapp · useConfirm
├── directives/reveal.js  v-reveal / v-reveal-group scroll animations
├── i18n/
│   ├── index.js          createI18n, locale persistence, dir/lang switching
│   └── locales/          ar.js · en.js  (kept at identical key sets)
├── layouts/              MainLayout.vue (public) · AdminLayout.vue (dashboard)
├── pages/
│   ├── Home/ Horses/ About/ Contact/ NotFound/
│   ├── Auth/Login.vue
│   └── Admin/            index.vue · Horses/ · Settings/
├── router/
│   ├── index.js          router + auth guards
│   └── routes/index.js   route definitions
├── services/             api.js (axios + Bearer interceptor) · toast.js
└── stores/               auth/ · locale/ · horses/ · settings/
```

Each page folder holds `index.vue` as its main view; sub-views sit beside it
(`Horses/HorseDetails.vue`). Each store is a folder with `index.js`.

---

## How the pieces work

### API access

All HTTP goes through **`services/api.js`** — one axios instance that attaches
`Authorization: Bearer <token>` from `localStorage` on every request. Never import axios directly
in a component.

Read payloads from `response.data.data` and messages from `response.data.message`, per the
[shared envelope](../README.md#the-response-contract). `apiErrorMessage(err, fallback)` handles the
error side.

### Auth

`stores/auth` holds the token and user. On boot, `main.js` calls `getTokens()` **before** the router
mounts so the very first navigation guard already sees a restored session. Routes marked
`meta.requireAuth` redirect to `/login`; `meta.guestOnly` bounces logged-in users to the dashboard.

The dashboard is intentionally **not linked** anywhere on the public site — reach it via `/admin`.

### Bilingual + RTL

`stores/locale` is the single source of truth. Switching language:

1. updates the vue-i18n locale
2. sets `lang` and `dir` on `<html>`
3. persists the choice to `localStorage`

Arabic is the default on first visit. Because `dir` flips for real, **always use logical Tailwind
utilities** — `ms-`/`me-`, `ps-`/`pe-`, `start-`/`end-` — never `ml-`/`mr-`/`left-`/`right-`.
Use `rtl:` only for things that must physically mirror, like a directional arrow.

Bilingual API fields arrive as `{ en, ar }`. Render them through `useLocalized()`:

```js
const { pick } = useLocalized()
const name = computed(() => pick(horse.value.name))
```

It falls back to the other language rather than rendering blank for a half-translated record.

**Both locale files must stay at identical key sets** — 118 keys each right now.

### Feedback: toast, not alerts

User-facing messages use **vue3-toastify** through `services/toast.js`, which reads the current
direction on every call so toasts mirror correctly:

```js
import { toastSuccess, toastError } from '@/services/toast'
toastSuccess(t('admin.saved'))
```

Field-level validation errors stay **inline under their input** — that is standard form UX, and
only general success/failure goes to a toast.

Never use `alert()` or `confirm()`. For confirmation use the custom dialog:

```js
import { confirm } from '@/composables/useConfirm'
if (!(await confirm(t('admin.deleteConfirm', { name })))) return
```

`<ConfirmDialog />` is mounted once in `App.vue`; the composable is a module-level singleton, so
any component can call `confirm()` without prop wiring.

### Styling and design tokens

Tailwind v4 is configured **in CSS**, not a JS config file. All tokens live in the `@theme` block
at the top of `assets/main.css`:

| Token family | Meaning |
|---|---|
| `gold-*` | The brand accent. Currently orange `#FD7E14` (from the Figma template) |
| `ink-*` | Neutral ramp, `50` (near-white) → `950` (black) |
| `cream`, `cream-deep` | Page and panel backgrounds |
| `--font-display` | Poppins — headings |
| `--font-sans` | Inter — body |

> The scale is still **named** `gold-*` from an earlier palette. Changing the whole site's accent
> colour is a one-line-per-stop edit in `@theme` — no component changes needed.

Arabic uses **Cairo** for both roles, since Poppins and Inter have no Arabic glyphs.

Reusable classes (`.btn-gold`, `.btn-outline`, `.card`, `.eyebrow`, `.field-input`, `.media-frame`)
are defined in the same file. Prefer them over re-typing long utility strings.

### Animation

Scroll reveals use a small IntersectionObserver directive — no animation library:

```html
<div v-reveal>…</div>              <!-- fades one element in -->
<div v-reveal-group>…</div>        <!-- staggers its direct children -->
```

`prefers-reduced-motion` is respected globally in `main.css`.

---

## Adding things

**A new page**

1. `src/pages/YourPage/index.vue`
2. Register it in `src/router/routes/index.js` under the right layout
3. Add its label to **both** `i18n/locales/ar.js` and `en.js`
4. If it belongs in the nav, add it to the `links` array in `layouts/MainLayout.vue`

**A new horse field**

It has to exist on both sides: migration + `$fillable` + FormRequest rules + `HorseResource` in the
backend, then the form field in `pages/Admin/Horses/HorseForm.vue` and the display in
`HorseDetails.vue` / `HorseCard.vue`.

**A new category**

No code change. Add the row in the backend (seeder or database) — the filter list and the admin
form both render whatever the API returns.

---

## Conventions

- `<script setup>` only, no Options API, no `lang="ts"` (this project is plain JavaScript)
- Order inside a component: imports → props/emits → composables & stores → state → functions →
  lifecycle
- Every async call in `try/catch`, with a loading state
- `alt` on every image; `aria-label` on icon-only buttons; keep focus rings
- Mobile-first; test at 375px as well as desktop
- Comments only where the *why* is non-obvious

---

## Assets

`public/images/` holds the owner's real, watermarked photography and the logo. **Reuse these — do
not regenerate, replace, or delete them.** `logo1.png` is an unused duplicate of `logo.png`.
