import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchWorldState } from '../services/synchronization/worldSync'
import type { WorldState } from '../types/game'
import { useBuildingStore } from './buildingStore'
import { useEventStore } from './eventStore'
import { useQuestStore } from './questStore'
import { useResidentStore } from './residentStore'
import { useResourceStore } from './resourceStore'
import { useSyncStore } from './syncStore'

export const useWorldStore = defineStore('world', () => {
  const worldId = ref<number | null>(null)
  const name = ref('')
  const cityLevel = ref(1)
  const population = ref(0)
  const currentEra = ref('')
  const revision = ref(0)
  const lastProcessedAt = ref<string | null>(null)

  async function loadWorld(id: number) {
    const syncStore = useSyncStore()
    syncStore.setStatus('syncing')

    try {
      const world = await fetchWorldState(id)
      applyWorldState(world)
      syncStore.setStatus('synced')
    } catch (error) {
      syncStore.setStatus('error', error instanceof Error ? error.message : 'unknown error')
      throw error
    }
  }

  function applyWorldState(world: WorldState) {
    worldId.value = world.id
    name.value = world.name
    cityLevel.value = world.cityLevel
    population.value = world.population
    currentEra.value = world.currentEra
    revision.value = world.revision
    lastProcessedAt.value = world.lastProcessedAt

    useResourceStore().setResources(world.resources)
    useBuildingStore().setBuildings(world.buildings)
    useResidentStore().setResidents(world.residents)
    useQuestStore().setQuests(world.quests)
    useEventStore().setEvents(world.events)
  }

  function setRevision(next: number) {
    revision.value = next
  }

  return {
    worldId,
    name,
    cityLevel,
    population,
    currentEra,
    revision,
    lastProcessedAt,
    loadWorld,
    setRevision,
  }
})
