<template>
  <div class="card">
    <div class="card-body p-3">
      <img :src="`${blogImage}`" :alt="id" class="img-fluid rounded" loading="lazy" />
      <div class="text-primary sub-title py-3">{{ blogDate }}</div>
      <router-link to="/blog-details" class="my-3 h5">{{ blogTitle }}</router-link>
      <div class="d-flex align-items-center mt-3">
        <span>{{ blogAuther }}</span>
        <span class="ms-3 text-primary">Admin</span>
      </div>
      <p class="pt-3 mb-4 line-count-3">{{ blogDescription }}</p>
      <router-link to="/blog-details" class="btn btn-primary">Read More</router-link>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    id: { type: String, default: '' },
    blogImage: { type: String, default: '' },
    blogDate: { type: String, default: '' },
    blogAuther: { type: String, default: '' },
    blogTitle: { type: String, default: '' },
    blogDescription: { type: String, default: '' }
  }
}
</script>
