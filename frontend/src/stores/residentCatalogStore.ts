import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchGameConfig } from '../services/api/gameConfigService'
import type { ResidentCatalogEntry } from '../types/gameConfig'

export const useResidentCatalogStore = defineStore('residentCatalog', () => {
  const residents = ref<ResidentCatalogEntry[]>([])
  const isLoaded = ref(false)

  async function load() {
    if (isLoaded.value) return

    residents.value = (await fetchGameConfig()).residents
    isLoaded.value = true
  }

  return { residents, isLoaded, load }
})
