<template>
  <div class="d-flex justify-content-between align-items-center">
    <h5 class="mt-4 mb-3">Menu Hide</h5>
    <div class="form-check form-switch">
      <checkbox-input btn-name="sidebar_show" type="switch" label-title="Mini" label-class="overflow-hidden p-0" id="sidebar-hide" :value="store.setting.sidebar_show.value == 'sidebar' ? 'sidebar-none' : 'sidebar'" @onChange="store.sidebar_show"> </checkbox-input>
    </div>
  </div>
</template>
<script setup>
import { useSetting } from '@/store/pinia'
import CheckboxInput from '@/components/custom/elements/CheckboxInput'
const store = useSetting()
</script>
