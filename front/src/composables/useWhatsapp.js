import { useI18n } from 'vue-i18n'
import { useSettingsStore } from '@/stores/settings'

export function useWhatsapp() {
  const { t } = useI18n()
  const settingsStore = useSettingsStore()

  function link(message) {
    const number = (settingsStore.settings.whatsapp || '').replace(/\D/g, '')

    if (!number) return null

    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`
  }

  function horseLink(horseName) {
    return link(t('whatsapp.horseMessage', { name: horseName }))
  }

  function generalLink() {
    return link(t('whatsapp.generalMessage'))
  }

  return { link, horseLink, generalLink }
}
