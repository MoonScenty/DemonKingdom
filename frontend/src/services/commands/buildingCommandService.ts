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

export interface MoveBuildingPayload {
  x: number
  y: number
  rotation: 0 | 90 | 180 | 270
}

export interface MoveBuildingChanges {
  buildings: PlacedBuildingChange[]
}

export function moveBuilding(
  worldId: number,
  buildingId: number,
  payload: MoveBuildingPayload,
  baseRevision: number,
): Promise<CommandResponse<MoveBuildingChanges>> {
  return sendCommand(
    `/worlds/${worldId}/buildings/${buildingId}/move`,
    'building.move',
    payload,
    baseRevision,
    'patch',
  )
}

export interface RemoveBuildingChanges {
  removedBuildingIds: number[]
}

export function removeBuilding(
  worldId: number,
  buildingId: number,
  baseRevision: number,
): Promise<CommandResponse<RemoveBuildingChanges>> {
  return sendCommand(
    `/worlds/${worldId}/buildings/${buildingId}`,
    'building.remove',
    {},
    baseRevision,
    'delete',
  )
}

export interface CollectBuildingChanges {
  resources: Partial<Record<ResourceType, number>>
  production: {
    buildingId: number
    resourceType: ResourceType
    collectedAmount: number
    storedAmount: number
  }
}

export function collectBuilding(
  worldId: number,
  buildingId: number,
  baseRevision: number,
): Promise<CommandResponse<CollectBuildingChanges>> {
  return sendCommand(
    `/worlds/${worldId}/buildings/${buildingId}/collect`,
    'building.collect',
    {},
    baseRevision,
  )
}
