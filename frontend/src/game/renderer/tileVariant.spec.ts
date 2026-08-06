import { describe, expect, it } from 'vitest'
import { TERRAIN_VARIANT_COUNT, pickTileVariant } from './tileVariant'

describe('pickTileVariant', () => {
  it('returns a stable variant for the same coordinate and terrain type', () => {
    const first = pickTileVariant(4, 7, 'grass')
    const second = pickTileVariant(4, 7, 'grass')

    expect(first).toBe(second)
  })

  it('returns a variant within the valid 1..N range', () => {
    for (let x = 0; x < 20; x++) {
      for (let y = 0; y < 20; y++) {
        const variant = pickTileVariant(x, y, 'grass')
        expect(variant).toBeGreaterThanOrEqual(1)
        expect(variant).toBeLessThanOrEqual(TERRAIN_VARIANT_COUNT)
      }
    }
  })

  it('varies by terrain type for the same coordinate', () => {
    const grass = pickTileVariant(2, 3, 'grass')
    const dirt = pickTileVariant(2, 3, 'dirt')

    expect(grass).not.toBe(dirt)
  })
})
