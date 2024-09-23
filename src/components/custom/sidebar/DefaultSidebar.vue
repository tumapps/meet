<script>
import { computed, onMounted } from 'vue'
import BrandLogo from '@/components/custom/logo/BrandLogo.vue';
import BrandName from '@/components/custom/logo/BrandName.vue';
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

    onMounted(() => {
      Scrollbar.init(document.querySelector('.data-scrollbar'), { continuousScrolling: false })
    })

    return { toggleSidebar, sidebarMenuStyle, sidebarType, sidebarColor, sidebarShow }
  }
}
</script>

<template>
  <aside :class="`sidebar-base ${sidebarColor} ${sidebarMenuStyle} ${sidebarType.join(' ')} ${sidebarShow}`" id="first-tour" data-toggle="main-sidebar" data-sidebar="responsive">
    <div class="sidebar-header d-flex align-items-center justify-content-start">
      <router-link :to="{ name: 'default.dashboard' }" class="navbar-brand">
        <brand-logo/>
        <h4 class="logo-title" data-setting="app_name">
          <brand-name/>
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
      <slot name="profile-card"></slot>
      <div class="sidebar-list">
        <slot></slot>
      </div>
    </div>
    <div class="sidebar-footer"></div>
  </aside>
</template>
