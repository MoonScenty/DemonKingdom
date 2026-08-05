import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'main-menu', component: () => import('../../views/MainMenuView.vue') },
    { path: '/login', name: 'login', component: () => import('../../views/LoginView.vue') },
    { path: '/register', name: 'register', component: () => import('../../views/RegisterView.vue') },
    {
      path: '/game',
      name: 'game',
      component: () => import('../../views/GameView.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !useAuthStore().isAuthenticated) {
    return { name: 'main-menu' }
  }
})
