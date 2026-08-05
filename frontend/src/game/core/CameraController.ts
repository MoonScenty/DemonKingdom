import type { Application, Container, FederatedPointerEvent } from 'pixi.js'

const MIN_SCALE = 0.5
const MAX_SCALE = 2
const WHEEL_ZOOM_FACTOR = 1.1

interface ScreenPoint {
  x: number
  y: number
}

export class CameraController {
  private readonly app: Application
  private readonly world: Container
  private readonly activePointers = new Map<number, ScreenPoint>()
  private isDragging = false
  private lastDragPoint: ScreenPoint = { x: 0, y: 0 }
  private pinchStartDistance = 0
  private pinchStartScale = 1

  constructor(app: Application, world: Container) {
    this.app = app
    this.world = world
    this.attachListeners()
  }

  centerView(): void {
    const bounds = this.world.getLocalBounds()
    const screen = this.app.screen

    this.world.scale.set(1)
    this.world.x = screen.width / 2 - (bounds.x + bounds.width / 2)
    this.world.y = screen.height / 2 - (bounds.y + bounds.height / 2)
  }

  destroy(): void {
    const stage = this.app.stage
    stage.off('pointerdown', this.onPointerDown)
    stage.off('pointermove', this.onPointerMove)
    stage.off('pointerup', this.onPointerUpOrCancel)
    stage.off('pointerupoutside', this.onPointerUpOrCancel)
    stage.off('pointercancel', this.onPointerUpOrCancel)
    this.app.canvas.removeEventListener('wheel', this.onWheel)
  }

  private attachListeners(): void {
    const stage = this.app.stage
    stage.eventMode = 'static'
    stage.hitArea = this.app.screen

    stage.on('pointerdown', this.onPointerDown)
    stage.on('pointermove', this.onPointerMove)
    stage.on('pointerup', this.onPointerUpOrCancel)
    stage.on('pointerupoutside', this.onPointerUpOrCancel)
    stage.on('pointercancel', this.onPointerUpOrCancel)

    this.app.canvas.addEventListener('wheel', this.onWheel, { passive: false })
  }

  private onPointerDown = (event: FederatedPointerEvent): void => {
    this.activePointers.set(event.pointerId, { x: event.global.x, y: event.global.y })

    if (this.activePointers.size === 1) {
      this.isDragging = true
      this.lastDragPoint = { x: event.global.x, y: event.global.y }
    } else if (this.activePointers.size === 2) {
      this.isDragging = false
      this.pinchStartDistance = this.getPinchDistance()
      this.pinchStartScale = this.world.scale.x
    }
  }

  private onPointerMove = (event: FederatedPointerEvent): void => {
    if (!this.activePointers.has(event.pointerId)) return
    this.activePointers.set(event.pointerId, { x: event.global.x, y: event.global.y })

    if (this.activePointers.size === 2) {
      this.handlePinchZoom()
      return
    }

    if (this.isDragging && this.activePointers.size === 1) {
      const dx = event.global.x - this.lastDragPoint.x
      const dy = event.global.y - this.lastDragPoint.y
      this.world.x += dx
      this.world.y += dy
      this.lastDragPoint = { x: event.global.x, y: event.global.y }
    }
  }

  private onPointerUpOrCancel = (event: FederatedPointerEvent): void => {
    this.activePointers.delete(event.pointerId)

    const remaining = [...this.activePointers.values()]
    this.isDragging = remaining.length === 1

    if (this.isDragging) {
      this.lastDragPoint = remaining[0]
    }
  }

  private onWheel = (event: WheelEvent): void => {
    event.preventDefault()

    const rect = this.app.canvas.getBoundingClientRect()
    const focalPoint = { x: event.clientX - rect.left, y: event.clientY - rect.top }
    const direction = event.deltaY > 0 ? 1 / WHEEL_ZOOM_FACTOR : WHEEL_ZOOM_FACTOR

    this.zoomTo(this.world.scale.x * direction, focalPoint)
  }

  private getPinchDistance(): number {
    const [a, b] = [...this.activePointers.values()]
    return Math.hypot(b.x - a.x, b.y - a.y)
  }

  private getPinchMidpoint(): ScreenPoint {
    const [a, b] = [...this.activePointers.values()]
    return { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 }
  }

  private handlePinchZoom(): void {
    if (this.pinchStartDistance === 0) return

    const distance = this.getPinchDistance()
    const nextScale = this.pinchStartScale * (distance / this.pinchStartDistance)

    this.zoomTo(nextScale, this.getPinchMidpoint())
  }

  private zoomTo(nextScale: number, focalPoint: ScreenPoint): void {
    const clampedScale = Math.min(MAX_SCALE, Math.max(MIN_SCALE, nextScale))

    const worldPointBeforeZoom = {
      x: (focalPoint.x - this.world.x) / this.world.scale.x,
      y: (focalPoint.y - this.world.y) / this.world.scale.y,
    }

    this.world.scale.set(clampedScale)
    this.world.x = focalPoint.x - worldPointBeforeZoom.x * clampedScale
    this.world.y = focalPoint.y - worldPointBeforeZoom.y * clampedScale
  }
}
