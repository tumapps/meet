<script>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useSetting } from '@/store/pinia'
// import { useAuthStore } from '@/store/auth.store.js'
import { useRouter } from 'vue-router'
import AxiosInstance from '@/api/axios'

export default {
  components: {},
  setup(props, { emit }) {
    const axiosInstance = AxiosInstance()

    const store = useSetting()

    const router = useRouter()

    const headerNavbar = computed(() => store.header_navbar_value)

    const fullScreen = ref(false)
    const isHidden = ref(false)

    const openFullScreen = () => {
      if (fullScreen.value) {
        fullScreen.value = false
        document.exitFullscreen()
      } else {
        fullScreen.value = true
        document.documentElement.requestFullscreen()
      }
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

    const logOut = async () => {
      try {
        await axiosInstance.logout()
        router.push({ path: '/auth/login' });
      } catch (error) {
        console.error('Error during logout:', error);
      }
    }

    const navbarHide = computed(() => [store.navbar_show_value])

    const carts = computed(() => store.carts)

    onMounted(() => {
      window.addEventListener('scroll', onscroll)
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', onscroll)
    })

    return {
      headerNavbar,
      openFullScreen,
      fullScreen,
      isHidden,
      carts,
      navbarHide,
      emit,
      logOut
    }
  },
}

</script>

<template>
  <nav :class="`nav navbar navbar-expand-xl navbar-light header-hover-menu iq-navbar ${headerNavbar} ${navbarHide}`" class="bg-warning">
    <div class="container-fluid navbar-inner">
      <slot></slot>
      <div class="d-flex align-items-center">
        <button id="navbar-toggle" class="navbar-toggler" type="button" v-b-toggle.navbarSupportedContent
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon">
            <span class="navbar-toggler-bar bar1 mt-1"></span>
            <span class="navbar-toggler-bar bar2"></span>
            <span class="navbar-toggler-bar bar3"></span>
          </span>
        </button>
      </div>
      <b-collapse class="navbar-collapse" id="navbarSupportedContent" @show="emit('menuOpen')"
        @hide="emit('menuClose')">
        <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-xl-0">
          <slot name="navbar-buttons-start"></slot>
          <li class="nav-item dropdown border-end pe-3 d-none d-xl-block">
            <div class="form-group input-group mb-0 search-input">
              <input type="text" class="form-control" placeholder="Search..." />
              <span class="input-group-text">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round"></circle>
                  <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"></path>
                </svg>
              </span>
            </div>
          </li>
          <li class="nav-item dropdown iq-responsive-menu border-end d-block d-xl-none">
            <div class="btn btn-sm bg-body" id="navbarDropdown-search-11" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <icon-component type="outlined" icon-name="search" :size="20"></icon-component>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown-search-11" style="width: 25rem">
              <li class="px-3 py-0">
                <div class="form-group input-group mb-0">
                  <input type="text" class="form-control" placeholder="Search..." />
                  <span class="input-group-text">
                    <icon-component type="outlined" icon-name="search" :size="20"></icon-component>
                  </span>
                </div>
              </li>
            </ul>
          </li>
          <b-dropdown :aria-labelledby="`dropdownMenu-${id}`"
            class="nav-item dropdown py-0 me-2 d-flex align-items-center" variant="none px-0" no-caret dropleft>
            <template #button-content>
              <b-button variant="primary btn-icon" pill size="sm">
                <span class="btn-inner">
                  <font-awesome-icon :icon="['fas', 'user']" />
                </span>
              </b-button>
            </template>
            <b-dropdown-item variant="none" href="#">Profile</b-dropdown-item>
            <b-dropdown-item variant="none" href="#">Privacy Setting</b-dropdown-item>
            <li>
              <hr class="dropdown-divider" />
            </li>
            <b-dropdown-item variant="none" @click="logOut">Logout</b-dropdown-item>
          </b-dropdown>
          <li class="nav-item iq-full-screen d-none d-xl-block" id="fullscreen-item">
            <a href="#" class="nav-link" id="btnFullscreen">
              <b-button variant="primary btn-icon" pill size="sm" @click="openFullScreen">
                <span class="btn-inner">
                  <svg :class="fullScreen ? 'normal-screen d-none' : 'normal-screen'" width="32" viewBox="0 0 24 24"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.5528 5.99656L13.8595 10.8961" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M14.8016 5.97618L18.5524 5.99629L18.5176 9.96906" stroke="white" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M5.8574 18.896L10.5507 13.9964" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M9.60852 18.9164L5.85775 18.8963L5.89258 14.9235" stroke="white" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <svg :class="fullScreen ? 'full-normal-screen' : 'full-normal-screen d-none'" width="32"
                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.7542 10.1932L18.1867 5.79319" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M17.2976 10.212L13.7547 10.1934L13.7871 6.62518" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.4224 13.5726L5.82149 18.1398" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M6.74391 13.5535L10.4209 13.5723L10.3867 17.2755" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
              </b-button>
            </a>
          </li>
          <slot name="navbar-buttons-end"></slot>
        </ul>
      </b-collapse>
    </div>
  </nav>
</template>


<!-- <template>
  <nav :class="`nav navbar navbar-expand-xl navbar-light header-hover-menu iq-navbar ${headerNavbar} ${navbarHide}`">
    <div class="container-fluid navbar-inner">
      <slot></slot>
      <div class="d-flex align-items-center">
        <button id="navbar-toggle" class="navbar-toggler" type="button" v-b-toggle.navbarSupportedContent
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon">
            <span class="navbar-toggler-bar bar1 mt-1"></span>
            <span class="navbar-toggler-bar bar2"></span>
            <span class="navbar-toggler-bar bar3"></span>
          </span>
        </button>
      </div>
      <b-collapse class="navbar-collapse" id="navbarSupportedContent" @show="emit('menuOpen')"
        @hide="emit('menuClose')">
        <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-xl-0">
          <slot name="navbar-buttons-start"></slot>
          <li class="nav-item dropdown border-end pe-3 d-none d-xl-block">
            <div class="form-group input-group mb-0 search-input">
              <input type="text" class="form-control" placeholder="Search..." />
              <span class="input-group-text">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="11.7669" cy="11.7666" r="8.98856" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round"></circle>
                  <path d="M18.0186 18.4851L21.5426 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"></path>
                </svg>
              </span>
            </div>
          </li>
          <li class="nav-item dropdown iq-responsive-menu border-end d-block d-xl-none">
            <div class="btn btn-sm bg-body" id="navbarDropdown-search-11" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <icon-component type="outlined" icon-name="search" :size="20"></icon-component>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown-search-11" style="width: 25rem">
              <li class="px-3 py-0">
                <div class="form-group input-group mb-0">
                  <input type="text" class="form-control" placeholder="Search..." />
                  <span class="input-group-text">
                    <icon-component type="outlined" icon-name="search" :size="20"></icon-component>
                  </span>
                </div>
              </li>
            </ul>
          </li>
          <b-dropdown :aria-labelledby="`dropdownMenu-${id}`"
            class="nav-item dropdown py-0 me-2 d-flex align-items-center" variant="none px-0" no-caret dropleft>
            <template #button-content>
              <b-button variant="primary btn-icon" pill size="sm">
                <span class="btn-inner">
                  <font-awesome-icon :icon="['fas', 'user']" />
                </span>
              </b-button>
            </template>
            <b-dropdown-item variant="none" href="#">Profile</b-dropdown-item>
            <b-dropdown-item variant="none" href="#">Privacy Setting</b-dropdown-item>
            <li>
              <hr class="dropdown-divider" />
            </li>
            <b-dropdown-item variant="none" @click="logOut">Logout</b-dropdown-item>
          </b-dropdown>
          <li class="nav-item iq-full-screen d-none d-xl-block" id="fullscreen-item">
            <a href="#" class="nav-link" id="btnFullscreen">
              <b-button variant="primary btn-icon" pill size="sm" @click="openFullScreen">
                <span class="btn-inner">
                  <svg :class="fullScreen ? 'normal-screen d-none' : 'normal-screen'" width="32" viewBox="0 0 24 24"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.5528 5.99656L13.8595 10.8961" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M14.8016 5.97618L18.5524 5.99629L18.5176 9.96906" stroke="white" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M5.8574 18.896L10.5507 13.9964" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M9.60852 18.9164L5.85775 18.8963L5.89258 14.9235" stroke="white" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <svg :class="fullScreen ? 'full-normal-screen' : 'full-normal-screen d-none'" width="32"
                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.7542 10.1932L18.1867 5.79319" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M17.2976 10.212L13.7547 10.1934L13.7871 6.62518" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M10.4224 13.5726L5.82149 18.1398" stroke="white" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <path d="M6.74391 13.5535L10.4209 13.5723L10.3867 17.2755" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </span>
              </b-button>
            </a>
          </li>
          <slot name="navbar-buttons-end"></slot>
        </ul>
      </b-collapse>
    </div>
  </nav>
</template>
<script>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useSetting } from '@/store/pinia'
import { useAuthStore } from '@/store/auth.store.js'
import { useRouter } from 'vue-router';
import createAxiosInstance from '@/api/axios.js'





export default {
  components: {},
  setup(props, { emit }) {
    const axiosInstance = createAxiosInstance()

    const store = useSetting()
    
    const router = useRouter()

    const headerNavbar = computed(() => store.header_navbar_value)

    const fullScreen = ref(false)
    const isHidden = ref(false)

    const openFullScreen = () => {
      if (fullScreen.value) {
        fullScreen.value = false
        document.exitFullscreen()
      } else {
        fullScreen.value = true
        document.documentElement.requestFullscreen()
      }
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

    const logOut = sync() => {
      try {
        await this.$logout()
        router.push({ path: '/auth/login' });
      } catch (error) {
        console.error('Error during logout:', error);
      }
      
    }

    const navbarHide = computed(() => [store.navbar_show_value])

    const carts = computed(() => store.carts)

    onMounted(() => {
      window.addEventListener('scroll', onscroll())
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', onscroll())
    })
    return {
      headerNavbar,
      openFullScreen,
      // fontSize,
      fullScreen,
      isHidden,
      carts,
      navbarHide,
      emit,
      logOut
    }
  },
  props: {
    fullsidebar: { type: Boolean, default: false }
  }
}
</script> -->
