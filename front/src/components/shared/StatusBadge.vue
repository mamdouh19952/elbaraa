<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  status: { type: String, required: true },
})

const { t } = useI18n()

// A small dot + tracked label on a dark frosted plate reads like a museum
// placard rather than a bright SaaS status chip — and stays legible over photos.
const dotStyles = {
  available: 'bg-emerald-400',
  reserved: 'bg-gold-400',
  sold: 'bg-ink-300',
}
</script>

<template>
  <span
    class="inline-flex items-center gap-2 rounded-full border border-gold-400/40 bg-ink-950/80 px-4 py-1.5 text-xs font-semibold text-white backdrop-blur-sm"
  >
    <span class="h-1.5 w-1.5 rounded-full" :class="dotStyles[status]" />
    {{ t(`status.${status}`) }}
  </span>
</template>
