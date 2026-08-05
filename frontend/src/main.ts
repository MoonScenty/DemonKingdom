import { createApp } from 'vue'
import App from './App.vue'
import { bootstrapSession, installApp } from './app/bootstrap'

const app = createApp(App)
installApp(app)

bootstrapSession().finally(() => {
  app.mount('#app')
})
