<template>
  <div class="mt-4 mb-3">
    <h6 class="d-inline-block mb-0 me-2">Footer</h6>
    <small class="badge bg-warning rounded-pill">Pro</small>
  </div>
  <div class="d-grid gap-3 grid-cols-3 mb-4">
    <radio-input btn-name="footer_style" id="footer_Sticky" labelClass=" d-block" :default-checked="footerStyle" value="sticky" @onChange="store.footer_style"> Sticky </radio-input>
    <radio-input btn-name="footer_style" id="footer_default" labelClass=" d-block" :default-checked="footerStyle" value="default" @onChange="store.footer_style"> Default </radio-input>
    <radio-input btn-name="footer_style" id="footer_glass" labelClass=" d-block" :default-checked="footerStyle" value="glass" @onChange="store.footer_style"> Glass </radio-input>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { useSetting } from '@/store/pinia'
import RadioInput from '@/components/custom/elements/RadioInput'
const store = useSetting()
const footerStyle = computed(() => store.footer_style_value)
</script>
