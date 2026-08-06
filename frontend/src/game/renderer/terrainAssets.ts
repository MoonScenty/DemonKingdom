const tileModules = import.meta.glob('../../assets/terrain/*/*.png', {
  eager: true,
  import: 'default',
}) as Record<string, string>

const TILE_FILENAME_PATTERN = /([a-z_]+)_(\d{2})\.png$/

const tileUrlByKey = new Map<string, string>()

for (const [path, url] of Object.entries(tileModules)) {
  const match = TILE_FILENAME_PATTERN.exec(path)
  if (!match) continue

  const [, terrainType, variant] = match
  tileUrlByKey.set(`${terrainType}:${Number(variant)}`, url)
}

export function getTerrainTileUrl(terrainType: string, variant: number): string | undefined {
  return tileUrlByKey.get(`${terrainType}:${variant}`)
}
