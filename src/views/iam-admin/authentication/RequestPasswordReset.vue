<script setup>
import { ref, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import createAxiosInstance from '@/api/axios'
import BrandLogo from '@/components/custom/logo/BrandLogo.vue'

const router = useRouter() // Make sure this is inside setup()
const { proxy } = getCurrentInstance()
const email = ref('')
const username = ref('')
const axiosInstance = createAxiosInstance()
const errors = ref({ email: '', username: '' })
const fallbackRoute = localStorage.getItem('menus') ? JSON.parse(localStorage.getItem('menus'))[0].route : ''

const onSubmit = async () => {
  errors.value = { email: '', username: '' } // Reset errors
  try {
    const response = await axiosInstance.post(
      '/v1/auth/password-reset-request',
      {
        email: email.value,
        username: username.value
      },
      {
        headers: {
          'X-Exclude-Interceptor': true
        }
      }
    )

    if (response.data.dataPayload && !response.data.dataPayload.error) {
      proxy.$showAlert({
        title: response.data.toastPayload.toastTheme,
        text: response.data.toastPayload.toastMessage,
        icon: response.data.toastPayload.toastTheme,
        //remove the confirm button and add a timer
        showConfirmButton: false,
        showCancelButton: false,
        timerProgressBar: true,
        willClose: () => {
          // After the alert is dismissed, redirect to the login page
          router.push('/auth/login')
        }
      })
    }
  } catch (error) {
    if (error.response.status !== 422) {
      proxy.$showAlert({
        title: error.response.data.errorPayload.toastTheme,
        text: error.response.data.errorPayload.toastMessage,
        icon: error.response.data.errorPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false
      })
    }

    if (error.response && error.response.status === 422 && error.response.data.errorPayload) {
      const errorDetails = error.response.data.errorPayload.errors
      // console.log("Validation error details:", errorDetails);

      errors.value.email = errorDetails.email || ''
      errors.value.username = errorDetails.username || '' // Add this line to handle username errors
    }
  }
}
</script>

<template>
  <section class="login-content">
    <div class="row m-0 align-items-center bg-white vh-100">
      <div class="col-md-6 d-md-block d-none bg-primary p-0 vh-100 overflow-hidden">
        <img src="@/assets/images/tum.jpg" class="img-fluid gradient-main" alt="images" loading="lazy" />
      </div>
      <div class="col-md-6 p-0">
        <div class="card card-transparent auth-card shadow-none d-flex justify-content-center mb-0">
          <div class="card-body">
            <router-link :to="{ name: fallbackRoute }" class="navbar-brand d-flex align-items-center mb-3">
              <BrandLogo />
              <h4 class="logo-title ms-3 mb-0"><brand-name></brand-name></h4>
            </router-link>
            <h2 class="mb-2">Reset Password</h2>
            <p>Enter your Username, and we'll send you an email with instructions on how to reset your password.</p>
            <form @submit.prevent="onSubmit">
              <div class="row">
                <div class="col-lg-12">
                  <div class="floating-label form-group">
                    <label for="username" class="form-label">Username</label>
                    <input v-model="username" type="text" class="form-control" id="username" aria-describedby="username" placeholder=" " />
                    <div v-if="errors.username" class="error" aria-live="polite">{{ errors.username }}</div>
                  </div>
                </div>
                <!-- <div class="col-lg-12">
                  <div class="floating-label form-group">
                    <label for="email" class="form-label">Email</label>
                    <input v-model="email" type="email" class="form-control" id="email" aria-describedby="email" placeholder=" " />
                    <div v-if="errors.email" class="error" aria-live="polite">{{ errors.email }}</div>
                  </div>
                </div> -->
              </div>
              <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Reset</button>
                <button type="button" class="btn btn-outline-warning" @click="router.push('/auth/login')">Back to Login</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<style scoped>
.error {
  color: red;
  font-size: 0.8rem;
}
</style>
