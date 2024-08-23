<template>
  <img :src="`${blogImage}`" :alt="id" class="img-fluid rounded object-cover iq-blog-experience mt-3 mb-3 w-100" loading="lazy" />
  <span class="text-primary sub-title mt-2">{{ blogDate }}</span>
  <div class="d-flex gap-3 mt-3">
    <router-link :to="{ name: 'blog.detail' }" class="iq-blog-adventure fs-6">{{ blogAuther }}</router-link>
    <span> | </span>
    <router-link :to="{ name: 'blog.detail' }" class="iq-blog-adventure text-primary fs-6">Jenny Wilson</router-link>
  </div>
</template>

<script>
export default {
  props: {
    id: { type: String, default: '' },
    blogImage: { type: String, default: '' },
    blogDate: { type: String, default: '' },
    blogTitle: { type: String, default: '' },
    blogAuther: { type: String, default: '' }
  }
}
</script>
