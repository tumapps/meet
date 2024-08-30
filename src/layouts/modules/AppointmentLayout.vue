<template>
  <!-- <loader-component :show="isLoader"></loader-component> -->
  <!-- Sidebar Component Start Here-->
  <sidebar-component></sidebar-component>
  <!-- Sidebar Component End Here-->
  <main class="main-content">
    <div :class="`position-relative  ${isBanner ? 'iq-banner' : ''}`">
      <!-- Header Component Start Here -->
      <header-component></header-component>
      <template v-if="isBanner">
        <!-- Sub Header Component Start Here-->
        <!-- Sub Header Component End Here-->
      </template>
      <!-- Header Component End Here -->
    </div>

    <!-- Main Content Start Here -->
    <main-content-component>
      <!-- Router View For Pages -->
      <router-view></router-view>
    </main-content-component>
    <!-- Main Content Start Here -->

    <!-- Footer Component Start Here -->
    <footer-component></footer-component>
    <!-- Footer Component End Here -->
  </main>
</template>

<script>
// Library
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'

// Components
import HeaderComponent from '@/components/partials/HeaderComponent.vue'
import SidebarComponent from '@/components/modules/appointment/partials/SidebarComponent.vue'
import MainContentComponent from '@/components/partials/MainContentComponent.vue'
import FooterComponent from '@/components/partials/FooterComponent.vue'
import LoaderComponent from '@/components/custom/loader/LoaderComponent.vue'
export default {
  components: { HeaderComponent, SidebarComponent, MainContentComponent, FooterComponent, LoaderComponent },
  setup() {
    const route = useRoute()
    const isBanner = computed(() => route.meta.isBanner)
    const isLoader = ref(true)
    onMounted(() => {
      setTimeout(() => {
        isLoader.value = false
      }, 300)
    })
    return { isBanner, isLoader }
  }
}
</script>

<style lang="scss">
@import '@/assets/modules/appointment/scss/appointment.scss';
</style>
