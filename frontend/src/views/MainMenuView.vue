<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '../components/common/BaseButton.vue'
import { useAuthStore } from '../stores/authStore'

const router = useRouter()
const authStore = useAuthStore()
const isStartingGuest = ref(false)
const errorMessage = ref('')

async function startGuest() {
  errorMessage.value = ''
  isStartingGuest.value = true
  try {
    await authStore.startGuestSession()
    router.push({ name: 'game' })
  } catch {
    errorMessage.value = '게스트 시작에 실패했습니다. 서버 연결을 확인해 주세요.'
  } finally {
    isStartingGuest.value = false
  }
}

function continueGame() {
  router.push({ name: 'game' })
}
</script>

<template>
  <main class="title-screen">
    <h1>문제투성이 마왕국</h1>
    <p class="subtitle">어느 날 눈떠보니 문제투성이 마왕국의 마왕이 되었다</p>

    <div class="menu">
      <BaseButton v-if="authStore.isAuthenticated" @click="continueGame">이어하기</BaseButton>
      <template v-else>
        <BaseButton @click="router.push({ name: 'login' })">로그인</BaseButton>
        <BaseButton variant="secondary" @click="router.push({ name: 'register' })">회원 가입</BaseButton>
        <BaseButton variant="secondary" :disabled="isStartingGuest" @click="startGuest">
          게스트 시작
        </BaseButton>
      </template>
    </div>

    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </main>
</template>

<style scoped>
.title-screen {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1em;
  text-align: center;
}

.subtitle {
  color: #a0a0ac;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 0.75em;
  min-width: 220px;
}

.error {
  color: #e53935;
}
</style>
