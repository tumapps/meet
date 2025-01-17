<script setup>
import { ref, onMounted, getCurrentInstance, onUnmounted } from 'vue'
import AxiosInstance from '@/api/axios'
// import { useRoute } from 'vue-router';

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

const handleResponse = async (responseType) => {
  isSubmitting.value = true
  try {
    await axiosInstance.post(`/api/meeting/response`, { response: responseType })
    responseMessage.value = `You have successfully ${responseType === 'confirm' ? 'confirmed' : 'declined'} the meeting.`

    proxy.$showToast({
      title: 'Success',
      text: 'You have successfully ',
      icon: 'success'
    })
  } catch (error) {
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}

// Fetch meeting details on page load
onMounted(() => {
  fetchMeetingDetails(1)
})
// Utility function for formatting dates
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString()

const confirmResponse = (id) => {
  // console.log("id", id);
  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: 'You are about to confirm your attendance to this meeting. This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        handleResponse(id)
      }
    })
}

// on close clear the data from the meetingDetails when the component is unmounted
onUnmounted(() => {
  meetingDetails.value = {}
})
onMounted(() => {
  // get meetig id from the route
  const id = proxy.$route.params.id
  fetchMeetingDetails(id)
})
</script>
<template>
  <section
    :style="{
      // backgroundImage: `url(${require('@/assets/images/tum.jpg')})`,
      backgroundSize: 'cover',
      backgroundRepeat: 'no-repeat',
      backgroundPosition: 'center'
    }">
    <div class="d-flex justify-content-center align-items-center vh-100">
      <!-- Outer Card -->
      <div class="card shadow-lg p-5" style="max-width: 600px; width: 100%; min-height: 500px">
        <div class="text-center">
          <!-- Logo -->
          <img src="/logo.ico" alt="Logo" class="mb-4" style="max-width: 150px" />
          <h3 class="mb-4 bg-primary" style="font-family: 'arial', sans-serif; color: #fff">Attendance Confirmation</h3>
        </div>

        <!-- Inner Card for Meeting Details -->
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
</template>
