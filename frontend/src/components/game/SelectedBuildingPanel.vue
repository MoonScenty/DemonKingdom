<script setup lang="ts">
import { computed } from 'vue'
import { BUILDING_STATE_LABELS } from '../../constants/buildingStateLabels'
import { RESOURCE_LABELS } from '../../constants/resourceLabels'
import type { Building } from '../../types/game'
import type { BuildingCatalogEntry } from '../../types/gameConfig'

const props = defineProps<{
  building: Building
  catalogEntry: BuildingCatalogEntry | undefined
  isMoving: boolean
  isCollecting: boolean
}>()

const emit = defineEmits<{
  move: []
  remove: []
  collect: []
  close: []
}>()

const isCastle = computed(() => props.building.buildingType === 'castle')
const hasCollectible = computed(() => (props.building.production?.storedAmount ?? 0) > 0)
</script>

<template>
  <div class="selected-building-panel">
    <div class="info">
      <strong>{{ catalogEntry?.name ?? building.buildingType }}</strong>
      <span class="state">{{ BUILDING_STATE_LABELS[building.state] }} · Lv.{{ building.level }}</span>
      <span v-if="building.production" class="production">
        {{ RESOURCE_LABELS[building.production.resourceType] }} {{ building.production.storedAmount }}개 대기
      </span>
    </div>
    <div class="actions">
      <button
        v-if="building.production"
        type="button"
        :disabled="!hasCollectible || isCollecting"
        @click="emit('collect')"
      >
        수확
      </button>
      <button type="button" :disabled="isMoving" @click="emit('move')">
        {{ isMoving ? '이동할 타일을 클릭하세요' : '이동' }}
      </button>
      <button v-if="!isCastle" type="button" class="danger" @click="emit('remove')">철거</button>
      <button type="button" class="ghost" @click="emit('close')">닫기</button>
    </div>
  </div>
</template>

<style scoped>
.selected-building-panel {
  position: absolute;
  left: 1em;
  bottom: 1em;
  display: flex;
  align-items: center;
  gap: 1em;
  padding: 0.6em 1em;
  background-color: #1c1c26;
  border: 1px solid #33333f;
  border-radius: 8px;
}

.info {
  display: flex;
  flex-direction: column;
  gap: 0.2em;
}

.state {
  font-size: 0.75rem;
  color: #a0a0ac;
}

.production {
  font-size: 0.75rem;
  color: #7fd97f;
}

.actions {
  display: flex;
  gap: 0.4em;
}

button {
  padding: 0.4em 0.8em;
  border-radius: 6px;
  border: 1px solid #6b6b7a;
  background-color: transparent;
  color: #f2f0e6;
  cursor: pointer;
  font-size: 0.85rem;
}

button:disabled {
  opacity: 0.6;
  cursor: default;
}

button.danger {
  border-color: #e53935;
  color: #ff8a80;
}

button.ghost {
  border-color: transparent;
  color: #a0a0ac;
}
</style>
