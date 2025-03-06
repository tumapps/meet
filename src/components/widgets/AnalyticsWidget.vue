<template>
  <div class="card">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-center gap-3">
        <div :class="`bg-${variant}-subtle p-2 rounded`">
          <!-- <icon-component type="dual-tone" :icon-name="icon" :size="32"></icon-component> -->
          <font-awesome-icon :icon="['fas', icon]" :style="{ color: Iconcolor }" class="anicon" />
        </div>
        <div>
            <h4 class="counter text-center">
            <Vue3autocounter ref="counter" separator="" :duration="3" :startAmount="0" :endAmount="value" />
            </h4>
          <span class="text-dark">{{ description }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Vue3autocounter from 'vue3-autocounter'
export default {
  components: {
    Vue3autocounter
  },
  props: {
    icon: {
      type: String,
      default: 'user'
    },
    description: {
      type: String,
      default: ''
    },
    value: {
      type: [Number, String],
      default: 0
    },
    Iconcolor: {
      type: String,
      default: '#D89837'
    },
    Iconsize: {
      type: String,
      default: '32'
    }
  }
}
</script>
<style scoped>
.anicon {
  font-size: 28px;
}
</style>
