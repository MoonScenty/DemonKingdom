export type ResourceType = 'gold' | 'food' | 'wood' | 'ore' | 'mana'

export interface ResourceState {
  resourceType: ResourceType
  amount: number
  capacity: number
}

export type BuildingState = 'ruin' | 'constructing' | 'active' | 'upgrading'

export interface Building {
  id: number
  buildingType: string
  x: number
  y: number
  rotation: 0 | 90 | 180 | 270
  level: number
  state: BuildingState
  startedAt: string | null
  finishesAt: string | null
}

export type ResidentRace = 'slime' | 'goblin' | 'ogre' | 'imp' | 'vampire' | 'lich'

export type ResidentCurrentState =
  | 'working'
  | 'moving'
  | 'resting'
  | 'eating'
  | 'unhappy'
  | 'injured'
  | 'incident'
  | 'festival'
  | 'strike'

export interface Resident {
  id: number
  residentType: ResidentRace
  name: string
  level: number
  experience: number
  loyalty: number
  currentState: ResidentCurrentState
  assignedBuildingId: number | null
}

export type QuestStatus = 'available' | 'in_progress' | 'completed' | 'rewarded'

export interface Quest {
  id: number
  questId: number
  status: QuestStatus
  progress: number
  completedAt: string | null
  rewardedAt: string | null
}

export type WorldEventStatus = 'pending' | 'active' | 'resolved' | 'expired'

export interface WorldEvent {
  id: number
  eventType: string
  status: WorldEventStatus
  payload: Record<string, unknown>
  occurredAt: string
  expiresAt: string | null
  resolvedAt: string | null
}

export interface WorldSummary {
  id: number
  name: string
  cityLevel: number
  population: number
  currentEra: string
  revision: number
  lastProcessedAt: string
}

export interface WorldState extends WorldSummary {
  resources: ResourceState[]
  buildings: Building[]
  residents: Resident[]
  quests: Quest[]
  events: WorldEvent[]
}

export type SyncStatus = 'idle' | 'syncing' | 'synced' | 'reconnecting' | 'conflict' | 'error'
