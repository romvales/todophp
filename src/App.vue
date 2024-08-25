<template>
  <div class='app-container'>
    <div class='todophp-layout authView' v-if='isLoggedIn'>
      <header class='todophp-header flex justify-between p-4'>
        <h1 class='text-2xl font-bold'>todophp</h1>
        
        <ul>
          <li>
            <Button class='text-lg' variant='ghost' @click='onLogout'>Logout {{ authService.user?.email }}</Button>
          </li>
        </ul>
      </header>

      <main class='todophp-app'>
        <div>
          <RouterView />
        </div>
      </main>
    </div>

    <div class='todophp-layout noauthView' v-if='!isLoggedIn'>
      <header class='todophp-header flex justify-between p-4'>
        <h1 class='text-2xl font-bold'>todophp</h1>
      </header>

      <main class='todophp-app'>
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang='ts'>
import { Button } from '@/components/ui/button'
import { RouterView } from 'vue-router'
import { AuthService } from '@/lib/services/AuthService'
import { useRouter } from 'vue-router'

const authService = new AuthService()
const router = useRouter()

const isLoggedIn = authService.isLoggedIn()

const onLogout = () => authService.logout().then(() => router.replace('/auth/login'))
</script>