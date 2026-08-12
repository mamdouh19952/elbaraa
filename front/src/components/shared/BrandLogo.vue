<script setup>
import { useI18n } from 'vue-i18n'

/**
 * The logo file carries its own dark radial-glow background (not transparent),
 * so it already reads as a self-contained medallion on any page background —
 * framed the same way everywhere, no light/dark variant needed.
 */
defineProps({
  size: { type: String, default: 'md' },
  asLink: { type: Boolean, default: true },
})

const { t } = useI18n()

const sizes = {
  sm: 'h-12',
  md: 'h-16',
  lg: 'h-28',
}
</script>

<template>
  <component
    :is="asLink ? 'RouterLink' : 'div'"
    v-bind="asLink ? { to: { name: 'home' } } : {}"
    class="inline-flex items-center"
  >
    <span class="inline-flex items-center justify-center rounded-lg ring-1 ring-gold-400/40">
      <img
        src="/images/logo.png"
        :alt="t('brand.name')"
        :class="sizes[size]"
        class="w-auto rounded-lg object-contain"
      />
    </span>
    <span class="sr-only">{{ t('brand.name') }}</span>
  </component>
</template>
