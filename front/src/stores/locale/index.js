import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import i18n, { applyLocale, storedLocale, SUPPORTED_LOCALES } from '@/i18n'

export const useLocaleStore = defineStore('locale', () => {
  const current = ref(storedLocale())
  const isRtl = computed(() => current.value === 'ar')

  function set(locale) {
    if (!SUPPORTED_LOCALES.includes(locale)) return

    current.value = locale
    i18n.global.locale.value = locale
    applyLocale(locale)
  }

  function init() {
    set(current.value)
  }

  return { current, isRtl, set, init }
})
