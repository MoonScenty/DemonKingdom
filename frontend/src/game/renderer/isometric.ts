export const TILE_WIDTH = 64
export const TILE_HEIGHT = 46
export const MAP_WIDTH = 20
export const MAP_HEIGHT = 20

export interface ScreenPoint {
  screenX: number
  screenY: number
}

export interface TileCoordinate {
  x: number
  y: number
}

function originX(): number {
  return (MAP_HEIGHT * TILE_WIDTH) / 2
}

export function tileToScreen(tileX: number, tileY: number): ScreenPoint {
  return {
    screenX: originX() + (tileX - tileY) * (TILE_WIDTH / 2),
    screenY: (tileX + tileY) * (TILE_HEIGHT / 2),
  }
}

export function screenToTile(localX: number, localY: number): TileCoordinate {
  const a = (localX - originX()) / (TILE_WIDTH / 2)
  const b = localY / (TILE_HEIGHT / 2)

  return {
    x: Math.round((a + b) / 2),
    y: Math.round((b - a) / 2),
  }
}

export function diamondPoints(screenX: number, screenY: number): number[] {
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

export interface ScreenBounds {
  left: number
  top: number
  width: number
  height: number
}

export function footprintScreenBounds(x: number, y: number, width: number, height: number): ScreenBounds {
  const top = tileToScreen(x, y)
  const right = tileToScreen(x + width - 1, y)
  const left = tileToScreen(x, y + height - 1)
  const bottom = tileToScreen(x + width - 1, y + height - 1)

  const boundingLeft = left.screenX - TILE_WIDTH / 2
  const boundingRight = right.screenX + TILE_WIDTH / 2
  const boundingTop = top.screenY
  const boundingBottom = bottom.screenY + TILE_HEIGHT

  return {
    left: boundingLeft,
    top: boundingTop,
    width: boundingRight - boundingLeft,
    height: boundingBottom - boundingTop,
  }
}

export function isWithinMap(x: number, y: number, width: number, height: number): boolean {
  return x >= 0 && y >= 0 && x + width <= MAP_WIDTH && y + height <= MAP_HEIGHT
}

export function footprintsOverlap(
  a: { x: number; y: number; width: number; height: number },
  b: { x: number; y: number; width: number; height: number },
): boolean {
  return (
    a.x <= b.x + b.width - 1 &&
    a.x + a.width - 1 >= b.x &&
    a.y <= b.y + b.height - 1 &&
    a.y + a.height - 1 >= b.y
  )
}
