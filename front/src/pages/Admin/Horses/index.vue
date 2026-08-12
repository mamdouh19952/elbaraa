<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHorsesStore } from '@/stores/horses'
import { useLocalized } from '@/composables/useLocalized'
import { confirm } from '@/composables/useConfirm'
import { toastSuccess } from '@/services/toast'
import HorsePrice from '@/components/horses/HorsePrice.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import AppSpinner from '@/components/shared/AppSpinner.vue'

const { t } = useI18n()
const horsesStore = useHorsesStore()
const { pick } = useLocalized()

const deletingId = ref(null)

async function handleDelete(horse) {
  const name = pick(horse.name)
  const confirmed = await confirm(t('admin.deleteConfirm', { name }))

  if (!confirmed) return

  deletingId.value = horse.id

  try {
    await horsesStore.deleteHorse(horse.id)
    toastSuccess(t('admin.deleted'))
  } finally {
    deletingId.value = null
  }
}

onMounted(() => horsesStore.fetchHorses({ per_page: 100 }))
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl text-ink-900">{{ t('admin.horses') }}</h1>
      <RouterLink :to="{ name: 'admin-horse-create' }" class="btn-gold !px-5 !py-2 text-xs">
        + {{ t('admin.addHorse') }}
      </RouterLink>
    </div>

    <AppSpinner v-if="horsesStore.loading" />

    <div v-else-if="horsesStore.horses.length" class="mt-6 space-y-2">
      <div
        v-for="horse in horsesStore.horses"
        :key="horse.id"
        class="card flex flex-wrap items-center gap-4 p-3"
      >
        <img
          v-if="horse.cover_image"
          :src="horse.cover_image"
          :alt="pick(horse.name)"
          class="h-16 w-16 shrink-0 rounded-lg object-cover"
        />
        <div v-else class="h-16 w-16 shrink-0 rounded-lg bg-ink-100" />

        <div class="min-w-0 flex-1">
          <p class="truncate font-medium text-ink-900">{{ pick(horse.name) }}</p>
          <p class="truncate text-sm text-ink-500">
            {{ t(`gender.${horse.gender}`) }}
            <span v-if="pick(horse.breed)"> · {{ pick(horse.breed) }}</span>
          </p>
          <div class="mt-1 flex flex-wrap gap-1">
            <span
              v-for="category in horse.categories"
              :key="category.id"
              class="rounded-full bg-gold-50 px-2 py-0.5 text-[11px] text-gold-800"
            >
              {{ pick(category.name) }}
            </span>
          </div>
        </div>

        <div class="text-sm">
          <HorsePrice :price="horse.price" :currency="horse.currency" />
        </div>

        <StatusBadge :status="horse.status" />

        <div class="flex gap-2">
          <RouterLink
            :to="{ name: 'admin-horse-edit', params: { id: horse.id } }"
            class="rounded-lg border border-ink-200 px-3 py-1.5 text-xs text-ink-600 transition hover:border-gold-400 hover:text-gold-700"
          >
            {{ t('admin.edit') }}
          </RouterLink>
          <button
            type="button"
            :disabled="deletingId === horse.id"
            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs text-red-600 transition hover:bg-red-50 disabled:opacity-50"
            @click="handleDelete(horse)"
          >
            {{ t('admin.delete') }}
          </button>
        </div>
      </div>
    </div>

    <p v-else class="mt-8 text-ink-400">{{ t('admin.noHorses') }}</p>
  </div>
</template>
