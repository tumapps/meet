<template>
  <div v-for="item in 5" :key="item">
    <RatingStarWidget v-if="rating >= item" fill="true" />
    <RatingStarWidget v-else />
  </div>
</template>

<script>
import RatingStarWidget from './RatingStarWidget.vue'

export default {
  components: {
    RatingStarWidget
  },
  props: {
    rating: {
      type: Number,
      default: 0
    }
  }
}
</script>
