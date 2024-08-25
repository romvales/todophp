import { toTypedSchema } from '@vee-validate/zod'
import { GenericObject, SubmissionHandler, useForm } from 'vee-validate'
import { AuthService } from './AuthService'
import { Ref, ref } from 'vue'
import { z } from 'zod'
import { orderBy } from 'lodash-es'

export { TaskService }

class TaskService {

  #ongoingTasks: Ref<Record<string, any>[]> = ref([])
  #doneTasks: Ref<Record<string, any>[]> = ref([])

  constructor(private authService: AuthService = new AuthService()) {}
  
  saveTask(task: GenericObject) {
    return this.authService.axios.post('/api/tasks', task)
  }

  deleteTaskById(id: number) {
    return this.authService.axios.delete('/api/tasks', { 
      data: { 
        owner_id: this.authService.user!._id,
        task_id: id,
      } 
    })
  }

  fetchUpdatedTasks() {
    this.authService.axios.get('/api/tasks')
      .then(res => {
        const ongoingTasks = [] as Record<string, any>[]
        const doneTasks = [] as Record<string, any>[]

        orderBy(res.data.tasks, ['created'], 'desc').map(task => {
          let isOngoing = false

          for (const todo of task.todos) {
            if (todo.status == 1) isOngoing = true
          }

          if (!task.todos.length) return ongoingTasks.push(task)

          if (isOngoing) ongoingTasks.push(task)
          else doneTasks.push(task)
        })

        this.#ongoingTasks.value = ongoingTasks
        this.#doneTasks.value = doneTasks
      })
      .catch()
  }

  getOngoingTasks() {
    return this.#ongoingTasks
  }

  getDoneTasks() {
    return this.#doneTasks
  }

  setupTaskFilterForm() {
    const validationSchema = toTypedSchema(z.object({
      name: z.string().min(5).max(128),
    }))

    const form = useForm({ validationSchema })

    const onSubmit = (callbackOnSubmit: SubmissionHandler) => form.handleSubmit(callbackOnSubmit)

    return { validationSchema, form, onSubmit }
  }

  #taskSchema = toTypedSchema(z.object({
    _id: z.number().optional(),
    owner_id: z.number(),
    title: z.string().min(8).max(256),
    created: z.string(),
    expected: z.string(),
    todos: z.array(z.object({
      _id: z.string(),
      owner_id: z.number(),
      created: z.string(),
      name: z.string().min(8).max(256),
      expected: z.string(),
      status: z.number(),
    })),
  }))

  setupCreateTaskForm() {
    const validationSchema = this.#taskSchema

    const form = useForm({ validationSchema })

    const onSubmit = (callbackOnSubmit: SubmissionHandler) => form.handleSubmit(callbackOnSubmit)
    
    return { validationSchema, form, onSubmit }
  }

  setupEditTaskForm() {
    const validationSchema = this.#taskSchema

    const form = useForm({ validationSchema })

    const onSubmit = (callbackOnSubmit: SubmissionHandler) => form.handleSubmit(callbackOnSubmit)

    return { validationSchema, form, onSubmit }
  }

}