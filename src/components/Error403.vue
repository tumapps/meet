<template>
  <div class="error-container">
    <div class="error-message">
      <h1>Error 403</h1>
      <p>Access Forbidden. You do not have permission to access this resource.</p>
    </div>
    <div class="error-actions">
      <button @click="$router.go(-1)" class="back-button">Go Back</button>
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
  background: linear-gradient(135deg, #ff7e5f, #feb47b); /* Gradient background */
  color: white;
  font-family: Arial, sans-serif;
  text-align: center;
  padding: 20px;
}

.error-message h1 {
  font-size: 5rem;
  margin: 0;
  color: #fff;
  text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
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
  font-size: 1rem;
  background-color: #ffffff;
  color: #ff7e5f;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.back-button:hover {
  background-color: #ffeedf;
  transform: scale(1.05);
}

.error-actions p {
  margin-top: 10px;
  font-size: 1rem;
  opacity: 0.8;
}
</style>
