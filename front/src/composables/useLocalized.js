import { useI18n } from 'vue-i18n'

/**
 * API resources expose bilingual fields as { en, ar }. This picks the active
 * language and falls back to the other one so a half-translated record still
 * renders something instead of a blank.
 */
export function useLocalized() {
  const { locale } = useI18n()

  function pick(field) {
    if (!field) return ''

    return field[locale.value] || field.en || field.ar || ''
  }

  return { pick }
}
