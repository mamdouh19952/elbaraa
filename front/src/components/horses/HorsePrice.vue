<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  price: { type: Number, default: null },
  currency: { type: String, default: 'USD' },
})

const { t, locale } = useI18n()

const hasPrice = computed(() => props.price !== null && props.price !== undefined)

const formatted = computed(() => {
  if (!hasPrice.value) return null

  return new Intl.NumberFormat(locale.value === 'ar' ? 'ar-EG' : 'en-US', {
    style: 'currency',
    currency: props.currency || 'USD',
    maximumFractionDigits: 0,
  }).format(props.price)
})
</script>

<template>
  <span v-if="hasPrice" class="font-display font-semibold text-gold-400">{{ formatted }}</span>
  <span v-else class="font-display font-medium text-ink-400">{{ t('horse.priceOnRequest') }}</span>
</template>
