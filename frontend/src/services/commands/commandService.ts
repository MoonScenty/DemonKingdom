import { AxiosError } from 'axios'
import { httpClient } from '../api/httpClient'
import { CommandConflictError, type CommandRequest, type CommandResponse } from '../../types/command'

function createCommandId(): string {
  return crypto.randomUUID()
}

export async function sendCommand<TPayload, TChanges>(
  url: string,
  type: string,
  payload: TPayload,
  baseRevision: number,
): Promise<CommandResponse<TChanges>> {
  const command: CommandRequest<TPayload> = {
    commandId: createCommandId(),
    baseRevision,
    type,
    payload,
  }

  try {
    const { data } = await httpClient.post<CommandResponse<TChanges>>(url, command)
    return data
  } catch (error) {
    if (error instanceof AxiosError && error.response?.status === 409) {
      const latestRevision = error.response.data?.revision ?? baseRevision
      throw new CommandConflictError(latestRevision)
    }
    throw error
  }
}
