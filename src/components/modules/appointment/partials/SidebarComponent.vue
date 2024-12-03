<template>
  <!-- Sidebar Component Start Here-->
  <default-sidebar>
    <ul class="navbar-nav iq-main-menu" id="e-commerce">
      <hr class="hr-horizontal" />
      <side-menu v-for="(menu, index) in menus" :key="index" :isTag="'router-link'" :title="menu.label" :icon="menu.icon" :miniTitle="getMiniTitle(menu.route)" :route="{ to: menu.route }"></side-menu>
    </ul>
  </default-sidebar>
  <!-- Sidebar Component End Here-->
</template>

<script setup>
import { computed } from 'vue'
import DefaultSidebar from '@/components/custom/sidebar/DefaultSidebar.vue'
import SideMenu from '@/components/custom/nav/SideMenu.vue'
import { useMenuStore } from '@/store/menuStore'

const menuStore = useMenuStore()
const menus = computed(() => menuStore.menus)

// Example functions to get icon and miniTitle based on route

function getMiniTitle(route) {
  const miniTitles = {
    dashboard: 'A',
    appointments: 'BA',
    users: 'BF'
  }
  return miniTitles[route] || 'MT'
}
</script>

<style></style>
