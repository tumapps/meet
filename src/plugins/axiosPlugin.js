// src/plugins/axiosPlugin.js
import AxiosInstance from '@/api/axios'

export default {
    install: (app) => {
        const axiosInstance = AxiosInstance()
        app.config.globalProperties.$axios = axiosInstance
        app.config.globalProperties.$logout = axiosInstance.logout
    }
}
