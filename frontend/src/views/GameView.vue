<script setup lang="ts">
import type { Application } from 'pixi.js'
import { AxiosError } from 'axios'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import BuildingPalette from '../components/game/BuildingPalette.vue'
import ResourceBar from '../components/game/ResourceBar.vue'
import SelectedBuildingPanel from '../components/game/SelectedBuildingPanel.vue'
import { CameraController } from '../game/core/CameraController'
import { createPixiApp, destroyPixiApp } from '../game/core/PixiApp'
import { PlacementController } from '../game/interaction/PlacementController'
import { CityRenderer, type BuildingFootprint } from '../game/renderer/CityRenderer'
import { footprintsOverlap, isWithinMap } from '../game/renderer/isometric'
import {
  collectBuilding,
  moveBuilding,
  placeBuilding,
  removeBuilding,
  type PlacedBuildingChange,
} from '../services/commands/buildingCommandService'
import { CommandConflictError } from '../types/command'
import { useBuildingCatalogStore } from '../stores/buildingCatalogStore'
import { useBuildingStore } from '../stores/buildingStore'
import { useResourceStore } from '../stores/resourceStore'
import { useSyncStore } from '../stores/syncStore'
import { useWorldStore } from '../stores/worldStore'

const cityCanvasContainer = ref<HTMLDivElement | null>(null)
const worldStore = useWorldStore()
const buildingStore = useBuildingStore()
const buildingCatalogStore = useBuildingCatalogStore()
const resourceStore = useResourceStore()
const syncStore = useSyncStore()

const activeCode = ref<string | null>(null)
const selectedBuildingId = ref<number | null>(null)
const isMoving = ref(false)
const isCollecting = ref(false)

let pixiApp: Application | null = null
let cityRenderer: CityRenderer | null = null
let cameraController: CameraController | null = null
let placementController: PlacementController | null = null

const footprints = computed(() => {
  const map = new Map<string, BuildingFootprint>()
  for (const entry of buildingCatalogStore.buildings) {
    map.set(entry.code, { width: entry.width, height: entry.height })
  }
  return map
})

const selectedBuilding = computed(
  () => buildingStore.buildings.find((building) => building.id === selectedBuildingId.value) ?? null,
)
const selectedCatalogEntry = computed(() =>
  selectedBuilding.value ? buildingCatalogStore.findByCode(selectedBuilding.value.buildingType) : undefined,
)

function isPlacementValid(
  x: number,
  y: number,
  footprint: BuildingFootprint,
  excludeBuildingId?: number,
): boolean {
  if (!isWithinMap(x, y, footprint.width, footprint.height)) return false

  return !buildingStore.buildings.some((building) => {
    if (building.id === excludeBuildingId) return false

    const existingFootprint = footprints.value.get(building.buildingType) ?? { width: 1, height: 1 }
    return footprintsOverlap(
      { x, y, width: footprint.width, height: footprint.height },
      { x: building.x, y: building.y, width: existingFootprint.width, height: existingFootprint.height },
    )
  })
}

function onStageTap() {
  if (placementController?.active) return

  selectedBuildingId.value = null
}

function onBuildingClick(buildingId: number) {
  if (placementController?.active) return

  selectedBuildingId.value = buildingId
}

function selectBuilding(code: string) {
  const footprint = footprints.value.get(code)
  if (!footprint || !placementController) return

  selectedBuildingId.value = null
  activeCode.value = code
  placementController.activate(
    code,
    footprint,
    (x, y) => isPlacementValid(x, y, footprint),
    (x, y) => void confirmPlacement(code, x, y),
    () => {
      activeCode.value = null
    },
  )
}

function cancelPlacement() {
  activeCode.value = null
  placementController?.deactivate()
}

function startMoving() {
  const building = selectedBuilding.value
  const footprint = building ? footprints.value.get(building.buildingType) : undefined
  if (!building || !footprint || !placementController) return

  isMoving.value = true
  placementController.activate(
    building.buildingType,
    footprint,
    (x, y) => isPlacementValid(x, y, footprint, building.id),
    (x, y) => void confirmMove(building.id, x, y),
    () => {
      isMoving.value = false
    },
  )
}

async function removeSelected() {
  const building = selectedBuilding.value
  const worldId = worldStore.worldId
  if (!building || !worldId) return

  try {
    const response = await removeBuilding(worldId, building.id, worldStore.revision)

    worldStore.setRevision(response.revision)
    buildingStore.removeBuilding(building.id)
    selectedBuildingId.value = null
    syncStore.setStatus('synced')
  } catch (error) {
    await handleCommandError(error, worldId)
  }
}

async function collectSelected() {
  const building = selectedBuilding.value
  const worldId = worldStore.worldId
  if (!building || !worldId || !building.production) return

  isCollecting.value = true
  try {
    const response = await collectBuilding(worldId, building.id, worldStore.revision)

    worldStore.setRevision(response.revision)
    resourceStore.applyDelta(response.changes.resources)
    buildingStore.upsertBuilding({
      ...building,
      production: {
        resourceType: response.changes.production.resourceType,
        storedAmount: response.changes.production.storedAmount,
      },
    })

    syncStore.setStatus('synced')
  } catch (error) {
    await handleCommandError(error, worldId)
  } finally {
    isCollecting.value = false
  }
}

async function confirmPlacement(code: string, x: number, y: number) {
  const worldId = worldStore.worldId
  if (!worldId) return

  try {
    const response = await placeBuilding(worldId, { buildingType: code, x, y, rotation: 0 }, worldStore.revision)

    worldStore.setRevision(response.revision)
    resourceStore.applyDelta(response.changes.resources)
    upsertBuildingFromChange(response.changes.buildings[0])

    syncStore.setStatus('synced')
    cancelPlacement()
  } catch (error) {
    await handleCommandError(error, worldId)
  }
}

async function confirmMove(buildingId: number, x: number, y: number) {
  const worldId = worldStore.worldId
  if (!worldId) return

  try {
    const response = await moveBuilding(worldId, buildingId, { x, y, rotation: 0 }, worldStore.revision)

    worldStore.setRevision(response.revision)
    upsertBuildingFromChange(response.changes.buildings[0])

    syncStore.setStatus('synced')
    isMoving.value = false
    placementController?.deactivate()
  } catch (error) {
    await handleCommandError(error, worldId)
  }
}

function upsertBuildingFromChange(change: PlacedBuildingChange | undefined) {
  if (!change) return

  // place/move 응답에는 production 정보가 없으므로 기존 값을 그대로 유지한다.
  const existing = buildingStore.buildings.find((building) => building.id === change.id)

  buildingStore.upsertBuilding({
    id: change.id,
    buildingType: change.type,
    x: change.x,
    y: change.y,
    rotation: change.rotation,
    level: change.level,
    state: change.state,
    startedAt: change.startedAt,
    finishesAt: change.finishesAt,
    production: existing?.production ?? null,
  })
}

async function handleCommandError(error: unknown, worldId: number) {
  if (error instanceof CommandConflictError) {
    syncStore.setStatus('conflict', '월드 상태가 변경되어 다시 불러옵니다.')
    await worldStore.loadWorld(worldId)
    syncStore.setStatus('synced')
    return
  }

  const message = error instanceof AxiosError ? (error.response?.data?.message ?? '명령 처리 실패') : '명령 처리 실패'
  syncStore.setStatus('error', message)
}

watch(
  () => buildingStore.buildings,
  (buildings) => {
    void cityRenderer?.setBuildings(buildings, footprints.value)
  },
  { deep: true },
)

onMounted(async () => {
  if (!cityCanvasContainer.value) return

  pixiApp = await createPixiApp(cityCanvasContainer.value)
  cityRenderer = new CityRenderer(pixiApp)
  await cityRenderer.ready
  cameraController = new CameraController(pixiApp, cityRenderer.view)
  cameraController.centerView()
  placementController = new PlacementController(pixiApp, cityRenderer)
  pixiApp.stage.on('pointertap', onStageTap)
  cityRenderer.onBuildingClick(onBuildingClick)

  void buildingCatalogStore.load()

  try {
    // buildingStore.buildings 변경은 위 watch가 감지해 setBuildings를 호출한다.
    await worldStore.loadWorld(1)
  } catch {
    // 서버 연동 전에는 도시 화면만 표시한다.
  }
})

onBeforeUnmount(() => {
  pixiApp?.stage.off('pointertap', onStageTap)
  placementController?.destroy()
  cameraController?.destroy()
  cityRenderer?.destroy()
  if (pixiApp) destroyPixiApp(pixiApp)
})
</script>

<template>
  <div class="game-view">
    <ResourceBar />
    <div class="game-body">
      <div ref="cityCanvasContainer" class="city-canvas">
        <SelectedBuildingPanel
          v-if="selectedBuilding"
          :building="selectedBuilding"
          :catalog-entry="selectedCatalogEntry"
          :is-moving="isMoving"
          :is-collecting="isCollecting"
          @move="startMoving"
          @remove="removeSelected"
          @collect="collectSelected"
          @close="selectedBuildingId = null"
        />
      </div>
      <BuildingPalette :active-code="activeCode" @select="selectBuilding" @cancel="cancelPlacement" />
    </div>
  </div>
</template>

<style scoped>
.game-view {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.game-body {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.city-canvas {
  position: relative;
  flex: 1;
  overflow: hidden;
}
</style>
