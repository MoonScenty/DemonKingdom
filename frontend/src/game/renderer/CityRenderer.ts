import { Application, Assets, Container, Graphics, Sprite, type Texture } from 'pixi.js'
import { getBuildingTextureUrl } from './buildingAssets'
import { getTerrainTileUrl } from './terrainAssets'
import { diamondPoints, footprintScreenBounds, tileToScreen, MAP_HEIGHT, MAP_WIDTH } from './isometric'
import { pickTileVariant, TERRAIN_VARIANT_COUNT } from './tileVariant'
import type { Building } from '../../types/game'

const DEFAULT_TERRAIN = 'grass'
// 지형 아트는 64x64 캔버스 안에 위쪽 여백을 두고 그려져 있어, 다이아몬드 꼭짓점을 격자 좌표에 맞추려면 보정이 필요하다.
const TERRAIN_TOP_PADDING = 6
// 건물 원화가 타일 대비 커 보여서 가로세로 비율은 유지한 채 2/5 크기로 줄인다.
const BUILDING_SCALE = 2 / 5
const PLACEMENT_VALID_COLOR = 0x3b82f6
const PLACEMENT_INVALID_COLOR = 0xff5c5c

export interface BuildingFootprint {
  width: number
  height: number
}

export class CityRenderer {
  readonly view = new Container()
  readonly ready: Promise<void>

  private readonly terrainLayer = new Container()
  private readonly buildingLayer = new Container()
  private readonly previewLayer = new Container()
  private buildingClickHandler: ((buildingId: number) => void) | null = null

  constructor(app: Application) {
    app.stage.addChild(this.view)
    this.view.addChild(this.terrainLayer, this.buildingLayer, this.previewLayer)
    this.ready = this.drawTileGrid()
  }

  onBuildingClick(handler: (buildingId: number) => void): void {
    this.buildingClickHandler = handler
  }

  async setBuildings(buildings: Building[], footprints: Map<string, BuildingFootprint>): Promise<void> {
    this.buildingLayer.removeChildren()

    const sorted = [...buildings].sort((a, b) => a.x + a.y - (b.x + b.y))

    for (const building of sorted) {
      const footprint = footprints.get(building.buildingType) ?? { width: 1, height: 1 }
      const url = getBuildingTextureUrl(building.buildingType, building.state)
      if (!url) continue

      const texture = await Assets.load<Texture>(url)
      const sprite = new Sprite(texture)
      this.anchorBuildingSprite(sprite, building.x, building.y, footprint)

      sprite.eventMode = 'static'
      sprite.cursor = 'pointer'
      sprite.on('pointertap', (event) => {
        event.stopPropagation()
        this.buildingClickHandler?.(building.id)
      })

      this.buildingLayer.addChild(sprite)
    }
  }

  async showPlacementPreview(
    code: string,
    x: number,
    y: number,
    footprint: BuildingFootprint,
    isValid: boolean,
  ): Promise<void> {
    const url = getBuildingTextureUrl(code, 'active')
    if (!url) return

    const texture = await Assets.load<Texture>(url)
    this.previewLayer.removeChildren()

    const color = isValid ? PLACEMENT_VALID_COLOR : PLACEMENT_INVALID_COLOR

    for (let dx = 0; dx < footprint.width; dx++) {
      for (let dy = 0; dy < footprint.height; dy++) {
        const { screenX, screenY } = tileToScreen(x + dx, y + dy)
        this.previewLayer.addChild(
          new Graphics().poly(diamondPoints(screenX, screenY)).fill({ color, alpha: 0.45 }),
        )
      }
    }

    const sprite = new Sprite(texture)
    sprite.alpha = 0.8
    this.anchorBuildingSprite(sprite, x, y, footprint)
    this.previewLayer.addChild(sprite)
  }

  private anchorBuildingSprite(sprite: Sprite, x: number, y: number, footprint: BuildingFootprint): void {
    const bounds = footprintScreenBounds(x, y, footprint.width, footprint.height)
    const groundCenterX = bounds.left + bounds.width / 2
    const groundCenterY = bounds.top + bounds.height

    sprite.scale.set(BUILDING_SCALE)
    sprite.x = groundCenterX - (sprite.texture.width * BUILDING_SCALE) / 2
    sprite.y = groundCenterY - sprite.texture.height * BUILDING_SCALE
  }

  hidePlacementPreview(): void {
    this.previewLayer.removeChildren()
  }

  private async drawTileGrid(): Promise<void> {
    const textures = await this.loadTerrainTextures()
    const tiles: { x: number; y: number }[] = []

    for (let x = 0; x < MAP_WIDTH; x++) {
      for (let y = 0; y < MAP_HEIGHT; y++) {
        tiles.push({ x, y })
      }
    }

    // 타일 아트에 두께(입체감)가 있어 뒤에서 앞 순서로 그려야 자연스럽게 겹친다.
    tiles.sort((a, b) => a.x + a.y - (b.x + b.y))

    for (const { x, y } of tiles) {
      const { screenX, screenY } = tileToScreen(x, y)
      const variant = pickTileVariant(x, y, DEFAULT_TERRAIN)
      const texture = textures.get(variant)

      if (texture) {
        const tile = new Sprite(texture)
        tile.x = screenX - texture.width / 2
        tile.y = screenY - TERRAIN_TOP_PADDING

        this.terrainLayer.addChild(tile)
      } else {
        this.terrainLayer.addChild(
          new Graphics()
            .poly(diamondPoints(screenX, screenY))
            .fill({ color: (x + y) % 2 === 0 ? 0x2a2a38 : 0x24242f }),
        )
      }
    }
  }

  private async loadTerrainTextures(): Promise<Map<number, Texture>> {
    const entries = await Promise.all(
      Array.from({ length: TERRAIN_VARIANT_COUNT }, async (_, index) => {
        const variant = index + 1
        const url = getTerrainTileUrl(DEFAULT_TERRAIN, variant)
        if (!url) return null

        const texture = await Assets.load<Texture>(url)
        return [variant, texture] as const
      }),
    )

    return new Map(entries.filter((entry): entry is [number, Texture] => entry !== null))
  }

  destroy(): void {
    this.view.destroy({ children: true })
  }
}
