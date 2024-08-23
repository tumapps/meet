<template>
  <div>
    <h6 class="d-inline-block mb-3 me-2">Page Transition</h6>
    <small class="badge bg-warning rounded-pill">Pro</small>
  </div>
  <div class="d-grid gap-3 grid-cols-2 mb-4">
    <radio-input btn-name="theme_transition" id="page-animation" labelClass=" d-block" :default-checked="themeTransition" value="theme-with-animation" @onChange="updateRadio"> Animation </radio-input>
    <radio-input btn-name="theme_transition" id="without-page-animation" labelClass=" d-block" :default-checked="themeTransition" value="theme-without-animation" @onChange="updateRadio"> No Animation </radio-input>
  </div>
</template>
<script>
import { computed } from 'vue'

// Pinia Store
import { useSetting } from '@/store/pinia'

import RadioInput from '@/components/custom/elements/RadioInput'
export default {
  name: 'AdproAnimation',
  components: {
    RadioInput
  },
  setup() {
    const store = useSetting()
    const themeTransition = computed(() => store.theme_transition)
    const updateRadio = (value, name) => {
      store[name](value)
    }
    return {
      themeTransition,
      updateRadio
    }
  }
}
</script>
