<template>
  <div>
    <h6 class="mt-4 mb-3">Active Menu Style</h6>
    <div class="d-grid gap-3 grid-cols-3 mb-4">
      <radio-input btn-name="sidebar_menu_style" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Rounded One Side" class-name="text-center" id="navs-rounded" :default-checked="sidebarMenuStyle" value="sidebar-default navs-rounded" @onChange="store.sidebar_menu_style">
        <img src="@/assets/images/settings/dark/06.png" alt="mini" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/06.png" alt="mini" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
      <radio-input btn-name="sidebar_menu_style" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Rounded All" class-name="text-center" id="navs-rounded-all" :default-checked="sidebarMenuStyle" value="sidebar-default navs-rounded-all" @onChange="store.sidebar_menu_style">
        <img src="@/assets/images/settings/dark/07.png" alt="hover" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/07.png" alt="hover" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
      <radio-input btn-name="sidebar_menu_style" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Pill One Side" class-name="text-center" id="navs-pill" :default-checked="sidebarMenuStyle" value="sidebar-default navs-pill" @onChange="store.sidebar_menu_style">
        <img src="@/assets/images/settings/dark/09.png" alt="boxed" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/09.png" alt="boxed" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
      <radio-input btn-name="sidebar_menu_style" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Pill All" class-name="text-center" id="navs-pill" :default-checked="sidebarMenuStyle" value="sidebar-default navs-pill-all" @onChange="store.sidebar_menu_style">
        <img src="@/assets/images/settings/dark/08.png" alt="boxed" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/08.png" alt="boxed" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
      <radio-input btn-name="sidebar_menu_style" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Left Bordered" class-name="text-center" id="left-bordered" :default-checked="sidebarMenuStyle" value="left-bordered" @onChange="store.sidebar_menu_style">
        <img src="@/assets/images/settings/dark/05.png" alt="boxed" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/05.png" alt="boxed" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
      <radio-input btn-name="sidebar_menu_style" :img-component="true" labelClass="p-0 d-block overflow-hidden" label-title="Full Width" class-name="text-center" id="navs-full-width" :default-checked="sidebarMenuStyle" value="sidebar-default navs-full-width" @onChange="store.sidebar_menu_style">
        <img src="@/assets/images/settings/dark/15.png" alt="boxed" class="mode dark-img img-fluid" width="200" height="200" loading="lazy" />
        <img src="@/assets/images/settings/light/15.png" alt="boxed" class="mode light-img img-fluid" width="200" height="200" loading="lazy" />
      </radio-input>
    </div>
  </div>
</template>
<script>
import { computed } from 'vue'

// Pinia Store
import { useSetting } from '@/store/pinia'

import RadioInput from '@/components/custom/elements/RadioInput'
export default {
  name: 'MenuActiveStyle',
  components: {
    RadioInput
  },
  setup() {
    const store = useSetting()
    const sidebarMenuStyle = computed(() => store.sidebar_menu_style_value)

    return {
      sidebarMenuStyle,
      store
    }
  }
}
</script>
