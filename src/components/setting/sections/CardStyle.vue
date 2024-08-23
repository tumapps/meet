<template>
  <div class="mt-4 mb-3">
    <h6 class="d-inline-block mb-0 me-2">Card Style</h6>
    <small class="badge bg-warning rounded-pill">Pro</small>
  </div>
  <div class="d-grid gap-3 grid-cols-2 mb-3">
    <radio-input btn-name="card_style" id="card-default" labelClass=" d-block" :default-checked="cardStyle" value="card-default" @onChange="store.card_style"> Default </radio-input>
    <radio-input btn-name="card_style" id="card-glass" labelClass=" d-block" :default-checked="cardStyle" value="card-glass" @onChange="store.card_style"> Glass </radio-input>
  </div>
  <radio-input btn-name="card_style" id="card-transparent" labelClass=" d-block" :default-checked="cardStyle" value="card-transparent" @onChange="store.card_style"> Transparent </radio-input>
</template>
<script>
import { computed } from 'vue'

// Pinia Store
import { useSetting } from '@/store/pinia'

import RadioInput from '@/components/custom/elements/RadioInput'
export default {
  name: 'CardStyle',
  components: {
    RadioInput
  },
  setup() {
    const store = useSetting()
    const cardStyle = computed(() => store.card_style_value)
    return {
      cardStyle,
      store
    }
  }
}
</script>
