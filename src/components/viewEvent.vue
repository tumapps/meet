<script setup>
import { ref, defineProps, defineEmits, defineExpose, getCurrentInstance, watch } from 'vue'
import AxiosInstance from '@/api/axios'
import FlatPickr from 'vue-flatpickr-component'

const axiosInstance = AxiosInstance()
const emits = defineEmits(['getEvents'])
const { proxy } = getCurrentInstance()

const props = defineProps({
  event_id: {
    type: Number,
    required: true
  }
})

const event_id = ref(props.event_id) // Set initial value

watch(
  () => props.event_id,
  (newEventId) => {
    console.log('New event_id:', newEventId)
    event_id.value = newEventId // Ensure it updates
  },
  { immediate: true }
)

const errors = ref({})
const isloading = ref(false)
const recordStatus = ref({ label: 'ACTIVE' })
const config = {
  dateFormat: 'Y-m-d',
  altInput: true,
  altFormat: 'F j, Y',
  minDate: 'today',
  disable: [
    function (date) {
      return date.getDay() === 6 || date.getDay() === 0
    }
  ]
}

const config2 = {
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i',
  time_24hr: true
}

const eventDetails = ref({
  title: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: '',
  description: ''
})

const editevent = ref(null)

const show = async (newEventId) => {
  if (newEventId) {
    event_id.value = newEventId // Update event_id directly
  }

  console.log('Opening modal with event_id:', event_id.value) // Debugging

  if (!event_id.value) {
    console.error('Error: event_id is null or undefined')
    return
  }

  await getEvent() // Fetch data before opening the modal
  editevent.value?.show()
}

const handleClose = () => {
  console.log('Modal closed')
  eventDetails.value = {
    title: '',
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    description: ''
  }
  errors.value = {}
}

const toastPayload = ref({})

const getEvent = async () => {
  try {
    console.log('Fetching event:', event_id.value) // Debugging
    const response = await axiosInstance.get(`v1/scheduler/events/${event_id.value}`)
    eventDetails.value = response.data.dataPayload.data
    recordStatus.value = eventDetails.value.recordStatus
  } catch (error) {
    console.error('Error fetching event:', error)
    editevent.value?.hide()
    const errorMessage = error?.response?.data?.errorPayload?.errors?.message

    proxy.$showToast({
      title: errorMessage,
      text: errorMessage,
      icon: 'error'
    })
  }
}
const saveEvent = async () => {
  try {
    isloading.value = true

    // Send the appropriate request based on isUpdate flag
    const response = await axiosInstance.put(`v1/scheduler/events/${eventDetails.value.id}`, eventDetails.value)
    // emit the getEvents event to update the events list
    emits('getEvents')
    // Close the modal
    editevent.value.hide()
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

defineExpose({ show })
</script>
<template>
  <b-modal ref="editevent" title="Edit Event" class="p-4 custom-modal-body" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleClose">
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
        <div v-if="errors.event_date" class="error" aria-live="polite">{{ errors.event_date }}</div>
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
      <b-col md="5">
        <div class="mb-3">
          <label for="endTimePicker" class="form-label">End Time</label>
          <flat-pickr v-model="eventDetails.end_time" class="form-control" :config="config2" id="endTimePicker" />
        </div>
        <div v-if="errors.end_time" class="error" aria-live="polite">{{ errors.end_time }}</div>
      </b-col>
      <!-- //description textbox   -->
      <b-col md="12">
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea v-model="eventDetails.description" class="form-control" id="description" rows="5" />
        </div>
        <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
      </b-col>
    </b-row>
    <div class="d-flex justify-content-center">
      <b-button v-if="isloading === false" @click="saveEvent()" variant="primary" :disabled="recordStatus.label !== 'ACTIVE'">Update</b-button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
        submitting...
      </button>
    </div>
  </b-modal>
</template>
