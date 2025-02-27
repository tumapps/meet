<template>
  <b-card class="card-block card-stretch card-height">
    <div class="mb-2 d-flex justify-content-between align-items-center">
      <span class="text-dark">{{ title }}</span>
      <b-badge tag="a" variant=" bg-primary-subtle"  pill @click="navigateToRoute"> {{ buttonText }} </b-badge>
    </div>
    <h2 class="counter"><Vue3autocounter ref="counter" separator="" :prefix="prefix" :duration="3" :startAmount="0" :endAmount="amount" /></h2>
    <small>{{ description }}</small>
  </b-card>
</template>

<script setup>
import { defineProps } from 'vue'
import Vue3autocounter from 'vue3-autocounter'
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  buttonText: {
    type: String,
    default: ''
  },
  buttonLink: {
    type: String,
    default: 'javascript:void(0);'
  },
  amount: {
    type: [String, Number],
    default: '0'
  },
  description: {
    type: String,
    default: ''
  },
  prefix: {
    type: String,
    default: ''
  }
})

const navigateToRoute = () => {
  if (props.buttonLink) {
    router.push({ name: props.buttonLink })
  }
}
</script>

<style lang="scss" scoped>
buttonNavLink {
  cursor: pointer !important;
}
</style>
