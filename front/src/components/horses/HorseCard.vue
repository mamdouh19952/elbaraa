<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLocalized } from '@/composables/useLocalized'
import HorsePrice from './HorsePrice.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'

const props = defineProps({
  horse: { type: Object, required: true },
  index: { type: Number, default: 0 },
})

const { t } = useI18n()
const { pick } = useLocalized()

const name = computed(() => pick(props.horse.name))
const breed = computed(() => pick(props.horse.breed))
const lotNumber = computed(() => String(props.index + 1).padStart(2, '0'))
const categoryLine = computed(() =>
  (props.horse.categories || []).map((category) => pick(category.name)).join('  ·  '),
)
</script>

<template>
  <RouterLink
    :to="{ name: 'horse-details', params: { id: horse.id } }"
    class="group block transition-transform duration-500 hover:-translate-y-1.5"
  >
    <div
      class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-ink-50 ring-1 ring-ink-200 transition-shadow duration-500 group-hover:shadow-xl group-hover:shadow-ink-900/10"
    >
      <img
        v-if="horse.cover_image"
        :src="horse.cover_image"
        :alt="name"
        loading="lazy"
        class="h-full w-full object-cover transition duration-[900ms] ease-out group-hover:scale-[1.07]"
      />
      <div v-else class="flex h-full w-full items-center justify-center text-ink-300">
        <svg
          class="h-12 w-12"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 6h.008v.008H18V6z"
          />
        </svg>
      </div>

      <div
        class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink-950/25 via-transparent to-transparent"
      />

      <span
        class="absolute top-3 start-3 rounded-full bg-white/90 px-2.5 py-0.5 font-display text-sm font-semibold text-gold-400 backdrop-blur-sm"
        style="text-shadow: 0 1px 6px rgb(0 0 0 / 0.5)"
      >
        N&deg; {{ lotNumber }}
      </span>

      <div class="absolute top-3 end-3">
        <StatusBadge :status="horse.status" />
      </div>
    </div>

    <div class="pt-5">
      <h3 class="text-2xl leading-tight text-ink-900 transition group-hover:text-gold-400">
        {{ name }}
      </h3>

      <p v-if="breed" class="mt-1 text-base text-ink-500">{{ breed }}</p>

      <p v-if="categoryLine" class="mt-3 text-sm font-medium text-ink-500">
        {{ categoryLine }}
      </p>

      <div
        class="mt-4 h-px w-full rounded-full bg-ink-200 transition-colors duration-500 group-hover:bg-gold-400"
      />

      <!-- Price on its own row so long names never collide with it. -->
      <div class="mt-3 flex items-baseline justify-between gap-3">
        <HorsePrice :price="horse.price" :currency="horse.currency" class="text-lg" />
        <span class="text-sm font-medium text-ink-400 transition group-hover:text-gold-400">
          {{ t('horses.details') }}
        </span>
      </div>
    </div>
  </RouterLink>
</template>
