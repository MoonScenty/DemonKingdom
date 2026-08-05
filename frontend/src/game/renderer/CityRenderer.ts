import { Application, Container, Graphics } from 'pixi.js'

const TILE_SIZE = 32
const MAP_WIDTH = 20
const MAP_HEIGHT = 20

export class CityRenderer {
  readonly view = new Container()

  constructor(app: Application) {
    app.stage.addChild(this.view)
    this.drawTileGrid()
  }

  private drawTileGrid(): void {
    const grid = new Graphics()

    for (let x = 0; x < MAP_WIDTH; x++) {
      for (let y = 0; y < MAP_HEIGHT; y++) {
        grid
          .rect(x * TILE_SIZE, y * TILE_SIZE, TILE_SIZE, TILE_SIZE)
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
