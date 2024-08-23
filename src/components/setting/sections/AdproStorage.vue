<template>
  <div>
    <h6 class="mb-3 d-inline-block">Storage</h6>
    <span data-bs-toggle="tooltip" data-bs-placement="right" title="You can save the setting changes you made for your project on either Local storage, Session storage.">
      <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </span>
  </div>
  <div class="d-grid gap-3 grid-cols-3 mb-4">
    <radio-input btn-name="saveLocal" id="save-localstorage" labelClass=" d-block" :default-checked="saveLocal" value="localStorage" @onChange="updateRadio"> Local </radio-input>
    <radio-input btn-name="saveLocal" id="save-sessionstorage" labelClass=" d-block" :default-checked="saveLocal" value="sessionStorage" @onChange="updateRadio"> Session </radio-input>
    <radio-input btn-name="saveLocal" id="save-none" labelClass=" d-block" :default-checked="saveLocal" value="null" @onChange="updateRadio"> None </radio-input>
  </div>
</template>
<script>
import { computed } from 'vue'
import { useSetting } from '@/store/pinia'
import RadioInput from '@/components/custom/elements/RadioInput'
export default {
  name: 'AdproStorage',
  components: {
    RadioInput
  },
  setup() {
    const store = useSetting()
    const saveLocal = computed(() => store.default_saveLocal)
    const updateRadio = (value) => {
      store[value]
    }
    return {
      saveLocal,
      updateRadio
    }
  }
}
</script>
