<script setup>
import { useLocaleStore } from '@/stores/locale'
import { SUPPORTED_LOCALES } from '@/i18n'

defineProps({
  variant: { type: String, default: 'dark' },
})

const localeStore = useLocaleStore()
const labels = { ar: 'العربية', en: 'English', zh: '中文' }
</script>

<template>
  <div class="inline-flex items-center gap-3 text-sm font-semibold">
    <button
      v-for="locale in SUPPORTED_LOCALES"
      :key="locale"
      type="button"
      :aria-label="`Switch language to ${labels[locale]}`"
      :aria-current="localeStore.current === locale ? 'true' : undefined"
      class="transition"
      :class="[
        localeStore.current === locale
          ? variant === 'light'
            ? 'text-gold-300'
            : 'text-gold-700'
          : variant === 'light'
            ? 'text-white/70 hover:text-gold-300'
            : 'text-ink-500 hover:text-gold-700',
      ]"
      @click="localeStore.set(locale)"
    >
      {{ labels[locale] }}
    </button>
  </div>
</template>
