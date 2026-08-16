import { useI18n } from 'vue-i18n'

/**
 * API resources expose translatable fields as { ar, en, zh }. This picks the
 * active language and falls back current locale → English → Arabic, so a
 * partially-translated record still renders something instead of a blank.
 */
export function useLocalized() {
  const { locale } = useI18n()

  function pick(field) {
    if (!field) return ''

    return field[locale.value] || field.en || field.ar || ''
  }

  return { pick }
}
