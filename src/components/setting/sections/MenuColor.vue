<template>
  <h6 class="mt-4 mb-3">Menu Color</h6>
  <div class="d-grid gap-3 grid-cols-3 mb-3">
    <radio-input btn-name="sidebar_color" labelClass="d-flex align-items-center bg-transparent gap-1" id="sidebar-white" :default-checked="sidebarcolor" value="sidebar-white" @onChange="store.sidebar_color">
      <i class="text-white">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" viewBox="0 0 24 24" fill="currentColor">
          <circle cx="12" cy="12" r="8" fill="currentColor" stroke="black" stroke-width="3"></circle>
        </svg>
      </i>
      <span> Default </span>
    </radio-input>
    <radio-input btn-name="sidebar_color" labelClass="d-flex align-items-center bg-transparent gap-1" id="sidebar-dark" :default-checked="sidebarcolor" value="sidebar-dark" @onChange="store.sidebar_color">
      <i class="text-dark">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" viewBox="0 0 24 24" fill="currentColor">
          <circle cx="12" cy="12" r="8" fill="currentColor"></circle>
        </svg>
      </i>
      <span> Dark </span>
    </radio-input>
    <radio-input btn-name="sidebar_color" labelClass="d-flex align-items-center bg-transparent gap-1" id="sidebar-color" :default-checked="sidebarcolor" value="sidebar-color" @onChange="store.sidebar_color">
      <icon-component type="solid" :size="18" icon-name="circle" class="text-primary"></icon-component>
      <span> Color </span>
    </radio-input>
  </div>
  <div class="d-grid gap-3 grid-cols-2 mb-4">
    <radio-input btn-name="sidebar_color" labelClass="d-flex align-items-center bg-transparent gap-1" :imgComponent="true" id="sidebar-transparent" :default-checked="sidebarcolor" value="sidebar-transparent" @onChange="store.sidebar_color">
      <i class="text-dark">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" viewBox="0 0 24 24" fill="currentColor">
          <circle cx="12" cy="12" r="8" fill="#F5F6FA" stroke="black" stroke-width="3"></circle>
        </svg>
      </i>
      <span> Transparent </span>
    </radio-input>
    <radio-input btn-name="sidebar_color" labelClass="d-flex align-items-center bg-transparent gap-1" :imgComponent="true" id="sidebar-glass" :default-checked="sidebarcolor" value="sidebar-glass" @onChange="store.sidebar_color">
      <i class="text-dark">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" viewBox="0 0 24 24" fill="currentColor">
          <circle cx="12" cy="12" r="8" fill="#F5F6FA" stroke="black" stroke-width="3"></circle>
        </svg>
      </i>
      <span> Glass </span>
    </radio-input>
  </div>
</template>
<script>
import { computed } from 'vue'

// Pinia Store
import { useSetting } from '@/store/pinia'

import RadioInput from '@/components/custom/elements/RadioInput'
export default {
  name: 'MenuColor',
  components: {
    RadioInput
  },
  setup() {
    const store = useSetting()
    const sidebarcolor = computed(() => store.sidebar_color_value)

    return {
      sidebarcolor,
      store
    }
  }
}
</script>
