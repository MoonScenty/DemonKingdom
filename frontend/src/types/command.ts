export interface CommandRequest<TPayload = Record<string, unknown>> {
  commandId: string
  baseRevision: number
  type: string
  payload: TPayload
}

export interface CommandResponse<TChanges = Record<string, unknown>> {
  success: true
  revision: number
  serverTime: string
  changes: TChanges
}

export interface CommandConflict {
  success: false
  revision: number
}

export class CommandConflictError extends Error {
  readonly latestRevision: number

  constructor(latestRevision: number) {
    super(`Command rejected: world revision advanced to ${latestRevision}`)
    this.name = 'CommandConflictError'
    this.latestRevision = latestRevision
  }
}
