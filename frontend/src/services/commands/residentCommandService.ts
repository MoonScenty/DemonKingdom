import { sendCommand } from './commandService'
import type { CommandResponse } from '../../types/command'
import type { Resident, ResidentRace, ResourceType } from '../../types/game'

export interface RecruitResidentChanges {
  resources: Partial<Record<ResourceType, number>>
  residents: Resident[]
}

export function recruitResident(
  worldId: number,
  race: ResidentRace,
  baseRevision: number,
): Promise<CommandResponse<RecruitResidentChanges>> {
  return sendCommand(`/worlds/${worldId}/residents`, 'resident.recruit', { race }, baseRevision)
}

export interface AssignResidentChanges {
  residents: Resident[]
}

export function assignResident(
  worldId: number,
  residentId: number,
  buildingId: number,
  baseRevision: number,
): Promise<CommandResponse<AssignResidentChanges>> {
  return sendCommand(
    `/worlds/${worldId}/residents/${residentId}/assign`,
    'resident.assign',
    { buildingId },
    baseRevision,
  )
}

export function unassignResident(
  worldId: number,
  residentId: number,
  baseRevision: number,
): Promise<CommandResponse<AssignResidentChanges>> {
  return sendCommand(
    `/worlds/${worldId}/residents/${residentId}/unassign`,
    'resident.unassign',
    {},
    baseRevision,
  )
}
