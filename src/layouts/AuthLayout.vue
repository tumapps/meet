<template>
  <router-view> </router-view>
  <!-- Live Customizer Component Start Here -->
  <setting-offcanvas :landing-page="landingPage" :buy-now="buyNow"></setting-offcanvas>
  <!-- Live Customizer Component End Here-->
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
