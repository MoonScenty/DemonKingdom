import { Application, Container, Graphics } from 'pixi.js'

const TILE_WIDTH = 128
const TILE_HEIGHT = 64
const MAP_WIDTH = 20
const MAP_HEIGHT = 20

function tileToScreen(tileX: number, tileY: number): { screenX: number; screenY: number } {
  const originX = (MAP_HEIGHT * TILE_WIDTH) / 2

  return {
    screenX: originX + (tileX - tileY) * (TILE_WIDTH / 2),
    screenY: (tileX + tileY) * (TILE_HEIGHT / 2),
  }
}

export class CityRenderer {
  readonly view = new Container()

  constructor(app: Application) {
    app.stage.addChild(this.view)
    this.drawTileGrid()
  }

  private drawTileGrid(): void {
    const grid = new Graphics()
    const halfWidth = TILE_WIDTH / 2
    const halfHeight = TILE_HEIGHT / 2

    for (let x = 0; x < MAP_WIDTH; x++) {
      for (let y = 0; y < MAP_HEIGHT; y++) {
        const { screenX, screenY } = tileToScreen(x, y)

        grid
          .poly([
            screenX,
            screenY,
            screenX + halfWidth,
            screenY + halfHeight,
            screenX,
            screenY + TILE_HEIGHT,
            screenX - halfWidth,
            screenY + halfHeight,
          ])
          .fill({ color: (x + y) % 2 === 0 ? 0x2a2a38 : 0x24242f })
          .stroke({ color: 0x000000, alpha: 0.15, width: 1 })
      }
    }

    this.view.addChild(grid)
  }

  destroy(): void {
    this.view.destroy({ children: true })
  }
}
