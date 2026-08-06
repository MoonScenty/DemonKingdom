import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchGameConfig } from '../services/api/gameConfigService'
import type { BuildingCatalogEntry } from '../types/gameConfig'

export const useBuildingCatalogStore = defineStore('buildingCatalog', () => {
  const buildings = ref<BuildingCatalogEntry[]>([])
  const isLoaded = ref(false)

  async function load() {
    if (isLoaded.value) return

    buildings.value = (await fetchGameConfig()).buildings
    isLoaded.value = true
  }

  function findByCode(code: string): BuildingCatalogEntry | undefined {
    return buildings.value.find((building) => building.code === code)
  }

  return { buildings, isLoaded, load, findByCode }
})
