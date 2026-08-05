import { httpClient } from '../api/httpClient'
import type { WorldState, WorldSummary } from '../../types/game'

export async function fetchWorlds(): Promise<WorldSummary[]> {
  const { data } = await httpClient.get<WorldSummary[]>('/worlds')
  return data
}

export async function fetchWorldState(worldId: number): Promise<WorldState> {
  const { data } = await httpClient.get<WorldState>(`/worlds/${worldId}/state`)
  return data
}

export async function fetchWorldChanges(
  worldId: number,
  afterRevision: number,
): Promise<Partial<WorldState> & { revision: number }> {
  const { data } = await httpClient.get<Partial<WorldState> & { revision: number }>(
    `/worlds/${worldId}/changes`,
    { params: { afterRevision } },
  )
  return data
}
