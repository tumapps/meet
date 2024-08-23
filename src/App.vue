<template>
  <router-view />
</template>

<script>
import { onMounted, onUnmounted, computed } from 'vue'

// Pinia Store
import { useSetting } from './store/pinia'

import '@/plugins/styles'
export default {
  name: 'App',
  setup() {
    const store = useSetting()
    const sidebarType = computed(() => store.sidebar_type_value)
    const resizePlugin = () => {
      const sidebarResponsive = document.querySelector('[data-sidebar="responsive"]')
      if (window.innerWidth < 1025) {
        if (sidebarResponsive !== null) {
          if (!sidebarResponsive.classList.contains('sidebar-mini')) {
            sidebarResponsive.classList.add('on-resize')
            store.sidebar_type([...sidebarType.value, 'sidebar-mini'])
          }
        }
      } else {
        if (sidebarResponsive !== null) {
          if (sidebarResponsive.classList.contains('sidebar-mini') && sidebarResponsive.classList.contains('on-resize')) {
            sidebarResponsive.classList.remove('on-resize')
            store.sidebar_type(sidebarType.value.filter((item) => item !== 'sidebar-mini'))
          }
        }
      }
    }
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
  }
}
</script>

<style lang="scss">
// @import '@/assets/custom-vue/scss/styles.scss';
@import '@/assets/scss/hope-ui.scss';
@import '@/assets/scss/pro.scss';
@import '@/assets/scss/custom.scss';
@import '@/assets/scss/customizer.scss';
@import '@/assets/custom-vue/scss/plugins.scss';
</style>
