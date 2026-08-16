<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useHorsesStore } from '@/stores/horses'
import { useLocalized } from '@/composables/useLocalized'
import { apiErrorMessage } from '@/services/api'
import { toastError } from '@/services/toast'
import AppSpinner from '@/components/shared/AppSpinner.vue'
import TranslationStatusBadge from '@/components/shared/TranslationStatusBadge.vue'

const props = defineProps({
  id: { type: [String, Number], default: null },
})

const { t } = useI18n()
const router = useRouter()
const horsesStore = useHorsesStore()
const { pick } = useLocalized()

const isEdit = computed(() => !!props.id)
const loading = ref(false)
const saving = ref(false)
const notFound = ref(false)
const errors = ref({})

const form = ref({
  name: { ar: '', en: '', zh: '' },
  breed: { ar: '', en: '', zh: '' },
  gender: 'female',
  date_of_birth: '',
  description: { ar: '', en: '', zh: '' },
  price: '',
  currency: 'EGP',
  status: 'available',
  is_featured: false,
  video_url: '',
  categories: [],
})

const nameStatus = computed(() => ({
  ar: !!form.value.name.ar,
  en: !!form.value.name.en,
  zh: !!form.value.name.zh,
}))
const breedStatus = computed(() => ({
  ar: !!form.value.breed.ar,
  en: !!form.value.breed.en,
  zh: !!form.value.breed.zh,
}))
const descriptionStatus = computed(() => ({
  ar: !!form.value.description.ar,
  en: !!form.value.description.en,
  zh: !!form.value.description.zh,
}))

const existingImages = ref([])
const existingVideo = ref(null)
const newImages = ref([])
const newVideo = ref(null)
const videoFileInput = ref(null)
const videoMode = ref('url')
const removedMediaIds = ref([])

const newImagePreviews = computed(() =>
  newImages.value.map((file) => ({ name: file.name, url: URL.createObjectURL(file) })),
)

function onImagesSelected(event) {
  newImages.value = [...newImages.value, ...Array.from(event.target.files)]
  event.target.value = ''
}

function removeNewImage(index) {
  newImages.value.splice(index, 1)
}

function markExistingRemoved(image) {
  removedMediaIds.value.push(image.id)
  existingImages.value = existingImages.value.filter((item) => item.id !== image.id)
}

function toggleCategory(id) {
  const index = form.value.categories.indexOf(id)

  if (index === -1) {
    form.value.categories.push(id)
  } else {
    form.value.categories.splice(index, 1)
  }
}

async function removeVideo() {
  if (isEdit.value) {
    await horsesStore.deleteVideo(props.id)
  }

  existingVideo.value = null
  form.value.video_url = ''
  newVideo.value = null
}

function onVideoSelected(event) {
  newVideo.value = event.target.files[0] || null
}

function clearNewVideo() {
  newVideo.value = null
  if (videoFileInput.value) videoFileInput.value.value = ''
}

// A new video is either a link or an uploaded file, never both — switching
// mode clears whichever field belongs to the other mode.
function setVideoMode(mode) {
  videoMode.value = mode

  if (mode === 'url') {
    clearNewVideo()
  } else {
    form.value.video_url = ''
  }
}

// name/breed/description are per-language groups — always send all three locale
// keys together (blank string if empty) so the group round-trips as a whole,
// rather than skipping empty locales like the other, non-translatable fields.
function appendTranslatable(data, key, group) {
  Object.entries(group).forEach(([locale, value]) => {
    data.append(`${key}[${locale}]`, value ?? '')
  })
}

function buildPayload() {
  const data = new FormData()

  appendTranslatable(data, 'name', form.value.name)
  appendTranslatable(data, 'breed', form.value.breed)
  appendTranslatable(data, 'description', form.value.description)

  Object.entries(form.value).forEach(([key, value]) => {
    if (['categories', 'name', 'breed', 'description'].includes(key)) return

    if (key === 'is_featured') {
      data.append(key, value ? '1' : '0')
      return
    }

    // Empty strings must not be sent for nullable fields — the API would reject
    // "" as an invalid url/number instead of treating it as "no value".
    if (value !== '' && value !== null && value !== undefined) {
      data.append(key, value)
    }
  })

  form.value.categories.forEach((id) => data.append('categories[]', id))
  newImages.value.forEach((file) => data.append('images[]', file))
  removedMediaIds.value.forEach((id) => data.append('removed_media_ids[]', id))

  if (newVideo.value) {
    data.append('video', newVideo.value)
  }

  return data
}

async function handleSubmit() {
  saving.value = true
  errors.value = {}

  try {
    await horsesStore.saveHorse(buildPayload(), props.id)
    router.push({ name: 'admin-horses' })
  } catch (err) {
    toastError(apiErrorMessage(err))
    errors.value = err.response?.data?.errors || {}
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  horsesStore.fetchCategories()

  if (!isEdit.value) return

  loading.value = true

  try {
    const horse = await horsesStore.fetchHorse(props.id)

    form.value = {
      name: { ar: horse.name.ar || '', en: horse.name.en || '', zh: horse.name.zh || '' },
      breed: { ar: horse.breed.ar || '', en: horse.breed.en || '', zh: horse.breed.zh || '' },
      gender: horse.gender,
      date_of_birth: horse.date_of_birth || '',
      description: {
        ar: horse.description.ar || '',
        en: horse.description.en || '',
        zh: horse.description.zh || '',
      },
      price: horse.price ?? '',
      currency: horse.currency || 'EGP',
      status: horse.status,
      is_featured: horse.is_featured,
      video_url: horse.video?.type === 'url' ? horse.video.url : '',
      categories: horse.categories.map((category) => category.id),
    }

    existingImages.value = horse.images
    existingVideo.value = horse.video
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="max-w-3xl">
    <h1 class="text-2xl text-ink-900">
      {{ isEdit ? t('admin.editHorse') : t('admin.newHorse') }}
    </h1>

    <AppSpinner v-if="loading" />

    <p v-else-if="notFound" class="mt-6 font-display text-xl text-ink-500">
      {{ t('horse.notFound') }}
    </p>

    <form v-else class="mt-6 space-y-6" @submit.prevent="handleSubmit">
      <!-- Names -->
      <div class="card space-y-4 p-5">
        <div class="flex items-center justify-between">
          <span class="field-label">{{ t('admin.nameGroup') }}</span>
          <TranslationStatusBadge :status="nameStatus" />
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
          <div>
            <label for="name_ar" class="field-label">{{ t('admin.nameAr') }} *</label>
            <input
              id="name_ar"
              v-model="form.name.ar"
              type="text"
              required
              dir="rtl"
              class="field-input"
            />
            <p v-if="errors['name.ar']" class="mt-1 text-xs text-red-600">{{ errors['name.ar'][0] }}</p>
          </div>
          <div>
            <label for="name_en" class="field-label">{{ t('admin.nameEn') }} *</label>
            <input
              id="name_en"
              v-model="form.name.en"
              type="text"
              required
              dir="ltr"
              class="field-input"
            />
            <p v-if="errors['name.en']" class="mt-1 text-xs text-red-600">{{ errors['name.en'][0] }}</p>
          </div>
          <div>
            <label for="name_zh" class="field-label">{{ t('admin.nameZh') }}</label>
            <input id="name_zh" v-model="form.name.zh" type="text" dir="ltr" class="field-input" />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <span class="field-label">{{ t('admin.breedGroup') }}</span>
          <TranslationStatusBadge :status="breedStatus" />
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
          <div>
            <label for="breed_ar" class="field-label">{{ t('admin.breedAr') }}</label>
            <input id="breed_ar" v-model="form.breed.ar" type="text" dir="rtl" class="field-input" />
          </div>
          <div>
            <label for="breed_en" class="field-label">{{ t('admin.breedEn') }}</label>
            <input id="breed_en" v-model="form.breed.en" type="text" dir="ltr" class="field-input" />
          </div>
          <div>
            <label for="breed_zh" class="field-label">{{ t('admin.breedZh') }}</label>
            <input id="breed_zh" v-model="form.breed.zh" type="text" dir="ltr" class="field-input" />
          </div>
        </div>
      </div>

      <!-- Core details -->
      <div class="card grid gap-4 p-5 sm:grid-cols-2">
        <div>
          <label for="gender" class="field-label">{{ t('horse.gender') }} *</label>
          <select id="gender" v-model="form.gender" class="field-input">
            <option value="female">{{ t('gender.female') }}</option>
            <option value="male">{{ t('gender.male') }}</option>
          </select>
        </div>

        <div>
          <label for="dob" class="field-label">{{ t('horse.dateOfBirth') }}</label>
          <input id="dob" v-model="form.date_of_birth" type="date" class="field-input" />
        </div>

        <div>
          <label for="status" class="field-label">{{ t('horse.status') }} *</label>
          <select id="status" v-model="form.status" class="field-input">
            <option value="available">{{ t('status.available') }}</option>
            <option value="reserved">{{ t('status.reserved') }}</option>
            <option value="sold">{{ t('status.sold') }}</option>
          </select>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <div class="col-span-2">
            <label for="price" class="field-label">{{ t('horse.price') }}</label>
            <input
              id="price"
              v-model="form.price"
              type="number"
              min="0"
              step="any"
              dir="ltr"
              class="field-input"
            />
          </div>
          <div>
            <label for="currency" class="field-label">{{ t('admin.currency') }}</label>
            <select id="currency" v-model="form.currency" class="field-input !px-2">
              <option value="EGP">EGP</option>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="SAR">SAR</option>
              <option value="AED">AED</option>
            </select>
          </div>
          <p class="col-span-3 text-xs text-ink-400">{{ t('admin.priceHelp') }}</p>
        </div>
      </div>

      <!-- Categories -->
      <div class="card p-5">
        <span class="field-label">{{ t('horse.categories') }}</span>
        <div class="mt-2 flex flex-wrap gap-2">
          <button
            v-for="category in horsesStore.categories"
            :key="category.id"
            type="button"
            class="rounded-full border px-4 py-1.5 text-sm transition"
            :class="
              form.categories.includes(category.id)
                ? 'border-gold-500 bg-gold-50 text-gold-800'
                : 'border-ink-200 text-ink-600 hover:border-gold-300'
            "
            @click="toggleCategory(category.id)"
          >
            {{ pick(category.name) }}
          </button>
        </div>
      </div>

      <!-- Descriptions -->
      <div class="card space-y-4 p-5">
        <div class="flex items-center justify-between">
          <span class="field-label">{{ t('admin.descriptionGroup') }}</span>
          <TranslationStatusBadge :status="descriptionStatus" />
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
          <div>
            <label for="desc_ar" class="field-label">{{ t('admin.descriptionAr') }}</label>
            <textarea
              id="desc_ar"
              v-model="form.description.ar"
              rows="5"
              dir="rtl"
              class="field-input"
            />
          </div>
          <div>
            <label for="desc_en" class="field-label">{{ t('admin.descriptionEn') }}</label>
            <textarea
              id="desc_en"
              v-model="form.description.en"
              rows="5"
              dir="ltr"
              class="field-input"
            />
          </div>
          <div>
            <label for="desc_zh" class="field-label">{{ t('admin.descriptionZh') }}</label>
            <textarea
              id="desc_zh"
              v-model="form.description.zh"
              rows="5"
              dir="ltr"
              class="field-input"
            />
          </div>
        </div>
      </div>

      <!-- Images -->
      <div class="card p-5">
        <span class="field-label">{{ t('admin.images') }}</span>

        <div v-if="existingImages.length" class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
          <div v-for="image in existingImages" :key="image.id" class="group relative">
            <img :src="image.thumb" alt="" class="aspect-square w-full rounded-lg object-cover" />
            <button
              type="button"
              :aria-label="t('admin.removeImage')"
              class="absolute top-1 end-1 rounded-full bg-red-600 px-2 py-0.5 text-xs text-white opacity-0 transition group-hover:opacity-100"
              @click="markExistingRemoved(image)"
            >
              ✕
            </button>
          </div>
        </div>

        <div v-if="newImagePreviews.length" class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
          <div
            v-for="(preview, index) in newImagePreviews"
            :key="preview.url"
            class="group relative"
          >
            <img
              :src="preview.url"
              alt=""
              class="aspect-square w-full rounded-lg object-cover ring-2 ring-gold-400"
            />
            <button
              type="button"
              class="absolute top-1 end-1 rounded-full bg-red-600 px-2 py-0.5 text-xs text-white opacity-0 transition group-hover:opacity-100"
              @click="removeNewImage(index)"
            >
              ✕
            </button>
          </div>
        </div>

        <label
          class="mt-3 flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-ink-200 px-4 py-6 text-sm text-ink-500 transition hover:border-gold-400 hover:text-gold-700"
        >
          <input type="file" accept="image/*" multiple class="sr-only" @change="onImagesSelected" />
          + {{ t('admin.addImages') }}
        </label>
      </div>

      <!-- Video -->
      <div class="card space-y-4 p-5">
        <span class="field-label">{{ t('admin.videoSection') }}</span>

        <div
          v-if="existingVideo"
          class="flex items-center justify-between rounded-lg bg-ink-50 px-4 py-2.5"
        >
          <span class="truncate text-sm text-ink-600" dir="ltr">{{ existingVideo.url }}</span>
          <button type="button" class="text-xs text-red-600 hover:underline" @click="removeVideo">
            {{ t('admin.removeVideo') }}
          </button>
        </div>

        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-full border px-4 py-1.5 text-sm transition"
            :class="
              videoMode === 'url'
                ? 'border-gold-500 bg-gold-50 text-gold-800'
                : 'border-ink-200 text-ink-600 hover:border-gold-300'
            "
            @click="setVideoMode('url')"
          >
            {{ t('admin.videoModeUrl') }}
          </button>
          <button
            type="button"
            class="rounded-full border px-4 py-1.5 text-sm transition"
            :class="
              videoMode === 'file'
                ? 'border-gold-500 bg-gold-50 text-gold-800'
                : 'border-ink-200 text-ink-600 hover:border-gold-300'
            "
            @click="setVideoMode('file')"
          >
            {{ t('admin.videoModeFile') }}
          </button>
        </div>

        <div v-if="videoMode === 'url'">
          <label for="video_url" class="field-label">{{ t('admin.videoUrl') }}</label>
          <input
            id="video_url"
            v-model="form.video_url"
            type="url"
            dir="ltr"
            placeholder="https://youtube.com/watch?v=…"
            class="field-input"
          />
          <p v-if="errors.video_url" class="mt-1 text-xs text-red-600">{{ errors.video_url[0] }}</p>
        </div>

        <div v-else>
          <span class="field-label">{{ t('admin.videoUpload') }}</span>

          <div
            v-if="newVideo"
            class="mt-1 flex items-center justify-between rounded-lg bg-ink-50 px-4 py-2.5"
          >
            <span class="truncate text-sm text-ink-600" dir="ltr">{{ newVideo.name }}</span>
            <button
              type="button"
              class="text-xs text-red-600 hover:underline"
              @click="clearNewVideo"
            >
              {{ t('admin.removeVideo') }}
            </button>
          </div>

          <label
            v-else
            class="mt-1 flex cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-ink-200 px-4 py-6 text-sm text-ink-500 transition hover:border-gold-400 hover:text-gold-700"
          >
            <input
              id="video_file"
              ref="videoFileInput"
              type="file"
              accept="video/*"
              class="sr-only"
              @change="onVideoSelected"
            />
            + {{ t('admin.addVideo') }}
          </label>

          <p v-if="errors.video" class="mt-1 text-xs text-red-600">{{ errors.video[0] }}</p>
        </div>
      </div>

      <!-- Featured -->
      <div class="card p-5">
        <label class="flex cursor-pointer items-center gap-3">
          <input
            v-model="form.is_featured"
            type="checkbox"
            class="h-4 w-4 rounded border-ink-300 text-gold-600 focus:ring-gold-500"
          />
          <span class="text-sm text-ink-700">{{ t('admin.featured') }}</span>
        </label>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn-gold" :disabled="saving">
          {{ saving ? t('admin.saving') : t('admin.save') }}
        </button>
        <RouterLink :to="{ name: 'admin-horses' }" class="btn-outline">
          {{ t('admin.cancel') }}
        </RouterLink>
      </div>
    </form>
  </div>
</template>
