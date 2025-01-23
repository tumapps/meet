<script setup>
import { ref, getCurrentInstance, watch, onMounted } from 'vue'
import createAxiosInstance from '@/api/axios'
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue' // Import the child component
import FlatPickr from 'vue-flatpickr-component'
import { useAuthStore } from '@/store/auth.store.js'
import AttendeesComponent from './AttendeesComponent.vue'

const authStore = useAuthStore()
const axiosInstance = createAxiosInstance()
const { proxy, emit } = getCurrentInstance()
//get can be booked status from store

// Problem: Many ref initializations are single-purpose and can be grouped.
// Solution: Use a single object for related states.
const role = ref('')
role.value = authStore.getRole()
const userId = ref('')
const appointmentModal = ref(null) // Reference for <b-modal>
const spaces = ref([]) // To store the spaces from the API
const attendees = ref([]) // To store the attendees from the API
const searchQuery = ref('') // Holds the current search query
const searchResults = ref([]) // Holds the filtered search results
const selectedItems = ref([]) // Holds the selected items
const uploadProgress = ref(0) // Holds the upload progress
const uploading = ref(false) // Holds the upload status
const availableUsers = ref([])

// /mergeProps
// defineProps({
//   name: String
// })
//CLEAR ERRORS

const resetErrors = () => {
  appointmentData.value = { ...initialAppointmentData }
  attendees.value = []
  UsersOptions.value = []
  errors.value = {}
  //clear form data
}

// Function to handle local search
const handleSearch = async () => {
  const query = searchQuery.value.trim().toLowerCase()

  try {
    const response = await axiosInstance.get('/v1/auth/users')

    if (response.data && response.data.dataPayload) {
      availableUsers.value = response.data.dataPayload.data
      console.log(searchResults.value)
    }
  } catch (error) {
    // console.error('Error fetching users:', error);
  }

  // Filter fakeAttendees based on the search query
  searchResults.value = availableUsers.value.filter((attendee) => attendee.username.toLowerCase().includes(query))

  console.log('search results', searchResults.value)
}

// add the selecteditems id to the attendees array
function addToAttendees() {
  const ids = selectedItems.value.map((item) => item.id) // Extract IDs
  appointmentData.value.attendees = [...attendees.value, ...ids] // Add to attendees
  console.log('Updated attendees: ', appointmentData.value.attendees)
}
// Function to add a selected item
const addSelectedItem = (item) => {
  // Avoid duplicates
  if (!selectedItems.value.find((selected) => selected.id === item.id)) {
    selectedItems.value.push(item)
  }
  addToAttendees()
  // Clear the search field and results
  searchQuery.value = ''
  searchResults.value = []

  console.log('added to list ', selectedItems.value)
}

// Computed property to format selectedItems as a comma-separated string
// const selectedItemsText = computed(() => {
//   return selectedItems.value.map((item) => item.username).join(', ')
// })

// Function to remove a selected item
const removeSelectedItem = (index) => {
  selectedItems.value.splice(index, 1)
}

const toastPayload = ref('')

const errors = ref({
  contact_name: '',
  email_address: '',
  start_time: '',
  mobile_number: '',
  subject: '',
  appointment_type: '',
  description: ''
})

// const props = defineProps({
//   selectedDate: String
// })

const timeSlots = ref([])
const apiResponse = ref([])
const selectedDate = ref(null)

const flatPickrConfig = {
  dateFormat: 'Y-m-d',
  altInput: true,
  altFormat: 'F j, Y',
  minDate: 'today'
}

const today = ref(new Date().toLocaleDateString())
// console.log(today.value)
const selectedUser_id = ref(null) // To store the corresponding user_id
const selectedPriority = ref(null) // To store the corresponding priority
const selectedSpaceName = ref(null) // To store the corresponding space name

//get user id from session storage
userId.value = authStore.getUserId()

const slotsData = ref({
  user_id: userId,
  date: ''
})

// Define your form data
const initialAppointmentData = {
  user_id: userId.value,
  appointment_date: selectedDate.value,
  start_time: '',
  end_time: '',
  contact_name: '',
  email_address: '',
  mobile_number: '',
  subject: '',
  description: '',
  appointment_type: '',
  space_id: selectedSpaceName.value,
  priority: '1',
  file: null,
  attendees: attendees.value
}

const appointmentData = ref({ ...initialAppointmentData })

// function resetAppointmentData() {
//   appointmentData.value = { ...initialAppointmentData }
//   attendees.value = []
//   UsersOptions.value = []
// }

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    appointmentData.value.file = file
  }
}

const closeModal = () => {
  //clear errors
  resetErrors()
  appointmentData.value = { ...initialAppointmentData }
  selectedDate.value = null
  appointmentModal.value.hide() // Close the modal using the hide() method
}
//handle date change
const handleDateChange = (newValue) => {
  appointmentData.value.appointment_date = newValue
  slotsData.value.date = newValue
  // console.log('date changed:', newValue);
  getSlots()
}

//watcher for date change
watch(selectedDate, (newValue) => {
  if (newValue) {
    console.log('Date changed:', newValue)
    handleDateChange(newValue)
  }
})

// Method to update time slots
const updateTimeSlots = (updatedSlots) => {
  timeSlots.value = updatedSlots
}

// Handle selected slots
const handleSelectedSlotsTimes = (selectedTimes) => {
  appointmentData.value.start_time = selectedTimes?.startTime || ''
  appointmentData.value.end_time = selectedTimes?.endTime || ''
}

//funtion to get spaces
const getSpaces = async () => {
  try {
    const response = await axiosInstance.get('/v1/scheduler/spaces')
    spaces.value = response.data.dataPayload.data
    console.log('Spaces data:', response.data.dataPayload.data)
  } catch (error) {
    // console.error('Error fetching spaces:', error);

    if (error.response && error.response.data && error.response.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'

      proxy.$showToast({
        title: errorMessage,
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

const getSlots = async () => {
  try {
    const response = await axiosInstance.post('/v1/scheduler/get-slots', slotsData.value)
    console.log('user_id', userId.value)
    // Update the `apiResponse` ref with the response data
    apiResponse.value = response.data.dataPayload.data.slots
    // Set all slots to `selected: false`
    const slotsWithSelected = apiResponse.value.map((slot) => ({
      ...slot,
      selected: false
    }))
    // Update `timeSlots`
    timeSlots.value = slotsWithSelected
  } catch (error) {
    // console.error('Error getting slots:', error);
    if (error.response && error.response.data.errorPayload) {
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error?.response?.data.errorPayload.errors?.message || 'An error occurred'

      proxy.$showToast({
        title: errorMessage,
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}
const submitAppointment = async () => {
  // Reset errors
  resetErrors()
  uploading.value = true
  uploadProgress.value = 0
  console.log('appointmentData: 100s', appointmentData.value)

  const formData = new FormData()
  formData.append('file', appointmentData.value.file)

  // Add other appointment fields to formData
  Object.keys(appointmentData.value).forEach((key) => {
    console.log('key:', key, ':', appointmentData.value[key])

    if (key !== 'file') {
      formData.append(key, appointmentData.value[key])
    }
  })

  console.log('Form data:', formData)

  try {
    const response = await axiosInstance.post('/v1/scheduler/appointments', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100)
        }
      }
    })

    appointmentModal.value.hide()

    // Show toast notification
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showAlert({
        title: toastPayload.value.toastTheme || 'Success',
        icon: toastPayload.value.toastTheme || 'success',
        text: toastPayload.value.toastMessage || 'Appointment created successfully',
        timer: 3000,
        showConfirmButton: false,
        showCancelButton: false,
        showprogressBar: true
      })
      emit('appointment-created')
    } else {
      proxy.$showToast({
        title: 'Success',
        icon: 'success'
      })
    }

    // Reset appointment data
    appointmentData.value = {}
  } catch (error) {
    if (error.response && error.response.status === 422 && error.response.data.errorPayload) {
      errors.value = error.response.data.errorPayload.errors
    } else {
      console.error('Error:', error)
      proxy.$showToast({ title: 'An error occurred', icon: 'error' })
    }
  } finally {
    uploading.value = false
  }
}

const appointmentTypeOptions = ref([])

const getAppointmentType = async () => {
  try {
    const response = await axiosInstance.get('/v1/scheduler/types')
    appointmentTypeOptions.value = response.data.dataPayload.data.types
  } catch (error) {
    // console.error('Error fetching appointment types:', error);

    if (error.response && error.response.data && error.response.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: errorMessage,
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

const UsersOptions = ref([])
const PriorityOptions = ref([])
const selectedUsername = ref('') // To hold the selected username

const getusers_booked = async () => {
  try {
    const response = await axiosInstance.get('/v1/auth/users')
    UsersOptions.value = response.data.dataPayload.data
    // console.log('Users data:', UsersOptions.value)
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: errorMessage,
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

const getPriority = async () => {
  try {
    const response = await axiosInstance.get('/v1/scheduler/priorities')
    PriorityOptions.value = response.data.dataPayload.data
    // console.log("Priority data:", PriorityOptions.value);
  } catch (error) {
    if (error.response && error.response.data && error.response.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: errorMessage,
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

// Watch for changes in the selected username to update selectedUser_id
watch(selectedUsername, (newUsername) => {
  console.log('newUsername:', newUsername)

  const selectedUser = UsersOptions.value.find((user) => user.username === newUsername)
  selectedUser_id.value = selectedUser ? selectedUser.id : null
  userId.value = newUsername || authStore.getUserId()
  appointmentData.value.user_id = userId.value

  if (appointmentData.value.appointment_date) {
    getSlots()
  }
  // getSlots()
})

watch(selectedPriority, (newPriority) => {
  const selectedPriority = PriorityOptions.value.find((priority) => priority.code === newPriority)
  appointmentData.value.priority = selectedPriority ? selectedPriority.code : null
})

// const close = () => {
//   //close
// }

const attendeeModal = ref(null)

// const openModal = () => {
//   console.log('Opening modal')
//   attendeeModal.value.$refs.attendeeModal.show()
// }

onMounted(() => {
  slotsData.value.date = today.value
  getSpaces()
  getAppointmentType()
  getAppointmentType()
  getusers_booked()
  getPriority()
  //clear errors
  errors.value = {}
})
</script>
<template>
  <!-- //AttendeesComponent -->
  <AttendeesComponent ref="attendeeModal" />
  <b-modal ref="appointmentModal" title="Book Appointment" class="modal-fullscreen my-modal rounded-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer :static="true" @hide="resetErrors">
    <form id="add-form" action="javascript:void(0)" method="post">
      <div class="d-flex flex-column align-items-start">
        <input type="hidden" name="id" />
        <input type="hidden" name="appointment_type" />
        <div class="w-100" id="v-pills-tabContent">
          <div class="fade active show m-5" id="-3">
            <b-row class="align-items-center form-group">
              <b-col cols="12" lg="12" class="mb-sm-3 mb-md-3 mb-lg-0">
                <div class="mb-3 form-floating custom-form-floating form-group">
                  <b-form-input type="text" id="subject" v-model="appointmentData.subject" class="form-control" placeholder="what the meeting is about" />
                  <label for="subject">Subject</label>
                  <div v-if="errors.subject" class="error" aria-live="polite">{{ errors.subject }}</div>
                </div>
              </b-col>
            </b-row>

            <b-row v-if="role === 'su'" class="align-items-center form-group">
              <b-col cols="12" lg="4" class="mb-4 mb-sm-3 mb-md-3 mb-lg-0">
                <div class="form-floating">
                  <select v-model="selectedUsername" name="service" class="form-select" id="receipent" required>
                    <option v-for="user in UsersOptions" :key="user.username" :value="user.id">
                      {{ user.username }}
                    </option>
                  </select>
                  <label for="receipent">Chair</label>
                </div>
                <div v-if="errors.user_id" class="error" aria-live="polite">
                  {{ errors.user_id }}
                </div>
              </b-col>

              <!-- Venue Field -->
              <b-col cols="12" lg="4" class="mb-4 mb-sm-3 mb-md-3 mb-lg-0">
                <div class="form-floating">
                  <select v-model="appointmentData.space_id" name="space" class="form-select" id="space" :disabled="!Array.isArray(spaces) || spaces.length === 0" required>
                    <!-- Default option -->
                    <option value="" disabled selected>
                      {{ Array.isArray(spaces) && spaces.length ? 'Choose Space' : 'No Spaces Available' }}
                    </option>
                    <!-- Loop through spaces array -->
                    <option v-for="space in spaces" :key="space.id" :value="space.id" :disabled="space.is_locked">{{ space.id }} : {{ space.name }}</option>
                  </select>
                  <label for="space">Venue</label>
                </div>
                <!-- Display errors if there are any -->
                <div v-if="errors.space_id" class="error" aria-live="polite">
                  {{ errors.space_id }}
                </div>
              </b-col>

              <!-- Date Field -->
              <b-col cols="12" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                <div class="form-floating">
                  <flat-pickr v-model="selectedDate" class="form-control" :config="flatPickrConfig" id="datePicker" :disabled="!selectedUsername" />
                  <label for="datePicker">Date</label>
                </div>
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group mt-3 p-2">
              <b-row>
                <b-col md="12" lg="12" sm="12">
                  <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" @selectedSlotsTimes="handleSelectedSlotsTimes" />
                </b-col>
              </b-row>
              <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
            </b-row>
            <b-row class="align-items-center form-group">
              <b-col cols="12" lg="4">
                <div class="mb-3 form-floating custom-form-floating form-group">
                  <b-form-input type="text" class="form-control" id="contactname" v-model="appointmentData.contact_name" placeholder="contact name" />
                  <!-- :state="errors.contact_name ? 'true' : 'true'"  for field border color validation  -->
                  <label for="contactname">Contact</label>

                  <div v-if="errors.contact_name" class="error" aria-live="polite">{{ errors.contact_name }}</div>
                </div>
              </b-col>
            </b-row>
            <b-row class="align-items-center form-group">
              <b-col cols="12" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                <div class="mb-3 form-floating custom-form-floating form-group">
                  <b-form-input type="text" id="addphonenumber" v-model="appointmentData.mobile_number" class="form-control" placeholder="phone number" />
                  <label for="addphonenumber" class="col-form-label">Phone</label>
                </div>

                <div v-if="errors.mobile_number" class="error" aria-live="polite">{{ errors.mobile_number }}</div>
              </b-col>

              <b-col cols="12" lg="5">
                <div class="mb-3 form-floating custom-form-floating form-group">
                  <b-form-input type="text" id="addemail" v-model="appointmentData.email_address" class="form-control" placeholder="email" />
                  <label for="addemail" class="col-form-label">Email</label>
                </div>

                <div v-if="errors.email_address" class="error" aria-live="polite">{{ errors.email_address }}</div>
              </b-col>
            </b-row>

            <b-row v-if="role !== 'su'" class="align-items-center form-group">
              <b-col cols="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                <label for="space" class="col-form-label">Venue </label>
              </b-col>
              <b-col cols="10" class="mb-sm-3 mb-md-3 mb-lg-0">
                <!-- Only render the select element if spaces is not null or an empty array -->
                <select v-if="spaces && spaces.length > 0" v-model="appointmentData.space_id" name="space" class="form-select" id="space" required>
                  <option value="" disabled selected>Choose Space</option>
                  <!-- Loop through spaces array and bind the option value to space.id -->
                  <option v-for="space in spaces" :key="space.id" :value="space.id" :disabled="space.is_locked">{{ space.id }} : {{ space.name }}</option>
                </select>

                <!-- Show a message if spaces is null or empty -->
                <p v-else>No spaces available</p>

                <!-- Display errors if there are any -->
                <div v-if="errors.space_id" class="error" aria-live="polite">
                  {{ errors.space_id }}
                </div>
              </b-col>
            </b-row>

            <b-row class="align-items-center form-group">
              <b-col cols="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                <label for="addappointmenttype" class="col-form-label"> Meeting Type </label>
              </b-col>
              <b-col cols="10" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                <select v-model="appointmentData.appointment_type" name="service" class="form-select" id="addappointmenttype">
                  <!-- Default placeholder option -->
                  <option value="">Appointment Type</option>
                  <!-- Dynamically populated options from API -->
                  <option v-for="type in appointmentTypeOptions" :key="type" :value="type">
                    {{ type }}
                  </option>
                </select>
                <div v-if="errors.appointment_type" class="error" aria-live="polite">{{ errors.appointment_type }}</div>
              </b-col>
            </b-row>

            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2" lg="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                <label for="addsubject" class="col-form-label">Agenda</label>
              </b-col>
              <b-col cols="10">
                <input type="file" class="form-control" id="fileUpload" @change="handleFileUpload" aria-label="Small file input" />
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="description" class="col-form-label">Notes</label>
              </b-col>
              <b-col cols="10">
                <textarea type="text" id="adddescription" v-model="appointmentData.description" class="form-control" rows="3" />
                <!-- //upload progress bar -->

                <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group">
              <!-- Label Section -->
              <b-col cols="2" class="d-flex align-items-center justify-content-start">
                <label for="addphonenumber" class="col-form-label">Attendees</label>
              </b-col>

              <!-- Input and Search Section -->
              <b-col cols="10">
                <div class="search-form shadow-sm">
                  <!-- Selected Items -- -->
                  <div v-if="selectedItems.length" class="mb-2">
                    <span v-for="(item, index) in selectedItems" :key="item.id" class="badge bg-primary text-white me-2 p-2" @click="removeSelectedItem(index)"> {{ item.username }} ✖ </span>
                  </div>

                  <!-- Search Input -- -->
                  <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control mb-2" placeholder="Search username..." aria-label="Search for a username" :disabled="!selectedDate" />

                  <!-- Search Results  -->
                  <ul v-if="searchResults.length" class="list-group position-relative" role="listbox">
                    <li v-for="result in searchResults" :key="result.id" class="list-group-item list-group-item-action" @click="addSelectedItem(result)">
                      {{ result.username }}
                    </li>
                  </ul>

                  <!-- No Results Message  -->
                  <p v-else-if="searchQuery && !searchResults.length" class="text-muted mt-2">No results found.</p>
                </div>
              </b-col>
            </b-row>
          </div>
        </div>
      </div>
    </form>
    <div class="modal-footer border-0">
      <button v-if="uploading === false" type="button" class="btn btn-primary" data-bs-dismiss="modal" name="save" @click="submitAppointment">Submit</button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
        Loading...
      </button>
      <button v-if="uploading === false" type="button" class="btn btn-warning" data-bs-dismiss="modal" @click="closeModal()">Close</button>
    </div>
  </b-modal>
</template>
<style>
.error {
  color: red;
  font-size: 0.9em;
}

/* Add rounded corners to the modal */
.modal-fullscreen .modal-content {
  border-radius: 5px !important;
  /* You can adjust the radius value */
}

.progress-bar {
  transition: width 0.2s ease;
}
</style>
