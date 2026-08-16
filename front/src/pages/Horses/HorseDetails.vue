<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useHorsesStore } from '@/stores/horses'
import { useSettingsStore } from '@/stores/settings'
import { useLocalized } from '@/composables/useLocalized'
import { useWhatsapp } from '@/composables/useWhatsapp'
import HorsePrice from '@/components/horses/HorsePrice.vue'
import StatusBadge from '@/components/shared/StatusBadge.vue'
import AppSpinner from '@/components/shared/AppSpinner.vue'

const props = defineProps({
  id: { type: [String, Number], required: true },
})

const { t, locale } = useI18n()
const horsesStore = useHorsesStore()
const settingsStore = useSettingsStore()
const { pick } = useLocalized()
const { horseLink } = useWhatsapp()

const horse = ref(null)
const loading = ref(true)
const notFound = ref(false)
const activeImage = ref(null)

const name = computed(() => (horse.value ? pick(horse.value.name) : ''))
const breed = computed(() => (horse.value ? pick(horse.value.breed) : ''))
const description = computed(() => (horse.value ? pick(horse.value.description) : ''))
const whatsappUrl = computed(() => (name.value ? horseLink(name.value) : null))

const INTL_LOCALES = { ar: 'ar-EG', en: 'en-GB', zh: 'zh-CN' }

const formattedDob = computed(() => {
  if (!horse.value?.date_of_birth) return null

  return new Intl.DateTimeFormat(INTL_LOCALES[locale.value] || 'en-GB', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(horse.value.date_of_birth))
})

const age = computed(() => {
  if (!horse.value?.date_of_birth) return null

  const birth = new Date(horse.value.date_of_birth)
  const months = Math.max(
    0,
    (new Date().getFullYear() - birth.getFullYear()) * 12 +
      new Date().getMonth() -
      birth.getMonth(),
  )
  const years = Math.floor(months / 12)

  return years >= 1 ? `${years} ${t('horse.years')}` : `${months} ${t('horse.months')}`
})

const youtubeId = computed(() => {
  const video = horse.value?.video
  if (!video || video.type !== 'url') return null

  const match = video.url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/)
  return match ? match[1] : null
})

const vimeoId = computed(() => {
  const video = horse.value?.video
  if (!video || video.type !== 'url') return null

  const match = video.url.match(/vimeo\.com\/(\d+)/)
  return match ? match[1] : null
})

// YouTube/Vimeo links need an embed URL; anything else renders as a plain link.
// autoplay=1 is safe here — the iframe is only mounted once the user clicks play (see videoActive).
const embedUrl = computed(() => {
  if (youtubeId.value) return `https://www.youtube.com/embed/${youtubeId.value}?autoplay=1`
  if (vimeoId.value) return `https://player.vimeo.com/video/${vimeoId.value}?autoplay=1`

  return null
})

// The real YouTube thumbnail can be built without an API call; Vimeo has no such
// direct URL, so it falls back to a plain play button on the dark background.
const thumbnailUrl = computed(() =>
  youtubeId.value ? `https://img.youtube.com/vi/${youtubeId.value}/hqdefault.jpg` : null,
)

// The embed iframe paints a black frame for a moment before the player is ready;
// showing a thumbnail facade until the user clicks play avoids that flash entirely.
const videoActive = ref(false)
watch(embedUrl, () => {
  videoActive.value = false
})

onMounted(async () => {
  settingsStore.fetchSettings()

  try {
    horse.value = await horsesStore.fetchHorse(props.id)
    activeImage.value = horse.value.images?.[0]?.url || horse.value.cover_image || null
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <AppSpinner v-if="loading" />

    <div v-else-if="notFound" class="py-28 text-center">
      <p class="font-display text-2xl text-ink-500">{{ t('horse.notFound') }}</p>
      <RouterLink :to="{ name: 'horses' }" class="btn-outline mt-8">
        {{ t('horse.backToHorses') }}
      </RouterLink>
    </div>

    <article v-else-if="horse">
      <RouterLink
        :to="{ name: 'horses' }"
        class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-gold-400 transition hover:text-gold-800"
      >
        <span class="rtl:rotate-180" aria-hidden="true">←</span>
        {{ t('horse.backToHorses') }}
      </RouterLink>

      <div class="grid gap-12 lg:grid-cols-[1.15fr_1fr] lg:gap-16">
        <!-- Gallery -->
        <div>
          <div class="media-frame aspect-[4/3] overflow-hidden bg-ink-100">
            <img
              v-if="activeImage"
              :src="activeImage"
              :alt="name"
              class="h-full w-full object-contain"
            />
          </div>

          <div v-if="horse.images.length > 1" class="mt-3 grid grid-cols-4 gap-3">
            <button
              v-for="image in horse.images"
              :key="image.id"
              type="button"
              class="aspect-square overflow-hidden rounded-xl transition duration-300 hover:scale-105"
              :class="
                activeImage === image.url
                  ? 'opacity-100 ring-1 ring-gold-500'
                  : 'opacity-55 hover:opacity-90'
              "
              @click="activeImage = image.url"
            >
              <img
                :src="image.thumb"
                :alt="name"
                loading="lazy"
                class="h-full w-full object-contain"
              />
            </button>
          </div>
        </div>

        <!-- Info -->
        <div class="lg:pt-2">
          <StatusBadge :status="horse.status" />

          <h1 class="mt-5 text-5xl leading-tight text-ink-900 sm:text-6xl">{{ name }}</h1>
          <p v-if="breed" class="mt-2 text-lg text-ink-500">{{ breed }}</p>

          <p v-if="horse.categories.length" class="mt-4 text-sm font-medium text-ink-500">
            {{ horse.categories.map((c) => pick(c.name)).join('  ·  ') }}
          </p>

          <!-- Price on a soft sand panel, not a lined box -->
          <div class="mt-8 rounded-2xl bg-cream-deep px-6 py-6">
            <span class="eyebrow !text-ink-400">{{ t('horse.price') }}</span>
            <div class="mt-2 text-4xl">
              <HorsePrice :price="horse.price" :currency="horse.currency" />
            </div>
          </div>

          <dl class="mt-8 grid grid-cols-2 gap-x-8 gap-y-6">
            <div class="border-s-2 border-gold-400 ps-4">
              <dt class="text-sm font-medium text-ink-500">{{ t('horse.gender') }}</dt>
              <dd class="mt-1 font-display text-2xl text-ink-900">
                {{ t(`gender.${horse.gender}`) }}
              </dd>
            </div>
            <div v-if="age" class="border-s-2 border-gold-400 ps-4">
              <dt class="text-sm font-medium text-ink-500">{{ t('horse.age') }}</dt>
              <dd class="mt-1 font-display text-2xl text-ink-900">{{ age }}</dd>
            </div>
            <div v-if="formattedDob" class="border-s-2 border-gold-400 ps-4">
              <dt class="text-sm font-medium text-ink-500">{{ t('horse.dateOfBirth') }}</dt>
              <dd class="mt-1 font-display text-2xl text-ink-900">{{ formattedDob }}</dd>
            </div>
          </dl>

          <div v-if="description" class="mt-10">
            <h2 class="eyebrow">{{ t('horse.about') }}</h2>
            <p class="mt-3 text-xl leading-relaxed whitespace-pre-line text-ink-600">
              {{ description }}
            </p>
          </div>

          <a
            v-if="whatsappUrl"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-gold mt-10 w-full sm:w-auto"
          >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 016.988 2.898 9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
              />
            </svg>
            {{ t('horse.enquire') }}
          </a>
        </div>
      </div>

      <!-- Video -->
      <section v-if="horse.video" class="mt-20">
        <p class="eyebrow">{{ t('horse.video') }}</p>

        <div class="relative mt-6 overflow-hidden rounded-2xl bg-ink-950">
          <template v-if="embedUrl">
            <iframe
              v-if="videoActive"
              :src="embedUrl"
              class="aspect-video w-full"
              :title="`${name} — ${t('horse.video')}`"
              allow="
                accelerometer;
                autoplay;
                clipboard-write;
                encrypted-media;
                gyroscope;
                picture-in-picture;
              "
              allowfullscreen
            />
            <button
              v-else
              type="button"
              class="group relative flex aspect-video w-full items-center justify-center"
              :aria-label="`${name} — ${t('horse.video')}`"
              @click="videoActive = true"
            >
              <img
                v-if="thumbnailUrl"
                :src="thumbnailUrl"
                alt=""
                class="absolute inset-0 h-full w-full object-cover opacity-80 transition group-hover:opacity-60"
              />
              <span
                class="relative flex h-16 w-16 items-center justify-center rounded-full bg-white/90 text-gold-700 shadow-lg transition group-hover:scale-105"
              >
                <svg class="h-6 w-6 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M8 5v14l11-7z" />
                </svg>
              </span>
            </button>
          </template>
          <video
            v-else-if="horse.video.type === 'file'"
            :src="horse.video.url"
            controls
            class="aspect-video w-full"
          />
          <a
            v-else
            :href="horse.video.url"
            target="_blank"
            rel="noopener noreferrer"
            class="block p-8 text-center text-gold-400 underline"
          >
            {{ horse.video.url }}
          </a>
        </div>
      </section>
    </article>
  </div>
</template>
