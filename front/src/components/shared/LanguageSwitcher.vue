<script setup>
import { useLocaleStore } from '@/stores/locale'

defineProps({
  variant: { type: String, default: 'dark' },
})

const localeStore = useLocaleStore()
const labels = { ar: 'العربية', en: 'English' }
</script>

<template>
  <button
    type="button"
    :aria-label="`Switch language to ${labels[localeStore.other]}`"
    class="group inline-flex items-center gap-2 text-sm font-semibold transition"
    :class="
      variant === 'light' ? 'text-white/90 hover:text-gold-300' : 'text-ink-600 hover:text-gold-700'
    "
    @click="localeStore.toggle()"
  >
    {{ labels[localeStore.other] }}
    <span
      class="h-px w-4 transition-all duration-300 group-hover:w-6"
      :class="variant === 'light' ? 'bg-gold-300' : 'bg-gold-500'"
    />
  </button>
</template>
