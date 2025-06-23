<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
import CreateAxiosInstance from '@/api/axios.js'
import UserSettings from '@/components/UserSettings.vue'
import SpaceUpdate from '@/components/modules/appointment/SpaceUpdate.vue'
import { useAuthStore } from '@/store/auth.store.js'

const authStore = useAuthStore()
const axiosInstance = CreateAxiosInstance()
const errors = ref({})
const { proxy } = getCurrentInstance()
const username = ref('')
const password = ref('')
const confirm_password = ref('')
const first_name = ref('')
const middle_name = ref('')
const last_name = ref('')
const mobile_number = ref('')
const email_address = ref('')
const oldPassword = ref('')
const user_id = ref('')
const role = ref('')

onMounted(() => {
  getProfile()
  user_id.value = authStore.getUserId()
  console.log(user_id.value)
  role.value = authStore.getRole()
})

//get user_id from session storage
// const userId = authStore.getUserId()

// const user_id = ref(userId)

const getProfile = async () => {
  try {
    // add userid to request

    const response = await axiosInstance.get('/v1/auth/profile-view')
    username.value = response.data.dataPayload.data.username
    first_name.value = response.data.dataPayload.data.first_name
    middle_name.value = response.data.dataPayload.data.middle_name
    last_name.value = response.data.dataPayload.data.last_name
    mobile_number.value = response.data.dataPayload.data.mobile_number
    email_address.value = response.data.dataPayload.data.email_address
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}
const toastPayload = ref({})

const updateProfile = async () => {
  errors.value = ''
  try {
    const response = await axiosInstance.put('v1/auth/profile-update', {
      username: username.value,
      password: password.value,
      confirm_password: confirm_password.value,
      first_name: first_name.value,
      middle_name: middle_name.value,
      last_name: last_name.value,
      mobile_number: mobile_number.value,
      email_address: email_address.value
    })

    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Updated successfully',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Updated successfully',
        icon: 'success'
      })
    }

    getProfile()
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

const updatePassword = async () => {
  errors.value = ''
  try {
    const response = await axiosInstance.post('v1/auth/update-password', {
      newPassword: password.value,
      confirm_password: confirm_password.value,
      oldPassword: oldPassword.value
    })

    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Updated successfully',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Updated successfully',
        icon: 'success'
      })
    }
    // console.log(response.data);
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

// Toggle visibility
const showOldPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
</script>

<template>
  <div class="bd-example bg-white">
    <nav>
      <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <button class="nav-link active d-flex align-items-center" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="true">Profile</button>
        <button v-if="role === 'user' || role === 'registrar'" class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Settings</button>
        <button class="nav-link" id="nav-password-tab" data-bs-toggle="tab" data-bs-target="#nav-password" type="button" role="tab" aria-controls="nav-password" aria-selected="false">Password</button>
        <button v-if="role === 'user' || role === 'registrar'" class="nav-link" id="nav-office-tab" data-bs-toggle="tab" data-bs-target="#nav-office" type="button" role="tab" aria-controls="nav-office" aria-selected="false">My Office</button>
      </div>
    </nav>
    <div class="tab-content iq-tab-fade-up" id="simple-tab-content">
      <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
        <!-- //account -->
        <b-card class="mb-3 p-3 shadow">
          <b-row>
            <div class="col-xl-12 col-lg-12">
              <div>
                <card-header class="card-header d-flex justify-content-between">
                  <!-- <div class="header-title">
                    <h4 class="card-title">Account Details</h4>
                  </div> -->
                </card-header>
                <b-card-body>
                  <div class="new-user-info pb-5">
                    <form @submit.prevent="updateProfile">
                      <b-row>
                        <b-col md="12" lg="4" class="form-group">
                          <label class="form-label" for="fname">First Name:</label>
                          <input v-model="first_name" type="text" class="form-control" id="fname" placeholder="First Name" />
                          <div v-if="errors.first_name" class="error" aria-live="polite">{{ errors.first_name }}</div>
                        </b-col>
                        <b-col md="12" lg="4" class="form-group">
                          <label class="form-label" for="cname">Middle Name:</label>
                          <input v-model="middle_name" type="text" class="form-control" id="cname" placeholder="Middle Name" />
                          <div v-if="errors.middle_name" class="error" aria-live="polite">{{ errors.middle_name }}</div>
                        </b-col>
                        <b-col md="12" lg="4" class="form-group">
                          <label class="form-label" for="lname">Last Name:</label>
                          <input v-model="last_name" type="text" class="form-control" id="lname" placeholder="Last Name" />
                          <div v-if="errors.last_name" class="error" aria-live="polite">{{ errors.last_name }}</div>
                        </b-col>
                        <b-col md="6" lg="4" class="form-group">
                          <label class="form-label" for="mobno">Mobile Number:</label>
                          <input v-model="mobile_number" type="text" class="form-control" id="mobno" placeholder="Mobile Number" />
                          <div v-if="errors.mobile_number" class="error" aria-live="polite">{{ errors.mobile_number }}</div>
                        </b-col>
                        <b-col md="6" lg="4" class="form-group">
                          <label class="form-label" for="email">Email:</label>
                          <input v-model="email_address" type="email" class="form-control" id="email" placeholder="Email" />
                          <div v-if="errors.email_address" class="error" aria-live="polite">{{ errors.email_address }}</div>
                        </b-col>
                      </b-row>
                      <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                  </div>
                </b-card-body>
              </div>
            </div>
          </b-row>
        </b-card>
      </div>
      <div class="tab-pane fade" id="nav-password" role="tabpanel" aria-labelledby="nav-password-tab">
        <!-- //password -->
        <b-card class="mb-3 p-3 shadow"
          ><b-row class="mt-5">
            <div class="col-xl-12 col-lg-12">
              <div>
                <card-header class="">
                  <div class="header-title">
                    <h4 class="mb-2">Change Password</h4>
                  </div>

                  <div>
                    <form @submit.prevent="updatePassword" class="mt-3">
                      <div class="row">
                        <!-- Old Password -->
                        <b-col md="12" class="form-group">
                          <label class="form-label" for="oldpass">Current Password:</label>
                          <div class="position-relative">
                            <input v-model="oldPassword" :type="showOldPassword ? 'text' : 'password'" class="form-control pe-5" id="oldpass" placeholder="Current Password" />
                            <i class="fas" :class="showOldPassword ? 'fa-eye' : 'fa-eye-slash'" @click="showOldPassword = !showOldPassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer"></i>
                          </div>
                          <div v-if="errors.oldPassword" class="error" aria-live="polite">{{ errors.oldPassword }}</div>
                        </b-col>

                        <!-- New Password -->
                        <b-col md="12" class="form-group">
                          <label class="form-label" for="pass">Password:</label>
                          <div class="position-relative">
                            <input v-model="password" :type="showNewPassword ? 'text' : 'password'" class="form-control pe-5" id="pass" placeholder="New Password" />
                            <i class="fas" :class="showNewPassword ? 'fa-eye' : 'fa-eye-slash'" @click="showNewPassword = !showNewPassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer"></i>
                          </div>
                          <div v-if="errors.newPassword" class="error" aria-live="polite">{{ errors.newPassword }}</div>
                        </b-col>

                        <!-- Confirm Password -->
                        <b-col md="12" class="form-group">
                          <label class="form-label" for="rpass">Repeat Password:</label>
                          <div class="position-relative">
                            <input v-model="confirm_password" :type="showConfirmPassword ? 'text' : 'password'" class="form-control pe-5" id="rpass" placeholder="Repeat Password" />
                            <i class="fas" :class="showConfirmPassword ? 'fa-eye' : 'fa-eye-slash'" @click="showConfirmPassword = !showConfirmPassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer"></i>
                          </div>
                          <div v-if="errors.confirm_password" class="error" aria-live="polite">{{ errors.confirm_password }}</div>
                        </b-col>
                      </div>

                      <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                  </div>
                </card-header>
              </div>
            </div>
          </b-row>
        </b-card>
      </div>
      <div v-if="role === 'user' || role === 'registrar'" class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
        <UserSettings :user_id="user_id" />
      </div>
      <div v-if="role === 'user' || role === 'registrar'" class="tab-pane fade" id="nav-office" role="tabpanel" aria-labelledby="nav-office-tab">
        <SpaceUpdate />
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
