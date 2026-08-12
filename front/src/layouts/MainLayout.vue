<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useSettingsStore } from '@/stores/settings'
import { useWhatsapp } from '@/composables/useWhatsapp'
import BrandLogo from '@/components/shared/BrandLogo.vue'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher.vue'

const { t } = useI18n()
const route = useRoute()
const settingsStore = useSettingsStore()
const { generalLink } = useWhatsapp()
const mobileOpen = ref(false)
const scrolled = ref(false)

const links = [
  { name: 'home', label: 'nav.home' },
  { name: 'horses', label: 'nav.horses' },
  { name: 'about', label: 'nav.about' },
  { name: 'contact', label: 'nav.contact' },
]

// Home and About open on a dark full-bleed hero image the header can float
// over — everywhere else it stays solid so nav text has something to sit on.
const hasHeroBand = computed(() => ['home', 'about'].includes(route.name))
const transparent = computed(() => hasHeroBand.value && !scrolled.value && !mobileOpen.value)

function onScroll() {
  scrolled.value = window.scrollY > 48
}

watch(
  () => route.fullPath,
  () => (mobileOpen.value = false),
)

onMounted(() => {
  settingsStore.fetchSettings()
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})

onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div class="flex min-h-screen flex-col">
    <header
      class="fixed inset-x-0 top-0 z-50 transition-all duration-500"
      :class="
        transparent ? 'bg-transparent' : 'border-b border-ink-100 bg-cream/95 backdrop-blur-md'
      "
    >
      <!-- Scrim so nav text keeps contrast over bright areas of a hero photo. -->
      <div
        v-if="transparent"
        class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-ink-950/70 to-transparent"
        aria-hidden="true"
      />

      <div
        class="relative mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8"
      >
        <BrandLogo size="sm" />

        <nav class="hidden items-center gap-9 md:flex" :aria-label="t('nav.home')">
          <RouterLink
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="group relative py-1 text-base font-medium transition-colors duration-300"
            :class="
              transparent ? 'text-white/90 hover:text-gold-400' : 'text-ink-700 hover:text-gold-400'
            "
            exact-active-class="!text-gold-400 !font-semibold"
          >
            {{ t(link.label) }}
            <span
              class="absolute inset-x-0 -bottom-1 h-0.5 origin-start scale-x-0 rounded-full bg-gold-400 transition-transform duration-300 group-hover:scale-x-100"
            />
          </RouterLink>
        </nav>

        <div class="flex items-center gap-5">
          <LanguageSwitcher
            class="hidden sm:inline-flex"
            :variant="transparent ? 'light' : 'dark'"
          />

          <button
            type="button"
            class="p-2 transition-colors duration-500 md:hidden"
            :class="transparent ? 'text-white' : 'text-ink-700'"
            :aria-expanded="mobileOpen"
            aria-label="Menu"
            @click="mobileOpen = !mobileOpen"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              aria-hidden="true"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                :d="
                  mobileOpen
                    ? 'M6 18L18 6M6 6l12 12'
                    : 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5'
                "
              />
            </svg>
          </button>
        </div>
      </div>

      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <nav v-if="mobileOpen" class="border-t border-ink-100 bg-cream px-4 py-4 md:hidden">
          <RouterLink
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="block py-3 text-base font-semibold text-ink-800 transition hover:text-gold-700"
            exact-active-class="!text-gold-700"
          >
            {{ t(link.label) }}
          </RouterLink>

          <div class="mt-3 flex items-center gap-5 border-t border-ink-100 pt-4">
            <LanguageSwitcher />
          </div>
        </nav>
      </Transition>
    </header>

    <main class="flex-1" :class="hasHeroBand ? '' : 'pt-[72px]'">
      <RouterView v-slot="{ Component }">
        <Transition
          mode="out-in"
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0"
          leave-active-class="transition duration-150 ease-in"
          leave-to-class="opacity-0"
        >
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>

    <footer class="border-t border-ink-200 bg-cream-deep text-ink-700">
      <div class="mx-auto max-w-7xl px-4 pt-16 pb-8 sm:px-6 lg:px-8">
        <div class="grid gap-12 md:grid-cols-[1.3fr_1fr_1fr]">
          <div>
            <BrandLogo size="md" />
            <p class="mt-6 max-w-xs font-display text-lg leading-relaxed text-ink-600">
              {{ t('brand.tagline') }}
            </p>
          </div>

          <div>
            <h4 class="mb-5 text-base font-semibold text-ink-900">{{ t('footer.quickLinks') }}</h4>
            <ul class="space-y-3 text-base">
              <li v-for="link in links" :key="link.name">
                <RouterLink
                  :to="{ name: link.name }"
                  class="text-ink-600 transition hover:text-gold-600"
                >
                  {{ t(link.label) }}
                </RouterLink>
              </li>
            </ul>
          </div>

          <div>
            <h4 class="mb-5 text-base font-semibold text-ink-900">{{ t('contact.title') }}</h4>
            <ul class="space-y-3 text-base text-ink-600">
              <li v-if="settingsStore.settings.phone">
                <a
                  :href="`tel:${settingsStore.settings.phone}`"
                  dir="ltr"
                  class="inline-block transition hover:text-gold-600"
                >
                  {{ settingsStore.settings.phone }}
                </a>
              </li>
              <li v-if="settingsStore.settings.email">
                <a
                  :href="`mailto:${settingsStore.settings.email}`"
                  class="transition hover:text-gold-600"
                >
                  {{ settingsStore.settings.email }}
                </a>
              </li>
              <li v-if="generalLink()">
                <a
                  :href="generalLink()"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="transition hover:text-gold-600"
                >
                  {{ t('contact.whatsapp') }}
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div
          class="mt-14 flex flex-col items-center justify-between gap-3 border-t border-ink-300/50 pt-6 text-center text-xs text-ink-500 sm:flex-row sm:text-start"
        >
          <span
            >© {{ new Date().getFullYear() }} {{ t('brand.name') }}. {{ t('footer.rights') }}</span
          >
          <span class="font-display text-sm text-ink-500">{{ t('brand.tagline') }}</span>
        </div>
      </div>
    </footer>
  </div>
</template>
