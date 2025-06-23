<script setup>
import { ref, getCurrentInstance } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AxiosInstance from '@/api/axios'

const axiosInstance = AxiosInstance()
const route = useRoute()

const router = useRouter()
const { proxy } = getCurrentInstance()
const password = ref('')
const repeatPassword = ref('')

const errors = ref({ password: '', repeatPassword: '', general: '' })
const isLoading = ref(false)
const token = route.query.token

// console.log(token);

const resetPassword = async () => {
  errors.value = { password: '', repeatPassword: '', general: '' }

  try {
    isLoading.value = true

    const response = await axiosInstance.post('/v1/auth/reset-password', {
      token: token,
      password: password.value,
      confirm_password: repeatPassword.value
    })

    if (response.data.toastPayload) {
      proxy.$showAlert({
        title: response.data.toastPayload.toastTheme,
        text: response.data.toastPayload.toastMessage,
        icon: response.data.toastPayload.toastTheme,
        timer: 2000,
        showConfirmButton: false,
        showCancelButton: false
      })
      router.push('/')
    }
  } catch (error) {
    // console.log(error)

    if (error.response && error.response.status === 422 && error.response.data.errorPayload) {
      const errorDetails = error.response.data.errorPayload.errors
      errors.value.password = errorDetails.password || ''
      errors.value.repeatPassword = errorDetails.repeatPassword || ''
      errors.value.general = errorDetails.message || ''
    }
    if (error.response.status !== 422) {
      proxy.$showAlert({
        title: error.response.data.errorPayload.toastTheme,
        text: error.response.data.errorPayload.toastMessage,
        icon: error.response.data.errorPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false
      })
    }
  } finally {
    isLoading.value = false
  }
}
const showPassword = ref(false)
const showRepeatPassword = ref(false)
</script>

<template>
  <!-- Password Reset 1 - Bootstrap Brain Component -->
  <div class="d-flex vh-100 maindiv">
    <div class="container my-auto">
      <div class="row justify-content-md-center align-items-center">
        <div class="col-12 col-md-11 col-lg-8 col-xl-7 col-xxl-6">
          <div class="bg- p-4 p-md-5 rounded shadow-sm bg-white">
            <div class="row gy-3 mb-5">
              <div class="col-12">
                <div class="text-center">
                  <img src="@/assets/images/logo.png" alt="tum-logo" />
                </div>
              </div>
              <div class="col-12 mt-5">
                <h2 class="fs-6 fw-bolder text-center text-secondary m-0 px-md-5">Reset Password</h2>
              </div>
            </div>
            <form @submit.prevent="resetPassword">
              <div class="row gy-3 gy-md-4 overflow-hidden">
                <!-- Password Field -->
                <div class="col-12">
                  <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input :type="showPassword ? 'text' : 'password'" v-model="password" class="form-control" name="password" id="password" />
                    <span class="input-group-text" style="cursor: pointer" @click="showPassword = !showPassword">
                      <i :class="showPassword ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
                    </span>
                  </div>
                  <div v-if="errors.password" class="error" aria-live="polite">{{ errors.password }}</div>
                </div>

                <!-- Repeat Password Field -->
                <div class="col-12">
                  <label for="repeatPassword" class="form-label">Repeat Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input :type="showRepeatPassword ? 'text' : 'password'" v-model="repeatPassword" class="form-control" name="repeatPassword" id="repeatPassword" />
                    <span class="input-group-text" style="cursor: pointer" @click="showRepeatPassword = !showRepeatPassword">
                      <i :class="showRepeatPassword ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
                    </span>
                  </div>
                  <div v-if="errors.repeatPassword" class="error" aria-live="polite">{{ errors.repeatPassword }}</div>
                  <div v-else-if="errors.general" class="error" aria-live="polite">{{ errors.general }}</div>
                </div>

                <!-- Submit Button -->
                <div class="col-12">
                  <div class="d-grid">
                    <button value="submit" class="btn btn-primary btn-lg" type="submit">Reset Password</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.error {
  color: red;
  font-size: 1.1em;
}

.maindiv {
  background-color: #f0f6f2;
  /* from src/assets/images/tum.jpg */
  background-image: url('@/assets/images/tum.jpg');
  background-size: cover;
  background-position: center;
}
</style>
