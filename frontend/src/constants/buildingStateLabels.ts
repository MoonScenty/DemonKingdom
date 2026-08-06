import type { BuildingState } from '../types/game'

export const BUILDING_STATE_LABELS: Record<BuildingState, string> = {
  ruin: '폐허',
  constructing: '건설 중',
  active: '가동 중',
  upgrading: '업그레이드 중',
}
