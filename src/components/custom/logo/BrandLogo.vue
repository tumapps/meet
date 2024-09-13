<template>
  <div class="circle-container">
    <img src="@/assets/images/logo.png" alt="Logo">
  </div>
</template>

<script>
export default {
  props: {
    color: { type: Boolean, default: false }
  }
}
</script>


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