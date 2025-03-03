<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const gohome = () => {
  router.push('/auth/login')
  localStorage.clear()
}
</script>
<template>
  <div class="error-container">
    <div class="error-message">
      <h1>Error 403</h1>
      <p>Access Forbidden. You do not have permission to access this resource.</p>
    </div>
    <div class="error-actions">
      <button @click="gohome" class="back-button">Go Back</button>
      <p>Contact the administrator if the issue persists.</p>
    </div>
  </div>
</template>

<style scoped>
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background: #fff; /* Gradient background */
  color: #000;
  font-family: Arial, sans-serif;
  text-align: center;
  padding: 20px;
}

.error-message h1 {
  font-size: 5rem;
  margin: 0;
  color: #000;
}

.error-message p {
  font-size: 1.5rem;
  margin-top: 10px;
  margin-bottom: 20px;
}

.error-actions {
  margin-top: 20px;
}

.back-button {
  padding: 10px 20px;
  font-size: 1.3rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  background-color: #fff;
  color: #097B3E;
}

.back-button:hover {
  background-color: #097B3E;
  transform: scale(1.05);
  color: #fff
}

.error-actions p {
  margin-top: 10px;
  font-size: 1rem;
  opacity: 0.8;
}
</style>
