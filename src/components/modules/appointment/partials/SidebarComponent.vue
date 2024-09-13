<template>
  <!-- Sidebar Component Start Here-->
  <default-sidebar>
    <!-- <ul class="navbar-nav iq-main-menu" id="e-commerce">
      <hr class="hr-horizontal" />
      <side-menu isTag="router-link" title="Dashboard" icon="home" miniTitle="A" :route="{ to: 'default.dashboard' }"></side-menu>
      <side-menu isTag="router-link" title="Appointments" icon="calendar" miniTitle="BA" :route="{ to: 'appointments' }"></side-menu>
      <side-menu isTag="router-link" title="Users" icon="users" miniTitle="BF" :route="{ to: 'default.users' }"></side-menu>
    </ul> -->
    <ul class="navbar-nav iq-main-menu" id="e-commerce">
      <hr class="hr-horizontal" />
      <side-menu
        v-for="(menu, index) in menus"
        :key="index"
        :isTag="'router-link'"
        :title="menu.label"
        :icon="getIcon(menu.label)"
        :miniTitle="getMiniTitle(menu.route)"
        :route="{ to: menu.route }"
      ></side-menu>
    </ul>
  </default-sidebar>
  <!-- Sidebar Component End Here-->
</template>

<script setup>
import { ref, computed} from 'vue'
import DefaultSidebar from '@/components/custom/sidebar/DefaultSidebar.vue'
import SideMenu from '@/components/custom/nav/SideMenu.vue'
import ProfileCard from '@/components/custom/sidebar/ProfileCard.vue'
import { useMenuStore } from '@/store/menuStore'

const menuStore = useMenuStore();
const menus = computed(() => menuStore.menus);

// Example functions to get icon and miniTitle based on route
function getIcon(label) {
  const iconMapping = {
    Dashboard: 'home',
    Appointments: 'calendar',
    Users: 'users',
  };
  return iconMapping[label] || 'default-icon'; // Fallback to 'default-icon' if label not found
}


function getMiniTitle(route) {
  const miniTitles = {
    dashboard: 'A',
    appointments: 'BA',
    users: 'BF',
  };
  return miniTitles[route] || 'MT';
}

// Define reactive properties
const visible = ref(false)

// Define methods
const openMenu = () => {
  visible.value = !visible.value
}

// const logout = () => {
//   console.log('logout')
// }

// Expose reactive properties and methods to the template
const components = {
  DefaultSidebar,
  SideMenu,
  ProfileCard
}

</script>


<style></style>
