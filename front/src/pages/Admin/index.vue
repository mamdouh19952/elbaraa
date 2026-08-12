<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import { useLocalized } from '@/composables/useLocalized'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import AppSpinner from '@/components/shared/AppSpinner.vue'

const { t } = useI18n()
const { pick } = useLocalized()

const horses = ref([])
const loading = ref(true)

const stats = computed(() => ({
  total: horses.value.length,
  available: horses.value.filter((h) => h.status === 'available').length,
  sold: horses.value.filter((h) => h.status === 'sold').length,
  forSale: horses.value.filter((h) => h.categories.some((c) => c.slug === 'sale')).length,
}))

const recent = computed(() => horses.value.slice(0, 5))

onMounted(async () => {
  try {
    const { data } = await api.get('/horses', { params: { per_page: 100 } })
    horses.value = data.data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <h1 class="text-2xl text-ink-900">{{ t('admin.dashboard') }}</h1>

    <AppSpinner v-if="loading" />

    <template v-else>
      <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card p-5">
          <p class="text-sm text-ink-500">{{ t('admin.totalHorses') }}</p>
          <p class="mt-1 text-3xl text-ink-900">{{ stats.total }}</p>
        </div>
        <div class="card p-5">
          <p class="text-sm text-ink-500">{{ t('admin.available') }}</p>
          <p class="mt-1 text-3xl text-emerald-600">{{ stats.available }}</p>
        </div>
        <div class="card p-5">
          <p class="text-sm text-ink-500">{{ t('admin.forSale') }}</p>
          <p class="mt-1 text-3xl text-gold-600">{{ stats.forSale }}</p>
        </div>
        <div class="card p-5">
          <p class="text-sm text-ink-500">{{ t('admin.sold') }}</p>
          <p class="mt-1 text-3xl text-ink-400">{{ stats.sold }}</p>
        </div>
      </div>

      <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg text-ink-900">{{ t('admin.recentHorses') }}</h2>
        <RouterLink :to="{ name: 'admin-horse-create' }" class="btn-gold !px-5 !py-2 text-xs">
          + {{ t('admin.addHorse') }}
        </RouterLink>
      </div>

      <div v-if="recent.length" class="mt-4 space-y-2">
        <RouterLink
          v-for="horse in recent"
          :key="horse.id"
          :to="{ name: 'admin-horse-edit', params: { id: horse.id } }"
          class="card flex items-center gap-4 p-3 transition hover:border-gold-300"
        >
          <img
            v-if="horse.cover_image"
            :src="horse.cover_image"
            :alt="pick(horse.name)"
            class="h-14 w-14 shrink-0 rounded-lg object-cover"
          />
          <div v-else class="h-14 w-14 shrink-0 rounded-lg bg-ink-100" />

          <div class="min-w-0 flex-1">
            <p class="truncate font-medium text-ink-900">{{ pick(horse.name) }}</p>
            <p class="truncate text-sm text-ink-500">{{ pick(horse.breed) }}</p>
          </div>

          <StatusBadge :status="horse.status" />
        </RouterLink>
      </div>

      <p v-else class="mt-4 text-ink-400">{{ t('admin.noHorses') }}</p>
    </template>
  </div>
</template>
