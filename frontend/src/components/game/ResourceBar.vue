<script setup lang="ts">
import { computed } from 'vue'
import { useResourceStore } from '../../stores/resourceStore'
import { useWorldStore } from '../../stores/worldStore'
import type { ResourceType } from '../../types/game'
import SyncStatusIndicator from './SyncStatusIndicator.vue'

const resourceStore = useResourceStore()
const worldStore = useWorldStore()

const resourceOrder: { type: ResourceType; label: string }[] = [
  { type: 'gold', label: '금화' },
  { type: 'food', label: '식량' },
  { type: 'wood', label: '목재' },
  { type: 'ore', label: '광석' },
  { type: 'mana', label: '마나' },
]

const amounts = computed(() =>
  resourceOrder.map(({ type, label }) => ({
    type,
    label,
    amount: resourceStore.resources[type]?.amount ?? 0,
  })),
)
</script>

<template>
  <div class="resource-bar">
    <div v-for="resource in amounts" :key="resource.type" class="resource">
      <span class="label">{{ resource.label }}</span>
      <span class="amount">{{ resource.amount }}</span>
    </div>
    <div class="resource">
      <span class="label">도시 레벨</span>
      <span class="amount">{{ worldStore.cityLevel }}</span>
    </div>
    <SyncStatusIndicator />
  </div>
</template>

<style scoped>
.resource-bar {
  display: flex;
  align-items: center;
  gap: 1.5em;
  padding: 0.6em 1em;
  background-color: #1c1c26;
  border-bottom: 1px solid #33333f;
}

.resource {
  display: flex;
  flex-direction: column;
  font-size: 0.85rem;
}

.label {
  color: #a0a0ac;
}

.amount {
  font-weight: 600;
}
</style>
