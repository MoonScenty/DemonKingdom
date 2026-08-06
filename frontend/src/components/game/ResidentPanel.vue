<script setup lang="ts">
import { onMounted } from 'vue'
import { RESIDENT_RACE_LABELS, RESIDENT_STATE_LABELS } from '../../constants/residentLabels'
import { useResidentCatalogStore } from '../../stores/residentCatalogStore'
import { useResidentStore } from '../../stores/residentStore'
import type { ResidentRace } from '../../types/game'

defineProps<{
  recruitingRace: ResidentRace | null
}>()

const emit = defineEmits<{
  recruit: [race: ResidentRace]
  unassign: [residentId: number]
}>()

const catalogStore = useResidentCatalogStore()
const residentStore = useResidentStore()

onMounted(() => {
  void catalogStore.load()
})
</script>

<template>
  <div class="resident-panel">
    <h2>주민 모집</h2>
    <ul class="catalog">
      <li v-for="entry in catalogStore.residents" :key="entry.race">
        <button
          type="button"
          class="recruit-entry"
          :disabled="recruitingRace === entry.race"
          @click="emit('recruit', entry.race)"
        >
          <span class="name">{{ RESIDENT_RACE_LABELS[entry.race] }}</span>
          <span class="cost">금화 {{ entry.recruitCost }}</span>
        </button>
      </li>
    </ul>

    <h2>주민 목록 ({{ residentStore.residents.length }})</h2>
    <ul class="roster">
      <li v-for="resident in residentStore.residents" :key="resident.id" class="roster-entry">
        <div class="info">
          <span class="name">{{ RESIDENT_RACE_LABELS[resident.residentType] }} · {{ resident.name }}</span>
          <span class="state">{{ RESIDENT_STATE_LABELS[resident.currentState] }}</span>
        </div>
        <button
          v-if="resident.assignedBuildingId"
          type="button"
          class="ghost"
          @click="emit('unassign', resident.id)"
        >
          해제
        </button>
      </li>
      <li v-if="residentStore.residents.length === 0" class="empty">아직 주민이 없습니다.</li>
    </ul>
  </div>
</template>

<style scoped>
.resident-panel {
  width: 220px;
  flex: 1;
  min-height: 0;
  padding: 0.8em;
  background-color: #1c1c26;
  border-left: 1px solid #33333f;
  overflow-y: auto;
}

h2 {
  font-size: 0.95rem;
  margin: 0.8em 0 0.6em;
  color: #a0a0ac;
}

h2:first-child {
  margin-top: 0;
}

.catalog,
.roster {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.4em;
}

.recruit-entry {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.15em;
  padding: 0.5em 0.7em;
  border-radius: 6px;
  border: 1px solid #33333f;
  background-color: transparent;
  color: #f2f0e6;
  cursor: pointer;
  text-align: left;
}

.recruit-entry:hover:not(:disabled) {
  border-color: #6b6b7a;
}

.recruit-entry:disabled {
  opacity: 0.5;
  cursor: default;
}

.recruit-entry .cost {
  font-size: 0.75rem;
  color: #a0a0ac;
}

.roster-entry {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.4em;
  padding: 0.4em 0.6em;
  border-radius: 6px;
  border: 1px solid #33333f;
}

.roster-entry .info {
  display: flex;
  flex-direction: column;
  gap: 0.1em;
  min-width: 0;
}

.roster-entry .name {
  font-size: 0.85rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.roster-entry .state {
  font-size: 0.7rem;
  color: #a0a0ac;
}

.roster-entry button {
  flex-shrink: 0;
  padding: 0.3em 0.6em;
  border-radius: 6px;
  border: 1px solid #6b6b7a;
  background-color: transparent;
  color: #a0a0ac;
  cursor: pointer;
  font-size: 0.75rem;
}

.empty {
  font-size: 0.8rem;
  color: #6b6b7a;
}
</style>
