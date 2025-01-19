<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
// import AxiosInstance from '@/api/axios'
import { useAuthStore } from '@/store/auth.store.js'
import Fullcalendar from '@/components/custom/calendar/FullCalender.vue'
import AxiosInstance from '@/api/axios'
import FlatPickr from 'vue-flatpickr-component'

const toastPayload = ref('')
const { proxy } = getCurrentInstance()
const axiosInstance = AxiosInstance()
const errors = ref('')
const newEvent = ref(null)

// const { proxy } = getCurrentInstance()
// const axiosInstance = AxiosInstance()

const authStore = useAuthStore()
const username = ref('')
//change the events owner
const role = ref('')
role.value = authStore.getRole()
const dashType = 'Registrar'

const InitialeventDetails = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  start_time: '',
  end_time: ''
})

const eventDetails = ref({ ...InitialeventDetails.value })

const resetFormData = () => {
  eventDetails.value = { ...InitialeventDetails.value }
}

const resetErrors = () => {
  errors.value = {}
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

function handleClose() {
  resetErrors()
  resetFormData()
}

const saveEvent = async (isUpdate = false) => {
  try {
    const method = isUpdate ? 'put' : 'post' // toggle method based on isUpdate flag
    const url = isUpdate ? `v1/scheduler/events/${eventDetails.value.id}` : 'v1/scheduler/events'

    // Send the appropriate request based on isUpdate flag
    const response = await axiosInstance[method](url, eventDetails.value)

    // Get events after the operation
    closeModal()

    // Close the modal
    // Handle toast notification response
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload

      proxy.$showAlert({
        title: toastPayload.value.toastMessage,
        icon: toastPayload.value.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 2000,
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
  }
}

onMounted(() => {
  username.value = localStorage.getItem('user.username')
})
// data table end
</script>
<template>
  <b-row>
    <b-col lg="12" md="12">
      <div class="headerimage">
        <!-- <img src="@/assets/images/tum.jpg" alt="header" class="w-100 h-25" /> -->
      </div>
      <b-card>
        <div class="d-flex flex-wrap align-items-center justify-content-between">
          <div class="d-flex flex-wrap align-items-center">
            <div class="profile-img position-relative me-3 mb-3 mb-lg-0 profile-logo profile-logo1">
              <img src="@/assets/images/avatars/01.png" alt="User-Profile" class="img-fluid rounded-pill avatar-100" />
            </div>
            <div class="d-flex flex-wrap align-items-center">
              <h4 class="me-2 h4 mb-0">{{ username }}</h4>
              <!-- <span class="text-muted"> - Director</span> -->
            </div>
          </div>
        </div>
      </b-card>
    </b-col>
  </b-row>
  <b-row>
    <b-col lg="12">
      <div class="full-calendar-container h-75">
        <!-- Ensures FullCalendar takes full height -->
        <!-- pass dashType as a prop to the Fullcalendar component -->
        <Fullcalendar :dashType="dashType" @createEvent="showModal" />
      </div>
    </b-col>
  </b-row>

  <b-modal ref="newEvent" title="Add Event" class="modal-fullscreen my-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleClose">
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
      <b-col md="12">
        <div class="mb-3">
          <label for="levelDropdown" class="form-label">Start Date </label>
          <flat-pickr v-model="eventDetails.start_date" class="form-control" :config="config" id="startDatePicker" />
        </div>
        <div v-if="errors.date" class="error" aria-live="polite">{{ errors.start_date }}</div>
      </b-col>
      <b-col md="12">
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
      <b-button @click="saveEvent()" variant="primary">Create</b-button>
    </div>
  </b-modal>
</template>

<style scoped>
.full-calendar-container {
  width: 100%;
}

.full-calendar-container .fc {
  width: 100%;
  height: 50%;
  /* Ensures calendar fills the container */
}

h1 {
  font-size: 2rem;
}

.profile-img img {
  width: 100px;
  height: 100px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.headerimage {
  height: 20vh;
  background-image: url(@/assets/images/tum.jpg);
}

.headerimage img {
  height: 100%;
  width: 100%;
  object-fit: cover;
}

b-card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}
</style>
