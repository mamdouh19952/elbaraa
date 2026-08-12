<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiErrorMessage } from '@/services/api'
import { toastError } from '@/services/toast'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const form = ref({ email: '', password: '' })
const loading = ref(false)

async function handleSubmit() {
  loading.value = true

  try {
    await auth.login(form.value)
    router.push(route.query.redirect || { name: 'admin-dashboard' })
  } catch (err) {
    toastError(apiErrorMessage(err, t('auth.failed')))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-12">
    <img
      src="/images/725621135_1541851770949941_8019762622346553312_n.jpeg"
      alt=""
      class="absolute inset-0 h-full w-full object-cover"
    />
    <div class="absolute inset-0 bg-ink-950/80" />

    <div class="absolute top-5 end-5 z-20">
      <LanguageSwitcher variant="light" />
    </div>

    <div class="relative z-10 w-full max-w-md">
      <div class="mb-8 text-center">
        <span class="inline-flex rounded-2xl bg-cream p-3 ring-1 ring-gold-400/30">
          <img src="/images/logo.png" :alt="t('brand.name')" class="h-20 w-auto object-contain" />
        </span>
      </div>

      <div class="rounded-3xl border-t-2 border-gold-400 bg-cream p-8 shadow-2xl">
        <h1 class="text-3xl text-ink-900">{{ t('auth.loginTitle') }}</h1>
        <p class="mt-1 text-sm text-ink-500">{{ t('auth.loginSubtitle') }}</p>

        <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
          <div>
            <label for="email" class="field-label">{{ t('auth.email') }}</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              dir="ltr"
              class="field-input"
            />
          </div>

          <div>
            <label for="password" class="field-label">{{ t('auth.password') }}</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              dir="ltr"
              class="field-input"
            />
          </div>

          <button type="submit" class="btn-gold w-full" :disabled="loading">
            {{ loading ? t('auth.signingIn') : t('auth.signIn') }}
          </button>
        </form>
      </div>

      <div class="mt-6 text-center">
        <RouterLink :to="{ name: 'home' }" class="text-sm text-ink-300 transition hover:text-white">
          {{ t('auth.backToSite') }}
        </RouterLink>
      </div>
    </div>
  </div>
</template>
