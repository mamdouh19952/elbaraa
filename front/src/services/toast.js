import { toast } from 'vue3-toastify'
import { useLocaleStore } from '@/stores/locale'

// Every toast reads direction fresh, since the user can switch language
// between calls — a global default set once at app.use() would go stale.
function withDirection(options) {
  return { rtl: useLocaleStore().isRtl, ...options }
}

export function toastSuccess(message, options) {
  toast.success(message, withDirection(options))
}

export function toastError(message, options) {
  toast.error(message, withDirection(options))
}
