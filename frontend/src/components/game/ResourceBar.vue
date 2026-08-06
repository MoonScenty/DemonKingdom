<script setup lang="ts">
import { computed } from 'vue'
import { RESOURCE_LABELS } from '../../constants/resourceLabels'
import { useResourceStore } from '../../stores/resourceStore'
import { useWorldStore } from '../../stores/worldStore'
import type { ResourceType } from '../../types/game'
import SyncStatusIndicator from './SyncStatusIndicator.vue'

const resourceStore = useResourceStore()
const worldStore = useWorldStore()

const resourceOrder: ResourceType[] = ['gold', 'food', 'wood', 'ore', 'mana']

const amounts = computed(() =>
  resourceOrder.map((type) => ({
    type,
    label: RESOURCE_LABELS[type],
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
