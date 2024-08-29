<template>
  <!-- Sidebar Component Start Here-->
  <default-sidebar>
    <template #profile-card>
      <profile-card></profile-card>
    </template>
    <ul class="navbar-nav iq-main-menu" id="e-commerce">
      <hr class="hr-horizontal" />
      <side-menu title="APPOINTMENT" :static-item="true"></side-menu>
      <side-menu isTag="router-link" title="Dashboard" icon="view-grid" miniTitle="A" :route="{ to: 'default.dashboard' }"></side-menu>
      <side-menu isTag="router-link" title="Book Appointment" icon="calendar" miniTitle="BA" :route="{ to: 'default.book-appointment' }"></side-menu>
      <!-- <side-menu isTag="router-link" title="pro" icon="scanner" miniTitle="DV" :route="{ to: 'default.doctor-visit' }"></side-menu> -->
      <hr class="hr-horizontal" />
      <side-menu title="OTHER" :static-item="true"></side-menu>
      <side-menu title="Settings" icon="user" miniTitle="BF"></side-menu>
      <side-menu title="Sign Out" icon="exit" miniTitle="BF"></side-menu>
      <side-menu title="Help" icon="exclamation-circle" miniTitle="BF"></side-menu>
    </ul>
  </default-sidebar>
  <!-- Sidebar Component End Here-->
</template>

<script>
import { ref } from 'vue'
import DefaultSidebar from '@/components/custom/sidebar/DefaultSidebar.vue'
import SideMenu from '@/components/custom/nav/SideMenu.vue'
import ProfileCard from '@/components/custom/sidebar/ProfileCard.vue'
export default {
  components: { DefaultSidebar, SideMenu, ProfileCard },
  setup() {
    const visible = ref(false)
    const openMenu = () => {
      visible.value = !visible.value
    }

    const logout = () => {
      console.log('logout')
    }
    return { visible, openMenu }
  }
}
</script>

<style></style>
