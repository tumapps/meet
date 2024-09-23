<template>
  <!-- Horizontal Menu Start -->
  <nav id="navbar_main" class="mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav mx-md-auto">
    <div class="container-fluid">
      <div class="offcanvas-header px-0">
        <div class="navbar-brand ms-0">
          <BrandLogo :color="true"></BrandLogo>
          <h4 class="logo-title"><brand-name></brand-name></h4>
        </div>
        <button class="btn-close float-end"></button>
      </div>
    </div>
    <!-- container-fluid.// -->
  </nav>
  <!-- Sidebar Menu End -->
</template>
<script setup>
import BrandLogo from '@/components/custom/logo/BrandLogo.vue';
</script>
<style lang="scss" scoped></style>