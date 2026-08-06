export const TERRAIN_VARIANT_COUNT = 16

function hashTileCoordinate(x: number, y: number, terrainType: string): number {
  const input = `${terrainType}:${x}:${y}`
  let hash = 0

  for (let i = 0; i < input.length; i++) {
    hash = (hash * 31 + input.charCodeAt(i)) | 0
  }

  return hash >>> 0
}

export function pickTileVariant(x: number, y: number, terrainType: string): number {
  return (hashTileCoordinate(x, y, terrainType) % TERRAIN_VARIANT_COUNT) + 1
}
