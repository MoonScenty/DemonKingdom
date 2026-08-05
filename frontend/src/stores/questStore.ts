import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Quest } from '../types/game'

export const useQuestStore = defineStore('quest', () => {
  const quests = ref<Quest[]>([])

  function setQuests(next: Quest[]) {
    quests.value = next
  }

  return { quests, setQuests }
})
