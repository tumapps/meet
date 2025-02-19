<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

const goBack = () => {
  router.go(-1) // Navigates back to the previous page
}
</script>

<template>
  <!-- Page Content -->
  <div class="d-flex justify-content-center align-items-center text-center">
    <div>
      <div class="bg-body-extra-light rounded">
        <div class="py-5 mt-5">
          <!-- Error Header -->
          <h1 class="display-1 fw-bolder text-modern">500</h1>
          <h2 class="h3 fw-normal text-muted mb-5 mt-5">We are sorry but our server encountered an internal error.</h2>
        </div>
      </div>
      <div class="content content-full text-muted fs-sm fw-medium mt-3">
        <RouterLink @click.prevent="goBack" class="link-fx"> ← Go Back </RouterLink>
      </div>
    </div>
  </div>
  <!-- END Page Content -->
</template>
