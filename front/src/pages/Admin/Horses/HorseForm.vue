<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useHorsesStore } from '@/stores/horses'
import { useLocalized } from '@/composables/useLocalized'
import { apiErrorMessage } from '@/services/api'
import { toastError } from '@/services/toast'
import AppSpinner from '@/components/shared/AppSpinner.vue'

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
  name_en: '',
  name_ar: '',
  breed_en: '',
  breed_ar: '',
  gender: 'female',
  date_of_birth: '',
  description_en: '',
  description_ar: '',
  price: '',
  currency: 'EGP',
  status: 'available',
  is_featured: false,
  video_url: '',
  categories: [],
})

const existingImages = ref([])
const existingVideo = ref(null)
const newImages = ref([])
const newVideo = ref(null)
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

function buildPayload() {
  const data = new FormData()

  Object.entries(form.value).forEach(([key, value]) => {
    if (key === 'categories') return

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
      name_en: horse.name.en || '',
      name_ar: horse.name.ar || '',
      breed_en: horse.breed.en || '',
      breed_ar: horse.breed.ar || '',
      gender: horse.gender,
      date_of_birth: horse.date_of_birth || '',
      description_en: horse.description.en || '',
      description_ar: horse.description.ar || '',
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
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="name_en" class="field-label">{{ t('admin.nameEn') }} *</label>
            <input
              id="name_en"
              v-model="form.name_en"
              type="text"
              required
              dir="ltr"
              class="field-input"
            />
            <p v-if="errors.name_en" class="mt-1 text-xs text-red-600">{{ errors.name_en[0] }}</p>
          </div>
          <div>
            <label for="name_ar" class="field-label">{{ t('admin.nameAr') }} *</label>
            <input
              id="name_ar"
              v-model="form.name_ar"
              type="text"
              required
              dir="rtl"
              class="field-input"
            />
            <p v-if="errors.name_ar" class="mt-1 text-xs text-red-600">{{ errors.name_ar[0] }}</p>
          </div>
          <div>
            <label for="breed_en" class="field-label">{{ t('admin.breedEn') }}</label>
            <input
              id="breed_en"
              v-model="form.breed_en"
              type="text"
              dir="ltr"
              class="field-input"
            />
          </div>
          <div>
            <label for="breed_ar" class="field-label">{{ t('admin.breedAr') }}</label>
            <input
              id="breed_ar"
              v-model="form.breed_ar"
              type="text"
              dir="rtl"
              class="field-input"
            />
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
      <div class="card grid gap-4 p-5 sm:grid-cols-2">
        <div>
          <label for="desc_en" class="field-label">{{ t('admin.descriptionEn') }}</label>
          <textarea
            id="desc_en"
            v-model="form.description_en"
            rows="5"
            dir="ltr"
            class="field-input"
          />
        </div>
        <div>
          <label for="desc_ar" class="field-label">{{ t('admin.descriptionAr') }}</label>
          <textarea
            id="desc_ar"
            v-model="form.description_ar"
            rows="5"
            dir="rtl"
            class="field-input"
          />
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

        <div>
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

        <div>
          <label for="video_file" class="field-label">{{ t('admin.videoUpload') }}</label>
          <input
            id="video_file"
            type="file"
            accept="video/*"
            class="field-input file:me-3 file:rounded file:border-0 file:bg-ink-100 file:px-3 file:py-1 file:text-sm"
            @change="newVideo = $event.target.files[0] || null"
          />
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
