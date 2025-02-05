<script setup>
import { ref, getCurrentInstance } from 'vue'
import AxiosInstance from '@/api/axios'

// Define reactive state for account and personal information
const axiosInstance = AxiosInstance()
const { proxy, emit } = getCurrentInstance()
const errors = ref({})

// modal close and open

// modal close and open
const showModal = ref(false)

//watch modal close and clear data

// Toggle modal visibility function
const toggleModal = () => {
  resetformData()

  showModal.value = !showModal.value
}

// Expose toggleModal so it can be accessed in the parent component
// eslint-disable-next-line no-undef
defineExpose({
  toggleModal
})

const InitialformData = {
  username: '',
  password: '',
  confirm_password: '',
  first_name: '',
  middle_name: '',
  last_name: '',
  mobile_number: '',
  email_address: ''
}

const formData = ref({ ...InitialformData })

function resetformData() {
  formData.value = { ...InitialformData }
}

const resetErrors = () => {
  errors.value = {}
  //clear form data
}

const toastPayload = ref(null) // Define toastPayload ref

// State to track form submission
const submitted = ref(false)

const submitForm = async () => {
  //clear errors
  errors.value = {}
  submitted.value = true
  try {
    // Start loader if needed
    const response = await axiosInstance.post('/v1/auth/register', formData.value)

    toggleModal()
    // Toast notification handling
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Created successfully',
        icon: toastPayload.value.toastTheme || 'success'
      })
      emit('user-created')
      console.log('emits emitted')
    }

    // Close modal after successful submission
    showModal.value = false
    // Reset errors
    errors.value = {}
    // Reset form data
    formData.value = ref('')

    // close the modal
  } catch (error) {
    // console.error(error);
    errors.value = error.response?.data?.errorPayload?.errors

    //if errors are empty then check error message
    if (Object.keys(errors.value).length === 0) {
      const errorMessage = error.response?.data?.errorPayload?.message
      proxy.$showToast({
        title: errorMessage,
        text: errorMessage,
        icon: 'error'
      })
    }
  } finally {
    submitted.value = false
  }
}
</script>
<template>
  <b-modal v-model="showModal" no-close-on-backdrop id="addUserModal" title="Add User" hide-footer size="xl" centered body-class="p-4 custom-modal-body" @hide="resetErrors">
    <b-container class="p-3">
      <!-- Container with padding -->
      <h3 class="mb-4">Account Information</h3>
      <form @submit.prevent="submitForm">
        <b-row>
          <!-- Email -->
          <b-col md="12" lg="6">
            <b-form-group label="Email*" label-class="mb-2">
              <b-form-input type="email" placeholder="Email address" v-model="formData.email_address" class="mb-3" />
              <div v-if="errors.email_address" class="errors">{{ errors.email_address }}</div>
            </b-form-group>
          </b-col>

          <!-- Username -->
          <b-col md="12" lg="6">
            <b-form-group label="Username*" label-class="mb-2">
              <b-form-input type="text" placeholder="Username" v-model="formData.username" class="mb-3" />
              <div v-if="errors.username" class="errors">{{ errors.username }}</div>
            </b-form-group>
          </b-col>
        </b-row>
        <!-- 
        <b-row>
          <b-col md="12" lg="6">
            <b-form-group label="Password*" label-class="mb-2">
              <b-form-input type="password" placeholder="Password" v-model="formData.password" required class="mb-3" />
              <div v-if="errors.password" class="errors">{{ errors.password }}</div>
            </b-form-group>
          </b-col>

          <b-col md="12" lg="6">
            <b-form-group label="Confirm Password*" label-class="mb-2">
              <b-form-input type="password" placeholder="Confirm Password" v-model="formData.confirm_password" required class="mb-4" />
              <div v-if="errors.confirm_password" class="errors">{{ errors.confirm_password }}</div>
            </b-form-group>
          </b-col>
        </b-row> -->

        <h3 class="mb-4">Personal Information</h3>

        <b-row>
          <!-- First Name -->
          <b-col md="12" lg="6">
            <b-form-group label="First Name*" label-class="mb-2">
              <b-form-input type="text" placeholder="First Name" v-model="formData.first_name" class="mb-3" />
              <div v-if="errors.first_name" class="errors">{{ errors.first_name }}</div>
            </b-form-group>
          </b-col>

          <!-- Middle Name -->
          <b-col md="12" lg="6">
            <b-form-group label="Middle Name" label-class="mb-2">
              <b-form-input type="text" placeholder="Middle Name" v-model="formData.middle_name" class="mb-3" />
              <div v-if="errors.middle_name" class="errors">{{ errors.middle_name }}</div>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <!-- Last Name -->
          <b-col md="12" lg="6">
            <b-form-group label="Last Name*" label-class="mb-2">
              <b-form-input type="text" placeholder="Last Name" v-model="formData.last_name" class="mb-3" />
              <div v-if="errors.last_name" class="errors">{{ errors.last_name }}</div>
            </b-form-group>
          </b-col>

          <!-- Contact Number -->
          <b-col md="12" lg="6">
            <b-form-group label="Contact Number*" label-class="mb-2">
              <b-form-input type="text" placeholder="Contact Number" v-model="formData.mobile_number" class="mb-4" />
              <div v-if="errors.mobile_number" class="errors">{{ errors.mobile_number }}</div>
            </b-form-group>
          </b-col>
        </b-row>

        <div class="text-center mt-4">
          <button v-if="!submitted" type="submit" class="btn btn-primary">Submit</button>
          <button v-else class="btn btn-primary" disabled>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Submitting...
          </button>
        </div>
      </form>
    </b-container>
  </b-modal>
</template>

<style scoped>
.errors {
  color: red;
  font-size: 1rem;
}
</style>
