import { describe, expect, it } from 'vitest'
import { getTerrainTileUrl } from './terrainAssets'

describe('getTerrainTileUrl', () => {
  it('resolves a url for every material and variant 1..16', () => {
    const materials = ['grass', 'dirt', 'gravel', 'shallow_water', 'deep_water']

    for (const material of materials) {
      for (let variant = 1; variant <= 16; variant++) {
        expect(getTerrainTileUrl(material, variant)).toBeTruthy()
      }
    }
  })

  it('returns undefined for an unknown material', () => {
    expect(getTerrainTileUrl('lava', 1)).toBeUndefined()
  })
})
