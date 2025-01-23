<template>
  <!-- Sidebar Component Start Here-->
  <default-sidebar>
    <ul class="navbar-nav iq-main-menu" id="e-commerce">
      <hr class="hr-horizontal" />
      <side-menu v-for="(menu, index) in menus" :key="index" :isTag="'router-link'" :title="menu.label" :icon="menu.icon" :miniTitle="getMiniTitle(menu.label)" :route="{ to: menu.route }">
        <b-collapse tag="ul" class="sub-nav" id="icons" accordion="sidebar-menu" :visible="currentRoute.includes('icons')">
          <side-menu isTag="router-link" title="Solid" icon="circle" :icon-size="10" icon-type="solid" miniTitle="S" :route="{ to: 'home' }"></side-menu>
          <side-menu isTag="router-link" title="Outlined" icon="circle" :icon-size="10" icon-type="solid" miniTitle="O" :route="{ to: 'home' }"></side-menu>
          <side-menu isTag="router-link" title="Dual Tone" icon="circle" :icon-size="10" icon-type="solid" miniTitle="DT" :route="{ to: 'home' }"></side-menu>
        </b-collapse>
      </side-menu>
    </ul>
  </default-sidebar>

  <!-- <default-sidebar>
    <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
      <side-menu v-for="(menu, index) in menuItems" :key="index" :title="menu.title" :static-item="menu.staticItem || false" :isTag="menu.isTag || false" :route="menu.route || {}" :icon="menu.icon || ''" :caret-icon="menu.caretIcon || false" @onClick="toggle">
        Conditionally render sub-menu items
        <b-collapse v-if="menu.subItems && menu.subItems.length" tag="ul" class="sub-nav" :id="menu.toggleId" accordion="sidebar-menu" :visible="currentRoute.includes(menu.route.to)">
          <side-menu v-for="(subItem, subIndex) in menu.subItems" :key="subIndex" :title="subItem.title" :route="subItem.route" :icon="subItem.icon" :miniTitle="subItem.miniTitle" :icon-size="subItem.iconSize || 10" :icon-type="subItem.iconType || 'solid'"></side-menu>
        </b-collapse>
        Optionally render a badge
        <<template v-if="menu.badge"> 
          <template #title>
            {{ menu.title }}
            <b-badge :variant="menu.badgeVariant" class="ms-3" pill>
              {{ menu.badge }}
            </b-badge>
          </template>
        </template> 
      </side-menu>
    </ul>
  </default-sidebar> -->
  <!-- Sidebar Component End Here-->
</template>

<script setup>
import { computed, ref } from 'vue'
import DefaultSidebar from '@/components/custom/sidebar/DefaultSidebar.vue'
import SideMenu from '@/components/custom/nav/SideMenu.vue'
import { useMenuStore } from '@/store/menuStore'
import { useRoute } from 'vue-router'
const route = useRoute()

const menuStore = useMenuStore()
const menus = computed(() => menuStore.menus)
const currentRoute = ref('')
const toggle = (route) => {
  if (route === currentRoute.value && route.includes('.')) {
    const menu = currentRoute.value.split('.')
    return (currentRoute.value = menu[menu.length - 2])
  }
  if (route !== currentRoute.value && currentRoute.value.includes(route)) {
    return (currentRoute.value = '')
  }
  if (route !== currentRoute.value) {
    return (currentRoute.value = route)
  }
  if (route === currentRoute.value) {
    return (currentRoute.value = '')
  }
  return (currentRoute.value = '')
}
toggle(route.name)

// Example functions to get icon and miniTitle based on route

function getMiniTitle(label) {
  // Ensure the label is a string and not empty
  if (typeof label === 'string' && label.length > 0) {
    return label.charAt(0).toUpperCase() // Return the first letter in uppercase
  }

  return 'MT' // Return 'MT' if the label is not valid
}

// const menuItems = ref([
//   {
//     title: 'Home',
//     staticItem: true
//   },
//   {
//     title: 'Dashboards',
//     route: { to: 'default.dashboard' },
//     icon: 'view-grid',
//     isTag: 'router-link'
//   },
//   {
//     title: 'Alternate Dashboard',
//     route: { to: 'default.alternate-dashboard' },
//     icon: 'dashboard',
//     isTag: 'router-link'
//   },
//   {
//     title: 'Menu Style',
//     icon: 'adjustment',
//     toggleId: 'menu-style',
//     caretIcon: true,
//     route: { popup: 'false', to: 'menu-style' },
//     subItems: [
//       { title: 'Horizontal', route: { to: 'horizontal.dashboard' }, miniTitle: 'H', icon: 'circle' },
//       { title: 'Dual Horizontal', route: { to: 'dual-horizontal.dashboard' }, miniTitle: 'D', icon: 'circle' }
//       // Add more sub-items as needed
//     ]
//   },
//   {
//     title: 'Design System',
//     icon: 'wallet',
//     route: { to: 'design-system.main', target: 'blank' },
//     badge: 'UI',
//     badgeVariant: 'success'
//   },
//   {
//     title: 'E-Commerce',
//     icon: 'cart',
//     toggleId: 'e-commerce',
//     caretIcon: true,
//     route: { popup: 'false', to: 'e-commerce' },
//     subItems: [
//       { title: 'Admin Dashboard', route: { to: 'e-commerce.dashboard' }, miniTitle: 'AD', icon: 'home' },
//       { title: 'Vendor Dashboard', route: { to: 'e-commerce.dashboard.vendor' }, miniTitle: 'VD', icon: 'chart-square-bar' },
//       {
//         title: 'Shop',
//         route: { popup: 'false', to: 'e-commerce.shop' },
//         miniTitle: 'SP',
//         caretIcon: true,
//         subItems: [
//           { title: 'Shop Main', route: { to: 'e-commerce.shop.main' }, miniTitle: 'SM', icon: 'circle' }
//           // Add more nested sub-items as needed
//         ]
//       }
//       // Add more sub-items as needed
//     ]
//   }
// ])
</script>

<style></style>
