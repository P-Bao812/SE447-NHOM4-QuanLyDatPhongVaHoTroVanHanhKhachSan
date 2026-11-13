import { createApp } from 'vue'
import { createPinia } from 'pinia'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import Default from './layout/wrapper/index_nhanvien.vue'
import Guest from './layout/wrapper/index_guest.vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)
app.component('nv-layout', Default)
app.component('guest-layout', Guest)
app.use(createPinia())
app.use(router)

app.mount('#app')
