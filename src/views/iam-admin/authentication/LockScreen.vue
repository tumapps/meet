<script setup>
import { ref, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth.store.js'
import createAxiosInstance from '@/api/axios'
import { useMenuStore } from '@/store/menuStore'

const menuStore = useMenuStore()
const isLoading = ref(false)
const password = ref('')
const errors = ref({ password: '', general: '' })
const authStore = useAuthStore()
const router = useRouter()
const axiosInstance = createAxiosInstance(router, authStore)
const { proxy } = getCurrentInstance()
const username = localStorage.getItem('user.username')

//function to go to first page

function goToFirstMenu() {
  menuStore.navigateToFirstMenu(router)
}

const logout = async () => {
  try {
    await axiosInstance.logout()
    router.push('/auth/login')
  } catch (error) {
    console.error('Error during logout:', error)
  }
}
const onSubmit = async () => {
  errors.value = { username: '', password: '', general: '' }

  try {
    isLoading.value = true

    const response = await axiosInstance.post('/v1/auth/login', {
      username,
      password: password.value
    })

    if (response.data.dataPayload && !response.data.dataPayload.error) {
      //set cookie
      const setCookieHeader = response.headers['set-cookie']
      // //console.log('Cookie received:', setCookieHeader);
      // set the httponly cookie
      document.cookie = setCookieHeader
      localStorage.setItem('loggedIn', true)

      proxy.$showToast({
        title: 'success',
        text: 'You have successfully logged in!',
        icon: 'success'
      })

      authStore.setToken(response.data.dataPayload.data.token, response.data.dataPayload.data.username)

      // router.push({ name: 'dashboard' })
      goToFirstMenu()
    } else {
      proxy.$showToast({
        title: 'An error occurred ',
        text: 'Ooops! an error has occured while logging in!',
        icon: 'error'
      })
    }
  } catch (error) {
    //console.log('Error:', error)
    proxy.$showToast({
      title: 'An error occurred ',
      text: 'Ooops! an error has occured while logging in!',
      icon: 'error'
    })

    if (error.response && error.response.status === 422 && error.response.data.errorPayload) {
      const errorDetails = error.response.data.errorPayload.errors
      // //console.log("Validation error details:", errorDetails);
      errors.value.password = errorDetails.password || ''
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <section class="backimg">
    <b-row class="align-items-center justify-content-center vh-100 w-100">
      <b-col cols="10" lg="6">
        <b-card class="text-center p-5 rounded-3 bg-aliceblue" style="min-height: 400px">
          <!-- Added padding and min-height -->
          <img src="@/assets/images/avatars/01.png" alt="User-Profile" class="theme-color-default-img img-fluid avatar avatar-50 avatar-rounded" loading="lazy" />
          <h4 class="m-4 m">Hi ! {{ username }}</h4>
          <p>Your session is locked. Please enter your password to continue.</p>
          <div class="form-group me-3">
            <!-- <label class="form-label" for="lock-pass">Password</label> -->
            <input type="password" class="form-control mb-0" id="password" v-model="password" placeholder="Enter password" aria-label="Password" autocomplete="off" />
            <div v-if="errors.password" class="error" aria-live="polite">{{ errors.password }}</div>
            <!-- Access errors.value -->
          </div>
          <div class="d-flex w-100 justify-content-center gap-5">
            <button class="btn btn-outline-primary" @click="onSubmit">Login</button>
            <button class="btn btn-outline-warning" @click="logout">go Back</button>
          </div>
        </b-card>
      </b-col>
    </b-row>
  </section>
</template>

<style lang="scss" scoped>
.backimg {
  background-image: url('@/assets/images/tum.jpg');
  background-size: cover;
  background-repeat: no-repeat;
  min-height: 100vh;
  /* background-color: black !important; */
}

.logout-btn {
  color: #d89837; /* Custom text color */
  font-weight: bold; /* Makes text bold */
  cursor: pointer; /* Pointer effect on hover */
  display: inline-flex; /* Aligns text and icon properly */
  align-items: center; /* Vertically aligns text and icon */
  gap: 5px; /* Adds space between text and arrow */
}

.logout-btn:hover {
  color: #b9782c; /* Darker shade on hover */
}
</style>
