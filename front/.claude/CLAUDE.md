# Vue.js Project — CLAUDE.md

> General template — customize the Project Overview section per project.

## Project Overview

- **Name**: El Baraa Arabians (مربط البراء) — horse showcase + admin dashboard
- **Description**: Public website for a single Arabian horse owner/breeder showcasing his horses
  (Sale / Mating / Breeding), plus an auth-gated admin dashboard in the same SPA for managing
  horses, images, video, categories, and status. Full detail: `../../specs/001-horse-website-mvp/spec.md`.
- **Language**: Arabic + English (Arabic default)
- **Type**: SPA (public site + admin dashboard together, route-gated)
- **Backend**: Laravel API (`back/`), Sanctum Bearer-token auth — see root `CLAUDE.md` §6
- **Router version**: Vue Router (per `package.json`)

Existing brand assets to reuse (don't regenerate — see root `CLAUDE.md` §10 for full detail):
logo at `front/public/images/logo.png`, horse photography at `front/public/images/*.{jpg,jpeg}`,
gold/bronze + black/white palette derived from the logo.

> This file duplicates `front/CLAUDE.md` (same template). Both are kept in sync for now —
> consider consolidating to one later.

---

## Before Making Changes

Before writing code:

1. Inspect the existing project structure.
2. Inspect relevant components, composables, stores, types, and API services.
3. Reuse existing patterns.
4. Do not create duplicate functionality.
5. Check `package.json` before suggesting or installing dependencies.
6. Understand the API contract before implementing API integration.
7. For multi-step features, explain the implementation plan before making large changes.

---

## Tech Stack

| Layer | Tech |
|-------|------|
| Framework | Vue 3 (Composition API, `<script setup>`) |
| Language | JavaScript (no TypeScript in this project) |
| State | Pinia (setup/composition syntax) |
| Router | Vue Router |
| Styling | Tailwind CSS (`@tailwindcss/vite`) |
| HTTP | Axios — single instance in `src/services/api.js` with a Bearer interceptor |
| Build | Vite |
| Alias | `@` → `src/` |

### Optional Libraries

Do not install or use an optional library unless it already exists in the project's `package.json` or the user explicitly requests it.

- **UI Components**: Flowbite / flowbite-vue
- **Icons**: Font Awesome (`@fortawesome/fontawesome-free`)
- **Forms**: VeeValidate + Yup
- **Notifications**: vue3-toastify
- **Loading**: vue-loading-overlay
- **Carousel**: vue3-carousel
- **Animation**: AOS, GSAP, @vueuse/motion

---

## Folder Structure

Follow the existing project structure. Do not rename or reorganize folders unless explicitly requested.

**Actual structure of this project:**

```
src/
├── assets/main.css      # Tailwind entry + @theme design tokens + component classes
├── components/
│   ├── horses/          #   HorseCard.vue, HorsePrice.vue
│   └── shared/          #   BrandLogo, LanguageSwitcher, StatusBadge, AppSpinner, ConfirmDialog
├── composables/         # useLocalized (bilingual field picker), useWhatsapp, useConfirm
├── directives/          # reveal.js — v-reveal / v-reveal-group scroll animations
├── i18n/
│   ├── index.js         #   createI18n + locale persistence + dir switching
│   └── locales/         #   ar.js, en.js (kept at identical key sets)
├── layouts/             # MainLayout.vue (public), AdminLayout.vue (dashboard)
├── pages/
│   ├── Home/            #   index.vue
│   ├── Horses/          #   index.vue (listing) + HorseDetails.vue
│   ├── About/, Contact/, NotFound/
│   ├── Auth/            #   Login.vue
│   └── Admin/           #   index.vue (dashboard), Horses/, Settings/
├── router/
│   ├── index.js         #   createRouter + auth guards
│   └── routes/index.js  #   route definitions
├── services/            # api.js (axios + Bearer interceptor), toast.js
└── stores/              # auth/, locale/, horses/, settings/ — each folder + index.js
```

> No `types/` folder: this project is plain JavaScript.

---

## Clean Code

- SOLID, DRY, KISS — كود نظيف وبسيط.
- Small focused methods — كل method تعمل حاجة واحدة.
- Meaningful names — أسماء واضحة ومعبرة للمتغيرات والـ functions والـ components.
- Reuse existing code — متكررش logic.
- Remove dead code — شيل أي كود مش مستخدم.
- No comments unless the "why" is non-obvious — متضيفش تعليقات إلا لو الـ "ليه" مش واضح.

---

## Component Conventions

### Structure

كل component يتبع الترتيب ده:

```vue
<script setup>
// 1. Imports
// 2. Props / Emits
// 3. Composables & Stores
// 4. Reactive state (ref, reactive, computed)
// 5. Functions
// 6. Lifecycle hooks (onMounted, etc.)
</script>

<template>
  <!-- Single root element preferred -->
</template>

<style scoped>
/* Only when Tailwind classes aren't enough */
</style>
```

### Rules

- **Always** use `<script setup>` (this project is plain JavaScript — do not add `lang="ts"`).
- **Always** use Composition API — no Options API.
- Keep components focused — لو component بيعمل حاجات كتير، قسّمه.
- Use `defineProps({})` / `defineEmits([])` with runtime options (no TS generics here).
- Reuse existing components before creating new ones.

---

## Naming Conventions

| What | Convention | Example |
|------|-----------|---------|
| Pages | PascalCase folder + `index.vue` | `pages/Home/index.vue` |
| Sub-pages | PascalCase in same folder | `pages/Products/ProductDetails.vue` |
| Components | PascalCase `.vue` | `component/home/Product.vue` |
| Composables | `useXxx` camelCase | `composables/useGetApi.ts` |
| Stores | `useXxxStore` | `stores/auth/index.ts` → `useAuthStore` |
| Types/Interfaces | `I` prefix + PascalCase | `IUserData`, `IProduct` |
| Route names | kebab-case | `'home'`, `'product-details'` |
| CSS classes | Tailwind utilities | `class="flex items-center gap-2"` |
| Events | camelCase | `@updateCart`, `@toggleWishlist` |
| Files (non-Vue) | camelCase `.ts` | `useGetApi.ts`, `authStoreTypes.ts` |

---

## Router Conventions

- Routes defined in `router/routes/index.ts` — separate from router setup.
- Layouts as parent routes wrapping children via `<router-view/>`.
- Use `meta` for auth guards: `{ requireAuth: true }`, `{ guestOnly: true }`.
- Router guard in `router/index.ts` using `beforeEach`.
- Navigate with named routes: `{ name: 'xxx' }`.
- 404 catch-all: `path: '/:pathMatch(.*)*'`.

```ts
// router/routes/index.ts
export const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      { path: '', name: 'home', component: Home },
      { path: 'login', name: 'login', component: Login, meta: { guestOnly: true } },
      { path: 'dashboard', name: 'dashboard', component: Dashboard, meta: { requireAuth: true } },
    ],
  },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
]
```

---

## API Handling

> This section applies only when the project connects to a backend API. For static/no-API projects, skip this.

### Frontend API Contract

- Follow the API contract defined by the backend.
- Do not assume any specific response format unless this project defines one.
- Check the actual API responses before writing integration code.

### Axios Usage

- Axios directly in stores/composables — مفيش centralized instance إلا لو المشروع كبير.
- For large projects needing interceptors (token injection, error handling) → create `api.ts` with axios instance.

### Composables for Reusable Fetching

```ts
// composables/useGetApi.ts
export function useGetApi() {
  const data = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function getData(url: string) {
    loading.value = true
    try {
      const response = await axios.get(url)
      data.value = response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Something went wrong'
    } finally {
      loading.value = false
    }
  }

  return { data, loading, error, getData }
}
```

### API in Stores

- API calls for specific state (auth, cart, wishlist) go inside their store.
- Always use `try/catch`.

---

## Store Conventions (Pinia)

- **Always** use setup/composition syntax (not options syntax).
- Each store in its own folder: `stores/auth/index.ts`.
- Naming: `useXxxStore`.
- Auth store calls `getTokens()` from localStorage in `App.vue`'s `onMounted`.

```ts
export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const userData = ref<IUserData | null>(null)
  const isLoggedIn = computed(() => !!token.value)

  function login(values: { email: string; password: string }) { /* ... */ }
  function logout() { /* ... */ }
  function getTokens() { /* from localStorage */ }

  return { token, userData, isLoggedIn, login, logout, getTokens }
})
```

---

## Error Handling

- Every API call must be in `try/catch`.
- Show clear error message using `toast.error()` (if vue3-toastify is installed).
- Use `err.response?.data?.message` as fallback message.
- Form validation errors handled via VeeValidate + Yup (if installed).
- 404 page for non-existent routes.
- Loading state required for every async operation.

```ts
try {
  const response = await axios.get(url)
  // handle success
} catch (err: any) {
  toast.error(err.response?.data?.message ?? 'Something went wrong', { theme: 'auto' })
} finally {
  loader.hide()
}
```

---

## Loading States

- `vue-loading-overlay` for full-page loading (if installed).
- `ref<boolean>` loading state for each async operation.
- Per-button loading state when needed (e.g. `addingToCartId`).

```ts
const $loading = useLoading()

async function fetchData() {
  const loader = $loading.show({ canCancel: false })
  try {
    // ...
  } finally {
    loader.hide()
  }
}
```

---

## Form Validation

> Only applies when VeeValidate + Yup are installed.

- Use VeeValidate (`<Form>`, `<Field>`, `<ErrorMessage>`).
- Yup for schema validation.
- Define fields as array of objects for simple forms.
- Define schema with Yup `object()`.

```vue
<Form @submit="handleSubmit" :validation-schema="schema">
  <div v-for="field in fields" :key="field.name">
    <label :for="field.name">{{ field.name }}</label>
    <Field :name="field.name" :type="field.type" :placeholder="field.placeholder" />
    <ErrorMessage :name="field.name" />
  </div>
  <button type="submit">Submit</button>
</Form>
```

---

## Styling (Tailwind CSS)

- **Tailwind utility classes first** — directly in templates.
- **Scoped `<style scoped>`** — only when Tailwind isn't enough (complex animations, carousel, deep selectors).
- **`@apply`** — use sparingly for repeated class groups.
- **Responsive**: Tailwind breakpoints (`sm:`, `md:`, `lg:`, `xl:`). Mobile-first approach.
- **Custom classes**: add in `main.css` or `tailwind.config` when needed.
- **Flowbite**: use its components when suitable (if installed).

---

## Animation

الأنيميشن مهم ومتوقع في أغلب المشاريع.

### Basic (CSS)

- Tailwind transitions: `transition-all duration-300`, `hover:scale-110`, `hover:-translate-y-1`.
- Vue `<Transition>` and `<TransitionGroup>`.
- Scoped CSS keyframes when needed.

### Advanced (only if library is installed)

- **AOS** — scroll-triggered animations.
- **GSAP** — complex animations (timelines, scroll triggers).
- **@vueuse/motion** — declarative animations in Vue.
- Pick the right library for the job — متحملش مكتبة كبيرة لأنيميشن بسيط.

### Conventions

- Page transitions: `<Transition>` on `<router-view>`.
- Hover effects: Tailwind `group-hover:`, `hover:`.
- Scroll animations: AOS or Intersection Observer.
- Keep animations smooth and purposeful — يخدم الـ UX مش يشتت.

---

## Language & RTL

- Most projects are **Arabic-only** or **Arabic + English**.
- If bilingual: **Arabic is the default** language on load.
- Set `dir="rtl"` and `lang="ar"` on the `<html>` tag.
- Tailwind RTL: use `rtl:` modifier or logical properties (`ms-`, `me-`, `ps-`, `pe-`, `start`, `end`).
- If bilingual: store locale in `localStorage` + Pinia store, toggle `dir`/`lang` dynamically.
- Translations: use `vue-i18n` or a static object depending on project size.

---

## Responsive Design

- **Mobile-first** approach with Tailwind.
- Breakpoints: `sm:640px`, `md:768px`, `lg:1024px`, `xl:1280px`.
- Every component must be responsive.
- Navigation: hamburger menu for mobile, full nav for desktop.
- Grid: `grid-cols-1` → `md:grid-cols-2` → `lg:grid-cols-4`.
- Images: `object-cover` + `w-full` + `max-w-` as needed.
- Test on multiple screen sizes before delivering.

---

## Accessibility

- Semantic HTML: `<nav>`, `<main>`, `<section>`, `<article>`, `<button>`.
- `alt` attribute on every `<img>`.
- `aria-label` for icon-only buttons.
- `<label>` linked to every `<input>` in forms.
- `sr-only` class for screen-reader-only text.
- Visible focus states — never remove them.
- Sufficient color contrast.
- Keyboard navigation must work (Tab, Enter, Escape).

---

## TypeScript Conventions

- Define interfaces in `types/` folder.
- Use `I` prefix: `IUserData`, `IProduct`, `ITokenPayload`.
- Use TypeScript generics in `defineProps<{}>()` and `defineEmits<{}>()`.
- Avoid `any` when possible — define specific types.
- Export all interfaces/types for reuse.

---

## Git Conventions

- **Commit messages**: clear and concise in English.
  - `feat: add product details page`
  - `fix: resolve cart count not updating`
  - `style: adjust navbar responsive layout`
  - `refactor: extract useGetApi composable`
- **Branches**: `feature/xxx`, `fix/xxx`, `hotfix/xxx`.
- **Never commit**: `node_modules/`, `.env`, `.DS_Store`, `dist/`.
- `.gitignore` must be set up properly from the start.

---

## Constraints

- Don't install new packages unless explicitly asked.
- Don't change unrelated code.
- Maintain backward compatibility.
- Follow existing naming conventions.
- Don't change folder structure unless asked.
- Remove all `console.log` before delivering.
