import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { SyncStatus } from '../types/game'

export const useSyncStore = defineStore('sync', () => {
  const status = ref<SyncStatus>('idle')
  const lastError = ref<string | null>(null)

  function setStatus(next: SyncStatus, error: string | null = null) {
    status.value = next
    lastError.value = error
  }

  return { status, lastError, setStatus }
})
