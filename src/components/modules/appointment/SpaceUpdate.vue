<script setup>
import { onMounted, ref, getCurrentInstance } from 'vue'
import AxiosInstance from '@/api/axios'
import FlatPickr from 'vue-flatpickr-component'
import { useAuthStore } from '@/store/auth.store.js'

const authStore = useAuthStore()

const toastPayload = ref({})

const { proxy } = getCurrentInstance()
const axiosInstance = AxiosInstance()

const errors = ref({})
const isLoading = ref(false)

const user_id = ref('')
user_id.value = authStore.getUserId()

const config2 = {
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i',
  time_24hr: true,
  minuteIncrement: 5
}

const InitialSpaceDetails = {
  name: '',
  level_id: '',
  opening_time: '',
  closing_time: '',
  capacity: ''
}

const SpaceDetails = ref({ ...InitialSpaceDetails })

//get space details
const getSpace = async (id) => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/space/${id}`)
    SpaceDetails.value = response.data.dataPayload.data
    console.log(SpaceDetails.value)
  } catch (error) {
    // console.error(error);
    const errorMessage = error?.response?.data?.errorPayload?.errors?.message || error?.response?.data?.errorPayload?.message || error?.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

//update space details
const updateSpaceDetails = async (id) => {
  try {
    isLoading.value = true
    // console.log(availabilityDetails.value);

    // Make the API call to update availability details
    const response = await axiosInstance.put(`v1/scheduler/space/${id}`, SpaceDetails.value)

    // Show success toast notification
    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      //close the modal
      toastPayload.value = response.data.toastPayload
      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Updated successfully',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showAlert({
        title: 'Updated successfully',
        icon: 'success',
        showCancelButton: false,
        showConfirmButton: false,
        timer: 4000
      })
    }
  } catch (error) {
    // Check if error.response exists to avoid TypeError
    if (error?.response && error.response?.data && error.response?.data.errorPayload) {
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
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  getSpace(user_id.value)
})
</script>
<template>
  <b-card class="mb-3 p-3 shadow">
    <b-row>
      <b-col md="12">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">Name</label>
          <input type="text" v-model="SpaceDetails.name" class="form-control" id="name" />
        </div>
        <div v-if="errors.name" class="error" aria-live="polite">{{ errors.name }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="6">
        <div class="mb-3">
          <label for="startTimePicker" class="form-label">Opening Hours</label>
          <flat-pickr v-model="SpaceDetails.opening_time" class="form-control" :config="config2" id="startTimePicker" />
        </div>
        <div v-if="errors.opening_time" class="error" aria-live="polite">{{ errors.opening_time }}</div>
      </b-col>
      <b-col md="6">
        <div class="mb-3">
          <label for="endTimePicker" class="form-label">Closing Hours</label>
          <flat-pickr v-model="SpaceDetails.closing_time" class="form-control" :config="config2" id="endTimePicker" />
        </div>
        <div v-if="errors.closing_time" class="error" aria-live="polite">{{ errors.closing_time }}</div>
      </b-col>
    </b-row>
    <b-row md="6">
      <b-col md="12">
        <div class="mb-3">
          <label for="capacity" class="form-label">Capacity</label>
          <input type="text" v-model="SpaceDetails.capacity" class="form-control" id="capacity" />
        </div>
        <div v-if="errors.capacity" class="error" aria-live="polite">{{ errors.capacity }}</div>
      </b-col>
    </b-row>
    <div class="d-flex justify-content-center mt-5">
      <b-button v-if="isLoading === false" @click="updateSpaceDetails(SpaceDetails.id)" variant="primary">Update</b-button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Updating...
      </button>
    </div>
  </b-card>
</template>
