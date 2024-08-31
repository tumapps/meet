<template>
  <!-- Sidebar Component Start Here-->
  <default-sidebar>

    <ul class="navbar-nav iq-main-menu" id="e-commerce">
      <hr class="hr-horizontal" />
      <side-menu title="APPOINTMENT" :static-item="true"></side-menu>
      <side-menu isTag="router-link" title="Dashboard" icon="home" miniTitle="A" :route="{ to: 'default.dashboard' }"></side-menu>
      <side-menu isTag="router-link" title="Book Appointment" icon="calendar" miniTitle="BA" :route="{ to: 'default.book-appointment' }"></side-menu>
      <!-- <side-menu isTag="router-link" title="pro" icon="scanner" miniTitle="DV" :route="{ to: 'default.doctor-visit' }"></side-menu> -->
      <hr class="hr-horizontal" />
      <side-menu title="OTHER" :static-item="true"></side-menu>
      <side-menu isTag="router-link" title="Add-User" icon="user-plus" miniTitle="BF" :route="{ to: 'default.adduser' }"></side-menu>
      <side-menu isTag="router-link" title="Profile" icon="user" miniTitle="BF" :route="{ to: 'profile' }"></side-menu>
      <side-menu isTag="router-link" title="Settings" icon="gears" miniTitle="BF" :route="{ to: 'default.settings' }"></side-menu>
      <side-menu title="Sign Out" icon="exit" miniTitle="BF"></side-menu>
      <side-menu title="Help" icon="exclamation-circle" miniTitle="BF"></side-menu>
    </ul>
  </default-sidebar>
  <!-- Sidebar Component End Here-->
</template>

<script setup>
import { ref } from 'vue'
import DefaultSidebar from '@/components/custom/sidebar/DefaultSidebar.vue'
import SideMenu from '@/components/custom/nav/SideMenu.vue'
import ProfileCard from '@/components/custom/sidebar/ProfileCard.vue'

// Define reactive properties
const visible = ref(false)

// Define methods
const openMenu = () => {
  visible.value = !visible.value
}

const logout = () => {
  console.log('logout')
}

// Expose reactive properties and methods to the template
const components = {
  DefaultSidebar,
  SideMenu,
  ProfileCard
}

</script>


<style></style>
