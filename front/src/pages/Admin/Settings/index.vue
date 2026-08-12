<script setup>
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useSettingsStore } from '@/stores/settings'
import { apiErrorMessage } from '@/services/api'
import { toastSuccess, toastError } from '@/services/toast'
import AppSpinner from '@/components/shared/AppSpinner.vue'

const { t } = useI18n()
const settingsStore = useSettingsStore()

const form = ref({
  about_en: '',
  about_ar: '',
  phone: '',
  whatsapp: '',
  email: '',
  address_en: '',
  address_ar: '',
  facebook: '',
  instagram: '',
})

const loading = ref(true)
const saving = ref(false)
const errors = ref({})

async function handleSubmit() {
  saving.value = true
  errors.value = {}

  try {
    await settingsStore.updateSettings(form.value)
    toastSuccess(t('admin.saved'))
  } catch (err) {
    toastError(apiErrorMessage(err))
    errors.value = err.response?.data?.errors || {}
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await settingsStore.fetchSettings(true)
  form.value = { ...form.value, ...settingsStore.settings }
  loading.value = false
})
</script>

<template>
  <div class="max-w-3xl">
    <h1 class="text-2xl text-ink-900">{{ t('admin.settingsTitle') }}</h1>
    <p class="mt-1 text-sm text-ink-500">{{ t('admin.settingsSubtitle') }}</p>

    <AppSpinner v-if="loading" />

    <form v-else class="mt-6 space-y-6" @submit.prevent="handleSubmit">
      <div class="card grid gap-4 p-5 sm:grid-cols-2">
        <div>
          <label for="about_en" class="field-label">{{ t('admin.aboutEn') }}</label>
          <textarea id="about_en" v-model="form.about_en" rows="8" dir="ltr" class="field-input" />
        </div>
        <div>
          <label for="about_ar" class="field-label">{{ t('admin.aboutAr') }}</label>
          <textarea id="about_ar" v-model="form.about_ar" rows="8" dir="rtl" class="field-input" />
        </div>
      </div>

      <div class="card grid gap-4 p-5 sm:grid-cols-2">
        <div>
          <label for="phone" class="field-label">{{ t('contact.phone') }}</label>
          <input id="phone" v-model="form.phone" type="text" dir="ltr" class="field-input" />
        </div>

        <div>
          <label for="whatsapp" class="field-label">{{ t('contact.whatsapp') }}</label>
          <input id="whatsapp" v-model="form.whatsapp" type="text" dir="ltr" class="field-input" />
          <p class="mt-1 text-xs text-ink-400">{{ t('admin.whatsappHelp') }}</p>
        </div>

        <div>
          <label for="email" class="field-label">{{ t('contact.email') }}</label>
          <input id="email" v-model="form.email" type="email" dir="ltr" class="field-input" />
          <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email[0] }}</p>
        </div>

        <div />

        <div>
          <label for="address_en" class="field-label">{{ t('admin.addressEn') }}</label>
          <input
            id="address_en"
            v-model="form.address_en"
            type="text"
            dir="ltr"
            class="field-input"
          />
        </div>
        <div>
          <label for="address_ar" class="field-label">{{ t('admin.addressAr') }}</label>
          <input
            id="address_ar"
            v-model="form.address_ar"
            type="text"
            dir="rtl"
            class="field-input"
          />
        </div>

        <div>
          <label for="facebook" class="field-label">Facebook</label>
          <input id="facebook" v-model="form.facebook" type="url" dir="ltr" class="field-input" />
          <p v-if="errors.facebook" class="mt-1 text-xs text-red-600">{{ errors.facebook[0] }}</p>
        </div>
        <div>
          <label for="instagram" class="field-label">Instagram</label>
          <input id="instagram" v-model="form.instagram" type="url" dir="ltr" class="field-input" />
          <p v-if="errors.instagram" class="mt-1 text-xs text-red-600">{{ errors.instagram[0] }}</p>
        </div>
      </div>

      <button type="submit" class="btn-gold" :disabled="saving">
        {{ saving ? t('admin.saving') : t('admin.save') }}
      </button>
    </form>
  </div>
</template>
