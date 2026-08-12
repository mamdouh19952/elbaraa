<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-3 py-20 text-ink-400">
    <span
      class="h-8 w-8 animate-spin rounded-full border-2 border-gold-200 border-t-gold-500"
      aria-hidden="true"
    />
    <span class="text-sm">{{ t('common.loading') }}</span>
  </div>
</template>
