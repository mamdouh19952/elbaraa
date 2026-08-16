<script setup>
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useSettingsStore } from '@/stores/settings'
import { useWhatsapp } from '@/composables/useWhatsapp'
import { useLocalized } from '@/composables/useLocalized'

const { t } = useI18n()
const settingsStore = useSettingsStore()
const { generalLink } = useWhatsapp()
const { pick } = useLocalized()

const settings = computed(() => settingsStore.settings)
const whatsappUrl = computed(() => generalLink())
const address = computed(() =>
  pick({
    ar: settings.value.address_ar,
    en: settings.value.address_en,
    zh: settings.value.address_zh,
  }),
)

onMounted(() => settingsStore.fetchSettings())
</script>

<template>
  <div class="mx-auto max-w-4xl px-4 py-20 sm:px-6">
    <header class="mb-14 text-center">
      <p class="eyebrow">{{ t('home.ctaEyebrow') }}</p>
      <h1 class="mt-3 text-5xl text-ink-900 sm:text-6xl">{{ t('contact.title') }}</h1>
      <p class="mt-4 text-lg text-ink-500">{{ t('contact.subtitle') }}</p>
    </header>

    <div class="grid border-t border-ink-200 sm:grid-cols-2">
      <a
        v-if="settings.phone"
        :href="`tel:${settings.phone}`"
        class="group flex items-center gap-5 border-b border-ink-200 px-2 py-6 transition-all duration-300 hover:bg-white ltr:hover:pl-4 rtl:hover:pr-4"
      >
        <span
          class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-gold-400 text-gold-600 transition-all duration-300 group-hover:scale-110 group-hover:border-gold-500 group-hover:bg-gold-400 group-hover:text-ink-950"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"
            />
          </svg>
        </span>
        <span class="min-w-0">
          <span class="block text-sm font-medium text-ink-500">{{ t('contact.phone') }}</span>
          <span dir="ltr" class="block truncate text-lg text-ink-900 group-hover:text-gold-700">{{
            settings.phone
          }}</span>
        </span>
      </a>

      <a
        v-if="settings.email"
        :href="`mailto:${settings.email}`"
        class="group flex items-center gap-5 border-b border-ink-200 px-2 py-6 transition-all duration-300 hover:bg-white ltr:hover:pl-4 rtl:hover:pr-4"
      >
        <span
          class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-gold-400 text-gold-600 transition-all duration-300 group-hover:scale-110 group-hover:border-gold-500 group-hover:bg-gold-400 group-hover:text-ink-950"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
            />
          </svg>
        </span>
        <span class="min-w-0">
          <span class="block text-sm font-medium text-ink-500">{{ t('contact.email') }}</span>
          <span dir="ltr" class="block truncate text-lg text-ink-900 group-hover:text-gold-700">{{
            settings.email
          }}</span>
        </span>
      </a>

      <div v-if="address" class="flex items-center gap-5 border-b border-ink-200 px-2 py-6">
        <span
          class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-gold-400 text-gold-600"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"
            />
          </svg>
        </span>
        <span>
          <span class="block text-sm font-medium text-ink-500">{{ t('contact.address') }}</span>
          <span class="block text-lg text-ink-900">{{ address }}</span>
        </span>
      </div>

      <a
        v-if="whatsappUrl"
        :href="whatsappUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="group flex items-center gap-5 border-b border-ink-200 px-2 py-6 transition-all duration-300 hover:bg-white ltr:hover:pl-4 rtl:hover:pr-4"
      >
        <span
          class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-emerald-600/50 text-emerald-600 transition-all duration-300 group-hover:scale-110 group-hover:border-emerald-600 group-hover:bg-emerald-600 group-hover:text-white"
        >
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path
              d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 016.988 2.898 9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
            />
          </svg>
        </span>
        <span>
          <span class="block text-sm font-medium text-ink-500">{{ t('contact.whatsapp') }}</span>
          <span class="block text-lg text-ink-900 group-hover:text-emerald-700">{{
            t('contact.chatOnWhatsapp')
          }}</span>
        </span>
      </a>
    </div>

    <div
      v-if="settings.facebook || settings.instagram"
      class="mt-10 flex flex-col items-center gap-3"
    >
      <span class="eyebrow">{{ t('contact.follow') }}</span>
      <div class="flex gap-8">
        <a
          v-if="settings.facebook"
          :href="settings.facebook"
          target="_blank"
          rel="noopener noreferrer"
          class="text-sm font-semibold text-gold-400 transition hover:text-gold-800"
        >
          Facebook
        </a>
        <a
          v-if="settings.instagram"
          :href="settings.instagram"
          target="_blank"
          rel="noopener noreferrer"
          class="text-sm font-semibold text-gold-400 transition hover:text-gold-800"
        >
          Instagram
        </a>
      </div>
    </div>
  </div>
</template>
