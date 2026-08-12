<script setup>
import { useI18n } from 'vue-i18n'
import { useConfirmState, resolveConfirm } from '@/composables/useConfirm'

const { t } = useI18n()
const state = useConfirmState()
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0"
    >
      <div
        v-if="state.visible"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-ink-950/60 p-4"
        @keydown.escape="resolveConfirm(false)"
      >
        <Transition
          appear
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          leave-active-class="transition duration-150 ease-in"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            class="w-full max-w-sm rounded-3xl border-t-2 border-gold-400 bg-cream p-6 shadow-2xl"
            role="alertdialog"
            aria-modal="true"
          >
            <p class="text-lg leading-relaxed text-ink-800">{{ state.message }}</p>

            <div class="mt-6 flex justify-end gap-3">
              <button
                type="button"
                class="btn-outline !px-5 !py-2.5"
                @click="resolveConfirm(false)"
              >
                {{ t('admin.cancel') }}
              </button>
              <button
                type="button"
                class="btn !border-red-700 !bg-red-700 !px-5 !py-2.5 !text-white hover:!bg-red-800"
                @click="resolveConfirm(true)"
              >
                {{ t('admin.delete') }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
