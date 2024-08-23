<template>
  <h6 class="mb-4">{{ featurTitle }}</h6>
  <p class="mb-4">{{ featurText }}</p>
  <a class="" href="#">Know More</a>
</template>

<script>
export default {
  props: {
    featurTitle: { type: String, default: '' },
    featurText: { type: String, default: '' }
  }
}
</script>
