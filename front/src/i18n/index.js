import { createI18n } from 'vue-i18n'
import ar from './locales/ar'
import en from './locales/en'

export const SUPPORTED_LOCALES = ['ar', 'en']
export const DEFAULT_LOCALE = 'ar'
const STORAGE_KEY = 'locale'

export function storedLocale() {
  const saved = localStorage.getItem(STORAGE_KEY)

  return SUPPORTED_LOCALES.includes(saved) ? saved : DEFAULT_LOCALE
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
  messages: { ar, en },
})

export default i18n
