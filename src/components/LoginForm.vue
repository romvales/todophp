<template>
  <form class='todophp-login-account form' @submit='onLoginFormSubmit'>
    <h1 class='text-2xl font-bold mb-1'>Login your Account</h1>
    <p class='pb-10 text-zinc-400'>To continue, please login your account.</p>

    <FormField v-slot='{ componentField }' name='email'>
      <FormItem>
        <FormLabel>Email address</FormLabel>
        <FormControl>
          <Input placeholder='contact@romvales.com' v-bind='componentField' />
        </FormControl>
        <FormDescription>
          Enter a valid email address here.
        </FormDescription>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot='{ componentField }' name='password'>
      <FormItem>
        <FormLabel>
          Password
        </FormLabel>
        <FormControl>
          <Input type='password' placeholder='your-super-secure-password' v-bind='componentField' />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <nav class='form nav mt-10 grid gap-2'>
      <Button type='submit'>Sign In</Button>
      <Button type='button' variant='outline' @click='onNavigateToNew'>Create an account</Button>
    </nav>
  </form>
</template>

<script setup lang='ts'>
import { 
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { AuthService } from '@/lib/services/AuthService'
import { useRouter } from 'vue-router'

const authService = new AuthService()
const { onSubmit } = authService.setupLoginForm()
const router = useRouter()


const onLoginFormSubmit = onSubmit((values) => {
  
  authService.axios.post('/auth/login', values)
    .then(() => {
      location.pathname = '/'
    })
    .catch(() => {
      router.replace({ path: '/' })
    })

})

const onNavigateToNew = () => router.push({ path: '/auth/new' })
</script>