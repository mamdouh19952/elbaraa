import './assets/main.css'
import 'vue3-toastify/dist/index.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Vue3Toastify from 'vue3-toastify'

import App from './App.vue'
import router from './router'
import i18n from './i18n'
import { useAuthStore } from '@/stores/auth'
import { useLocaleStore } from '@/stores/locale'
import { vReveal, vRevealGroup } from '@/directives/reveal'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(i18n)
app.use(Vue3Toastify, { autoClose: 3500, position: 'top-center', theme: 'light' })
app.directive('reveal', vReveal)
app.directive('reveal-group', vRevealGroup)

// Restore session and language before the router mounts, so the very first
// navigation guard already sees the saved token and the correct direction.
useAuthStore(pinia).getTokens()
useLocaleStore(pinia).init()

app.use(router)
app.mount('#app')
