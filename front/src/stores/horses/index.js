import { ref } from 'vue'
import { defineStore } from 'pinia'
import api, { apiErrorMessage } from '@/services/api'

export const useHorsesStore = defineStore('horses', () => {
  const horses = ref([])
  const categories = ref([])
  const meta = ref(null)
  const loading = ref(false)
  const error = ref(null)

  async function fetchHorses(params = {}) {
    loading.value = true
    error.value = null

    try {
      const { data } = await api.get('/horses', { params })
      horses.value = data.data.data
      meta.value = data.data.meta
    } catch (err) {
      error.value = apiErrorMessage(err)
      horses.value = []
    } finally {
      loading.value = false
    }
  }

  async function fetchCategories() {
    if (categories.value.length) return

    try {
      const { data } = await api.get('/categories')
      categories.value = data.data
    } catch (err) {
      error.value = apiErrorMessage(err)
    }
  }

  async function fetchHorse(id) {
    const { data } = await api.get(`/horses/${id}`)

    return data.data
  }

  async function saveHorse(payload, id = null) {
    const url = id ? `/horses/${id}` : '/horses'
    const { data } = await api.post(url, payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    return data.data
  }

  async function deleteHorse(id) {
    await api.delete(`/horses/${id}`)
    horses.value = horses.value.filter((horse) => horse.id !== id)
  }

  async function deleteImage(horseId, mediaId) {
    const { data } = await api.delete(`/horses/${horseId}/images/${mediaId}`)

    return data.data
  }

  async function deleteVideo(horseId) {
    const { data } = await api.delete(`/horses/${horseId}/video`)

    return data.data
  }

  return {
    horses,
    categories,
    meta,
    loading,
    error,
    fetchHorses,
    fetchCategories,
    fetchHorse,
    saveHorse,
    deleteHorse,
    deleteImage,
    deleteVideo,
  }
})
