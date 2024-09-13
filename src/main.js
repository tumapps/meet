// import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import { createApp } from 'vue'
import App from './App.vue'
import './registerServiceWorker'
import router from './router'

// Library Components
import VueSweetalert2 from 'vue-sweetalert2'
import BootstrapVueNext from 'bootstrap-vue-next'
// fontawesome activation
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas, faFolder, faFolderOpen, faFile  } from '@fortawesome/free-solid-svg-icons'
import '@fortawesome/fontawesome-free/css/all.css'
library.add(fas, faFolder, faFolderOpen, faFile)
// Custom Components & Directives
import globalComponent from './plugins/global-components'
import globalDirective from './plugins/global-directive'
import globalMixin from './plugins/global-mixin'
import axiosPlugin from './plugins/axiosPlugin'
import globalUtils from '@/utilities/globalUtils'
import { showAlert, showToast } from '@/utilities/sweetAlert';
import sweetAlertPlugin from '@/plugins/sweetAlertPlugin'
import Vue3Autocounter from 'vue3-autocounter';
import 'flatpickr/dist/flatpickr.css';
import 'flatpickr/dist/themes/material_blue.css'; // Optional theme, pick one that fits
import { useMenuStore } from '@/store/menuStore'
import { createPinia } from 'pinia'
// import Vue3Autocounter from 'vue3-autocounter'
require('waypoints/lib/noframework.waypoints.min')

const app = createApp(App)
const pinia = createPinia()
//fontawesome
app.component('font-awesome-icon', FontAwesomeIcon)
app.component('vue3-autocounter', Vue3Autocounter)
//router
app.use(router).use(pinia)
// Library Components
app.use(VueSweetalert2)
app.use(BootstrapVueNext)
// Custom Components & Directives
app.use(globalComponent)
app.use(globalDirective)
app.mixin(globalMixin)
app.use(axiosPlugin)
app.use(sweetAlertPlugin)
// Make functions globally accessible
app.provide('showAlert', showAlert)
app.provide('showToast', showToast)
app.config.globalProperties.$utils = globalUtils;
app.mount('#app')

// Load menus from session storage when the app starts
const menuStore = useMenuStore();
menuStore.loadMenusFromStorage();
export default app
