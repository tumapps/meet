<script setup>
import { ref, computed, onMounted, getCurrentInstance, watch } from 'vue'

// import { ref, getCurrentInstance,computed, watch, onMounted } from 'vue'
import createAxiosInstance from '@/api/axios'
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue' // Import the child component
// import FlatPickr from 'vue-flatpickr-component'
import { useAuthStore } from '@/store/auth.store.js'
import AttendeesComponent from '@/components/modules/appointment/partials/AttendeesComponent.vue'
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
//   //console.log('attendees in parent', newValue)
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

const today = ref(new Date().toLocaleDateString())
// //console.log(today.value)
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
  getSlots()
}

//watcher for date change
watch(selectedDate, (newValue) => {
  if (newValue) {
    //console.log('Date changed:', newValue)
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
    //console.log('Spaces data:', response.data.dataPayload.data)
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
    //console.log('user_id', userId.value)
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
  //console.log('appointmentData finallll:', appointmentData.value)
  // Reset errors
  resetErrors()
  uploading.value = true
  uploadProgress.value = 0
  //console.log('appointmentData: 100s', appointmentData.value)

  const formData = new FormData()
  formData.append('file', appointmentData.value.file)

  // Add other appointment fields to formData
  Object.keys(appointmentData.value).forEach((key) => {
    //console.log('key:', key, ':', appointmentData.value[key])

    if (key !== 'file') {
      formData.append(key, appointmentData.value[key])
    }
  })

  //console.log('Form data:', formData)

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
    //console.log('Appointment Type data:', appointmentTypeOptions.value)
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
    // //console.log('Users data:', UsersOptions.value)
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
  //console.log('form data attend', appointmentData.value.attendees)
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
  //console.log('selectedSpace:', selectedSpace)
})

// Watch for changes in the selected username to update selectedUser_id
const user_searchQuery = ref(null)
// watch user_search query and filter UsersOptions and reasign to UsersOptions
//duplicate usersOptions
const UsersOptionsCopy = ref([])
UsersOptionsCopy.value = UsersOptions.value
watch(user_searchQuery, (newUserSearchQuery) => {
  //console.log('useroptions:', UsersOptions.value)
  //console.log('newUsername:', newUserSearchQuery)
  if (!newUserSearchQuery) {
    UsersOptionsCopy.value = []
    return
  }
  UsersOptionsCopy.value = UsersOptions.value.filter((user) => user.username.toLowerCase().includes(newUserSearchQuery.toLowerCase()) || user.email.toLowerCase().includes(newUserSearchQuery.toLowerCase()) || user.fullname.toLowerCase().includes(newUserSearchQuery.toLowerCase()))
  //console.log('UsersOptionsCopy:', UsersOptionsCopy.value)
})

const selectedUsername = ref(null)

const handleUserSelection = (id) => {
  selectedUsername.value = UsersOptionsCopy.value.find((user) => user.id === id).fullname
  selectedUserId.value = id
  UsersOptionsCopy.value = [] // Clear the list after selection
}

watch(selectedUserId, (newUserId) => {
  //console.log('newUsername:', newUserId)
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
//below is the multistep controls

const currentStep = ref(1)
// const selectedDate = ref(null)
const selectedTime = ref(null)

// Calendar state
const currentDate = ref(new Date())
const calendarDays = ref([])

// Computed properties
const currentMonth = computed(() => {
  return currentDate.value.toLocaleString('default', { month: 'long' })
})

const currentYear = computed(() => {
  return currentDate.value.getFullYear()
})

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return selectedUsername.value !== null
    case 2:
      return appointmentData.value.attendees !== null
    case 3:
      return selectedDate.value !== null && selectedTime.value !== null
    case 4:
    default:
      return true
  }
})

// Methods
const nextStep = () => {
  if (currentStep.value < 6 && canProceed.value) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const selectDate = (date) => {
  //console.log('date:', date)
  selectedDate.value = date
  selectedTime.value = null // Reset time when date changes
}

const prevMonth = () => {
  const newDate = new Date(currentDate.value)
  newDate.setMonth(newDate.getMonth() - 1)
  currentDate.value = newDate
  generateCalendar()
}

const nextMonth = () => {
  const newDate = new Date(currentDate.value)
  newDate.setMonth(newDate.getMonth() + 1)
  currentDate.value = newDate
  generateCalendar()
}

// const generateCalendar = () => {
//   const year = currentDate.value.getFullYear()
//   const month = currentDate.value.getMonth()

//   // First day of the month
//   const firstDay = new Date(year, month, 1)
//   // Last day of the month
//   const lastDay = new Date(year, month + 1, 0)
//   // Days from previous month to show
//   const prevMonthDays = firstDay.getDay()

//   // Days from next month to show
//   const nextMonthDays = 6 - lastDay.getDay()

//   // Total days to show (6 weeks)
//   // const totalDays = prevMonthDays + lastDay.getDate() + nextMonthDays

//   const daysArray = []

//   // Previous month days
//   const prevMonthLastDay = new Date(year, month, 0).getDate()
//   for (let i = prevMonthLastDay - prevMonthDays + 1; i <= prevMonthLastDay; i++) {
//     daysArray.push({
//       day: i,
//       date: `${year}-${month}-${i}`,
//       currentMonth: false
//     })
//   }

//   // Current month days
//   for (let i = 1; i <= lastDay.getDate(); i++) {
//     daysArray.push({
//       day: i,
//       date: `${year}-${month + 1}-${i}`,
//       currentMonth: true
//     })
//   }

//   // Next month days
//   for (let i = 1; i <= nextMonthDays; i++) {
//     daysArray.push({
//       day: i,
//       date: `${year}-${month + 2}-${i}`,
//       currentMonth: false
//     })
//   }

//   calendarDays.value = daysArray
// }

const generateCalendar = (minDate = new Date()) => {
  // eslint-disable-next-line no-unused-vars
  const pad = (n) => n.toString().padStart(2, '0')
  const formatDate = (date) => {
    const yyyy = date.getFullYear()
    const mm = String(date.getMonth() + 1).padStart(2, '0') // month is 0-indexed
    const dd = String(date.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
  }

  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()

  const firstDayOfMonth = new Date(year, month, 1)
  const lastDayOfMonth = new Date(year, month + 1, 0)

  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const todayFormatted = formatDate(today)

  const normalizedMinDate = new Date(minDate)
  normalizedMinDate.setHours(0, 0, 0, 0)

  const isWeekend = (date) => {
    const day = date.getDay()
    return day === 0 || day === 6
  }

  const isBeforeMinDate = (date) => date < normalizedMinDate

  const isToday = (date) => formatDate(date) === todayFormatted

  const createDayObject = (date) => ({
    day: date.getDate(),
    date: formatDate(date),
    currentMonth: true,
    isToday: isToday(date),
    isDisabled: isWeekend(date) || isBeforeMinDate(date)
  })

  const calendar = []

  // 1. Insert placeholders for the start of the month
  const startDay = firstDayOfMonth.getDay() // 0 = Sunday, 1 = Monday, etc.
  for (let i = 0; i < startDay; i++) {
    calendar.push({ type: 'placeholder' })
  }

  // 2. Fill current month dates
  for (let i = 1; i <= lastDayOfMonth.getDate(); i++) {
    const date = new Date(year, month, i)
    calendar.push(createDayObject(date))
  }

  calendarDays.value = calendar
}

// Initialize the calendar
onMounted(() => {
  generateCalendar()
})
</script>
<template>
  <body>
    <div id="app">
      <div>
        <!-- Step Indicator -->
        <div class="step-indicator">
          <div class="row g-0">
            <div class="col step" :class="{ active: currentStep === 1, completed: currentStep > 1 }">
              <div class="step-icon">1</div>
              <div class="step-title">Meeting Details</div>
            </div>
            <div class="col step" :class="{ active: currentStep === 2, completed: currentStep > 2 }">
              <div class="step-icon">2</div>
              <div class="step-title">Attendees</div>
            </div>
            <div class="col step" :class="{ active: currentStep === 3, completed: currentStep > 3 }">
              <div class="step-icon">3</div>
              <div class="step-title">Confirmation</div>
            </div>
          </div>
        </div>

        <!-- Step Content -->
        <div class="step-content">
          <!-- Step 1: Specialty Selection -->
          <div v-if="currentStep === 1">
            <form id="add-form" action="javascript:void(0)" method="post">
              <div class="d-flex flex-column align-items-start">
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
                    <b-row class="align-items-center form-group mb-5"> </b-row>
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
                      <b-col v-if="role === 'su' || role === 'secretary'" cols="12" lg="6" class="mb-4 mb-sm-3 mb-md-3 mb-lg-0">
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
                      <b-col cols="12" lg="6" class="mb-4 mb-lg-0">
                        <b-form-group label="Venue:" label-for="space">
                          <div class="position-relative d-flex flex-column">
                            <!-- Show search input only if spaces exist -->
                            <b-form-input v-if="appointmentData.space_id === null" v-model="searchQuery" placeholder="Search venue ..." class="mb-2" @click="filteredSpaces = spaces"> </b-form-input>
                            <!-- Show selected space name (read-only) -->
                            <b-form-input v-if="appointmentData.space_id !== null" v-model="selectedSpaceName" placeholder="Search Space..." class="mb-2" readonly></b-form-input>
                            <!-- Clear selection -->
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
                    </b-row>
                    <b-col cols="12" class="mb-sm-3 mb-md-3 mb-lg-0">
                      <b-row class="d-flex justify-content-center align-items-center date-time">
                        <!-- Date Picker Column -->
                        <b-col cols="12" lg="6" class="mt-sm-md-28">
                          <div class="timeslots-calendar">
                            <div class="calendar-header">
                              <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-sm btn-outline-primary" @click="prevMonth">
                                  <i class="fas fa-chevron-left"></i>
                                </button>
                                <h5 class="mb-0">{{ currentMonth }} {{ currentYear }}</h5>
                                <button class="btn btn-sm btn-outline-primary" @click="nextMonth">
                                  <i class="fas fa-chevron-right"></i>
                                </button>
                              </div>
                            </div>
                            <div class="calendar-grid mb-4">
                              <div class="fw-bold">Su</div>
                              <div class="fw-bold">Mo</div>
                              <div class="fw-bold">Tu</div>
                              <div class="fw-bold">We</div>
                              <div class="fw-bold">Th</div>
                              <div class="fw-bold">Fr</div>
                              <div class="fw-bold">Sa</div>

                              <div
                                v-for="(day, index) in calendarDays"
                                :key="index"
                                class="calendar-day"
                                :class="{
                                  disabled: day?.isDisabled,
                                  today: day?.isToday,
                                  selected: day?.date === selectedDate,
                                  placeholder: day?.type === 'placeholder'
                                }"
                                @click="day && !day.isDisabled && day.date && selectDate(day.date)">
                                <span v-if="day && day.type !== 'placeholder'">{{ day.day }}</span>
                              </div>
                            </div>
                          </div>

                          <div v-if="errors.appointment_date" class="error" aria-live="polite">
                            {{ errors.appointment_date }}
                          </div>
                        </b-col>
                        <!-- Time Slots Column -->
                        <b-col cols="12" lg="6">
                          <div class="custom-timeslots">
                            <h3 class="text-center">Available Timeslots</h3>
                            <TimeSlotComponent v-if="selectedUsername" :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" @selectedSlotsTimes="handleSelectedSlotsTimes" />
                            <div v-else class="alert alert-info">Please select a date & Chairperson to see available time slots.</div>
                          </div>
                        </b-col>
                      </b-row>
                    </b-col>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <!-- Step 2: Doctor Selection -->
          <div v-if="currentStep === 2">
            <h2 class="mb-4">Attendees</h2>
            <div>
              <AttendeesComponent @newAttendee="updateAttendees" />
              <div v-if="errors.attendees" class="error" aria-live="polite">{{ errors.attendees }}</div>
            </div>
          </div>
          <!-- Step 3: Date & Time Selection -->
          <div v-if="currentStep === 3" class="timeslots">
            <!-- <h2 class="mb-4">Select Date & Time</h2> -->
            <b-card>
              <b-row class="mb-4 myheading">
                <b-col lg="12" class="d-flex justify-content-left">
                  <div>
                    <h2>Meeting Summary</h2>
                  </div>
                </b-col>
              </b-row>
              <div class="d-flex flex-column align-items-start">
                <div class="w-100">
                  <div class="fade active show m-5">
                    <!-- Subject -->
                    <b-row class="align-items-center form-group">
                      <b-col cols="12" lg="12" class="mb-sm-3 mb-md-3 mb-lg-0">
                        <b-form-group label="Subject:" label-for="input-subject">
                          <b-form-input id="input-subject" type="text" :value="appointmentData.subject" class="form-control" readonly plaintext />
                        </b-form-group>
                      </b-col>
                    </b-row>

                    <!-- Chairperson, Date, Venue, Time -->
                    <b-row class="align-items-center form-group">
                      <!-- Chairperson -->
                      <b-col v-if="role === 'su' || role === 'secretary'" cols="12" lg="6" class="mb-4 mb-sm-3 mb-md-3 mb-lg-0">
                        <b-form-group label="Chairperson:" label-for="input-chair">
                          <b-form-input id="input-chair" :value="appointmentData.contact_name" class="form-control" readonly plaintext />
                        </b-form-group>
                      </b-col>

                      <!-- Date -->
                      <b-col cols="12" :lg="role === 'user' ? 6 : role === 'registrar' ? 6 : 4" class="mb-sm-3 mb-md-3 mb-lg-0">
                        <b-form-group label="Date:" label-for="input-date">
                          <b-form-input id="input-date" :value="appointmentData.appointment_date" class="form-control" readonly plaintext />
                        </b-form-group>
                      </b-col>

                      <!-- Venue -->
                      <b-col cols="12" lg="6" class="mb-4 mb-lg-0">
                        <b-form-group label="Venue:" label-for="input-space">
                          <b-form-input id="input-space" :value="appointmentData.space_id" class="form-control" readonly plaintext />
                        </b-form-group>
                      </b-col>

                      <!-- Time -->
                      <b-col cols="12" lg="6" class="mb-4 mb-lg-0">
                        <b-form-group label="Time:" label-for="input-time">
                          <b-form-input id="input-time" :value="`${appointmentData.start_time} to ${appointmentData.end_time}`" class="form-control" readonly plaintext />
                        </b-form-group>
                      </b-col>
                    </b-row>

                    <!-- Agenda File + Description -->
                    <b-row class="align-items-center form-group">
                      <!-- Agenda File -->
                      <b-col cols="12" lg="6" class="mb-sm-3 mb-md-3 mb-lg-0">
                        <b-form-group label="Agenda File:" label-for="input-file">
                          <b-form-input v-if="appointmentData.file" :value="appointmentData.file.name" class="text-primary mt-2" readonly plaintext />
                          <b-form-input v-else :value="'No file attached'" class="text-muted mt-2" readonly plaintext />
                        </b-form-group>
                      </b-col>

                      <!-- Description -->
                      <b-col cols="12" lg="6" class="mb-sm-3 mb-md-3 mb-lg-0">
                        <b-form-group label="Description:" label-for="input-description">
                          <b-form-input id="input-description" :value="appointmentData.description" class="form-control" readonly plaintext />
                        </b-form-group>
                      </b-col>
                    </b-row>
                  </div>
                </div>
              </div>
            </b-card>
          </div>

          <!-- Navigation Buttons -->
          <div class="nav-buttons px-4 pb-4">
            <button v-if="currentStep > 1 && currentStep < 6" class="btn btn-outline-primary" @click="prevStep"><i class="fas fa-arrow-left me-2"></i> Back</button>
            <div v-else></div>

            <button v-if="currentStep < 3" class="btn btn-primary ms-auto" :disabled="!canProceed" @click="nextStep">Continue <i class="fas fa-arrow-right ms-2"></i></button>

            <button v-if="currentStep === 3" class="btn btn-primary" @click="submitAppointment">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </body>
</template>
<style scoped>
.step-indicator {
  background: white;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.step {
  text-align: center;
  font-size: 0.85rem;
  color: #6c757d;
  position: relative;
}

.step.active {
  color: #097b3e;
  font-weight: 600;
}

.step.completed .step-icon {
  background-color: #097b3e;
  color: white;
}

.step-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: var(--light-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.5rem;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.step.active .step-icon {
  background-color: #d89837;
  color: white;
}

.step-title {
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.step-content {
  padding: 2rem;
  min-height: 500px;
}

.timeslots-calendar {
  margin-top: 20px;
  margin-bottom: 20px;
  width: 90%;
  background-color: #fff;
  padding: 30px;
  border-radius: 20px;
}

.calendar-header {
  /* background-color: var(--light-bg); */
  border-radius: 8px;
  padding: 0.75rem;
  margin-bottom: 1rem;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
  text-align: center;
}

.calendar-day {
  padding: 0.75rem 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.calendar-day:hover {
  background-color: rgba(67, 238, 124, 0.1);
}

.calendar-day.selected {
  background-color: #097b3e;
  color: white;
}

.calendar-day.disabled {
  color: #adb5bd;
  cursor: not-allowed;
}

.today {
  background-color: rgba(67, 238, 124, 0.37);
}

.form-control:focus {
  border-color: #097b3e;
  box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
}

.summary-card {
  background-color: var(--light-bg);
  border-radius: 10px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.summary-item {
  margin-bottom: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border-color);
}

.summary-item:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.btn-primary {
  background-color: #097b3e;
  border-color: #097b3e;
}

/* .btn-primary:hover {
  background-color: var(--secondary);
  border-color: var(--secondary);
} */

.btn-outline-primary {
  color: #097b3e;
  border-color: #097b3e;
}

.btn-outline-primary:hover {
  background-color: #097b3e;
  color: #fff;
  border-color: #097b3e;
}

.nav-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.confirmation-icon {
  font-size: 4rem;
  color: #28a745;
  margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
  .step-title {
    display: none;
  }

  .step-icon {
    margin-bottom: 0;
  }

  .step-content {
    padding: 1.5rem;
  }
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
.calendar-day.placeholder {
  background-color: transparent;
  pointer-events: none;
  visibility: hidden;
}

.userlistul {
  z-index: 1000 !important;
  /* //on mouse leave hide the list */
}
.custom-calendar {
  margin: 0;
  padding: 0;
  /* margin-left: 160px; */
}

.timeslots-slots {
  font-family: 'Segoe UI', Arial, sans-serif;
  max-height: 50vh;
}

.timeslots-slots h5 {
  align-items: center;
  align-self: center;
}

.corporate-heading {
  color: #2c3e50;
  font-weight: 600;
  border-bottom: 2px solid #e0e6ed;
  padding-bottom: 12px;
  font-size: 1.2rem;
}

.custom-timeslots {
  min-height: 440px;
  width: 100%;
  padding: 20px;
  background-color: #fff;
  border-radius: 25px;
}
.time-group {
  margin-bottom: 24px;
}

.time-group-title {
  color: #fff;
  font-weight: 800;
  font-size: 0.95rem;
  margin-bottom: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background-color: #097b3e;
  padding: 2px;
  border-radius: 3px;
}

.slot-container {
  display: flex;
  flex-wrap: wrap;
  /* gap: 10px; */
}

.time-slot {
  display: inline-block;
  margin: 0.25rem;
  padding: 0.5rem;
  border: 1px solid #dcdfe6;
  border-radius: 6px;
  background-color: #f8f9fa;
  color: #2c3e50;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 84x;
  min-width: 84px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}

.time-slot:hover:not(.disabled) {
  background-color: #07a36f;
  border-color: #c0c4cc;
  transform: translateY(-1px);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05);
}

.time-slot.selected {
  background-color: #097b3e;
  color: white;
  border: none;
  font-weight: 500;
  box-shadow: 0 2px 6px rgba(44, 62, 80, 0.2);
}

.time-slot.disabled {
  background-color: #d89837;
  color: #000;
  cursor: not-allowed;
  opacity: 0.8;
  box-shadow: none;
}
@media (max-width: 991.98px) {
  .mt-sm-md-28 {
    margin-top: 28px !important;
  }
}

.date-time {
  background-color: #f0f6f2;
  border: 0.9px solid #f0f6f2;
  border-radius: 25px;
}
</style>
