<template>
  <h6 class="mb-3 d-inline-block">App Name</h6>
  <span data-bs-toggle="tooltip" data-bs-placement="right" title="Edit the App Name with your business name to familiarize your audience. ">
    <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  </span>
  <div class="d-grid gap-3 grid-cols-1 mb-4">
    <div class="form-group mb-0">
      <input type="text" maxlength="10" name="app_name" class="form-control" v-model="inputValue" placeholder="Enter App Name" @input="appNameAction" />
    </div>
  </div>
</template>
<script>
import { ref } from 'vue'
import { useSetting } from '@/store/pinia'
export default {
  name: 'AdproAppName',
  setup() {
    const store = useSetting()

    const inputValue = ref(store.app_name_value)

    const appNameAction = () => {
      store.app_name(inputValue.value)
    }

    return {
      inputValue,
      appNameAction
    }
  }
}
</script>
