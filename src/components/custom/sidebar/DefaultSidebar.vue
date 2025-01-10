<!-- <script>
import { computed, onMounted, ref } from 'vue'
import BrandLogo from '@/components/custom/logo/BrandLogo.vue'
import BrandName from '@/components/custom/logo/BrandName.vue'
import Scrollbar from 'smooth-scrollbar'
import { useSetting } from '@/store/pinia'

export default {
  components: {
    BrandLogo,
    BrandName
  },
  setup() {
    const store = useSetting()
    const sidebarType = computed(() => store.sidebar_type_value)
    const sidebarShow = computed(() => [store.sidebar_show_value])
    const sidebarColor = computed(() => [store.sidebar_color_value])
    const sidebarMenuStyle = computed(() => [store.sidebar_menu_style_value])

    const toggleSidebar = () => {
      document.getElementsByTagName('ASIDE')[0].classList.toggle('sidebar-mini')
    }

    const isSidebarOpen = ref(false)
    const openSidebarOnHover = () => {
      isSidebarOpen.value = true
      document.getElementsByTagName('ASIDE')[0].classList.remove('sidebar-mini')
    }

    const closeSidebarOnLeave = () => {
      isSidebarOpen.value = false
      document.getElementsByTagName('ASIDE')[0].classList.add('sidebar-mini')
    }

    onMounted(() => {
      Scrollbar.init(document.querySelector('.data-scrollbar'), { continuousScrolling: false })
    })

    return { toggleSidebar, sidebarMenuStyle, sidebarType, sidebarColor, sidebarShow, openSidebarOnHover, closeSidebarOnLeave }
  }
}
</script> -->

<!-- <template>
  <aside :class="`sidebar-base ${sidebarColor} ${sidebarMenuStyle} ${sidebarType.join(' ')} ${sidebarShow}`" id="first-tour" data-toggle="main-sidebar" data-sidebar="responsive" @mouseover="openSidebarOnHover" @mouseleave="closeSidebarOnLeave">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
      <router-link :to="{ name: 'default.dashboard' }" class="navbar-brand">
        <brand-logo />
        <h4 class="logo-title" data-setting="app_name">
          <brand-name />
        </h4>
      </router-link>
      <div class="sidebar-toggle" @click="toggleSidebar">
        <i class="icon">
          <svg class="icon-20" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </i>
      </div>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar">
      <! <slot name="profile-card"></slot> -->
<!-- <div class="sidebar-list">
        <slot></slot>
      </div>
    </div>
    <div class="sidebar-footer"></div>
  </aside>
</template> -->

<script>
import { computed, onMounted, ref } from 'vue'
import BrandLogo from '@/components/custom/logo/BrandLogo.vue'
import BrandName from '@/components/custom/logo/BrandName.vue'
import Scrollbar from 'smooth-scrollbar'
import { useSetting } from '@/store/pinia'

export default {
  components: {
    BrandLogo,
    BrandName
  },
  setup() {
    const fallbackRoute = localStorage.getItem('menus') ? JSON.parse(localStorage.getItem('menus'))[0].route : '/'

    const store = useSetting()
    const sidebarType = computed(() => store.sidebar_type_value)
    const sidebarShow = computed(() => [store.sidebar_show_value])
    const sidebarColor = computed(() => [store.sidebar_color_value])
    const sidebarMenuStyle = computed(() => [store.sidebar_menu_style_value])

    const isSidebarMini = ref(false)
    const isSidebarOpen = ref(false)

    // Toggle the sidebar's mini state
    const toggleSidebar = () => {
      isSidebarMini.value = !isSidebarMini.value
      const asideElement = document.querySelector('aside')
      if (isSidebarMini.value) {
        asideElement.classList.add('sidebar-mini')
      } else {
        asideElement.classList.remove('sidebar-mini')
      }
    }

    // Open sidebar on hover
    const openSidebarOnHover = () => {
      isSidebarOpen.value = true
      const asideElement = document.querySelector('aside')
      asideElement.classList.remove('sidebar-mini')
    }

    // Close sidebar on leave but respect the toggle state
    const closeSidebarOnLeave = () => {
      isSidebarOpen.value = false
      const asideElement = document.querySelector('aside')
      if (isSidebarMini.value) {
        asideElement.classList.add('sidebar-mini')
      } else {
        asideElement.classList.remove('sidebar-mini')
      }
    }

    // Initialize the scrollbar
    onMounted(() => {
      Scrollbar.init(document.querySelector('.data-scrollbar'), { continuousScrolling: false })
    })

    return {
      toggleSidebar,
      sidebarMenuStyle,
      sidebarType,
      sidebarColor,
      sidebarShow,
      openSidebarOnHover,
      closeSidebarOnLeave,
      fallbackRoute
    }
  }
}
</script>

<template>
  <aside :class="`sidebar-base ${sidebarColor} ${sidebarMenuStyle} ${sidebarType.join(' ')} ${sidebarShow}`" id="first-tour" data-toggle="main-sidebar" data-sidebar="responsive">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
      <router-link :to="{ name: fallbackRoute }" class="navbar-brand">
        <brand-logo />
        <h4 class="logo-title" data-setting="app_name">
          <brand-name />
        </h4>
      </router-link>
      <div class="sidebar-toggle" @click="toggleSidebar">
        <i class="icon">
          <svg class="icon-20" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4.25 12.2744L19.25 12.2744" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M10.2998 18.2988L4.2498 12.2748L10.2998 6.24976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </i>
      </div>
    </div>
    <div class="sidebar-body pt-0 data-scrollbar" @mouseover="openSidebarOnHover" @mouseleave="closeSidebarOnLeave">
      <div class="sidebar-list">
        <slot></slot>
      </div>
    </div>
    <div class="sidebar-footer"></div>
  </aside>
</template>
