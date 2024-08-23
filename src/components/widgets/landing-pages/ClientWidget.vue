<template>
  <img :src="`${clientImage}`" :alt="alt" loading="lazy" />
</template>

<script>
export default {
  props: {
    clientImage: { type: String, default: '' }
  }
}
</script>
