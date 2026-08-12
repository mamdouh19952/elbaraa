<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ConfirmDialog from '@/components/shared/ConfirmDialog.vue'

const auth = useAuthStore()

onMounted(() => {
  auth.fetchUser()
})
</script>

<template>
  <RouterView />
  <ConfirmDialog />
</template>
