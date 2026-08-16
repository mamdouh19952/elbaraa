<script setup>
defineProps({
  status: { type: Object, required: true }, // { ar: Boolean, en: Boolean, zh: Boolean }
})

const labels = { ar: 'AR', en: 'EN', zh: '中' }
const locales = ['ar', 'en', 'zh']
</script>

<template>
  <span class="inline-flex items-center gap-1.5">
    <span
      v-for="locale in locales"
      :key="locale"
      class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold"
      :class="status[locale] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
    >
      <span class="h-1 w-1 rounded-full" :class="status[locale] ? 'bg-emerald-400' : 'bg-amber-400'" />
      {{ labels[locale] }}
    </span>
  </span>
</template>
