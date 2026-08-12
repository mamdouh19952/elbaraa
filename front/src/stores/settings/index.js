import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useSettingsStore = defineStore('settings', () => {
  const settings = ref({})
  const loaded = ref(false)

  async function fetchSettings(force = false) {
    if (loaded.value && !force) return

    try {
      const { data } = await api.get('/settings')
      settings.value = data.data
      loaded.value = true
    } catch {
      settings.value = {}
    }
  }

  async function updateSettings(payload) {
    const { data } = await api.put('/settings', payload)
    settings.value = data.data

    return data.data
  }

  return { settings, loaded, fetchSettings, updateSettings }
})
