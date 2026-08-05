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

  function reset() {
    resources.value = {} as Record<ResourceType, ResourceState>
  }

  return { resources, setResources, reset }
})
