import { Application, Assets, Container, Graphics, Sprite, type Texture } from 'pixi.js'
import { getTerrainTileUrl } from './terrainAssets'
import { pickTileVariant, TERRAIN_VARIANT_COUNT } from './tileVariant'

const TILE_WIDTH = 128
const TILE_HEIGHT = 64
const MAP_WIDTH = 20
const MAP_HEIGHT = 20
const DEFAULT_TERRAIN = 'grass'

function tileToScreen(tileX: number, tileY: number): { screenX: number; screenY: number } {
  const originX = (MAP_HEIGHT * TILE_WIDTH) / 2

  return {
    screenX: originX + (tileX - tileY) * (TILE_WIDTH / 2),
    screenY: (tileX + tileY) * (TILE_HEIGHT / 2),
  }
}

function diamondPoints(screenX: number, screenY: number): number[] {
  const halfWidth = TILE_WIDTH / 2
  const halfHeight = TILE_HEIGHT / 2

  return [
    screenX,
    screenY,
    screenX + halfWidth,
    screenY + halfHeight,
    screenX,
    screenY + TILE_HEIGHT,
    screenX - halfWidth,
    screenY + halfHeight,
  ]
}

export class CityRenderer {
  readonly view = new Container()
  readonly ready: Promise<void>

  constructor(app: Application) {
    app.stage.addChild(this.view)
    this.ready = this.drawTileGrid()
  }

  private async drawTileGrid(): Promise<void> {
    const textures = await this.loadTerrainTextures()
    const halfWidth = TILE_WIDTH / 2

    for (let x = 0; x < MAP_WIDTH; x++) {
      for (let y = 0; y < MAP_HEIGHT; y++) {
        const { screenX, screenY } = tileToScreen(x, y)
        const points = diamondPoints(screenX, screenY)
        const variant = pickTileVariant(x, y, DEFAULT_TERRAIN)
        const texture = textures.get(variant)

        if (texture) {
          const mask = new Graphics().poly(points).fill(0xffffff)
          const tile = new Sprite(texture)
          tile.x = screenX - halfWidth
          tile.y = screenY
          tile.width = TILE_WIDTH
          tile.height = TILE_HEIGHT
          tile.mask = mask

          this.view.addChild(mask)
          this.view.addChild(tile)
        } else {
          this.view.addChild(
            new Graphics().poly(points).fill({ color: (x + y) % 2 === 0 ? 0x2a2a38 : 0x24242f }),
          )
        }

        this.view.addChild(new Graphics().poly(points).stroke({ color: 0x000000, alpha: 0.15, width: 1 }))
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
