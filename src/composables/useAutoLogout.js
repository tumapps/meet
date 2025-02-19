import { onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'

export function useAutoLogout(logout_After) {
  const logoutAfter = logout_After
  const lastActivity = ref(Date.now())
  const router = useRouter()
  let timeout

  const resetTimer = () => {
    lastActivity.value = Date.now()
    clearTimeout(timeout)
    timeout = setTimeout(handleLogout, logoutAfter)
  }

  const handleLogout = () => {
    //check if the user is logged in
    if (localStorage.getItem('loggedIn') === 'true') {
      // Clear tokens, notify the server, etc.
      localStorage.removeItem('user.token') // Example for token removal
      localStorage.setItem('loggedIn', false) // Set loggedIn to false
      router.push('/lockscreen') // Redirect to login
      // alert('You have been logged out due to inactivity.')
    }
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
