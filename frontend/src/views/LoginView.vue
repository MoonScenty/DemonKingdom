<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '../components/common/BaseButton.vue'
import { useAuthStore } from '../stores/authStore'
import { extractErrorMessage } from '../utils/apiError'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const isSubmitting = ref(false)
const errorMessage = ref('')

async function submit() {
  errorMessage.value = ''
  isSubmitting.value = true
  try {
    await authStore.login({ email: email.value, password: password.value })
    router.push({ name: 'game' })
  } catch (error) {
    errorMessage.value = extractErrorMessage(error, '로그인에 실패했습니다. 이메일과 비밀번호를 확인해 주세요.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="auth-screen">
    <form class="auth-form" @submit.prevent="submit">
      <h1>로그인</h1>
      <input v-model="email" type="email" placeholder="이메일" autocomplete="email" required />
      <input
        v-model="password"
        type="password"
        placeholder="비밀번호"
        autocomplete="current-password"
        required
      />
      <BaseButton :disabled="isSubmitting" type="submit">로그인</BaseButton>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <RouterLink to="/register">계정이 없으신가요? 회원 가입</RouterLink>
      <RouterLink to="/">타이틀로 돌아가기</RouterLink>
    </form>
  </main>
</template>

<style scoped>
.auth-screen {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 0.75em;
  width: 280px;
}

input {
  padding: 0.6em;
  border-radius: 6px;
  border: 1px solid #33333f;
  background-color: #1c1c26;
  color: #f2f0e6;
}

.error {
  color: #e53935;
}

a {
  color: #a0a0ac;
  font-size: 0.85rem;
}
</style>
