import './assets/main.css'

import Aura from '@primeuix/themes/aura'
import { createPinia } from 'pinia'
import piniaPluginPersistedState from 'pinia-plugin-persistedstate'
import PrimeVue from 'primevue/config'
import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App)

const pinia = createPinia()
pinia.use(piniaPluginPersistedState)
app.use(pinia)

app.use(PrimeVue, {
  theme: {
    preset: Aura,
  },
  license: import.meta.env.VITE_PRIMEUI_LICENSE_KEY,
})

app.mount('#app')
