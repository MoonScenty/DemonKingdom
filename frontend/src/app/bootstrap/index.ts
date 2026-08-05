import type { App } from 'vue'
import { createPinia } from 'pinia'
import { router } from '../router'
import { useAuthStore } from '../../stores/authStore'

export function installApp(app: App): void {
  app.use(createPinia())
  app.use(router)
}

export async function bootstrapSession(): Promise<void> {
  await useAuthStore().restoreSession()
}
