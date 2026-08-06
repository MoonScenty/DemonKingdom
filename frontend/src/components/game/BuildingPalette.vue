<script setup lang="ts">
import { onMounted } from 'vue'
import { RESOURCE_LABELS } from '../../constants/resourceLabels'
import { useBuildingCatalogStore } from '../../stores/buildingCatalogStore'
import type { ResourceType } from '../../types/game'

const props = defineProps<{
  activeCode: string | null
}>()

const emit = defineEmits<{
  select: [code: string]
  cancel: []
}>()

const catalogStore = useBuildingCatalogStore()

onMounted(() => {
  void catalogStore.load()
})

function formatCost(cost: Partial<Record<ResourceType, number>>): string {
  return Object.entries(cost)
    .map(([type, amount]) => `${RESOURCE_LABELS[type as ResourceType]} ${amount}`)
    .join(' · ')
}

function handleClick(code: string) {
  if (props.activeCode === code) {
    emit('cancel')
    return
  }

  emit('select', code)
}
</script>

<template>
  <aside class="building-palette">
    <h2>건설</h2>
    <ul>
      <li v-for="building in catalogStore.buildings" :key="building.code">
        <button
          type="button"
          class="building-entry"
          :class="{ active: activeCode === building.code }"
          @click="handleClick(building.code)"
        >
          <span class="name">{{ building.name }}</span>
          <span class="cost">{{ formatCost(building.buildCost) }}</span>
        </button>
      </li>
    </ul>
    <p v-if="activeCode" class="hint">타일을 클릭해 배치하세요 (Esc로 취소)</p>
  </aside>
</template>

<style scoped>
.building-palette {
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
  margin: 0 0 0.6em;
  color: #a0a0ac;
}

ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.4em;
}

.building-entry {
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

.building-entry:hover {
  border-color: #6b6b7a;
}

.building-entry.active {
  border-color: #a8483c;
  background-color: rgba(168, 72, 60, 0.2);
}

.name {
  font-weight: 600;
  font-size: 0.9rem;
}

.cost {
  font-size: 0.75rem;
  color: #a0a0ac;
}

.hint {
  margin-top: 0.8em;
  font-size: 0.75rem;
  color: #a0a0ac;
}
</style>
