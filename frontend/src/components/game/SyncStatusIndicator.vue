<script setup lang="ts">
import { computed } from 'vue'
import { useSyncStore } from '../../stores/syncStore'
import type { SyncStatus } from '../../types/game'

const syncStore = useSyncStore()

const colorByStatus: Record<SyncStatus, string> = {
  idle: '#6b6b7a',
  synced: '#4caf50',
  syncing: '#ffc107',
  reconnecting: '#ff9800',
  conflict: '#ff9800',
  error: '#e53935',
}

const labelByStatus: Record<SyncStatus, string> = {
  idle: '대기 중',
  synced: '서버 동기화 완료',
  syncing: '명령 처리 중',
  reconnecting: '재연결 중',
  conflict: '데이터 충돌',
  error: '서버 오류',
}

const color = computed(() => colorByStatus[syncStore.status])
const label = computed(() => labelByStatus[syncStore.status])
</script>

<template>
  <div class="sync-status">
    <span class="dot" :style="{ backgroundColor: color }" />
    <span>{{ label }}</span>
  </div>
</template>

<style scoped>
.sync-status {
  display: inline-flex;
  align-items: center;
  gap: 0.4em;
  font-size: 0.85rem;
}

.dot {
  width: 0.6em;
  height: 0.6em;
  border-radius: 50%;
}
</style>
