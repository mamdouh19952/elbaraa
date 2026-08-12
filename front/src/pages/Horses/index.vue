<script setup>
import { onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useHorsesStore } from '@/stores/horses'
import { useLocalized } from '@/composables/useLocalized'
import HorseCard from '@/components/horses/HorseCard.vue'
import AppSpinner from '@/components/shared/AppSpinner.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const horsesStore = useHorsesStore()
const { pick } = useLocalized()

const activeCategory = ref(route.query.category || '')

function selectCategory(slug) {
  activeCategory.value = slug
  router.replace({ query: slug ? { category: slug } : {} })
}

function load() {
  horsesStore.fetchHorses(activeCategory.value ? { category: activeCategory.value } : {})
}

watch(activeCategory, load)

onMounted(() => {
  horsesStore.fetchCategories()
  load()
})
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <header class="border-b border-ink-200 pb-10">
      <p class="eyebrow">{{ t('home.featuredEyebrow') }}</p>
      <div class="mt-3">
        <h1 class="text-5xl text-ink-900 sm:text-6xl">{{ t('horses.title') }}</h1>
        <p class="mt-3 text-lg text-ink-500">{{ t('horses.subtitle') }}</p>
      </div>
    </header>

    <!-- Filters read as a catalogue index rather than pill buttons -->
    <div class="flex flex-wrap items-center gap-x-8 gap-y-3 border-b border-ink-200 py-5">
      <button
        type="button"
        class="text-sm font-semibold transition"
        :class="activeCategory === '' ? 'text-gold-700' : 'text-ink-500 hover:text-gold-600'"
        @click="selectCategory('')"
      >
        {{ t('horses.all') }}
        <span
          class="mt-1.5 block h-0.5 transition-all duration-300"
          :class="activeCategory === '' ? 'bg-gold-400' : 'bg-transparent'"
        />
      </button>

      <button
        v-for="category in horsesStore.categories"
        :key="category.id"
        type="button"
        class="text-sm font-semibold transition"
        :class="
          activeCategory === category.slug ? 'text-gold-700' : 'text-ink-500 hover:text-gold-600'
        "
        @click="selectCategory(category.slug)"
      >
        {{ pick(category.name) }}
        <span
          class="mt-1.5 block h-0.5 transition-all duration-300"
          :class="activeCategory === category.slug ? 'bg-gold-400' : 'bg-transparent'"
        />
      </button>
    </div>

    <AppSpinner v-if="horsesStore.loading" />

    <div v-else-if="horsesStore.error" class="py-20 text-center">
      <p class="text-ink-500">{{ t('horses.loadError') }}</p>
      <button type="button" class="btn-outline mt-6" @click="load">{{ t('common.retry') }}</button>
    </div>

    <div
      v-else-if="horsesStore.horses.length"
      class="grid gap-x-8 gap-y-14 pt-14 sm:grid-cols-2 lg:grid-cols-3"
    >
      <HorseCard
        v-for="(horse, index) in horsesStore.horses"
        :key="horse.id"
        :horse="horse"
        :index="index"
      />
    </div>

    <p v-else class="py-24 text-center font-display text-xl text-ink-400">
      {{ t('horses.empty') }}
    </p>
  </div>
</template>
