import type { ResourceType } from './game'

export interface BuildingCatalogEntry {
  code: string
  name: string
  width: number
  height: number
  baseBuildTime: number
  buildCost: Partial<Record<ResourceType, number>>
}

export interface GameConfig {
  buildings: BuildingCatalogEntry[]
}
