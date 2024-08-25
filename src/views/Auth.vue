<template>
  <div class='todophp-login' v-if='isLoginPage'>
    <section class='todophp-login-account'>
      <LoginForm></LoginForm>
    </section>  
  </div>

  <div class='todophp-new' v-if='!isLoginPage'>
    <section class='todophp-new-account'>
      <NewAccountForm />
    </section>
  </div>
</template>

<script setup lang='ts'>
import LoginForm from '@/components/LoginForm.vue';
import NewAccountForm from '@/components/NewAccountForm.vue'

import { AuthService } from '@/lib/services/AuthService'
import { useRouter } from 'vue-router'
import { onBeforeMount } from 'vue'

const router = useRouter()
const authService = new AuthService()
const isLoginPage = authService.isLoginPage()
const isLoggedIn = authService.isLoggedIn()

onBeforeMount(() => {
  if (isLoggedIn.value) {
    router.replace({ path: '/' })
  }
})
</script>