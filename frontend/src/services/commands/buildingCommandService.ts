import { sendCommand } from './commandService'
import type { CommandResponse } from '../../types/command'
import type { BuildingState, ResourceType } from '../../types/game'

export interface PlaceBuildingPayload {
  buildingType: string
  x: number
  y: number
  rotation: 0 | 90 | 180 | 270
}

export interface PlacedBuildingChange {
  id: number
  type: string
  x: number
  y: number
  rotation: 0 | 90 | 180 | 270
  level: number
  state: BuildingState
  startedAt: string | null
  finishesAt: string | null
}

export interface PlaceBuildingChanges {
  resources: Partial<Record<ResourceType, number>>
  buildings: PlacedBuildingChange[]
}

export function placeBuilding(
  worldId: number,
  payload: PlaceBuildingPayload,
  baseRevision: number,
): Promise<CommandResponse<PlaceBuildingChanges>> {
  return sendCommand(`/worlds/${worldId}/buildings`, 'building.place', payload, baseRevision)
}
