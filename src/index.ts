import './assets/index.css'
import './assets/global.css'

import { createApp } from 'vue'
import { router } from './views'
import App from './App.vue'

createApp(App).use(router).mount('#app')
