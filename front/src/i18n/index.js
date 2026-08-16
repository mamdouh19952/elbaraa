import { createI18n } from 'vue-i18n'
import ar from './locales/ar'
import en from './locales/en'
import zh from './locales/zh'

export const SUPPORTED_LOCALES = ['ar', 'en', 'zh']
export const DEFAULT_LOCALE = 'ar'
const STORAGE_KEY = 'locale'

// Only consulted on a visitor's first-ever visit (storedLocale() only reaches
// here when localStorage has no saved value yet) — picks the first of the
// browser's preferred languages that we actually support, falling back to
// Arabic when none match.
function detectBrowserLocale() {
  const candidates = navigator.languages?.length ? navigator.languages : [navigator.language]

  for (const raw of candidates) {
    const primary = raw?.toLowerCase().split('-')[0]
    if (SUPPORTED_LOCALES.includes(primary)) return primary
  }

  return DEFAULT_LOCALE
}

export function storedLocale() {
  const saved = localStorage.getItem(STORAGE_KEY)

  if (SUPPORTED_LOCALES.includes(saved)) return saved

  return detectBrowserLocale()
}

export function applyLocale(locale) {
  const html = document.documentElement
  html.setAttribute('lang', locale)
  html.setAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr')
  localStorage.setItem(STORAGE_KEY, locale)
}

const i18n = createI18n({
  legacy: false,
  locale: storedLocale(),
  fallbackLocale: 'en',
  messages: { ar, en, zh },
})

export default i18n
