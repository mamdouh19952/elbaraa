import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(null)
  const user = ref(null)
  const isLoggedIn = computed(() => !!token.value)

  function setToken(value) {
    token.value = value
    if (value) {
      localStorage.setItem('token', value)
    } else {
      localStorage.removeItem('token')
    }
  }

  async function login(credentials) {
    const { data } = await api.post('/login', credentials)
    setToken(data.data.token)
    user.value = data.data.user

    return data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } finally {
      setToken(null)
      user.value = null
    }
  }

  function getTokens() {
    const saved = localStorage.getItem('token')

    if (saved) {
      token.value = saved
    }
  }

  async function fetchUser() {
    if (!token.value) return

    try {
      const { data } = await api.get('/me')
      user.value = data.data
    } catch {
      // Token is stale or revoked — clear it so guards send the admin to login.
      setToken(null)
      user.value = null
    }
  }

  return { token, user, isLoggedIn, login, logout, getTokens, fetchUser }
})
