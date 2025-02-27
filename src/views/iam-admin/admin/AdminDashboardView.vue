<script setup>
import { ref } from 'vue'
import Analytica from '@/components/widgets/AnalyticsWidget.vue'

const Meetings = ref('Meetings')
const buttonText = ref('View All')
const buttonLink = ref('appointments')
const amount = ref(88)
const description = ref('Total meetings')
</script>
<template>
  <h1>admin dashboard</h1>
  <Analytica :title="Meetings" :buttonText="buttonText" :buttonLink="buttonLink" :amount="amount" :description="description"  />
</template>
