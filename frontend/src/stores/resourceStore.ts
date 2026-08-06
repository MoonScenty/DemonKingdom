import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { ResourceState, ResourceType } from '../types/game'

export const useResourceStore = defineStore('resource', () => {
  const resources = ref<Record<ResourceType, ResourceState>>({} as Record<ResourceType, ResourceState>)

  function setResources(next: ResourceState[]) {
    for (const resource of next) {
      resources.value[resource.resourceType] = resource
    }
  }

  function applyDelta(amounts: Partial<Record<ResourceType, number>>) {
    for (const [type, amount] of Object.entries(amounts) as [ResourceType, number][]) {
      const existing = resources.value[type]
      if (existing) existing.amount = amount
    }
  }

  function reset() {
    resources.value = {} as Record<ResourceType, ResourceState>
  }

  return { resources, setResources, applyDelta, reset }
})
