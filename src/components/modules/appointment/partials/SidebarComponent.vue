<template>
  <!-- Sidebar Component Start Here -->
  <default-sidebar>
    <template #profile-card>
      <profile-card></profile-card>
    </template>
    <hr class="hr-horizontal" />
    <ul class="navbar-nav iq-main-menu" id="e-commerce">
      <template v-for="(item, index) in menuItems" :key="index">
        <side-menu v-if="!item.children" :isTag="item.isTag" :title="item.title" :icon="item.icon" :miniTitle="item.miniTitle" :route="{ to: item.route }" :static-item="item.static" />

        <side-menu v-else :title="item.title" :icon="item.icon" :miniTitle="item.miniTitle" :toggle-id="item.toggleId" :caret-icon="true" :route="{ popup: 'false', to: item.route }" @onClick="toggle" :active="currentRoute.includes(item.route)">
          <b-collapse tag="ul" class="sub-nav" :id="item.toggleId" accordion="e-commerce" :visible="currentRoute.includes(item.route)">
            <side-menu v-for="(subItem, subIndex) in item.children" :key="subIndex" isTag="router-link" :title="subItem.title" :icon="subItem.icon" :icon-size="subItem.iconSize" icon-type="solid" :miniTitle="subItem.miniTitle" :route="{ to: subItem.route }" />
          </b-collapse>
        </side-menu>
      </template>
    </ul>
  </default-sidebar>
  <!-- Sidebar Component End Here -->
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import DefaultSidebar from '@/components/custom/sidebar/DefaultSidebar.vue'
import SideMenu from '@/components/custom/nav/SideMenu.vue'
import ProfileCard from '@/components/custom/sidebar/ProfileCard.vue'
import { useRoute } from 'vue-router'
//use menu store to get menus
import { useMenuStore } from '@/store/menuStore'

const menuStore = useMenuStore()
const rawMenus = computed(() => menuStore.menus)
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

const menuItems = ref([])
// Transform the backend menu items into a format that the Sidebar component can use.

function transformMenuItem(backendItem) {
  // Default assignment for properties if missing in backend data.
  const transformed = {
    // If a route exists, we assume it should use a router-link
    isTag: backendItem.route ? 'router-link' : undefined,
    title: backendItem.title,
    icon: backendItem.icon,
    // Use backend's miniTitle if provided, otherwise default to an empty string
    miniTitle: backendItem.miniTitle || '',
    // If there are children, use a toggleId (fallback: a slugified title)
    toggleId: backendItem.children ? backendItem.toggleId || backendItem.title.toLowerCase().replace(/\s+/g, '-') : undefined,
    // Use the route directly
    route: backendItem.route,
    // You might have a popup flag; if not, default to 'false'
    popup: backendItem.popup || 'false',
    // If children exist, we want to show the caret icon
    caretIcon: backendItem.children ? true : false
  }

  // If the backend provides children, recursively transform them.
  if (backendItem.children && Array.isArray(backendItem.children)) {
    transformed.children = backendItem.children.map((child) => transformMenuItem(child))
  }

  return transformed
}

onMounted(() => {
  console.log('Fetching menu items from backend...', rawMenus.value)
  menuItems.value = rawMenus.value.map((item) => transformMenuItem(item))
  console.log('Transformed menu items:', menuItems.value)
})
</script>
