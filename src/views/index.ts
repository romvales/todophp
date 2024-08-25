import { createWebHistory, createRouter } from 'vue-router'

export { router }

import Home from './Home.vue'
import Auth from './Auth.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Home },
    { path: '/auth/login', component: Auth },
    { path: '/auth/new', component: Auth },
  ],
});