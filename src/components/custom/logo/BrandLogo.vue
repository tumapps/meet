<!-- <script setup>
// import logo from '@/assets/images/logo.png';

const logo = new URL("@/assets/images/logo.png", import.meta.url).href;

</script>
<template>
  <div class="mylogo">
    <img :src="logo" alt="Logo">
  </div>
</template>

<style scoped>
.mylogo-container {
    /* width: 60px; Adjust as needed */
    /* height: 60px; Must be equal to width */
    border: 2px solid #D89837; /* Primary border color */
    background-color: #ffffff; /* Background color */
    border-radius: 50%; /* Makes the shape circular */
    display: flex;
    align-items: center;
    justify-content: center;
}

.mylogo-container img {
    max-width: 90%; /* Ensure the image fits within the circle */
    height: auto;
}
</style> -->

<template>
  <div class="circle-container">
    <img src="@/assets/images/logo.png" alt="Logo">
  </div>
</template>

<script>

</script>


<style scoped>
.circle-container {
    width: 60px !important; /* Adjust as needed */
    height: 60px !important; /* Must be equal to width */
    border: 2px solid #D89837 !important; /* Primary border color */
    background-color: #ffffff !important; /* Background color */
    border-radius: 50% !important; /* Makes the shape circular */
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.circle-container img {
    max-width: 90%; /* Ensure the image fits within the circle */
    height: auto;
}
</style>