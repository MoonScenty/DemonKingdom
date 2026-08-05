import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import * as authService from '../services/auth/authService'
import type { AuthUser, LoginPayload, RegisterPayload } from '../types/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const isGuest = ref(false)

  const isAuthenticated = computed(() => user.value !== null)

  async function register(payload: RegisterPayload) {
    user.value = await authService.register(payload)
    isGuest.value = false
  }

  async function login(payload: LoginPayload) {
    user.value = await authService.login(payload)
    isGuest.value = false
  }

  async function startGuestSession() {
    user.value = await authService.startGuestSession()
    isGuest.value = true
  }

  async function logout() {
    await authService.logout()
    user.value = null
    isGuest.value = false
  }

  async function restoreSession() {
    try {
      user.value = await authService.fetchCurrentUser()
    } catch {
      user.value = null
    }
  }

  return { user, isGuest, isAuthenticated, register, login, startGuestSession, logout, restoreSession }
})
