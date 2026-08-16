<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  price: { type: Number, default: null },
  currency: { type: String, default: 'USD' },
})

const { t, locale } = useI18n()

const INTL_LOCALES = { ar: 'ar-EG', en: 'en-US', zh: 'zh-CN' }

const hasPrice = computed(() => props.price !== null && props.price !== undefined)

const formatted = computed(() => {
  if (!hasPrice.value) return null

  return new Intl.NumberFormat(INTL_LOCALES[locale.value] || 'en-US', {
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
