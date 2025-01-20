<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useSetting } from '@/store/pinia'
import DefaultNavbar from '../custom/navbar/DefaultNavbar.vue'
import RadioInput from '@/components/custom/elements/RadioInput'
import BrandLogo from '@/components/custom/logo/BrandLogo.vue'
import BrandName from '@/components/custom/logo/BrandName.vue'
import { useRoute } from 'vue-router'
const store = useSetting()

const fontSize = computed(() => store.theme_font_size_value)

// const themeSchemeDirection = computed(() => store.theme_scheme_direction_value)

const route = useRoute()

const responsiveNav = ref(false)

const showOffcanvas = () => {
  responsiveNav.value = true
}

const hideOffcanvas = () => {
  responsiveNav.value = false
}

const onscroll = () => {
  const yOffset = document.documentElement.scrollTop
  const navbar = document.querySelector('.navs-sticky')
  if (navbar !== null) {
    if (yOffset >= 100) {
      navbar.classList.add('menu-sticky')
    } else {
      navbar.classList.remove('menu-sticky')
    }
  }
}

const toggleSidebar = () => {
  document.getElementsByTagName('ASIDE')[0].classList.toggle('sidebar-mini')
}

onMounted(() => {
  window.addEventListener('scroll', onscroll())
  console.log('mounted the navv bar hubsjhdjbhj', fontSize)
})

onUnmounted(() => {
  window.removeEventListener('scroll', onscroll())
})
//i removed this to prevent sidebar open on mouse enter since the toggle button was listening for the event ..the sidebar was opening and closing on mouse enter and leave is restricted to the aside .sidebar-body element only change at will
// the default behavior of the sidebar is to open on mouse enter and close on mouse leave was on the whole <aside> element
// Select the first ASIDE element
// const asideElement = document.getElementsByTagName('ASIDE')[0]

// Function to add the 'sidebar-mini' class when hovering
// asideElement.addEventListener('mouseleave', () => {
//   asideElement.classList.add('sidebar-mini')
// })

// Function to remove the 'sidebar-mini' class when mouse leaves
// asideElement.addEventListener('mouseenter', () => {
//   asideElement.classList.remove('sidebar-mini')
// })
</script>
<template>
  <default-navbar @menuOpen="showOffcanvas" @menuClose="hideOffcanvas">
    <template v-slot:navbar-buttons-start>
      <li class="nav-item dropdown me-0 me-xl-3">
        <div class="d-flex align-items-center mr-2 iq-font-style" role="group" aria-label="First group">
          <radio-input btn-name="theme_font_size" id="font-size-sm" label-class="border-0 btn-icon btn-sm" :default-checked="fontSize" value="theme-fs-sm" @onChange="store.theme_font_size">
            <span class="mb-0 h6" style="color: inherit !important">A</span>
          </radio-input>
          <radio-input btn-name="theme_font_size" id="font-size-md" label-class="border-0 btn-icon" :default-checked="fontSize" value="theme-fs-md" @onChange="store.theme_font_size">
            <span class="mb-0 h4" style="color: inherit !important">A</span>
          </radio-input>
          <radio-input btn-name="theme_font_size" id="font-size-lg" label-class="border-0 btn-icon" :default-checked="fontSize" value="theme-fs-lg" @onChange="store.theme_font_size">
            <span class="mb-0 h2" style="color: inherit !important">A</span>
          </radio-input>
        </div>
      </li>
    </template>
    <a href="#" class="navbar-brand">
      <BrandLogo />
      <h4 class="logo-title d-block d-xl-none" data-setting="app_name"><brand-name /></h4>
    </a>
    <div class="sidebar-toggle" data-toggle="sidebar" data-active="true" @click="toggleSidebar()">
      <i class="icon d-flex">
        <svg width="20px" viewBox="0 0 24 24">
          <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" />
        </svg>
      </i>
    </div>
    <div class="d-flex align-items-center justify-content-between product-offcanvas">
      <div class="breadcrumb-title border-end me-3 pe-3 d-none d-xl-block">
        <small class="mb-0 text-capitalize">{{ route.meta.name }}</small>
      </div>
      <slot></slot>
    </div>
  </default-navbar>
</template>
<style>
.iq-product-menu-responsive .offcanvas-header {
  display: none;
}

#offcanvass {
  z-index: 100 !important;
}
</style>
