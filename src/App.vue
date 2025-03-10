<template>
  <router-view />
</template>

<script setup>
import { onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAutoLogout } from '@/composables/useAutoLogout'
import createAxiosInstance from '@/api/axios'
import { useCleanup } from '@/composables/useCleanup'
import { useSetting } from '@/store/pinia'

import '@/plugins/styles'

// ✅ Initialize necessary utilities
const { registerCleanup } = useCleanup()
const store = useSetting()
const axiosInstance = createAxiosInstance()
const router = useRouter()

// ⏳ Auto logout after inactivity (15 minutes = 900000ms)
useAutoLogout(900000)

// ✅ Computed property for sidebar type
const sidebarType = computed(() => store.sidebar_type_value)

// ✅ Function to refresh token (Runs only on app load)
const RunrefreshToken = async () => {
  try {
    if (localStorage.getItem('loggedIn') !== 'true') {
      const newToken = await axiosInstance.RunAccessToken()
      return !!newToken
    }
    return false
  } catch (error) {
    console.error('Failed to refresh token:', error)
    return false
  }
}

// ✅ Resize function to handle sidebar responsiveness (Debounced)
const resizePlugin = () => {
  const sidebarResponsive = document.querySelector('[data-sidebar="responsive"]')
  if (!sidebarResponsive) return

  if (window.innerWidth < 1025) {
    if (!sidebarResponsive.classList.contains('sidebar-mini')) {
      sidebarResponsive.classList.add('on-resize')
      store.sidebar_type([...sidebarType.value, 'sidebar-mini'])
    }
  } else {
    if (sidebarResponsive.classList.contains('sidebar-mini') && sidebarResponsive.classList.contains('on-resize')) {
      sidebarResponsive.classList.remove('on-resize')
      store.sidebar_type(sidebarType.value.filter((item) => item !== 'sidebar-mini'))
    }
  }
}

// ✅ Unauthenticated routes (Skip session check)
const unauthenticatedRoutes = ['/auth/login', '/request-password-reset', '/reset-password', '/email-confirmed', '/lockscreen', '/confirm']

// ✅ Add navigation guard (Only once, outside lifecycle hooks)

const validToken = async () => {
  if (localStorage.getItem('loggedIn') !== 'true') {
    router.beforeEach((to, from, next) => {
      // Allow access to unauthenticated routes
      if (unauthenticatedRoutes.includes(to.path) || to.path.startsWith('/confirm/')) {
        return next()
      }

      // Refresh token before allowing navigation
      RunrefreshToken().then(() => {
        next()
      })
    })
  }
}

// ✅ Lifecycle Hooks
onMounted(() => {
  console.log('App Mounted')

  // Run token refresh on mount
  validToken()

  // ✅ Attach event listener for resize
  window.addEventListener('resize', resizePlugin)
  registerCleanup(() => window.removeEventListener('resize', resizePlugin))

  // ✅ Initial sidebar adjustment
  setTimeout(resizePlugin, 200)

  // ✅ Set application settings
  store.setSetting()
})

onUnmounted(() => {
  console.log('App Unmounted: Cleaning up...')
  registerCleanup(() => localStorage.setItem('loggedIn', false))
})
</script>

<style lang="scss">
@import '@/assets/scss/hope-ui.scss';
@import '@/assets/scss/pro.scss';
@import '@/assets/scss/custom.scss';
@import '@/assets/scss/customizer.scss';
@import '@/assets/custom-vue/scss/plugins.scss';

.table tbody tr td {
  padding: 0.5px 8px 0 22px !important;
}

.modal-title {
  min-width: 100% !important;
}

.error {
  color: red;
  font-size: 1rem;
}
</style>
