<script setup>
import { ref, getCurrentInstance, defineExpose, defineEmits } from 'vue'
import AxiosInstance from '@/api/axios'
import FlatPickr from 'vue-flatpickr-component'

const { proxy } = getCurrentInstance()
const axiosInstance = AxiosInstance()
const errors = ref({})
const isloading = ref(false)
const emits = defineEmits(['NewEvent'])
const InitialeventDetails = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: ''
})

const eventDetails = ref({ ...InitialeventDetails.value })

const newEvent = ref(null)
const toastPayload = ref('')

const resetFormData = () => {
  eventDetails.value = { ...InitialeventDetails.value }
}

const resetErrors = () => {
  errors.value = {}
}

function handleClose() {
  resetErrors()
  resetFormData()
}

const showModal = () => {
  console.log('close modal', newEvent.value)

  if (newEvent.value) {
    newEvent.value.show()
  }
}

const closeModal = () => {
  console.log('close modal', newEvent.value)
  if (newEvent.value) {
    newEvent.value.hide() // Call the hide() method
  }
}
const config = {
  dateFormat: 'Y-m-d',
  altInput: true,
  altFormat: 'F j, Y',
  minDate: 'today'
}

const config2 = {
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i',
  time_24hr: true
}

const saveEvent = async (isUpdate = false) => {
  try {
    isloading.value = true
    const method = isUpdate ? 'put' : 'post' // toggle method based on isUpdate flag
    const url = isUpdate ? `v1/scheduler/events/${eventDetails.value.id}` : 'v1/scheduler/events'

    // Send the appropriate request based on isUpdate flag
    const response = await axiosInstance[method](url, eventDetails.value)
    // Get events after the operation
    //replace with emits to get all events
    emits('NewEvent')

//close 
closeModal()
    handleClose()

    // Close the modal
    // Handle toast notification response
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload

      proxy.$showAlert({
        title: toastPayload.value.toastMessage,
        icon: toastPayload.value.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
      })
    } else {
      proxy.$showToast({
        title: 'success',
        icon: 'success'
      })
    }
  } catch (error) {
    // Handle error if the request fails
    if (error.response && error.response.data.errorPayload) {
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  } finally {
    isloading.value = false
  }
}

defineExpose({
  showModal
})
</script>
<template>
  <b-modal ref="newEvent" title="Add Event" class="p-4 custom-modal-body" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleClose">
    <b-row>
      <b-col md="12">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">Title</label>
          <input type="text" v-model="eventDetails.title" class="form-control" id="name" />
        </div>
        <div v-if="errors.title" class="error" aria-live="polite">{{ errors.title }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="levelDropdown" class="form-label">Start Date </label>
          <flat-pickr v-model="eventDetails.start_date" class="form-control" :config="config" id="startDatePicker" />
        </div>
        <div v-if="errors.date" class="error" aria-live="polite">{{ errors.start_date }}</div>
      </b-col>
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="levelDropdown" class="form-label">End Date </label>
          <flat-pickr v-model="eventDetails.end_date" class="form-control" :config="config" id="startDatePicker" />
        </div>
        <div v-if="errors.end_date" class="error" aria-live="polite">{{ errors.end_date }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="6">
        <div class="mb-3">
          <label for="startTimePicker" class="form-label">Start Time</label>
          <flat-pickr v-model="eventDetails.start_time" class="form-control" :config="config2" id="startTimePicker" />
        </div>
        <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
      </b-col>
      <b-col md="6">
        <div class="mb-3">
          <label for="endTimePicker" class="form-label">End Time</label>
          <flat-pickr v-model="eventDetails.end_time" class="form-control" :config="config2" id="endTimePicker" />
        </div>
        <div v-if="errors.end_time" class="error" aria-live="polite">{{ errors.end_time }}</div>
      </b-col>
    </b-row>
    <b-row>
      <!-- //text area for description -->
      <!-- default row height is 12 -->
      <b-col md="12">
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea v-model="eventDetails.description" class="form-control" id="description" rows="5" />
        </div>
        <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
      </b-col>
    </b-row>
    <div class="d-flex justify-content-center mt-5">
      <b-button v-if="isloading === false" @click="saveEvent()" variant="primary">Submit</b-button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
        submitting...
      </button>
    </div>
  </b-modal>
</template>
