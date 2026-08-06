import type { Application, FederatedPointerEvent } from 'pixi.js'
import type { BuildingFootprint, CityRenderer } from '../renderer/CityRenderer'
import { screenToTile } from '../renderer/isometric'

type ValidityCheck = (x: number, y: number) => boolean
type ConfirmHandler = (x: number, y: number) => void

export class PlacementController {
  private readonly app: Application
  private readonly cityRenderer: CityRenderer

  private code: string | null = null
  private footprint: BuildingFootprint = { width: 1, height: 1 }
  private isValid: ValidityCheck = () => false
  private onConfirm: ConfirmHandler = () => {}
  private onCancel: () => void = () => {}

  constructor(app: Application, cityRenderer: CityRenderer) {
    this.app = app
    this.cityRenderer = cityRenderer
    this.attachListeners()
  }

  get active(): boolean {
    return this.code !== null
  }

  activate(
    code: string,
    footprint: BuildingFootprint,
    isValid: ValidityCheck,
    onConfirm: ConfirmHandler,
    onCancel: () => void = () => {},
  ): void {
    this.code = code
    this.footprint = footprint
    this.isValid = isValid
    this.onConfirm = onConfirm
    this.onCancel = onCancel
  }

  deactivate(): void {
    const wasActive = this.code !== null

    this.code = null
    this.cityRenderer.hidePlacementPreview()

    if (wasActive) this.onCancel()
  }

  destroy(): void {
    this.app.stage.off('pointermove', this.onPointerMove)
    this.app.stage.off('pointertap', this.onPointerTap)
    window.removeEventListener('keydown', this.onKeyDown)
  }

  private attachListeners(): void {
    this.app.stage.on('pointermove', this.onPointerMove)
    this.app.stage.on('pointertap', this.onPointerTap)
    window.addEventListener('keydown', this.onKeyDown)
  }

  private onPointerMove = (event: FederatedPointerEvent): void => {
    if (!this.code) return

    const tile = this.tileAt(event)
    const valid = this.isValid(tile.x, tile.y)

    void this.cityRenderer.showPlacementPreview(this.code, tile.x, tile.y, this.footprint, valid)
  }

  private onPointerTap = (event: FederatedPointerEvent): void => {
    if (!this.code) return

    const tile = this.tileAt(event)
    if (!this.isValid(tile.x, tile.y)) return

    this.onConfirm(tile.x, tile.y)
  }

  private tileAt(event: FederatedPointerEvent) {
    const local = this.cityRenderer.view.toLocal(event.global)
    const raw = screenToTile(local.x, local.y)

    // footprint의 북쪽 꼭짓점이 아니라 커서 근처가 앵커가 되도록 중앙 쪽으로 당긴다.
    return {
      x: raw.x - Math.floor(this.footprint.width / 2),
      y: raw.y - Math.floor(this.footprint.height / 2),
    }
  }

  private onKeyDown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') {
      this.deactivate()
    }
  }
}
