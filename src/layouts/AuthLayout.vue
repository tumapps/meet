<template>
  <router-view> </router-view>
</template>

<script>
import SettingOffcanvas from '@/components/setting/SettingOffcanvas.vue'
import { computed } from 'vue'
import { useRoute } from 'vue-router'
export default {
  components: { SettingOffcanvas },
  setup() {
    const route = useRoute()
    const landingPage = computed(() => route.meta.landingPage)
    const buyNow = computed(() => route.meta.buyNow)

    return {
      landingPage,
      buyNow
    }
  }
}
</script>
