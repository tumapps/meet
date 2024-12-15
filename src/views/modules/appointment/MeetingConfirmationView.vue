<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
import AxiosInstance from '@/api/axios'
// import { useRoute } from 'vue-router';

const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()
// Reactive data
const meetingDetails = ref({})
const isSubmitting = ref(false)
const responseMessage = ref('')
// const route = useRoute();

const fetchMeetingDetails = async () => {
  const meetingId = route.params.meetingId
  try {
    const { data } = await axiosInstance.get(`/api/meeting/${meetingId}`)
    meetingDetails.value = data
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
// onMounted(fetchMeetingDetails)

// Utility function for formatting dates
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString()

const confirmResponse = (id) => {
  // console.log("id", id);
  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: 'You are about to Delete this appointment. Do you want to proceed?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Delete it!',
      cancelButtonText: 'No, keep it',
      confirmButtonColor: '#d33',
      cancelButtonColor: '#076232'
    })
    .then((result) => {
      if (result.isConfirmed) {
        handleResponse(id)
      }
    })
}
</script>
<template>
  <section
    :style="{
      backgroundImage: `url(${require('@/assets/images/tum.jpg')})`,
      backgroundSize: 'cover',
      backgroundRepeat: 'no-repeat',
      backgroundPosition: 'center'
    }">
    <div class="d-flex justify-content-center align-items-center vh-100">
      <!-- Outer Card -->
      <div class="card shadow-lg p-5" style="max-width: 600px; width: 100%; min-height: 500px">
        <div class="text-center">
          <!-- Logo -->
          <img src="@/assets/images/logo.png" alt="Logo" class="mb-4" style="max-width: 150px" />
          <h3 class="mb-4 bg-primary" style="font-family: 'arial', sans-serif; color: #fff">Meeting Confirmation</h3>
        </div>

        <!-- Inner Card for Meeting Details -->
        <div class="card-header">
          <h3 class="card-title">{{ meetingDetails.title }}</h3>
          <p class="mb-0"><strong>Organized by:</strong>{{ meetingDetails.organizer }}</p>
        </div>
        <div class="card-body d-flex flex-column">
          <div>
            <p><strong>Date:</strong> {{ formatDate(meetingDetails.date) }}</p>
            <p><strong>Time:</strong> {{ meetingDetails.time }}</p>
            <p><strong>Description:</strong> {{ meetingDetails.description }}</p>
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
