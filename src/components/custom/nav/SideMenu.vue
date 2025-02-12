<template>
  <li :class="navItemClass" v-if="isTag !== 'router-link'">
    <a ref="elem" :class="navLinkClass" aria-current="page" :href="createRoute(route.to)" :to="route" @click.prevent="onClickNav" :aria-expanded="collapseActive">
      <i :class="iconClass" v-if="iconClass" v-b-tooltip.hover.right="title" :title="title">
        <font-awesome-icon :icon="['fas', icon]" />
      </i>
      <i class="sidenav-mini-icon" v-if="miniTitle !== '' && miniTitle !== null" v-b-tooltip.hover.right="title" :title="title"> {{ miniTitle }} </i>
      <span :class="titleClass">
        <slot name="title">
          {{ title }}
        </slot>
      </span>
      <span v-if="staticItem" class="mini-icon">-</span>
      <i class="right-icon" v-if="caretIcon">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </i>
    </a>
    <slot></slot>
  </li>
  <router-link :to="{ name: route.to }" v-else v-slot="{ navigate, isExactActive }">
    <li :class="navItemClass + ' ' + (isExactActive ? 'active' : '')">
      <a ref="elem" :class="navLinkClass + ' ' + (isExactActive && isChild === 'false' ? 'mychild' : isExactActive && isChild !== 'true' ? 'active' : '')"
      aria-current="page" @click="navigate">
        <i :class="iconClass" v-if="iconClass" v-b-tooltip.hover.right="title" :title="title">
          <font-awesome-icon :icon="['fas', icon]" />
        </i>
        <i class="sidenav-mini-icon" v-if="miniTitle !== '' && miniTitle !== null" v-b-tooltip.hover.right="title" :title="title"> {{ miniTitle }} </i>
        <span :class="titleClass">
          <slot name="title">
            {{ title }}
          </slot>
        </span>
        <span v-if="staticItem" class="mini-icon">-</span>
        <i class="right-icon" v-if="caretIcon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </i>
      </a>
      <slot></slot>
    </li>
  </router-link>
</template>
<script setup>
import { ref, watch, computed, defineProps, defineEmits } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  staticItem: { type: Boolean, default: false },
  isChild: { type: String, default: 'false' },
  modelValue: { type: String, default: '' },
  title: { type: String, default: 'Dashboard' },
  miniTitle: { type: String, default: '' },
  icon: { type: String, default: '' },
  caretIcon: { type: Boolean, default: false },
  iconType: { type: String, default: 'dual-tone' },
  iconSize: { type: Number, default: 20 },
  toggleId: { type: String, default: '' },
  route: {
    type: Object,
    default: () => ({ name: 'default' })
  },
  active: { type: Boolean, default: false },
  isTag: { type: String, default: 'a' }
})

const emit = defineEmits(['onClick'])
const router = useRouter()

const navItemClass = ref(['nav-item'])
const navLinkClass = ref(['nav-link'])
const iconClass = ref('icon')
const titleClass = ref('item-name')
const elem = ref()
const collapseActive = ref(false)

const createRoute = (routeName) => {
  try {
    const routeData = router.resolve({ name: routeName })
    return routeData ? routeData.href : '/'
  } catch {
    return false
  }
}

const onClickNav = () => {
  if (props.route.popup !== 'false') {
    if (props.route.target === 'blank') {
      const routeData = router.resolve({ name: props.route.to })
      window.open(routeData.href, '_blank')
    } else {
      router.push({ name: props.route.to })
    }
  } else {
    emit('onClick', props.route.to)
  }
}

if (props.staticItem) {
  navItemClass.value.push('static-item')
  navLinkClass.value.push('static-item disabled text-start')
  iconClass.value = false
  titleClass.value = 'default-icon'
}

const addActiveClass = () => {
  navItemClass.value.push('active')
  navLinkClass.value.push('active')
}

const removeActiveClass = () => {
  navItemClass.value = navItemClass.value.filter((e) => e !== 'active')
  navLinkClass.value = navLinkClass.value.filter((e) => e !== 'active')
}

if (props.active) {
  addActiveClass()
  collapseActive.value = true
} else {
  removeActiveClass()
  collapseActive.value = false
}

const isChild = computed(() => props.isChild)

watch(
  () => props.active,
  (newVal) => {
    if (newVal) {
      addActiveClass()
      collapseActive.value = true
    } else {
      removeActiveClass()
      collapseActive.value = false
    }
  },
  { deep: true }
)

watch(() => props,isChild, (newVal) => {
  if (newVal) {
    navLinkClass.value.push('mychild')
  }
})
</script>
<style scoped>
.mychild {
  color: #d89837 !important;
  font-weight: 800 !important;
}
</style>
