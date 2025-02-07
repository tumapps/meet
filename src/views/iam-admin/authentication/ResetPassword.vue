<script setup>
import { ref, getCurrentInstance } from 'vue'
import { useRoute } from 'vue-router'
import AxiosInstance from '@/api/axios'

const axiosInstance = AxiosInstance()
const router = useRoute()
const { proxy } = getCurrentInstance()
const password = ref('')
const repeatPassword = ref('')

const errors = ref({ password: '', repeatPassword: '', general: '' })
const isLoading = ref(false)
const token = router.query.token

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

    if (response.data.dataPayload && !response.data.dataPayload.error) {
      proxy.$showAlert({
        title: 'success',
        text: 'Password reset successfully!',
        icon: 'success',
        timer: 5000,
        showConfirmButton: false,
        showCancelButton: false
      })
      router.push('/auth/login')
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
</script>

<template>
  <!-- Password Reset 1 - Bootstrap Brain Component -->
  <div class="d-flex vh-100">
    <div class="container my-auto">
      <div class="row justify-content-md-center align-items-center">
        <div class="col-12 col-md-11 col-lg-8 col-xl-7 col-xxl-6">
          <div class="bg- p-4 p-md-5 rounded shadow-sm">
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
                <div class="col-12">
                  <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input v-model="password" type="password" class="form-control" name="password" id="password" />
                  </div>
                  <div v-if="errors.password" class="error" aria-live="polite">{{ errors.password }}</div>
                </div>
                <div class="col-12">
                  <label for="repeatPassword" class="form-label">Repeat Password <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input v-model="repeatPassword" type="password" class="form-control" name="repeatPassword" id="repeatPassword" />
                  </div>
                  <div v-if="errors.repeatPassword" class="error" aria-live="polite">{{ errors.repeatPassword }}</div>
                  <div v-else-if="errors.general" class="error" aria-live="polite">{{ errors.general }}</div>
                </div>
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
</style>
