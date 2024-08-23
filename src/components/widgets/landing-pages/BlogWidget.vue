<template>
  <img :src="`${blogImage}`" :alt="id" class="img-fluid" loading="lazy" />
  <div class="card-body blog-card">
    <p class="sub-title text-primary mb-2">{{ blogText }}</p>
    <a href="./blog-details" class="mt-2 h5 line-count-2">{{ blogTitle }}</a>
    <div class="mt-5">
      <svg class="text-primary" xmlns="http://www.w3.org/2000/svg" width="13" height="12" viewBox="0 0 13 12" fill="none">
        <path d="M6.2108 0.102935L6.22367 1.1237L10.766 1.18087L0.816548 11.1303L1.54744 11.8612L11.4969 1.91176L11.554 6.45402L12.5748 6.46689L12.4957 0.181986L6.2108 0.102935Z" fill="currentColor"></path>
      </svg>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    id: { type: String, default: '' },
    blogTitle: { type: String, default: '' },
    blogImage: { type: String, default: '' },
    blogText: { type: String, default: '' }
  }
}
</script>
