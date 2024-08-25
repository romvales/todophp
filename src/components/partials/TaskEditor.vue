<template>
  <form class='item-content border rounded-md shadow' @submit='onClickSaveTask'>
    <nav class='item-content nav' :draggable='true'>
      <Menubar class='bg-transparent border-0 border-b rounded-none shadow-none'>
        <MenubarMenu>
          <MenubarTrigger>Task</MenubarTrigger>
          <MenubarContent>
            <MenubarItem :disabled='true'>Share (unavailable)</MenubarItem>
            <MenubarItem>View</MenubarItem>
            <MenubarItem>Collapse</MenubarItem>
            <MenubarItem @click="onClickDeleteTask(task._id)">Delete</MenubarItem>
            <MenubarSeparator></MenubarSeparator>
            <MenubarItem @click='onClickSaveTask'>Save</MenubarItem>
            <MenubarItem :disabled='true'>Print</MenubarItem>
          </MenubarContent>
        </MenubarMenu>
        <MenubarMenu>
          <MenubarTrigger>Edit</MenubarTrigger>
          <MenubarContent>
            <MenubarItem @click='onClickNewTodoItem()'>New Todo</MenubarItem>
            <MenubarItem :disabled='true'>Undo</MenubarItem>
            <MenubarItem>Clear</MenubarItem>
          </MenubarContent>
        </MenubarMenu>
      </Menubar>
    </nav>

    <div class='item-content content'>
      <section class='item-content header'>
        <FormField v-slot='{ componentField }' name='title'>
          <FormItem>
            <FormLabel>Title</FormLabel>
            <FormDescription>Expected to be completed {{ moment(task.expected).fromNow() }}.</FormDescription>
            <FormControl>
              <Input class='shadow-none' type='text' v-bind='componentField' />
            </FormControl>
          </FormItem>
        </FormField>
        <FormField v-slot='{ componentField }' name='expected'>
          <FormItem>
            <FormLabel>Target Date</FormLabel>
            <FormControl>
              <Input 
                class='hidden' 
                aria-hidden='true' 
                type='date'
                hidden 
                v-bind='componentField' />
              <Popover>
                <PopoverTrigger as-child>
                  <Button :class='cn("w-full flex justify-start gap-2 items-center text-left text-muted-foreground")' type='button' variant='outline'>
                    <CalendarIcon class='w-4 h-4 mt-[0.1rem]' />
                    <p>{{ targetDate }}</p>
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
      </section>

      <section class='item-content list'>
        <nav>
          <h2 class='list title'>Todos ({{ task.todos.length }})</h2>

          <ul class='list actions'>
            <li class='action item'>
              <Button type='button' @click='onClickNewTodoItem()' variant='link'>
                New todo
              </Button>
            </li>
            <li class='action item'>
              <Button type='submit' variant='link'>
                Save
              </Button>
            </li>
            <li class='action item'>
              <Button @click='onChangeMode' class='flex gap-2' type='button' variant='link' v-if='!deleteMode'>
                <Trash2Icon class='w-4 h-4' />
                rm_mode
              </Button>
              <Button @click='onChangeMode' class='flex gap-2' type='button' variant='link' v-if='deleteMode'>
                <PencilLineIcon class='w-4 h-4' />
                ed_mode
              </Button>
            </li>
          </ul>
        </nav>

        <Separator />
        
        <ul class='item-content list'>
          <li cass='list-item' v-for='(todo, i) of (form.values.todos as Record<string, any>[])' :key='todo._id'>
            <div class='list-item content'>
              <Checkbox class='mt-3' :checked='todo.status == 2' @update:checked='onTodoCheckUpdate(i)' v-if='!deleteMode' />
              <Button 
                :disabled='form.values.todos!.length == 1'
                @click='() => onTodoDelete(todo._id)' 
                class='p-3 text-red-500 hover:text-red-300 hover:bg-red-300/10'
                type='button' 
                v-if='deleteMode' variant='ghost'>
                <Trash2Icon class='w-4 h-4' />
              </Button>

              <FormField v-slot='{ componentField }' :name='`todoName-${i}`'>
                <FormItem>
                  <FormControl>
                    <Input 
                      class='shadow-none' 
                      type='text'
                      v-bind='componentField'
                      v-bind:default-value='todo.name'
                      @input='(ev: InputEvent) => onTaskUpdateName(ev, i)' />
                  </FormControl>
                  <FormDescription>Created {{  moment(todo.created).fromNow()  }}</FormDescription>
                </FormItem>
              </FormField>
              <FormField v-slot='{ componentField }' :name='`todoDeadline-${i}`'>
                <FormItem>
                  <FormControl>
                    <Input 
                      type='datetime-local' 
                      v-bind='componentField' 
                      v-bind:default-value='todo.expected'
                      @change='(ev: InputEvent) => onTaskUpdateDeadline(ev, i)' />
                  </FormControl>
                  <FormDescription>Deadline</FormDescription>
                </FormItem>
              </FormField>
            </div>
          </li>
        </ul>
      </section>

      <section class='item-content footer'>
        <p>
          Created: 
          <time :datetime='task.created'>{{ moment(task.created).calendar() }}</time>
        </p>
      </section>
    </div>
  </form>
</template>

<script setup lang='ts'>
import {
  Menubar,
  MenubarTrigger,
  MenubarContent,
  MenubarItem,
  MenubarMenu } from '@/components/ui/menubar'
import {
  FormField,
  FormItem,
  FormControl,
  FormDescription,
  FormLabel } from '@/components/ui/form'
import { Popover, PopoverTrigger, PopoverContent } from '@/components/ui/popover'
import { Calendar } from '@/components/ui/calendar'
import { Input } from '@/components/ui/input'
import Separator from '../ui/separator/Separator.vue'
import Checkbox from '../ui/checkbox/Checkbox.vue'
import MenubarSeparator from '../ui/menubar/MenubarSeparator.vue'
import { Button } from '@/components/ui/button'
import { 
  Calendar as CalendarIcon, 
  Trash2Icon,
  PencilLineIcon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import moment from 'moment'
import { AuthService } from '@/lib/services/AuthService'
import { computed, onMounted, Ref, ref, watch } from 'vue'
import { DateValue, parseDate } from '@internationalized/date'
import { TaskService } from '@/lib/services/TaskService'
import { cloneDeep } from 'lodash-es'

const emit = defineEmits(['updated'])
const { task } = defineProps<{ task: Record<string, any> }>()

const authService = new AuthService()
const taskService = new TaskService(authService)

const { onSubmit, form } = taskService.setupEditTaskForm()

const deleteMode = ref(false)
const targetDate = ref(parseDate(task.expected)) as Ref<DateValue>
watch(targetDate, () => form.setFieldValue('expected', targetDate.value.toString()))

const $userId = computed<number>(() => authService.user!._id)

const onClickSaveTask = onSubmit((values) => taskService.saveTask(values).then(() => emit('updated')))

const onClickDeleteTask = (id: number) => {
  taskService.deleteTaskById(id).then(() => emit('updated'))
}

const onClickNewTodoItem = () => {
  const taskClone = cloneDeep(task)
  const todosClone = cloneDeep(form.values.todos! as any)

  todosClone.push({
    _id: crypto.randomUUID(),
    owner_id: $userId.value,
    created: moment().format('YYYY-MM-DDTHH:mm').toString(),
    status: 1, // ongoing
  })

  taskClone.todos = todosClone

  form.setValues(taskClone)
}

const onTaskUpdateName = (ev: InputEvent, index: number) => {
  const taskClone = cloneDeep(task)
  const todosClone = cloneDeep(form.values.todos!)
  const input = ev.target as HTMLInputElement

  todosClone[index].name = input.value
  taskClone.todos = todosClone

  form.setValues(taskClone)
}

const onChangeMode = () => deleteMode.value = !deleteMode.value

const onTodoCheckUpdate = (index: number) => {
  const taskClone = cloneDeep(task)
  const todosClone = cloneDeep(form.values.todos!)

  todosClone[index].status = todosClone[index].status == 2 ? 1 : 2
  taskClone.todos = todosClone
  
  form.setValues(taskClone)
  form.validate(taskClone).then(() => taskService.saveTask(form.values).then(() => emit('updated')))
}

const onTodoDelete = (todoId: string) => {
  const taskClone = cloneDeep(task)
  const todosClone = cloneDeep<Record<string, any>[]>(form.values.todos!).filter(todo => todo._id != todoId)

  if (todosClone.length == 0) return

  taskClone.todos = todosClone
  form.setValues(taskClone)
}

const onTaskUpdateDeadline = (ev: InputEvent, index: number) => {
  const taskClone = cloneDeep(task)
  const todosClone = cloneDeep(form.values.todos!)
  const input = ev.target as HTMLInputElement

  todosClone[index].expected = input.value
  taskClone.todos = todosClone
 
  form.setValues(taskClone)
}

onMounted(() => form.setValues(task))
</script>