<template>
  <router-view />
</template>

<script setup>
import { onMounted, onUnmounted, computed } from 'vue'
import { useMenuStore } from '@/store/menuStore'
import { watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAutoLogout } from '@/composables/useAutoLogout'

// Import Pinia Store
import { useSetting } from './store/pinia'

import '@/plugins/styles'

useAutoLogout(900000) // Set 2 minutes (120000 ms) for inactivity
// Initialize the store
const menuStore = useMenuStore()
const route = useRoute()

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

// Lifecycle hooks for component mount and unmount
onMounted(() => {
  window.addEventListener('resize', resizePlugin)
  setTimeout(() => {
    resizePlugin()
  }, 200)
  store.setSetting()
})

onUnmounted(() => {
  window.removeEventListener('resize', resizePlugin)
})
</script>

<style lang="scss">
@import '@/assets/scss/hope-ui.scss';
@import '@/assets/scss/pro.scss';
@import '@/assets/scss/custom.scss';
@import '@/assets/scss/customizer.scss';
@import '@/assets/custom-vue/scss/plugins.scss';
</style>
