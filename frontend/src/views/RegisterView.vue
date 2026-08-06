<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '../components/common/BaseButton.vue'
import { useAuthStore } from '../stores/authStore'
import { extractErrorMessage } from '../utils/apiError'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const isSubmitting = ref(false)
const errorMessage = ref('')

async function submit() {
  errorMessage.value = ''

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = '비밀번호가 일치하지 않습니다.'
    return
  }

  isSubmitting.value = true
  try {
    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      passwordConfirmation: passwordConfirmation.value,
    })
    router.push({ name: 'game' })
  } catch (error) {
    errorMessage.value = extractErrorMessage(error, '회원 가입에 실패했습니다. 입력값을 확인해 주세요.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="auth-screen">
    <form class="auth-form" @submit.prevent="submit">
      <h1>회원 가입</h1>
      <input v-model="name" type="text" placeholder="닉네임" autocomplete="nickname" required />
      <input v-model="email" type="email" placeholder="이메일" autocomplete="email" required />
      <input
        v-model="password"
        type="password"
        placeholder="비밀번호"
        autocomplete="new-password"
        required
      />
      <input
        v-model="passwordConfirmation"
        type="password"
        placeholder="비밀번호 확인"
        autocomplete="new-password"
        required
      />
      <BaseButton :disabled="isSubmitting" type="submit">회원 가입</BaseButton>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <RouterLink to="/login">이미 계정이 있으신가요? 로그인</RouterLink>
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
