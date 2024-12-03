import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store.js'

let userIP // Initialize

const AxiosInstance = () => {
  const axiosInstance = axios.create({
    // Set your API base URL
    withCredentials: true
  })
  axios.defaults.timeout = 10000 // 10 seconds timeout globally

  const authStore = useAuthStore()
  const refreshAndRetryQueue = []
  let isRefreshing = false
  const router = useRouter()

  const fetchUserIp = async () => {
    try {
      const response = await axios.get('https://api.ipify.org?format=json')
      userIP = response.data.ip // Assign the fetched IP to userIP
    } catch (error) {
      // console.log('Error fetching IP address:', error)
    }
  }

  // Immediately fetch the IP address when the axios instance is created
  fetchUserIp()

  axiosInstance.interceptors.request.use(
    async (config) => {
      fetchUserIp() // Fetch the IP address for every request

      // Fetch the token from localStorage
      if (config.headers['X-Exclude-Interceptor']) {
        return config
      }
      const token = localStorage.getItem('user.token')
      if (token) {
        config.headers['Authorization'] = `Bearer ${token}`
      }
      // Add the stored IP address to the headers
      if (userIP) {
        config.headers['X-User-IP'] = userIP
      }
      return config
    },
    (error) => {
      return Promise.reject(error)
    }
  )
  const TOKEN_EXPIRED_CODE = 440
  const UNAUTHORIZED_CODE = 401

  axiosInstance.interceptors.response.use(
    (response) => response,
    async (error) => {
      const originalRequest = error.config

      const refreshAccessToken = async () => {
        localStorage.removeItem('user.token')
        try {
          const response = await axiosInstance.post('/v1/auth/refresh')
          const newToken = response.data?.dataPayload.data.token

          // Store the new access token
          authStore.setToken(newToken, response.data.dataPayload.data.username)
          return newToken
        } catch (refreshError) {
          if (refreshError.response?.status === TOKEN_EXPIRED_CODE) {
            router.push({ path: `/auth/login` })
          } else {
            console.error('Failed to refresh access token', refreshError)
          }
          throw refreshError
        }
      }

      if (error.response) {
        const statusCode = error.response.status

        if (statusCode === TOKEN_EXPIRED_CODE) {
          // Handle session expiration
          localStorage.removeItem('user.token')
          router.push({ path: `/auth/login` })
          return Promise.reject(error)
        }

        if (statusCode === UNAUTHORIZED_CODE) {
          console.log('Token expired, attempting to refresh...')

          if (!isRefreshing) {
            isRefreshing = true
            try {
              const newToken = await refreshAccessToken()
              originalRequest.headers['Authorization'] = `Bearer ${newToken}`

              // Retry requests in queue with new token
              refreshAndRetryQueue.forEach(({ config, resolve, reject }) => {
                axiosInstance.request(config).then(resolve).catch(reject)
              })
              refreshAndRetryQueue.length = 0

              return axiosInstance(originalRequest)
            } catch (refreshError) {
              router.push({ path: `/auth/login` })
              throw refreshError
            } finally {
              isRefreshing = false
            }
          }

          // Queue the request until token is refreshed
          return new Promise((resolve, reject) => {
            refreshAndRetryQueue.push({ config: originalRequest, resolve, reject })
          })
        }

        // Handle other errors by redirecting to custom error page
        if (![422, TOKEN_EXPIRED_CODE, UNAUTHORIZED_CODE].includes(statusCode)) {
          console.log(`Redirecting to error page due to status: ${statusCode}`)
          router.push({ path: `/error/${statusCode}` })
        }
      }

      return Promise.reject(error)
    }
  )

  const logout = async () => {
    authStore.removeToken() // Update the store
    try {
      await axiosInstance.delete('/v1/auth/refresh')
      // Optionally, redirect after logout
      router.push({ path: '/auth/login' })
    } catch (error) {
      // console.error('Error during logout:', error);
    }
  }
  axiosInstance.logout = logout
  return axiosInstance
}

export default AxiosInstance

// Define the logout function
