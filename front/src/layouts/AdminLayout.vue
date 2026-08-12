<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BrandLogo from '@/components/shared/BrandLogo.vue'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const sidebarOpen = ref(false)

const links = [
  { name: 'admin-dashboard', label: 'admin.dashboard' },
  { name: 'admin-horses', label: 'admin.horses' },
  { name: 'admin-settings', label: 'admin.settings' },
]

watch(
  () => route.fullPath,
  () => (sidebarOpen.value = false),
)

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'home' })
}
</script>

<template>
  <div class="min-h-screen bg-ink-50">
    <header class="sticky top-0 z-40 border-b border-ink-200 bg-white">
      <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="rounded-lg p-2 text-ink-600 transition hover:bg-ink-100 lg:hidden"
            aria-label="Menu"
            @click="sidebarOpen = !sidebarOpen"
          >
            <svg
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              aria-hidden="true"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
              />
            </svg>
          </button>
          <BrandLogo size="sm" />
        </div>

        <div class="flex items-center gap-2">
          <LanguageSwitcher />
          <RouterLink
            :to="{ name: 'home' }"
            class="hidden rounded-full border border-ink-200 px-4 py-1.5 text-xs text-ink-600 transition hover:border-gold-400 hover:text-gold-700 sm:inline-flex"
          >
            {{ t('admin.viewSite') }}
          </RouterLink>
          <button
            type="button"
            class="rounded-full bg-ink-900 px-4 py-1.5 text-xs text-white transition hover:bg-ink-800"
            @click="handleLogout"
          >
            {{ t('nav.logout') }}
          </button>
        </div>
      </div>
    </header>

    <div class="mx-auto flex max-w-7xl gap-6 px-4 py-6 sm:px-6">
      <aside
        class="fixed inset-y-0 start-0 z-50 w-64 shrink-0 border-e border-ink-200 bg-white p-4 transition-transform lg:static lg:z-auto lg:w-56 lg:translate-x-0 lg:border-0 lg:bg-transparent lg:p-0"
        :class="
          sidebarOpen ? 'translate-x-0' : 'max-lg:ltr:-translate-x-full max-lg:rtl:translate-x-full'
        "
      >
        <nav class="space-y-1">
          <RouterLink
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="block rounded-lg px-4 py-2.5 text-sm text-ink-600 transition hover:bg-white hover:text-gold-800"
            active-class="!bg-white !text-gold-800 font-medium shadow-sm"
          >
            {{ t(link.label) }}
          </RouterLink>
        </nav>
      </aside>

      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-40 bg-ink-950/40 lg:hidden"
        @click="sidebarOpen = false"
      />

      <main class="min-w-0 flex-1">
        <RouterView />
      </main>
    </div>
  </div>
</template>
