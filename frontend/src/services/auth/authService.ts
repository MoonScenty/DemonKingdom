import { ensureCsrfCookie, httpClient } from '../api/httpClient'
import type { AuthUser, LoginPayload, RegisterPayload } from '../../types/auth'

export async function register(payload: RegisterPayload): Promise<AuthUser> {
  await ensureCsrfCookie()
  const { data } = await httpClient.post<AuthUser>('/register', payload)
  return data
}

export async function login(payload: LoginPayload): Promise<AuthUser> {
  await ensureCsrfCookie()
  const { data } = await httpClient.post<AuthUser>('/login', payload)
  return data
}

export async function logout(): Promise<void> {
  await httpClient.post('/logout')
}

export async function fetchCurrentUser(): Promise<AuthUser> {
  const { data } = await httpClient.get<AuthUser>('/user')
  return data
}

export async function startGuestSession(): Promise<AuthUser> {
  const { data } = await httpClient.post<AuthUser>('/guest')
  return data
}

export async function convertGuestSession(payload: RegisterPayload): Promise<AuthUser> {
  const { data } = await httpClient.post<AuthUser>('/guest/convert', payload)
  return data
}
