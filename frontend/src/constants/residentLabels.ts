import type { ResidentCurrentState, ResidentRace } from '../types/game'

export const RESIDENT_RACE_LABELS: Record<ResidentRace, string> = {
  slime: '슬라임',
  goblin: '고블린',
  ogre: '오우거',
  imp: '임프',
  vampire: '뱀파이어',
  lich: '리치',
}

export const RESIDENT_STATE_LABELS: Record<ResidentCurrentState, string> = {
  idle: '대기 중',
  working: '근무 중',
  moving: '이동 중',
  resting: '휴식 중',
  eating: '식사 중',
  unhappy: '불만',
  injured: '부상',
  incident: '사고 발생',
  festival: '축제 참여',
  strike: '파업',
}
