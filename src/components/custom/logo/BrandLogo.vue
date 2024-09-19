<script setup>
import logo from '@/assets/images/logo.png';

// const logo = new URL("@/assets/images/logo.png", import.meta.url).href;

</script>
<template>
  <div class="circle-container">
    <img :src="logo" alt="Logo">
  </div>
</template>

<style scoped>
.circle-container {
    width: 60px; /* Adjust as needed */
    height: 60px; /* Must be equal to width */
    border: 2px solid #D89837; /* Primary border color */
    background-color: #ffffff; /* Background color */
    border-radius: 50%; /* Makes the shape circular */
    display: flex;
    align-items: center;
    justify-content: center;
}

.circle-container img {
    max-width: 90%; /* Ensure the image fits within the circle */
    height: auto;
}
</style>