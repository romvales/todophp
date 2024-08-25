<template>
  <form class='todophp-new-account form' @submit='onNewAccountSubmit'>
    <h1 class='text-2xl font-bold mb-1'>Create an Account</h1>
    <p class='pb-10 text-zinc-400'>To continue in the app, please create an account.</p>

    <FormField v-slot='{ componentField }' name='name'>
      <FormItem>
        <FormLabel>What's your name?</FormLabel>
        <FormControl>
          <Input placeholder='(e.g. Rom Vales Villanueva)' v-bind='componentField' />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

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

    <div>
      <Button 
        type='button'
        class='px-0 text-zinc-400' 
        variant='link' 
        @click='onNavigateToLogin'>Already have an account?</Button>
    </div>

    <div class='grid mt-5'>
      <Button type='submit'>Sign Up</Button>
    </div>
  </form>
  <Alert variant='destructive' v-if='isDuplicateError'>
    <AlertTitle>Duplicate User Error</AlertTitle>
    <AlertDescription>
      Oh no! It seems {{emailAddress}} has already been taken by someone else.
    </AlertDescription>
  </Alert>
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
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert'
import { AuthService } from '@/lib/services/AuthService'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const authService = new AuthService()
const { onSubmit } = authService.setupNewAccountForm()

const emailAddress = ref('')
const isDuplicateError = ref(false)
const router = useRouter()

const onNewAccountSubmit = onSubmit((values) => {
  emailAddress.value = values.email

  authService.axios.post('/auth', values)
    .then(() => {
      router.replace({ path: '/auth/login' })
    })
    .catch(() => {
      isDuplicateError.value = true
    })

})

const onNavigateToLogin = () => router.push({ path: '/auth/login' })
</script>