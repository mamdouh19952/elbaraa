<script setup>
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useSettingsStore } from '@/stores/settings'

const { t, locale } = useI18n()
const settingsStore = useSettingsStore()

const paragraphs = computed(() => {
  const value =
    locale.value === 'ar' ? settingsStore.settings.about_ar : settingsStore.settings.about_en

  return (value || '').split('\n').filter((line) => line.trim())
})

onMounted(() => settingsStore.fetchSettings())
</script>

<template>
  <div>
    <section class="relative flex h-[70vh] min-h-[420px] items-end overflow-hidden bg-ink-950">
      <img
        src="/images/764925234_1394687099426033_7425239511347304196_n.jpeg"
        :alt="t('about.title')"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/50 to-ink-950/25" />

      <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <p class="eyebrow text-gold-400">{{ t('home.aboutEyebrow') }}</p>
        <h1 class="mt-4 max-w-2xl text-5xl leading-tight text-white sm:text-6xl">
          {{ t('about.title') }}
        </h1>
        <p class="mt-4 text-lg text-ink-100">{{ t('about.subtitle') }}</p>
      </div>
    </section>

    <!-- Editorial body: first paragraph set larger as a standfirst -->
    <section class="mx-auto max-w-3xl px-4 py-20 text-center sm:px-6">
      <div v-reveal>
        <p
          v-if="paragraphs[0]"
          class="mt-6 font-display text-3xl leading-relaxed text-ink-800 sm:text-4xl"
        >
          {{ paragraphs[0] }}
        </p>

        <div class="mt-8 space-y-5 text-start text-xl leading-relaxed text-ink-600">
          <p v-for="(paragraph, index) in paragraphs.slice(1)" :key="index">{{ paragraph }}</p>
        </div>
      </div>
    </section>

    <!-- Asymmetric image pair -->
    <section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
      <div v-reveal-group class="grid gap-6 sm:grid-cols-5">
        <!-- Portrait-oriented source photo, so it fills a 3:4 frame without an awkward crop. -->
        <img
          src="/images/731866426_987207100754875_3438598706395273385_n.jpg"
          alt=""
          loading="lazy"
          class="media-frame aspect-[3/4] w-full object-cover sm:col-span-3"
        />
        <img
          src="/images/616565689_33814184764839496_5653523334166532694_n.jpg"
          alt=""
          loading="lazy"
          class="aspect-[3/4] w-full self-end rounded-2xl object-cover transition-transform duration-500 hover:-translate-y-1.5 sm:col-span-2"
        />
      </div>
    </section>

    <section class="bg-cream-deep py-24 text-center">
      <div class="mx-auto max-w-3xl px-4 sm:px-6">
        <p class="eyebrow">{{ t('home.ctaEyebrow') }}</p>
        <h2 class="mt-4 text-4xl text-ink-900 sm:text-5xl">{{ t('home.ctaTitle') }}</h2>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
          <RouterLink :to="{ name: 'horses' }" class="btn-gold">{{
            t('home.viewHorses')
          }}</RouterLink>
          <RouterLink :to="{ name: 'contact' }" class="btn-outline">{{
            t('home.contactUs')
          }}</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>
