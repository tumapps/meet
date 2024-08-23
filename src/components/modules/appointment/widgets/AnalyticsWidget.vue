<template>
  <button @click="action">
  <div class="card">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3">
        <div :class="`bg-${variant}-subtle p-2 rounded`">
          <icon-component type="dual-tone" :icon-name="icon" :size="32"></icon-component>
        </div>
        <div>
          <h4 class="counter">
            
          </h4>
          <span class="text-dark">{{ description }}</span>
        </div>
      </div>
    </div>
  </div>
  </button>
</template>

<script setup>
// import Vue3autocounter from 'vue3-autocounter'

// Props
const props = defineProps({
  icon: {
    type: String,
    default: 'document'
  },
  description: {
    type: String,
    default: ''
  },
  value: {
    type: [Number, String],
    default: 0
  },
  variant: {
    type: String,
    default: 'primary'
  }
})

// You can use Vue3autocounter directly in the template
</script>
<style scoped>
button {
  border: none;
  background: none;
  cursor: pointer;
}
</style>
