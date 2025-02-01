import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store.js'

// let userIP // Initialize

// const AxiosInstance = () => {
//   const axiosInstance = axios.create({
//     // Set your API base URL
//     withCredentials: true
//   })
//   axios.defaults.timeout = 30000 // 30 seconds timeout globally

//   const authStore = useAuthStore()
//   const refreshAndRetryQueue = []
//   let isRefreshing = false
//   let refreshPromise = null
//   const MAX_QUEUE_SIZE = 50
//   const router = useRouter()

//   const fetchUserIp = async () => {
//     try {
//       const response = await axios.get('https://api.ipify.org?format=json')
//       userIP = response.data.ip // Assign the fetched IP to userIP
//     } catch (error) {
//       // console.log('Error fetching IP address:', error)
//     }
//   }

//   // Immediately fetch the IP address when the axios instance is created
//   fetchUserIp()

//   axiosInstance.interceptors.request.use(
//     async (config) => {
//       // fetchUserIp() // Fetch the IP address for every request

//       // Fetch the token from localStorage
//       if (config.headers['X-Exclude-Interceptor']) {
//         return config
//       }
//       const token = localStorage.getItem('user.token')
//       if (token) {
//         config.headers['Authorization'] = `Bearer ${token}`
//       }
//       // Add the stored IP address to the headers
//       if (userIP) {
//         config.headers['X-User-IP'] = userIP
//       }
//       return config
//     },
//     (error) => {
//       return Promise.reject(error)
//     }
//   )
//   const TOKEN_EXPIRED_CODE = 440
//   const UNAUTHORIZED_CODE = 401

//   axiosInstance.interceptors.response.use(
//     (response) => response,
//     async (error) => {
//       const originalRequest = error.config

//       // const refreshAccessToken = async () => {
//       //   localStorage.removeItem('user.token')
//       //   try {
//       //     const response = await axiosInstance.post('/v1/auth/refresh')
//       //     const newToken = response.data?.dataPayload.data.token

//       //     // Store the new access token
//       //     authStore.setToken(newToken, response.data.dataPayload.data.username)
//       //     return newToken
//       //   } catch (refreshError) {
//       //     if (refreshError.response?.status === TOKEN_EXPIRED_CODE) {
//       //       //clear queue
//       //       refreshAndRetryQueue.length = 0
//       //       router.push({ path: `/auth/login` })
//       //       localStorage.clear()
//       //     } else {
//       //       console.error('Failed to refresh access token', refreshError)
//       //     }
//       //     throw refreshError
//       //   }
//       // }

//       const refreshAccessToken = async () => {
//         if (isRefreshing) {
//           return refreshPromise // Return the existing refresh promise
//         }

//         isRefreshing = true
//         refreshPromise = (async () => {
//           try {
//             const response = await axiosInstance.post('/v1/auth/refresh')
//             const newToken = response.data?.dataPayload.data.token
//             authStore.setToken(newToken, response.data.dataPayload.data.username)
//             return newToken
//           } catch (refreshError) {
//             if (refreshError.response?.status === TOKEN_EXPIRED_CODE) {
//               refreshAndRetryQueue.length = 0 // Clear queue
//               logout()
//               router.push({ path: `/auth/login` })
//               localStorage.clear()
//             } else {
//               console.error('Failed to refresh access token', refreshError)
//             }
//             throw refreshError
//           } finally {
//             isRefreshing = false
//             refreshPromise = null
//           }
//         })()

//         return refreshPromise
//       }

//       if (error.response) {
//         const statusCode = error.response.status

//         if (statusCode === TOKEN_EXPIRED_CODE) {
//           // Handle session expiration
//           localStorage.removeItem('user.token')
//           logout()
//           // router.push({ path: `/auth/login` })
//           return Promise.reject(error)
//         }

//         if (statusCode === UNAUTHORIZED_CODE) {
//           console.log('Token expired, attempting to refresh...')

//           if (!isRefreshing) {
//             isRefreshing = true
//             try {
//               const newToken = await refreshAccessToken()

//               // if successfull try
//               if (newToken) {
//                 originalRequest.headers['Authorization'] = `Bearer ${newToken}`

//                 // Retry requests in queue with new token
//                 refreshAndRetryQueue.forEach(({ config, resolve, reject }) => {
//                   axiosInstance.request(config).then(resolve).catch(reject)
//                 })
//                 refreshAndRetryQueue.length = 0
//               }

//               return axiosInstance(originalRequest)
//             } catch (refreshError) {
//               router.push({ path: `/auth/login` })
//               throw refreshError
//             } finally {
//               isRefreshing = false
//             }
//           }

//           // Queue the request until token is refreshed
//           return new Promise((resolve, reject) => {
//             if (refreshAndRetryQueue.length >= MAX_QUEUE_SIZE) {
//               return Promise.reject(new Error('Too many requests waiting for token refresh'))
//             } else {
//               refreshAndRetryQueue.push({ config: originalRequest, resolve, reject })
//             }
//           })
//         }

//         // Handle other errors by redirecting to custom error page
//         if (![422, TOKEN_EXPIRED_CODE, UNAUTHORIZED_CODE].includes(statusCode)) {
//           console.log(`Redirecting to error page due to status: ${statusCode}`)
//           router.push({ path: `/error/${statusCode}` })
//         }
//       }

//       return Promise.reject(error)
//     }
//   )

//   const logout = async () => {
//     authStore.removeToken() // Update the store
//     authStore.remove
//     localStorage.setItem('loggedIn', false)

//     try {
//       await axiosInstance.delete('/v1/auth/refresh')
//     } catch (error) {
//       console.error('Error during logout:', error)
//     } finally {
//       router.push({ path: '/auth/login' })
//     }
//   }

//   axiosInstance.logout = logout
//   axiosInstance.refreshAccessToken = refreshAccessToken
//   return axiosInstance
// }

// export default AxiosInstance

// // Define the logout function

const AxiosInstance = () => {
  const axiosInstance = axios.create({
    // Set your API base URL
    withCredentials: true
  })
  axios.defaults.timeout = 30000 // 30 seconds timeout globally

  const authStore = useAuthStore()
  const refreshAndRetryQueue = []
  let isRefreshing = false
  let refreshPromise = null
  const MAX_QUEUE_SIZE = 50
  const router = useRouter()

  let userIP

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
          logout()
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
          logout()
          // router.push({ path: `/auth/login` })
          return Promise.reject(error)
        }

        if (statusCode === UNAUTHORIZED_CODE) {
          console.log('Token expired, attempting to refresh...')

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
          console.log(`Redirecting to error page due to status: ${statusCode}`)
          router.push({ path: `/error/${statusCode}` })
        }
      }

      return Promise.reject(error)
    }
  )

  const logout = async () => {
    authStore.removeToken() // Update the store
    authStore.remove
    localStorage.setItem('loggedIn', false)

    try {
      await axiosInstance.delete('/v1/auth/refresh')
    } catch (error) {
      console.error('Error during logout:', error)
    } finally {
      router.push({ path: '/auth/login' })
    }
  }

  axiosInstance.logout = logout
  axiosInstance.refreshAccessToken = refreshAccessToken // Expose the refreshAccessToken function
  return axiosInstance
}

export default AxiosInstance
