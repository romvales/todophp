<template>
  <Sheet v-model:open='sheetOpenState'>
    <SheetTrigger>
      <Button variant='default'>Create new task</Button>
    </SheetTrigger>
    <SheetContent>
      <form class='todophp-home-create-task-form' @submit='onCreateNewTaskSubmit'>
        <SheetHeader>
          <SheetTitle>Create New Task</SheetTitle>
          <SheetDescription>You're about to create a new task to your {{ authService.user!.email }} account.</SheetDescription>
        </SheetHeader>

        <FormField v-slot='{ componentField }' name='owner_id'>
          <FormItem>
            <FormControl>
              <Input class='hidden' aria-hidden type='number' hidden v-bind='componentField' disabled />
            </FormControl>
          </FormItem>
        </FormField>

        <FormField v-slot='{ componentField }' name='title'>
          <FormItem>
            <FormLabel>Title</FormLabel>
            <FormControl>
              <Input placeholder='e.g. A list of activities todo this long weekend.' v-bind='componentField' />
            </FormControl>
          </FormItem>
        </FormField>

        <FormField v-slot='{ componentField }' name='created'>
          <FormItem>
            <FormLabel>Created</FormLabel>
            <FormDescription>Automatically set by the app to the current time.</FormDescription>
            <FormControl>
              <Input type='datetime-local' v-bind='componentField' disabled />
            </FormControl>
          </FormItem>
        </FormField>

        <FormField v-slot='{ componentField }' name='expected'>
          <FormItem>
            <FormLabel>Target Date</FormLabel>
            <FormControl>
              <Input class='hidden' aria-hidden='true' type='date' hidden v-bind='componentField' />
              <Popover>
                <PopoverTrigger as-child>
                  <Button :class='cn("w-full flex justify-start gap-2 items-center text-left text-muted-foreground")' type='button' variant='outline'>
                    <CalendarIcon class='w-4 h-4 mt-[0.1rem]' />
                    <p>{{ form.values.expected }}</p>
                  </Button>
                </PopoverTrigger>
                <PopoverContent>
                  <Calendar 
                    v-model='targetDate'
                    initial-focus />
                </PopoverContent>
              </Popover>
            </FormControl>
          </FormItem>
        </FormField>

        <section class='form todos'>
          <FormField name='serializedTodos'>
            <FormItem>
              <FormLabel>Initial todos ({{ toSerializeTodos.length }}/3)</FormLabel>
              <FormDescription>Having at least an initial three (3) todo for this task is recommended to get started.</FormDescription>
              <FormControl>
                <ScrollArea class='todos scrollArea'>
                  <Button 
                    type='button' 
                    class='w-full sticky top-0 uppercase text-xs flex gap-1 justify-start text-zinc-600' 
                    variant='secondary'
                    :disabled='!$todoLimit'
                    @click='onAddTodo'>
                    <PlusIcon class='w-4 h-4' />
                    Add new task
                  </Button>

                  <ul class='todos items'>
                    <li class='item' v-for='(todo, i) in toSerializeTodos'>
                      <nav class='item nav'>
                        <h4>Todo #{{ i+1 }}</h4>

                        <Button
                          type='button'
                          variant='ghost'
                          class='rounded-full'
                          @click='onDeleteTodo(todo._id)'>
                          <X class='w-4 h-4' />
                        </Button>
                      </nav>

                      <FormDescription>Make sure that you give each todo with a meaningful name or description.</FormDescription>

                      <fieldset>
                        <FormLabel>Name</FormLabel>
                        <Input type='text' name='name' v-bind:default-value='toSerializeTodos[i].name' @input="(ev: InputEvent) => onTodoInput(ev, i)" />
                      </fieldset>
                      <fieldset>
                        <FormLabel>Created</FormLabel>
                        <Input type='datetime-local' name='created' v-bind:default-value='todo.created' disabled />
                      </fieldset>
                      <fieldset>
                        <FormLabel>Deadline</FormLabel>
                        <Input class='block' type='datetime-local' name='expected' v-bind:default-value='todo.expected' @change="(ev: InputEvent) => onTodoDateChange(ev, i)" />
                      </fieldset>
                    </li>
                  </ul>
                </ScrollArea>
              </FormControl>
            </FormItem>
          </FormField>
        </section>

        <SheetFooter>
          <Button type='submit'>
            Create Task
          </Button>
        </SheetFooter>
      </form>
    </SheetContent>
  </Sheet>
</template>

<script setup lang='ts'>
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
  SheetFooter } from '@/components/ui/sheet'
import {
  FormField,
  FormItem,
  FormLabel,
  FormControl,
  FormDescription } from '@/components/ui/form'
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover'
import { Calendar } from '@/components/ui/calendar'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { AuthService } from '@/lib/services/AuthService'
import { cn } from '@/lib/utils'

import ScrollArea from './ui/scroll-area/ScrollArea.vue'
import { 
  PlusIcon,
  Calendar as CalendarIcon,
  X,
} from 'lucide-vue-next'
import { TaskService } from '@/lib/services/TaskService'
import { computed, ref, watch } from 'vue'
import { cloneDeep } from 'lodash-es'
import moment from 'moment/moment'

const emit = defineEmits(['ok'])

const authService = new AuthService()
const taskService = new TaskService()
const { onSubmit, form } = taskService.setupCreateTaskForm()

const sheetOpenState = ref()

const cloneSerializedTodos = () => cloneDeep<any>(toSerializeTodos.value)

const toSerializeTodos = ref<Record<string, any>[]>([])
watch(toSerializeTodos, () => form.setFieldValue('todos', cloneSerializedTodos()))

const targetDate = ref()
watch(targetDate, () => form.setFieldValue('expected', targetDate.value?.toString()))

watch(sheetOpenState, () => {
  if (!sheetOpenState.value) {
    toSerializeTodos.value = []
    targetDate.value = null
  }

  form.setFieldValue('created', moment().format('YYYY-MM-DDTHH:mm'))
  form.setFieldValue('owner_id', Number($userId.value))
})

const $userId = computed<number>(() => authService.user!._id)

const $todoLimit = computed(() => toSerializeTodos.value.length < 3)

const onCreateNewTaskSubmit = onSubmit((values) => {
  taskService.saveTask(values)
    .then(() => {
      sheetOpenState.value = false
      form.resetForm({})
      emit('ok')
    })
    .catch(err => {
      console.error(err)
    })
})

const onAddTodo = () => {
  if (toSerializeTodos.value.length < 3) {
    const todos = cloneSerializedTodos()

    todos.push({
      _id: crypto.randomUUID(),
      owner_id: $userId.value,
      created: moment().format('YYYY-MM-DDTHH:mm').toString(),
      status: 1, // ongoing
    })

    toSerializeTodos.value = todos
  }
}

const onDeleteTodo = (_id: number) => {
  toSerializeTodos.value = toSerializeTodos.value.filter(item => item._id != _id)
}

const onTodoInput = (ev: InputEvent, i: number) => {
  const input = ev.target as HTMLInputElement
  const todos = cloneSerializedTodos()
  
  todos[i].name = input.value
  toSerializeTodos.value = todos
}

const onTodoDateChange = (ev: InputEvent, i: number) => {
  const input = ev.target as HTMLInputElement
  const todos = cloneSerializedTodos()

  todos[i].expected = input.value
  toSerializeTodos.value = todos
}
</script>