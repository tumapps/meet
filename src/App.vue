<template>
  <router-view />
</template>

<script setup>
import { onBeforeMount, onMounted, computed, watch } from 'vue'
import { useMenuStore } from '@/store/menuStore'
import { useRoute } from 'vue-router'
import { useAutoLogout } from '@/composables/useAutoLogout'
import createAxiosInstance from '@/api/axios'

import { useRouter } from 'vue-router'

// Import Pinia Store
import { useSetting } from '@/store/pinia'

import '@/plugins/styles'

const router = useRouter()

// Function to check if a route requires authentication
const requiresAuth = (route) => {
  return route.matched.some((record) => record.meta.requiresAuth)
}

useAutoLogout(900000) // Set 2 minutes (120000 ms) for inactivity
// Initialize the store
const menuStore = useMenuStore()
const axiosInstance = createAxiosInstance()
const route = useRoute()
// const authStore = useAuthStore()

watch(
  () => route.meta.customMenus,
  (newMenus) => {
    if (newMenus) {
      menuStore.setMenus(newMenus)
    }
  },
  { immediate: true } // Trigger immediately on load
)

const store = useSetting()

// Computed property for sidebar type
const sidebarType = computed(() => store.sidebar_type_value)

// Resize function to handle sidebar responsiveness
const resizePlugin = () => {
  const sidebarResponsive = document.querySelector('[data-sidebar="responsive"]')
  if (window.innerWidth < 1025) {
    if (sidebarResponsive && !sidebarResponsive.classList.contains('sidebar-mini')) {
      sidebarResponsive.classList.add('on-resize')
      store.sidebar_type([...sidebarType.value, 'sidebar-mini'])
    }
  } else {
    if (sidebarResponsive && sidebarResponsive.classList.contains('sidebar-mini') && sidebarResponsive.classList.contains('on-resize')) {
      sidebarResponsive.classList.remove('on-resize')
      store.sidebar_type(sidebarType.value.filter((item) => item !== 'sidebar-mini'))
    }
  }
}

// Function to refresh token (Only runs once on app load)
const RunrefreshToken = async () => {
  try {
    const newToken = await axiosInstance.RunAccessToken()
    return !!newToken // Return `true` if token is refreshed, `false` otherwise
  } catch (error) {
    console.error('Failed to refresh token:', error)
    return false // Token refresh failed
  }
}

// Lifecycle hooks for component mount and unmount
onMounted(() => {
  // Add the event listener when the component is mounted
  // window.addEventListener('beforeunload', handleBeforeUnload)
  window.addEventListener('resize', resizePlugin)
  setTimeout(() => {
    resizePlugin()
  }, 200)
  store.setSetting()
})

router.beforeEach((to, from, next) => {
  if (requiresAuth(to)) {
    // Token check already handled in onBeforeMount, so no need to re-run
    onBeforeMount(async () => {
      const isAuthenticated = await RunrefreshToken()
      if (!isAuthenticated) {
        router.replace({ path: '/auth/login' }) // Redirect if token refresh fails
      }
    })
    next()
  } else {
    next()
  }
})
</script>

<style lang="scss">
@import '@/assets/scss/hope-ui.scss';
@import '@/assets/scss/pro.scss';
@import '@/assets/scss/custom.scss';
@import '@/assets/scss/customizer.scss';
@import '@/assets/custom-vue/scss/plugins.scss';

.table tbody tr td {
  /* background-color: rgb(96, 96, 177) !important; */
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
