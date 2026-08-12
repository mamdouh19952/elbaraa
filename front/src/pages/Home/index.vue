<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useSettingsStore } from '@/stores/settings'
import api from '@/services/api'
import HorseCard from '@/components/horses/HorseCard.vue'
import AppSpinner from '@/components/shared/AppSpinner.vue'

const { t, locale } = useI18n()
const settingsStore = useSettingsStore()

const featured = ref([])
const loading = ref(true)

const aboutText = computed(() => {
  const value =
    locale.value === 'ar' ? settingsStore.settings.about_ar : settingsStore.settings.about_en

  return (value || '').split('\n').filter(Boolean)[0] || ''
})

onMounted(async () => {
  settingsStore.fetchSettings()

  try {
    const { data } = await api.get('/horses', { params: { featured: 1, per_page: 3 } })
    featured.value = data.data.data
  } catch {
    featured.value = []
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <!-- Hero: full-bleed photo, text anchored low + to the start edge -->
    <section class="relative flex min-h-screen items-end overflow-hidden bg-ink-950">
      <img
        src="/images/612612968_33687602540831053_7359887036261105813_n.jpg"
        :alt="t('brand.name')"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/45 to-ink-950/20" />
      <div
        class="absolute inset-0 bg-gradient-to-r from-ink-950/70 via-transparent to-transparent"
      />

      <div class="relative z-10 w-full px-4 pb-20 sm:px-6 lg:px-8 lg:pb-28">
        <div class="mx-auto max-w-7xl">
          <div class="max-w-2xl">
            <p class="fade-up eyebrow text-gold-400">{{ t('home.heroEyebrow') }}</p>
            <h1 class="fade-up mt-5 text-5xl leading-[1.05] text-white sm:text-6xl lg:text-7xl">
              {{ t('home.heroTitle') }}
            </h1>
            <p class="fade-up mt-6 max-w-xl text-lg leading-relaxed text-ink-100 sm:text-xl">
              {{ t('home.heroSubtitle') }}
            </p>
            <div class="fade-up mt-10 flex flex-wrap items-center gap-4">
              <RouterLink :to="{ name: 'horses' }" class="btn-gold">
                {{ t('home.viewHorses') }}
              </RouterLink>
              <RouterLink :to="{ name: 'contact' }" class="btn-ghost-light">
                {{ t('home.contactUs') }}
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      <div class="absolute bottom-6 start-1/2 z-10 -translate-x-1/2 rtl:translate-x-1/2">
        <svg
          class="h-5 w-5 animate-bounce text-white/50"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M19.5 8.25l-7.5 7.5-7.5-7.5"
          />
        </svg>
      </div>
    </section>

    <!-- Featured horses -->
    <section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
      <div
        v-reveal
        class="mb-14 flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end"
      >
        <div>
          <p class="eyebrow">{{ t('home.featuredEyebrow') }}</p>
          <h2 class="mt-3 text-4xl text-ink-900 sm:text-5xl">{{ t('home.featuredTitle') }}</h2>
          <p class="mt-3 text-lg text-ink-500">{{ t('home.featuredSubtitle') }}</p>
        </div>
        <RouterLink
          v-if="featured.length"
          :to="{ name: 'horses' }"
          class="hidden shrink-0 items-center gap-2 text-sm font-semibold text-gold-400 transition hover:text-gold-800 sm:inline-flex"
        >
          {{ t('home.viewAll') }}
          <span aria-hidden="true" class="rtl:rotate-180">→</span>
        </RouterLink>
      </div>

      <AppSpinner v-if="loading" />

      <div
        v-else-if="featured.length"
        v-reveal-group
        class="grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3"
      >
        <HorseCard
          v-for="(horse, index) in featured"
          :key="horse.id"
          :horse="horse"
          :index="index"
        />
      </div>

      <p v-else class="py-10 text-center text-lg text-ink-400">{{ t('horses.empty') }}</p>

      <div v-if="featured.length" class="mt-14 text-center sm:hidden">
        <RouterLink :to="{ name: 'horses' }" class="btn-outline">{{
          t('home.viewAll')
        }}</RouterLink>
      </div>
    </section>

    <!-- Heritage / about teaser — asymmetric image + text split -->
    <section class="bg-cream-deep">
      <div class="mx-auto grid max-w-7xl items-stretch lg:grid-cols-2">
        <div v-reveal class="media-frame relative min-h-[380px] overflow-hidden lg:min-h-[560px]">
          <img
            src="/images/580475452_32882301471361168_2141722473212184555_n.jpg"
            :alt="t('home.aboutTitle')"
            loading="lazy"
            class="absolute inset-0 h-full w-full object-cover"
          />
        </div>

        <div v-reveal class="flex flex-col justify-center px-6 py-16 sm:px-12 lg:px-16 lg:py-20">
          <p class="eyebrow">{{ t('home.aboutEyebrow') }}</p>
          <h2 class="mt-4 max-w-md text-4xl leading-tight text-ink-900 sm:text-5xl">
            {{ t('home.aboutTitle') }}
          </h2>
          <p class="mt-6 max-w-md text-lg leading-relaxed text-ink-600">{{ aboutText }}</p>
          <RouterLink :to="{ name: 'about' }" class="btn-outline mt-10 self-start">
            {{ t('home.aboutMore') }}
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="mx-auto max-w-3xl px-4 py-28 text-center sm:px-6">
      <p v-reveal class="eyebrow">{{ t('home.ctaEyebrow') }}</p>
      <h2 v-reveal class="mt-4 text-4xl text-ink-900 sm:text-5xl">{{ t('home.ctaTitle') }}</h2>
      <p v-reveal class="mt-4 text-lg text-ink-500">{{ t('home.ctaBody') }}</p>
      <RouterLink v-reveal :to="{ name: 'contact' }" class="btn-gold mt-10">
        {{ t('home.contactUs') }}
      </RouterLink>
    </section>
  </div>
</template>
