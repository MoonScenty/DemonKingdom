<script setup lang="ts">
import type { Application } from 'pixi.js'
import { AxiosError } from 'axios'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import BuildingPalette from '../components/game/BuildingPalette.vue'
import ResourceBar from '../components/game/ResourceBar.vue'
import { CameraController } from '../game/core/CameraController'
import { createPixiApp, destroyPixiApp } from '../game/core/PixiApp'
import { PlacementController } from '../game/interaction/PlacementController'
import { CityRenderer, type BuildingFootprint } from '../game/renderer/CityRenderer'
import { footprintsOverlap, isWithinMap } from '../game/renderer/isometric'
import { placeBuilding } from '../services/commands/buildingCommandService'
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

function isPlacementValid(x: number, y: number, footprint: BuildingFootprint): boolean {
  if (!isWithinMap(x, y, footprint.width, footprint.height)) return false

  return !buildingStore.buildings.some((building) => {
    const existingFootprint = footprints.value.get(building.buildingType) ?? { width: 1, height: 1 }
    return footprintsOverlap(
      { x, y, width: footprint.width, height: footprint.height },
      { x: building.x, y: building.y, width: existingFootprint.width, height: existingFootprint.height },
    )
  })
}

function selectBuilding(code: string) {
  const footprint = footprints.value.get(code)
  if (!footprint || !placementController) return

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

async function confirmPlacement(code: string, x: number, y: number) {
  const worldId = worldStore.worldId
  if (!worldId) return

  try {
    const response = await placeBuilding(worldId, { buildingType: code, x, y, rotation: 0 }, worldStore.revision)

    worldStore.setRevision(response.revision)
    resourceStore.applyDelta(response.changes.resources)

    const placed = response.changes.buildings[0]
    if (placed) {
      buildingStore.upsertBuilding({
        id: placed.id,
        buildingType: placed.type,
        x: placed.x,
        y: placed.y,
        rotation: placed.rotation,
        level: placed.level,
        state: placed.state,
        startedAt: placed.startedAt,
        finishesAt: placed.finishesAt,
      })
    }

    syncStore.setStatus('synced')
    cancelPlacement()
  } catch (error) {
    if (error instanceof CommandConflictError) {
      syncStore.setStatus('conflict', '월드 상태가 변경되어 다시 불러옵니다.')
      await worldStore.loadWorld(worldId)
      syncStore.setStatus('synced')
      return
    }

    const message = error instanceof AxiosError ? (error.response?.data?.message ?? '건물 배치 실패') : '건물 배치 실패'
    syncStore.setStatus('error', message)
  }
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

  void buildingCatalogStore.load()

  try {
    await worldStore.loadWorld(1)
    await cityRenderer.setBuildings(buildingStore.buildings, footprints.value)
  } catch {
    // 서버 연동 전에는 도시 화면만 표시한다.
  }
})

onBeforeUnmount(() => {
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
      <div ref="cityCanvasContainer" class="city-canvas" />
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
  flex: 1;
  overflow: hidden;
}
</style>
