import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Resident } from '../types/game'

export const useResidentStore = defineStore('resident', () => {
  const residents = ref<Resident[]>([])

  function setResidents(next: Resident[]) {
    residents.value = next
  }

  function upsertResident(resident: Resident) {
    const index = residents.value.findIndex((existing) => existing.id === resident.id)
    if (index === -1) {
      residents.value.push(resident)
    } else {
      residents.value[index] = resident
    }
  }

  return { residents, setResidents, upsertResident }
})
