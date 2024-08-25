<template>
  <div class='todophp-home' v-if='isLoggedIn'>
    <section class='todophp-home filter'>
      <form class='filter form' @submit='onSubmitSearchFilter'>
        <div class='search-wrapper'>
          <FormField v-slot='{ componentField }' name='name'>
            <FormItem>
              <FormControl>
                <Input class='shadow-none' placeholder='Search for a task...' v-bind='componentField' @input='onInputTaskName' />
              </FormControl>
            </FormItem>
          </FormField>
          <Button class='shadow-none' type='submit' variant='default'>Search</Button>
        </div>
      </form>
    </section>

    <section class='todophp-home content'>
      <div class='content sidebar'>
        <span class='font-bold'>TODOLIST</span>
      </div>
      <div class='content sections'>
        <section class='todophp-home welcome'>
          <h2 class='text-lg font-light'>Welcome, {{ authService.user!.name }} <{{ authService.user!.email }}>!</h2>
        </section>

        <section class='todophp-home dashboard'>
          <h1></h1>

          <div class='dashboard-desktop'>

            <nav class='desktop nav'>
              <h2 class='desktop title'>Tasks</h2>
              
              <ul class='desktop actions'>
                <li>
                  <NewTaskForm @ok='onNewTaskFormOkay' />
                </li>
              </ul>
            </nav>

            <div class='desktop content'>
              <section class='desktop ongoing'>
                <OngoingView :taskService='taskService' :searchQuery='searchQuery' />
              </section>

              <section class='desktop done'>
                <DoneView :taskService='taskService' :searchQuery='searchQuery' />
              </section>
            </div>
          </div>

          <div class='dashboard-mobile'>
            <section class='mobile ongoing'>
              <OngoingView :taskService='taskService' :searchQuery='searchQuery' />
            </section>

            <section class='mobile done'>
              <DoneView :taskService='taskService' :searchQuery='searchQuery' />
            </section>
          </div> 
        </section>
      </div>
    </section>
  </div>
</template>

<script setup lang='ts'>
import {
  FormField,
  FormItem,
  FormControl } from '@/components/ui/form'
import { Input } from '@/components/ui/input'

import { AuthService } from '@/lib/services/AuthService'
import { TaskService } from '@/lib/services/TaskService'
import { onBeforeMount, ref } from 'vue';
import { useRouter } from 'vue-router'
import NewTaskForm from '@/components/NewTaskForm.vue'
import OngoingView from '@/components/sections/OngoingView.vue'
import DoneView from '@/components/sections/DoneView.vue'
import Button from '@/components/ui/button/Button.vue'

const authService = new AuthService()
const taskService = new TaskService(authService)
const router = useRouter()
const isLoggedIn = authService.isLoggedIn()

const { onSubmit } = taskService.setupTaskFilterForm()
const onNewTaskFormOkay = () => taskService.fetchUpdatedTasks()
const searchQuery = ref<Record<string, any>>({ name: '' })

const onSubmitSearchFilter = onSubmit((values) => {
  searchQuery.value = values
})

const onInputTaskName = () => searchQuery.value = { name: '' }

onBeforeMount(() => {
  taskService.fetchUpdatedTasks()

  if (!isLoggedIn.value) router.replace({ path: '/auth/login' })
})
</script>