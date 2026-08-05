import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, describe, expect, it } from 'vitest'
import { useResourceStore } from './resourceStore'

describe('resourceStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('stores resources keyed by type', () => {
    const store = useResourceStore()

    store.setResources([
      { resourceType: 'gold', amount: 420, capacity: 1000 },
      { resourceType: 'wood', amount: 850, capacity: 2000 },
    ])

    expect(store.resources.gold.amount).toBe(420)
    expect(store.resources.wood.capacity).toBe(2000)
  })
})
