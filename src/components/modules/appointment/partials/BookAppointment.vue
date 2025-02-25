<script setup>
import { ref, getCurrentInstance, watch, onMounted } from 'vue'
import createAxiosInstance from '@/api/axios'
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue' // Import the child component
import FlatPickr from 'vue-flatpickr-component'
import { useAuthStore } from '@/store/auth.store.js'
import AttendeesComponent from './AttendeesComponent.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// import AttendeesComponent from './AttendeesComponent.vue'

const authStore = useAuthStore()
const axiosInstance = createAxiosInstance()
const { proxy } = getCurrentInstance()
//get can be booked status from store

// Problem: Many ref initializations are single-purpose and can be grouped.
// Solution: Use a single object for related states.
const role = ref('')
role.value = authStore.getRole()
const userId = ref('')
const spaces = ref([]) // To store the spaces from the API
// const attendees = ref([]) // To store the attendees from the API
const uploadProgress = ref(0) // Holds the upload progress
const uploading = ref(false) // Holds the upload status

// watch attendees
// watch(attendees,(newValue) => {
//   console.log('attendees in parent', newValue)
// })

const resetErrors = () => {
  // attendees.value = []
  errors.value = {}
  //clear form data
}

const toastPayload = ref('')

const errors = ref({})
const showme = ref(false)

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
  minDate: 'today',
  disable: [
    function (date) {
      return date.getDay() === 6 || date.getDay() === 0
    }
  ]
}

const today = ref(new Date().toLocaleDateString())
// console.log(today.value)
// const selectedUser_id = ref(null) // To store the corresponding user_id
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
  appointment_type_id: '',
  space_id: selectedSpaceName.value,
  file: null,
  attendees: ''
}

const appointmentData = ref({ ...initialAppointmentData })

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    appointmentData.value.file = file
    fileName.value = file.name
  }
}

const fileName = ref(null)
const fileInput = ref(null)

const removeFile = () => {
  appointmentData.value.file = null
  fileName.value = null
  fileInput.value.value = ''
}

// //handle date change
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
    if (Array.isArray(response.data.dataPayload.data)) {
      spaces.value = response.data.dataPayload.data
    }
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
  console.log('appointmentData finallll:', appointmentData.value)
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

    // Show toast notification
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showAlert({
        title: toastPayload.value.toastTheme || 'Success',
        icon: toastPayload.value.toastTheme || 'success',
        text: toastPayload.value.toastMessage || 'Appointment created successfully',
        timer: 4000,
        showConfirmButton: false,
        showCancelButton: false,
        showprogressBar: true
      })
      //push to the dashboard
      router.push({ name: 'home' })
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

      if (error.response.data.errorPayload.errors.message) {
        proxy.$showAlert({
          title: error.response.data.errorPayload.errors?.message,
          // text: error.response.data.errorPayload.errors?.message,
          icon: 'error',
          showCancelButton: false,
          showConfirmButton: false,
          timer: 4000
        })
      }
    }
  } finally {
    uploading.value = false
  }
}

const appointmentTypeOptions = ref([])

const getAppointmentType = async () => {
  try {
    const response = await axiosInstance.get('/v1/scheduler/meeting-types')
    appointmentTypeOptions.value = response.data.dataPayload.data
    console.log('Appointment Type data:', appointmentTypeOptions.value)
  } catch (error) {
    handleApiError(error)
  }
}

const handleApiError = (error) => {
  if (error.response && error.response.data && error.response.data.errorPayload) {
    errors.value = error.response.data.errorPayload.errors
  } else {
    const errorMessage = error.response.data?.errorPayload?.errors?.message || 'An unknown error occurred'
    proxy.$showToast({
      title: errorMessage,
      text: errorMessage,
      icon: 'error'
    })
  }
}

const UsersOptions = ref([])
const selectedUserId = ref('') // To hold the selected username

const getusers_booked = async () => {
  try {
    const response = await axiosInstance.get('/v1/auth/users')
    if (Array.isArray(response.data.dataPayload.data)) {
      UsersOptions.value = response.data.dataPayload.data
    }
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

//get attendees from component  via emits
const updateAttendees = (attendeesId) => {
  appointmentData.value.attendees = attendeesId
  console.log('form data attend', appointmentData.value.attendees)
}
//search for spaces
const searchQuery = ref('')
const filteredSpaces = ref([])

watch(searchQuery, (newSearchQuery) => {
  if (newSearchQuery === '') {
    filteredSpaces.value = []
    return
  }
  filteredSpaces.value = spaces.value.filter((space) => space.name.toLowerCase().includes(newSearchQuery.toLowerCase()))
})

//click on space assing space id to appointment data space id
watch(selectedSpaceName, (newSpaceName) => {
  const selectedSpace = spaces.value.find((space) => space.name === newSpaceName)
  appointmentData.value.space_id = selectedSpace ? selectedSpace.id : null
  console.log('selectedSpace:', selectedSpace)
})

// Watch for changes in the selected username to update selectedUser_id
const user_searchQuery = ref(null)
// watch user_search query and filter UsersOptions and reasign to UsersOptions
//duplicate usersOptions
const UsersOptionsCopy = ref([])
UsersOptionsCopy.value = UsersOptions.value
watch(user_searchQuery, (newUserSearchQuery) => {
  console.log('useroptions:', UsersOptions.value)
  console.log('newUsername:', newUserSearchQuery)
  if (!newUserSearchQuery) {
    UsersOptionsCopy.value = []
    return
  }
  UsersOptionsCopy.value = UsersOptions.value.filter((user) => user.username.toLowerCase().includes(newUserSearchQuery.toLowerCase()) || user.email.toLowerCase().includes(newUserSearchQuery.toLowerCase()) || user.fullname.toLowerCase().includes(newUserSearchQuery.toLowerCase()))
  console.log('UsersOptionsCopy:', UsersOptionsCopy.value)
})

const selectedUsername = ref(null)

const handleUserSelection = (id) => {
  selectedUsername.value = UsersOptionsCopy.value.find((user) => user.id === id).fullname
  selectedUserId.value = id
  UsersOptionsCopy.value = [] // Clear the list after selection
}

watch(selectedUserId, (newUserId) => {
  console.log('newUsername:', newUserId)
  userId.value = newUserId || authStore.getUserId()
  appointmentData.value.user_id = userId.value

  if (appointmentData.value.appointment_date) {
    getSlots()
  }
  // getSlots()
})
const selectedType = ref('')

onMounted(() => {
  slotsData.value.date = today.value
  getSpaces()
  getAppointmentType()
  getAppointmentType()
  getusers_booked()
  //clear errors
  errors.value = {}
})
</script>
<template>
  <!-- //AttendeesComponent -->
  <!-- <AttendeesComponent ref="attendeeModal" /> -->
  <!-- <b-modal ref="appointmentModal" title="Book Meeting" class="modal-fullscreen my-modal rounded-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer :static="true" @hide="closeModal"> -->
  <b-card>
    <b-row class="mb-4 myheading">
      <b-col lg="12" class="d-flex justify-content-left">
        <div>
          <h2>New Meeting</h2>
        </div>
      </b-col>
    </b-row>
    <form id="add-form" action="javascript:void(0)" method="post">
      <div class="d-flex flex-column align-items-start">
        <input type="hidden" name="id" />
        <input type="hidden" name="appointment_type_id" />
        <div class="w-100" id="v-pills-tabContent">
          <div class="fade active show m-5" id="-3">
            <b-row class="align-items-center form-group">
              <b-col cols="12" lg="12" class="mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="Subject:" label-for="input-1">
                  <b-form-input type="text" id="subject" v-model="appointmentData.subject" class="form-control" placeholder="what the meeting is about" />

                  <div v-if="errors.subject" class="error" aria-live="polite">{{ errors.subject }}</div>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row class="align-items-center form-group">
              <b-col v-if="role === 'su' || role === 'secretary'" cols="12" lg="4" class="mb-4 mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="ChairPerson:" label-for="input-1">
                  <div class="position-relative d-flex flex-column">
                    <!-- Show search input only if UsersOptions is not empty -->
                    <b-form-input v-if="!selectedUserId" v-model="user_searchQuery" placeholder="Search User..." class="mb-2" @click="UsersOptionsCopy = UsersOptions"></b-form-input>

                    <!-- Show when a user is selected (read-only) -->
                    <b-form-input v-if="selectedUserId" v-model="selectedUsername" placeholder="Search User..." class="mb-2" readonly></b-form-input>

                    <!-- Remove selected user -->
                    <span v-if="selectedUserId" class="xuser" @click=";(selectedUsername = ''), (selectedUserId = ''), (userId = '')">
                      <i class="fas fa-times"></i>
                    </span>
                    <p v-if="!selectedUserId && user_searchQuery && !UsersOptionsCopy.length" class="text-muted mt-2">No results found.</p>

                    <!-- Show user list dropdown -->
                    <ul v-if="UsersOptionsCopy.length" class="mt-5 userlistul list-group position-absolute w-100 bg-white border rounded shadow" role="listbox" style="max-height: 160px; overflow-y: auto" @mouseleave=";(UsersOptionsCopy = []), (user_searchQuery = '')">
                      <li v-for="user in UsersOptionsCopy" :key="user.id" class="list-group-item list-group-item-action" @click="handleUserSelection(user.id)">
                        {{ user.fullname }}
                      </li>
                    </ul>
                  </div>
                </b-form-group>

                <div v-if="errors.user_id" class="error" aria-live="polite">
                  {{ errors.user_id }}
                </div>
              </b-col>

              <!-- Venue Field -->
              <b-col cols="12" :lg="role === 'user' ? 6 : role === 'registrar' ? 6 : 4" class="mb-4 mb-lg-0">
                <b-form-group label="Venue:" label-for="space">
                  <div class="position-relative d-flex flex-column">
                    <!-- Show search input only if spaces exist -->
                    <b-form-input v-model="searchQuery" placeholder="Search venue ..." class="mb-2" @click="filteredSpaces = spaces"></b-form-input>
                    <span v-if="appointmentData.space_id" class="clear-btn" @click=";(appointmentData.space_id = ''), (selectedSpaceName = ''), (filteredSpaces = [])">
                      <i class="fas fa-times"></i>
                    </span>
                    <p v-if="searchQuery && filteredSpaces.length === 0" class="text-muted mt-2">No venues available.</p>
                    <ul v-if="filteredSpaces.length > 0 && appointmentData.space_id === null" class="userlistul mt-5 list-group position-absolute w-100 bg-white border rounded shadow" role="listbox" style="max-height: 160px; overflow-y: auto" @mouseleave=";(filteredSpaces = []), (searchQuery = '')">
                      <li v-for="space in filteredSpaces" :class="{ disabled: space.is_locked }" :key="space.id" class="list-group-item list-group-item-action" @click=";(appointmentData.space_id = space.id), (selectedSpaceName = space.name)">
                        {{ space.name }}
                      </li>
                    </ul>
                  </div>
                </b-form-group>
                <div v-if="errors.space_id" class="error" aria-live="polite">
                  {{ errors.space_id }}
                </div>
              </b-col>

              <!-- Date Field -->
              <b-col cols="12" :lg="role === 'user' ? 6 : role === 'registrar' ? 6 : 4" class="mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="Date:" label-for="input-1">
                  <flat-pickr v-model="selectedDate" class="form-control" :config="flatPickrConfig" id="datePicker" :disabled="!selectedUsername && role === 'su'" v-b-tooltip.hover="{ title: 'Chair must be selected', disabled: !!selectedUsername }" />
                </b-form-group>
                <div v-if="errors.appointment_date" class="error" aria-live="polite">{{ errors.appointment_date }}</div>
              </b-col>
            </b-row>
            <b-row class="align-items-center form-group">
              <b-col cols="12" lg="6" class="mb-sm-3 mb-md-3 mb-lg-0 mb-4">
                <b-form-group label="Meeting Type:" label-for="input-1">
                  <div class="position-relative d-flex flex-column">
                    <b-form-input v-model="selectedType" placeholder="Meeting Type" @focus="showme = true" />

                    <ul v-if="appointmentTypeOptions.length && showme" class="list-group position-relative" role="listbox" style="max-height: 160px; overflow-y: auto" @mouseleave="showme = false">
                      <li v-for="type in appointmentTypeOptions" :key="type" class="list-group-item list-group-item-action" @click=";((appointmentData.appointment_type_id = type.id), (selectedType = type.type)), (showme = false)">{{ type.type }}</li>
                    </ul>
                    <span v-if="appointmentData.appointment_type_id" class="clear-btn" @click=";(appointmentData.appointment_type_id = ''), (selectedType = '')">
                      <i class="fas fa-times"></i>
                    </span>
                  </div>
                </b-form-group>

                <div v-if="errors.appointment_type_id" class="error" aria-live="polite">{{ errors.appointment_type_id }}</div>
              </b-col>
              <b-col cols="12" lg="6" class="mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="Agenda:" label-for="input-1">
                  <div class="file-input-container position-relative d-flex align-items-center">
                    <input type="file" class="form-control" id="fileUpload" @change="handleFileUpload" aria-label="Small file input" ref="fileInput" />
                    <span v-if="fileName" class="clear-file ms-2" @click="removeFile">
                      <i class="fas fa-times"></i>
                    </span>
                  </div>
                  <p v-if="fileName" class="text-primary">{{ fileName }}</p>
                  <div v-if="errors.uploadedFile" class="error" aria-live="polite">{{ errors.uploadedFile }}</div>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group">
              <b-row>
                <b-col md="12" lg="12" sm="12">
                  <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" @selectedSlotsTimes="handleSelectedSlotsTimes" />
                </b-col>
              </b-row>
              <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
            </b-row>
            <b-row class="align-items-center form-group mb-5">
              <!-- <b-col v-if="role === 'su' || role === 'secretary'" cols="12" :lg="role === 'su' ? 6 : 4" class="mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="Meeting Type:" label-for="input-1">
                  <select v-model="appointmentData.appointment_type_id" name="service" class="form-select" id="addappointmenttype">
                    Default placeholder option
                    <option value="">Meeting Type</option>
                    Dynamically populated options from API
                    <option v-for="type in appointmentTypeOptions" :key="type" :value="type">
                      {{ type }}
                    </option>
                  </select>
                </b-form-group>
                <div v-if="errors.appointment_type_id" class="error" aria-live="polite">{{ errors.appointment_type_id }}</div>
              </b-col> -->
              <!-- <b-col cols="12" :lg="role === 'su' ? 6 : role === 'secretary' ? 6 : 12" class="mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="Agenda:" label-for="input-1">
                  <div class="file-input-container position-relative d-flex align-items-center">
                    <input type="file" class="form-control" id="fileUpload" @change="handleFileUpload" aria-label="Small file input" ref="fileInput" />
                    <span v-if="fileName" class="clear-file ms-2" @click="removeFile">
                      <i class="fas fa-times"></i>
                    </span>
                  </div>
                  <p v-if="fileName" class="text-primary">{{ fileName }}</p>
                  <div v-if="errors.uploadedFile" class="error" aria-live="polite">{{ errors.uploadedFile }}</div>
                </b-form-group>
              </b-col> -->
            </b-row>
            <b-row class="align-items-center form-group">
              <b-col cols="12" lg="4">
                <b-form-group label="Contact Name:" label-for="input-1">
                  <b-form-input type="text" class="form-control" id="contactname" v-model="appointmentData.contact_name" />
                  <!-- :state="errors.contact_name ? 'true' : 'true'"  for field border color validation  -->
                  <div v-if="errors.contact_name" class="error" aria-live="polite">{{ errors.contact_name }}</div>
                </b-form-group>
              </b-col>

              <b-col cols="12" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="Phone Number:" label-for="input-1">
                  <b-form-input type="text" id="addphonenumber" v-model="appointmentData.mobile_number" class="form-control" />
                  <div v-if="errors.mobile_number" class="error" aria-live="polite">{{ errors.mobile_number }}</div>
                </b-form-group>
              </b-col>

              <b-col cols="12" lg="4">
                <b-form-group label="Email:" label-for="input-1"> <b-form-input type="text" id="addemail" v-model="appointmentData.email_address" class="form-control" placeholder="email" /> </b-form-group>

                <div v-if="errors.email_address" class="error" aria-live="polite">{{ errors.email_address }}</div>
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group">
              <b-col cols="12" lg="12" class="mb-sm-3 mb-md-3 mb-lg-0">
                <b-form-group label="Description:" label-for="input-1">
                  <textarea type="text" id="description" v-model="appointmentData.description" class="form-control" rows="3" placeholder="description" />
                  <!-- //upload progress bar -->
                  <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row class="align-items-center form-group">
              <!-- Label Section -->
              <!-- Input and Search Section -->
              <!-- <b-col cols="12">
                <div class="search-form shadow-sm">
                  Selected Items --
                  <div v-if="selectedItems.length" class="mb-2">
                    <span v-for="(item, index) in selectedItems" :key="item.id" class="badge bg-primary text-white me-2 p-2" @click="removeSelectedItem(index)"> {{ item.username }} ✖ </span>
                  </div>

                  Search Input --
                  <b-form-group label="Attendees:" label-for="input-1">
                    <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control mb-2" placeholder="Search username..." aria-label="Search for a username" :disabled="!selectedDate" />
                  </b-form-group>
                  Search Results 
                  <ul v-if="searchResults.length" class="list-group position-relative" role="listbox">
                    <li v-for="result in searchResults" :key="result.id" class="list-group-item list-group-item-action" @click="addSelectedItem(result)">
                      {{ result.username }}
                    </li>
                  </ul>
                  No Results Message 
                  <p v-else-if="searchQuery && !searchResults.length" class="text-muted mt-2">No results found.</p>
                </div>
              </b-col> -->
              <AttendeesComponent @newAttendee="updateAttendees" />
              <div v-if="errors.attendees" class="error" aria-live="polite">{{ errors.attendees }}</div>
            </b-row>
          </div>
        </div>
      </div>
    </form>
    <div class="modal-footer border-0 d-flex justify-content-center w-100">
      <button v-if="uploading === false" type="button" class="btn btn-primary" data-bs-dismiss="modal" name="save" @click="submitAppointment">Submit</button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
        Loading...
      </button>
    </div>
  </b-card>
  <!-- </b-modal> -->
</template>
<style>
.error {
  color: red;
  font-size: 0.9em;
}

.myheading {
  border-bottom: 2px solid #e9e6e6;
}

.file-input-container {
  display: flex;
  align-items: center;
}

.clear-file {
  cursor: pointer;
  color: #070707;
  /* Default icon color */
  font-size: 16px;
  /* Adjust icon size */
  margin-left: 10px;
  /* Space between input and icon */
}

.clear-file:hover {
  color: #3d4453;
  /* Icon color on hover */
}

.clear-btn {
  position: absolute;
  right: 20px;
  top: 40%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 1rem;
  color: #118820;
}
.clear-btn:hover {
  color: #000;
}

.xuser {
  cursor: pointer;
  color: #118820;
  font-size: 1rem;
  position: absolute;
  top: 40%;
  right: 20px;
  transform: translateY(-50%);
  /* Adjust icon size */

  /* Space between input and icon */
}

.userlistul {
  z-index: 1000 !important;
  /* //on mouse leave hide the list */
}
</style>
