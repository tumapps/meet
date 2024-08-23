<template>
  <div>
    <h6 class="mb-3">Menu Style</h6>
    <div class="d-grid gap-3 grid-cols-4 mb-4">
      <checkbox-input btn-name="sidebar_type" :imgComponent="true" label-title="Mini" id="sidebar-mini" label-class="p-0" :default-checked="sidebarType" value="sidebar-mini" @onChange="sidebar_update">
        <img src="@/assets/images/settings/dark/03.png" alt="mini" className="mode dark-img img-fluid" loading="lazy" />
        <img src="@/assets/images/settings/light/03.png" alt="mini" className="mode light-img img-fluid" loading="lazy" />
      </checkbox-input>
      <checkbox-input btn-name="sidebar_type" :imgComponent="true" label-title="Hover" id="sidebar-hover" label-class="p-0" :default-checked="sidebarType" value="sidebar-hover" @onChange="sidebar_update">
        <img src="@/assets/images/settings/dark/04.png" alt="hover" className="mode dark-img img-fluid" loading="lazy" />
        <img src="@/assets/images/settings/light/04.png" alt="hover" className="mode light-img img-fluid" loading="lazy" />
      </checkbox-input>
      <checkbox-input btn-name="sidebar_type" :imgComponent="true" label-title="Boxed" id="sidebar-boxed" label-class="p-0" :default-checked="sidebarType" value="sidebar-boxed" @onChange="sidebar_update">
        <img src="@/assets/images/settings/dark/05.png" alt="boxed" className="mode dark-img img-fluid" loading="lazy" />
        <img src="@/assets/images/settings/light/05.png" alt="boxed" className="mode light-img img-fluid" loading="lazy" />
      </checkbox-input>
      <checkbox-input btn-name="sidebar_type" :imgComponent="true" label-title="Soft" id="sidebar-soft" label-class="p-0" :default-checked="sidebarType" value="sidebar-soft" @onChange="sidebar_update">
        <img src="@/assets/images/settings/dark/05.png" alt="soft" className="mode dark-img img-fluid" loading="lazy" />
        <img src="@/assets/images/settings/light/05.png" alt="soft" className="mode light-img img-fluid" loading="lazy" />
      </checkbox-input>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { useSetting } from '@/store/pinia'
import CheckboxInput from '@/components/custom/elements/CheckboxInput'

const store = useSetting()
const sidebarType = computed(() => store.sidebar_type_value)

const sidebar_update = (value) => {
  store.sidebar_type(value)
}
</script>
