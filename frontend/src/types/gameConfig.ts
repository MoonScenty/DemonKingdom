import type { ResidentRace, ResourceType } from './game'

export interface BuildingCatalogEntry {
  code: string
  name: string
  width: number
  height: number
  baseBuildTime: number
  buildCost: Partial<Record<ResourceType, number>>
  workerCapacity: number
}

export interface ResidentCatalogEntry {
  race: ResidentRace
  recruitCost: number
  baseProduction: number
  baseConstruction: number
}

export interface GameConfig {
  buildings: BuildingCatalogEntry[]
  residents: ResidentCatalogEntry[]
}
