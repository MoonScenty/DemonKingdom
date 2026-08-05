import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Building } from '../types/game'

export const useBuildingStore = defineStore('building', () => {
  const buildings = ref<Building[]>([])

  function setBuildings(next: Building[]) {
    buildings.value = next
  }

  function upsertBuilding(building: Building) {
    const index = buildings.value.findIndex((existing) => existing.id === building.id)
    if (index === -1) {
      buildings.value.push(building)
    } else {
      buildings.value[index] = building
    }
  }

  function removeBuilding(buildingId: number) {
    buildings.value = buildings.value.filter((building) => building.id !== buildingId)
  }

  return { buildings, setBuildings, upsertBuilding, removeBuilding }
})
