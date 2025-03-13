<!-- a simple page for entering the 2FA code and submitting it to the server with a countdown timer of 5 minutes after a success response from backend. -->

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const code = ref('')
const router = useRouter()

onMounted(() => {
  // check if the user is already authenticated
  if (localStorage.getItem('isAuthenticated') === 'true') {
    router.push('/dashboard')
  }
})

const submitCode = () => {
  // submit the code to the server
  // if the response is successful, set the isAuthenticated to true and redirect to the dashboard
  // else, show an error message
}
</script>
<template>
  <div>
    <h1>2FA</h1>
    <p>Enter the 2FA code sent to your email</p>
    <input type="text" v-model="code" placeholder="Enter the code" />
    <button @click="submitCode">Submit</button>
  </div>
</template>
