import { computed, Ref, ref } from 'vue'
import { useRoute } from 'vue-router'
import cookie from 'js-cookie'

import { SubmissionHandler, useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'

import axios from 'axios'

export { AuthService }

class AuthService {

  #data?: Ref<Record<string, any> | undefined>
  #axios = axios.create({ 
    baseURL: import.meta.env.VITE_API_URL!,
    withCredentials: true,
  })

  constructor() {
    const data = cookie.get('authenticated')!
    if (data) this.#data = ref(JSON.parse(atob(data)))
  }

  get axios() { return this.#axios }

  isLoggedIn = () => computed(() => {
    if (this.user?.email) {
      return true
    } else {
      return false
    }
  })

  get user() { return this.#data?.value }

  isLoginPage = () => computed(() => {
    const route = useRoute()
    return route.path == '/auth/login'
  })

  isNewPage = () => computed(() => {
    const route = useRoute()
    return route.path = '/auth/new'
  })

  logout = async () => {
    const res = await this.#axios.post('/auth/logout')
    this.#data!.value = undefined
    return res
  }

  setupNewAccountForm() {
    const validationSchema = toTypedSchema(z.object({
      name: z.string().min(3).max(50),
      email: z.string().email(),
      password: z.string().min(8).max(25),
    }))


    const form = useForm({ validationSchema })

    const onSubmit = (callbackOnSubmit: SubmissionHandler) => form.handleSubmit(callbackOnSubmit)

    return { validationSchema, form, onSubmit }
  }

  setupLoginForm() {
    const validationSchema = toTypedSchema(z.object({
      email: z.string().email(),
      password: z.string().min(8).max(25),
    }))

    const form = useForm({ validationSchema })

    const onSubmit = (callbackOnSubmit: SubmissionHandler) => form.handleSubmit((values, ctx) => {
      callbackOnSubmit(values, ctx)
    })

    return { validationSchema, form, onSubmit }
  }

}