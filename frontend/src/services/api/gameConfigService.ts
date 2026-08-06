import { httpClient } from './httpClient'
import type { GameConfig } from '../../types/gameConfig'

export async function fetchGameConfig(): Promise<GameConfig> {
  const { data } = await httpClient.get<GameConfig>('/game-config')
  return data
}
