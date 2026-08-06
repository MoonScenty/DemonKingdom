import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'https://dk.moonscenty.me/api/v1'

export const httpClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
})

export async function ensureCsrfCookie(): Promise<void> {
  const appOrigin = new URL(API_BASE_URL).origin
  await httpClient.get(`${appOrigin}/sanctum/csrf-cookie`)
}
