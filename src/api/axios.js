import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store.js'
import { useMenuStore } from '@/store/menuStore.js'

const baseURL = import.meta.env.VITE_API_BASE_URL

const AxiosInstance = () => {
  const axiosInstance = axios.create({
    // Set your API base URL
    baseURL,
    withCredentials: true
  })
  axios.defaults.timeout = 30000 // 30 seconds timeout globally

  const authStore = useAuthStore()
  const menuStore = useMenuStore()
  const refreshAndRetryQueue = []
  let isRefreshing = false
  let refreshPromise = null
  const MAX_QUEUE_SIZE = 50
  const router = useRouter()

  let userIP

  const fetchUserIp = async () => {
    // console.log('[Axios] Using baseURL:', baseURL)

    try {
      const response = await axios.get('https://api.ipify.org?format=json')
      userIP = response.data.ip // Assign the fetched IP to userIP
    } catch (error) {
      console.error(error)
    }
  }

  function goToFirstMenu() {
    menuStore.navigateToFirstMenu(router)
  }

  // Immediately fetch the IP address when the axios instance is created
  fetchUserIp()

  axiosInstance.interceptors.request.use(
    async (config) => {
      // fetchUserIp() // Fetch the IP address for every request

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

  const refreshAccessToken = async () => {
    if (isRefreshing) {
      return refreshPromise // Return the existing refresh promise
    }

    isRefreshing = true
    refreshPromise = (async () => {
      try {
        const response = await axiosInstance.post('/v1/auth/refresh')
        const newToken = response.data?.dataPayload.data.token
        authStore.setToken(newToken, response.data.dataPayload.data.username)
        return newToken
      } catch (refreshError) {
        if (refreshError.response?.status === TOKEN_EXPIRED_CODE) {
          refreshAndRetryQueue.length = 0 // Clear queue
          router.push({ path: `/auth/login` })
          localStorage.clear()
        } else {
          console.error('Failed to refresh access token', refreshError)
        }
        throw refreshError
      } finally {
        isRefreshing = false
        refreshPromise = null
      }
    })()

    return refreshPromise
  }

  axiosInstance.interceptors.response.use(
    (response) => response,
    async (error) => {
      const originalRequest = error.config

      if (error.response) {
        const statusCode = error.response.status

        if (statusCode === TOKEN_EXPIRED_CODE) {
          // Handle session expiration
          localStorage.removeItem('user.token')
          router.push({ path: `/auth/login` })
          localStorage.clear()
          return Promise.reject(error)
        }

        if (statusCode === UNAUTHORIZED_CODE) {
          if (!isRefreshing) {
            isRefreshing = true
            try {
              const newToken = await refreshAccessToken()

              // if successfull try
              if (newToken) {
                originalRequest.headers['Authorization'] = `Bearer ${newToken}`

                // Retry requests in queue with new token
                refreshAndRetryQueue.forEach(({ config, resolve, reject }) => {
                  axiosInstance.request(config).then(resolve).catch(reject)
                })
                refreshAndRetryQueue.length = 0
              }

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
            if (refreshAndRetryQueue.length >= MAX_QUEUE_SIZE) {
              return Promise.reject(new Error('Too many requests waiting for token refresh'))
            } else {
              refreshAndRetryQueue.push({ config: originalRequest, resolve, reject })
            }
          })
        }

        // Handle other errors by redirecting to custom error page
        if (![422, TOKEN_EXPIRED_CODE, UNAUTHORIZED_CODE].includes(statusCode)) {
          router.push({ path: `/error/${statusCode}` })
        }
      }

      return Promise.reject(error)
    }
  )

  const logout = async () => {
    authStore.removeToken() // Update the store
    router.push({ path: '/auth/login' })

    authStore.remove
    localStorage.setItem('loggedIn', false)

    try {
      await axiosInstance.delete('/v1/auth/refresh')
    } catch (error) {
      console.error('Error during logout:', error)
    } finally {
      //you can decide to wait for the backend to respond and logout or  logout on frontend and backend left doing its thing
      // router.push({ path: '/auth/login' })
      localStorage.clear()
    }
  }

  //replica refresh token function but to be used to know where the refresh token is valid or not on app load
  const RunAccessToken = async () => {
    if (isRefreshing) {
      return refreshPromise // Wait for the existing refresh to complete
    }

    isRefreshing = true
    refreshPromise = (async () => {
      try {
        const response = await axiosInstance.post('/v1/auth/refresh')
        const newToken = response.data?.dataPayload.data.token

        if (localStorage.getItem('user.token') && localStorage.getItem('user.username')) {
          authStore.setToken(newToken, response.data.dataPayload.data.username)
          localStorage.setItem('loggedIn', true)
          goToFirstMenu()
          return newToken
          // Return the new token
        } else if (!localStorage.getItem('user.token') && localStorage.getItem('user.username')) {
          router.push({ name: 'locked' })
          throw new Error('User locked')
        }
      } catch (refreshError) {
        if (refreshError.response?.status === TOKEN_EXPIRED_CODE) {
          refreshAndRetryQueue.length = 0 // Clear queue
          localStorage.clear()
          router.push({ path: `/auth/login` })
        } else {
          console.error('refresh token is not valid')
        }
        throw refreshError
      } finally {
        isRefreshing = false
        refreshPromise = null
      }
    })()

    return refreshPromise
  }

  axiosInstance.logout = logout
  axiosInstance.RunAccessToken = RunAccessToken // Expose the refreshAccessToken function
  return axiosInstance
}

export default AxiosInstance
