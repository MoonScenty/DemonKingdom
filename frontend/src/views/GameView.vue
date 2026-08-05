<script setup lang="ts">
import type { Application } from 'pixi.js'
import { onBeforeUnmount, onMounted, ref } from 'vue'
import ResourceBar from '../components/game/ResourceBar.vue'
import { CityRenderer } from '../game/renderer/CityRenderer'
import { createPixiApp, destroyPixiApp } from '../game/core/PixiApp'
import { useWorldStore } from '../stores/worldStore'

const cityCanvasContainer = ref<HTMLDivElement | null>(null)
const worldStore = useWorldStore()

let pixiApp: Application | null = null
let cityRenderer: CityRenderer | null = null

onMounted(async () => {
  if (!cityCanvasContainer.value) return

  pixiApp = await createPixiApp(cityCanvasContainer.value)
  cityRenderer = new CityRenderer(pixiApp)

  try {
    await worldStore.loadWorld(1)
  } catch {
    // 서버 연동 전에는 도시 화면만 표시한다.
  }
})

onBeforeUnmount(() => {
  cityRenderer?.destroy()
  if (pixiApp) destroyPixiApp(pixiApp)
})
</script>

<template>
  <div class="game-view">
    <ResourceBar />
    <div ref="cityCanvasContainer" class="city-canvas" />
  </div>
</template>

<style scoped>
.game-view {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.city-canvas {
  flex: 1;
  overflow: hidden;
}
</style>
