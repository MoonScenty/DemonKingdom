import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { WorldEvent } from '../types/game'

export const useEventStore = defineStore('event', () => {
  const events = ref<WorldEvent[]>([])

  function setEvents(next: WorldEvent[]) {
    events.value = next
  }

  return { events, setEvents }
})
