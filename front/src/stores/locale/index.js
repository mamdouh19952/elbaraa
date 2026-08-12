import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import i18n, { applyLocale, storedLocale } from '@/i18n'

export const useLocaleStore = defineStore('locale', () => {
  const current = ref(storedLocale())
  const isRtl = computed(() => current.value === 'ar')
  const other = computed(() => (current.value === 'ar' ? 'en' : 'ar'))

  function set(locale) {
    current.value = locale
    i18n.global.locale.value = locale
    applyLocale(locale)
  }

  function toggle() {
    set(other.value)
  }

  function init() {
    set(current.value)
  }

  return { current, isRtl, other, set, toggle, init }
})
