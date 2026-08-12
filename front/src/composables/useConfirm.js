import { reactive } from 'vue'

// Module-level singleton so any component can call confirm() and the one
// <ConfirmDialog /> mounted in App.vue renders it — no prop/emit wiring needed.
const state = reactive({
  visible: false,
  message: '',
  resolve: null,
})

export function confirm(message) {
  state.message = message
  state.visible = true

  return new Promise((resolve) => {
    state.resolve = resolve
  })
}

export function useConfirmState() {
  return state
}

export function resolveConfirm(value) {
  state.visible = false
  state.resolve?.(value)
  state.resolve = null
}
