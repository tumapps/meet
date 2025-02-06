<template>
  <!-- Sidebar Component Start Here-->
  <default-sidebar>
    <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
      <side-menu v-for="(item, index) in sidebarConfig" :key="index" :title="item.title" :static-item="item.staticItem" :isTag="item.isTag" :icon="item.icon" :route="item.route" :toggle-id="item.toggleId" :caret-icon="item.caretIcon" @onClick="toggle" :active="currentRoute.includes(item.route?.to)">
        <template v-if="item.children">
          <b-collapse tag="ul" class="sub-nav" :id="item.toggleId" accordion="sidebar-menu" :visible="currentRoute.includes(item.route?.to)">
            <side-menu v-for="(child, childIndex) in item.children" :key="childIndex" :title="child.title" :icon="child.icon" :icon-size="child.iconSize" :icon-type="child.iconType" :miniTitle="child.miniTitle" :route="child.route" />
          </b-collapse>
        </template>
      </side-menu>
    </ul>
  </default-sidebar>
  <!-- Sidebar Component End Here-->
</template>

<script setup>
import DefaultSidebar from '@/components/custom/sidebar/DefaultSidebar.vue'
import SideMenu from '@/components/custom/nav/SideMenu.vue'
import { ref } from 'vue'
import { useRoute } from 'vue-router'
// import { sidebarConfig } from './sidebarConfig' // Import the configuration

const currentRoute = ref('')
const route = useRoute()

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

const sidebarConfig = [
  // {
  //   title: "Home",
  //   staticItem: true,
  // },
  {
    isTag: 'router-link',
    title: 'Dashboard',
    icon: 'home',
    route: { to: 'home' }
  },
  {
    isTag: 'router-link',
    title: 'Appointments',
    icon: 'table',
    route: { to: 'appointments' }
  },
  {
    title: 'IAM Admin',
    icon: 'adjustment',
    toggleId: 'settings',
    caretIcon: true,
    route: { popup: 'false', to: 'settings' },
    children: [
      {
        title: 'Add User',
        icon: 'user',
        // iconSize: 10,
        iconType: 'solid',
        miniTitle: 'A',
        route: { to: 'default.adduser' }
      },
      {
        title: 'Users',
        icon: 'users',
        // iconSize: 10,
        iconType: 'solid',
        miniTitle: 'U',
        route: { to: 'default.users' }
      }
      // Add more children as needed
    ]
  }
  // Add more menu items as needed
]
</script>
