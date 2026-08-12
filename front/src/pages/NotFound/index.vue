<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-cream px-6 text-center">
    <span class="inline-flex rounded-2xl bg-cream p-3 ring-1 ring-gold-400/30">
      <img src="/images/logo.png" :alt="t('brand.name')" class="h-20 w-auto object-contain" />
    </span>
    <p class="figure-accent mt-10 text-7xl">404</p>
    <h1 class="mt-2 text-4xl text-ink-900">{{ t('notFound.title') }}</h1>
    <p class="mt-3 text-ink-500">{{ t('notFound.body') }}</p>
    <RouterLink :to="{ name: 'home' }" class="btn-gold mt-10">{{ t('notFound.home') }}</RouterLink>
  </div>
</template>
