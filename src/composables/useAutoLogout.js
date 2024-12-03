import { onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'

export function useAutoLogout(logoutAfter = 120000) {
  const lastActivity = ref(Date.now())
  const router = useRouter()
  let timeout

  const resetTimer = () => {
    lastActivity.value = Date.now()
    clearTimeout(timeout)
    timeout = setTimeout(handleLogout, logoutAfter)
  }

  const handleLogout = () => {
    // Clear tokens, notify the server, etc.
    localStorage.removeItem('authToken') // Example for token removal
    router.push('/lockscreen') // Redirect to login
    alert('You have been logged out due to inactivity.')
  }

  const activityEvents = ['mousemove', 'keydown', 'click']

  const attachListeners = () => {
    activityEvents.forEach((event) => {
      window.addEventListener(event, resetTimer)
    })
  }

  const detachListeners = () => {
    activityEvents.forEach((event) => {
      window.removeEventListener(event, resetTimer)
    })
  }

  onMounted(() => {
    attachListeners()
    timeout = setTimeout(handleLogout, logoutAfter)
  })

  onUnmounted(() => {
    detachListeners()
    clearTimeout(timeout)
  })
}
