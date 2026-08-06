import { AxiosError } from 'axios'

export function extractErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as { message?: unknown } | undefined
    if (typeof data?.message === 'string') {
      return data.message
    }
  }

  return fallback
}
