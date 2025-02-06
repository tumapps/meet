<script setup>
import { ref, onMounted, getCurrentInstance, onUnmounted, watch } from 'vue'
import AxiosInstance from '@/api/axios'
import { useRoute } from 'vue-router'

const route = useRoute()
const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()
// Reactive data
const meetingDetails = ref({})
const isSubmitting = ref(false)
const responseMessage = ref('')
// const route = useRoute();

//get meeting id from route
// const meetingId = route.params.meetingId

const fetchMeetingDetails = async (id) => {
  try {
    const response = await axiosInstance.get(`/v1/scheduler/appointments/${id}`)
    meetingDetails.value = response.data.dataPayload.data
  } catch (error) {
    console.error('Error fetching meeting details:', error)
    responseMessage.value = 'Failed to load meeting details.'
  }
}
const meetingId = route.params.meetingId
const attendeeId = route.params.attendeeId
const answer = ref({
  feedback: '',
  decline_reason: ''
})
const handleResponse = async () => {
  isSubmitting.value = true
  try {
    const response = await axiosInstance.post(`/v1/scheduler/confirm-attendance/${meetingId}/${attendeeId}`, answer.value)

    proxy.$showAlert({
      title: response.data.toastPayload.toastTheme,
      text: response.data.toastPayload.toastMessage,
      icon: response.data.toastPayload.toastTheme,
      showCancelButton: false,
      showConfirmButton: false
    })
  } catch (error) {
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

    if (errorMessage) {
      proxy.$showAlert({
        title: error.response.data.errorPayload?.toastTheme || 'Error',
        text: error.response.data.errorPayload?.toastMessage || errorMessage,
        icon: error.response.data.errorPayload?.toastTheme || 'error',
        showCancelButton: false,
        showConfirmButton: false
      })
    }
  } finally {
    isSubmitting.value = false
  }
}

// Fetch meeting details on page load
// onMounted(() => {
//   fetchMeetingDetails(1)
// })
// Utility function for formatting dates
// const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString()

// console.log("id", id);
const confirmResponse = (scene) => {
  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: `You are about to ${scene} your invitation to this meeting. This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        if (scene === 'Accept') {
          answer.value.feedback = 1
          handleResponse()
        } else {
          // Pop up to ask for reason, and reason is required
          proxy
            .$showAlert({
              title: 'Reason for Declining',
              text: 'Please provide a reason for declining this invitation.',
              input: 'text',
              inputPlaceholder: 'Enter your reason here...',
              inputValidator: (value) => {
                return !value ? 'Reason is required!' : null
              }
            })
            .then((reason) => {
              if (reason.value) {
                answer.value.feedback = 0
                answer.value.decline_reason = reason.value
                handleResponse()
              }
            })
        }
      }
    })
}

// on close clear the data from the meetingDetails when the component is unmounted
onUnmounted(() => {
  meetingDetails.value = {}
})
watch(
  () => route.params.meetingId,
  (newId, oldId) => {
    console.log(`Route changed: ${oldId} → ${newId}`)
    if (newId) {
      fetchMeetingDetails(newId)
    }
  }
)
onMounted(() => {
  const meetingId = route.params.meetingId // Use `meetingId` as per your route definition
  console.log('Route params:', route.params) // Debugging route parameters
  console.log('Extracted meetingId:', meetingId) // Ensure `meetingId` is not undefined
  if (meetingId) {
    fetchMeetingDetails(meetingId)
  } else {
    responseMessage.value = 'Meeting not found'
  }
})
</script>
<!-- <template>
  <section
    :style="{
      // backgroundImage: `url(${require('@/assets/images/tum.jpg')})`,
      backgroundSize: 'cover',
      backgroundRepeat: 'no-repeat',
      backgroundPosition: 'center'
    }">
    <div class="d-flex justify-content-center align-items-center vh-100">
   
      <div class="card shadow-lg p-5" style="max-width: 600px; width: 100%; min-height: 500px">
        <div class="text-center">

          <img src="/logo.ico" alt="Logo" class="mb-4" style="max-width: 150px" />
          <h3 class="mb-4 bg-primary" style="font-family: 'arial', sans-serif; color: #fff">Attendance Confirmation</h3>
        </div>

  
        <div class="card-header">
          <h3 class="card-title">{{ meetingDetails.title }}</h3>
          <p class="mb-0"><strong>Organized by:</strong>{{ meetingDetails.organizer }}</p>
        </div>
        <div class="card-body d-flex flex-column">
          <div>
            <p><strong>Date:</strong> {{ formatDate(meetingDetails.appointment_date) }}</p>
            <p><strong>Time:</strong> {{ meetingDetails.start_time }} to {{ meetingDetails.end_time }}</p>
            <p><strong>Description:</strong> {{ meetingDetails.description }}</p>
            <p><strong>Location:</strong> {{ meetingDetails.space }}</p>
          </div>
          <div class="mt-auto text-center pt-3">
            <button class="btn btn-success mx-5" :disabled="isSubmitting" @click="confirmResponse(1)">Confirm</button>
            <button class="btn btn-danger mx-5" :disabled="isSubmitting" @click="confirmResponse(0)">Decline</button>
          </div>
          <p v-if="responseMessage" class="mt-3 text-center">{{ responseMessage }}</p>
        </div>
      </div>
    </div>
  </section>
</template> -->

<!-- <template>
  <section
    :style="{
      background: 'linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url(/assets/images/meeting-bg.jpg)',
      backgroundSize: 'cover',
      backgroundRepeat: 'no-repeat',
      backgroundPosition: 'center'
    }">
    <div class="d-flex justify-content-center align-items-center vh-100">
      
      <div class="card shadow-lg p-5 rounded" style="max-width: 600px; width: 100%; min-height: 500px; background: #fff">

        <div class="text-center mb-4">
          <img src="/logo.ico" alt="Company Logo" class="mb-3" style="max-width: 120px" />
          <h2 style="font-family: 'Arial', sans-serif; font-weight: bold; color: #003366">Meeting Attendance Confirmation</h2>
          <p style="color: #666">Kindly confirm your attendance for the upcoming meeting by selecting an option below.</p>
        </div>


        <div class="card-header bg-light rounded mb-3">
          <h4 class="card-title text-primary">{{ meetingDetails.title }}</h4>
          <p><strong>Organized by:</strong> {{ meetingDetails.contact_name }}</p>
        </div>
        <div class="card-body">
          <ul class="list-unstyled">
            <li><strong>Date:</strong> {{ meetingDetails.appointment_date}}</li>
            <li><strong>Time:</strong> {{ meetingDetails.start_time }} to {{ meetingDetails.end_time }}</li>
            <li><strong>Location:</strong> {{ meetingDetails.space }}</li>
            <li><strong>Agenda:</strong> {{ meetingDetails.description }}</li>
          </ul>
        </div>

       
        <div class="mt-4 text-center">
          <p class="mb-4 text-secondary">Please confirm or decline your attendance below.</p>
          <button class="btn btn-success mx-3" :disabled="isSubmitting" @click="confirmResponse('confirm')"><i class="fas fa-check-circle"></i> Confirm Attendance</button>
          <button class="btn btn-danger mx-3" :disabled="isSubmitting" @click="confirmResponse('decline')"><i class="fas fa-times-circle"></i> Decline Attendance</button>
        </div>

     
        <div v-if="responseMessage" class="mt-4 text-center">
          <p :class="{ 'text-success': responseMessage.includes('confirmed'), 'text-danger': responseMessage.includes('declined') }">
            {{ responseMessage }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template> -->

<template>
  <section
    :style="{
      background: 'linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.95)), url(/assets/images/meeting-bg.jpg)',
      backgroundSize: 'cover',
      backgroundRepeat: 'no-repeat',
      backgroundPosition: 'center'
    }">
    <div class="d-flex justify-content-center align-items-center vh-100">
      <!-- Outer Card -->
      <div class="card shadow-lg p-5 rounded" style="max-width: 800px; width: 100%; min-height: 550px; background: #f5f5f5">
        <!-- Header Section -->
        <div class="text-center mb-4">
          <img src="/logo.ico" alt="Company Logo" class="mb-3" style="max-width: 180px" />
          <h2 style="font-family: 'Arial', sans-serif; font-weight: bold; color: #003366">Attendance Confirmation Request</h2>
          <p style="color: #666">
            You have been requested to be present at the following appointment, organized by <strong>{{ meetingDetails.contact_name }}</strong
            >, alongside <strong>{{ meetingDetails.bookedFor }}</strong
            >.
          </p>
        </div>

        <!-- Meeting Details -->
        <div class="card-header bg-light rounded mb-3">
          <h4 class="card-title mb-3" style="text-decoration: underline; text-transform: uppercase">RE: {{ meetingDetails.subject }}</h4>
          <!-- <p>
            <strong>Booking Organizer:</strong> {{ meetingDetails.organizer }}<br />
            <strong>For:</strong> {{ meetingDetails.bookedFor }}
          </p> -->
        </div>
        <div class="card-body">
          <ul class="list-unstyled">
            <li><strong>Date:</strong> {{ meetingDetails.appointment_date }}</li>
            <li><strong>Time:</strong> {{ meetingDetails.start_time }} to {{ meetingDetails.end_time }}</li>
            <li><strong>Location:</strong> {{ meetingDetails?.space?.name }}</li>
            <li>
              <strong>Agenda:</strong>
              <a class="m-3" :href="meetingDetails.attachment?.downloadLink || '#'" target="_blank" rel="noopener noreferrer">
                {{ meetingDetails.description || 'View Agenda' }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Confirmation Section -->
        <div class="mt-4 text-center">
          <p class="mb-4 text-secondary">Your confirmation of attendance is required to finalize this appointment. Please respond below.</p>
          <button class="btn btn-success mx-3" :disabled="isSubmitting" @click="confirmResponse('Accept')"><i class="fas fa-check-circle"></i> Confirm Attendance</button>
          <button class="btn btn-danger mx-3" :disabled="isSubmitting" @click="confirmResponse('Reject')"><i class="fas fa-times-circle"></i> Decline Attendance</button>
        </div>

        <!-- Response Message -->
        <div v-if="responseMessage" class="mt-4 text-center">
          <p :class="{ 'text-success': responseMessage.includes('confirmed'), 'text-danger': responseMessage.includes('declined') }">
            {{ responseMessage }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
