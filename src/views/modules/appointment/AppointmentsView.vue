<script setup>
import { onMounted, ref, getCurrentInstance, computed, watch } from 'vue'
import AxiosInstance from '@/api/axios'
// import globalUtils from '@/utilities/globalUtils'
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue'
import { useAuthStore } from '@/store/auth.store.js'
import UppyDashboard from '@/components/custom/uppy/UppyDashboard.vue'
import FlatPickr from 'vue-flatpickr-component'
import { debounce } from 'lodash-es'
import AttendeesComponent from '../../../components/modules/appointment/partials/AttendeesComponent.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const authStore = useAuthStore()

const role = ref('')
role.value = authStore.getRole()
// const globalUtils = require('@/utils/globalUtils');
const showModal = () => {
  //redirect to booking page
  router.push({ name: 'booking' })
}
const downloadLink = ref(null)
const toastPayload = ref('')
const { proxy } = getCurrentInstance()
const axiosInstance = AxiosInstance()
const tableData = ref([])
const sortKey = ref('') // Active column being sorted
const sortOrder = ref('asc') // Sorting order: 'asc' or 'desc'
const isArray = ref(false)
const currentPage = ref(1) // The current page being viewed
const totalPages = ref(1) // Total number of pages from the API
const perPage = ref(20) // Number of items per page (from API response)
const selectedPerPage = ref(20) // Number of items per page (from dropdown)
const perPageOptions = ref([10, 20, 50, 100])
const searchQuery = ref('')
const errors = ref({})
const errorDetails = ref({})
const recordStatus = ref('')
const space = ref('')
const attendees = ref([])

//get user_id from session storage
// const userId = authStore.getUserId();

const InitialappointmentDetails = {
  appointment_date: '',
  start_time: '',
  end_time: '',
  contact_name: '',
  email_address: '',
  mobile_number: '',
  subject: '',
  appointment_type_id: '',
  status: '',
  created_at: '',
  updated_at: '',
  statusLabel: '',
  user_id: '',
  space_id: ''
}

const appointmentDetails = ref({ ...InitialappointmentDetails })
const uploading = ref(false) // Holds the upload status

const fileName = ref(null)
const fileInput = ref(null)

//file upload
const agenda = ref(null)
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    agenda.value = file
    fileName.value = file.name
  }
}

//function to upload file
const uploadFile = async () => {
  uploading.value = true
  const formData = new FormData()
  formData.append('file', agenda.value)
  try {
    const response = await axiosInstance.post(`v1/scheduler/upload-file/${meetingId.value}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showAlert({
        title: toastPayload.value.toastMessage || 'File uploaded successfully',
        icon: toastPayload.value.toastTheme || 'success',
        timer: 4000,
        timerProgressBar: true,
        showCancelButton: false,
        showConfirmButton: false
      })
    }

    console.log('response', response)
  } catch (error) {
    console.log('error', error)
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'
    proxy.$showAlert({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error',
      showCancelButton: false,
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true
    })
  } finally {
    uploading.value = false
  }
}

const removeFile = () => {
  agenda.value = null
  fileName.value = null
  // Check if fileInput.value exists before accessing `.value`
  if (fileInput.value) {
    fileInput.value.value = null
  }
}

// const minDate = ref('today')

const flatPickrConfig = {
  dateFormat: 'Y-m-d',
  altInput: true,
  altFormat: 'd-M-Y',
  // minDate: 'today',
  disable: [
    function (date) {
      return date.getDay() === 6 || date.getDay() === 0
    }
  ]
}

const myModal = ref(null)

const goToPage = (page) => {
  // Ensure the page is within the valid range
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page
    getAppointments(page) // Fetch appointments for the selected page
  }
}

const updatePerPage = async () => {
  currentPage.value = 1 // Reset to first page when changing items per page
  await getAppointments(1) // Fetch appointments with the new perPage value
}

// Method to update time slots
const updateTimeSlots = (updatedSlots) => {
  timeSlots.value = updatedSlots
}
// Handle selected slots

const handleSelectedSlotsTimes = (selectedTimes) => {
  appointmentDetails.value.start_time = selectedTimes?.startTime || ''
  appointmentDetails.value.end_time = selectedTimes?.endTime || ''
}

const sortTable = (key) => {
  if (sortKey.value === key) {
    // Toggle the sort order if the same column is clicked again
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

// Sort the data based on the current sort key and order
const sortedData = computed(() => {
  return [...tableData.value].sort((a, b) => {
    let modifier = sortOrder.value === 'asc' ? 1 : -1
    if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier
    if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier
    return 0
  })
})

const confirmDelete = (id) => {
  // console.log("id", id);
  selectedAppointmentId.value = id
  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: 'You are about to Delete this appointment. Do you want to proceed?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      cancelButtonText: 'cancel',
      confirmButtonColor: '076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        deleteAppointment(id)
        getAppointments(1)
      }
    })
}

const confirmCancel = (id) => {
  selectedAppointmentId.value = id
  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: 'You are about to CANCEL this appointment. Do you want to proceed?',
      icon: 'warning',
      input: 'textarea', // Adding input field
      inputLabel: 'Reason for Cancellation',
      inputPlaceholder: 'Please type your reason here...',
      inputAttributes: {
        'aria-label': 'Please type your reason here'
      },
      showCancelButton: true,
      confirmButtonText: 'Yes, CANCEL it!',
      cancelButtonText: 'No, keep it',
      confirmButtonColor: '076232',
      cancelButtonColor: '#d33',
      preConfirm: (inputValue) => {
        if (!inputValue) {
          // Display error message if input is empty
          proxy.$showAlert({
            title: 'Ooops!',
            text: 'You need to provide a reason for cancellation.',
            icon: 'info',
            showConfirmButton: false,
            showCancelButton: false,
            timer: 4000
            // background: '#d33',
          })
          return false // Prevents closing the alert
        }
        return inputValue // Passes the input value to the `then` block
      }
    })
    .then((result) => {
      if (result.isConfirmed) {
        const reason = result.value // Access the input value here
        // console.log('Cancellation reason:', reason); // Optional: log the reason
        // Proceed with the cancellation using the provided reason
        cancelAppointment(id, reason)
        getAppointments(1)
      }
    })
}

const cancelAppointment = async (id, reason) => {
  try {
    // console.log("reasinsnius", reason);
    const response = await axiosInstance.put(`v1/scheduler/cancel-meeting/${id}`, { cancellation_reason: reason })
    getAppointment(id)
    getAppointments(1)
    toastPayload.value = response.data.toastPayload
    proxy.$showAlert({
      title: toastPayload.value.toastMessage || 'Appointment Deleted successfully',
      icon: toastPayload.value.toastTheme || 'success',
      timer: 4000,
      timerProgressBar: true,
      showCancelButton: false,
      showConfirmButton: false
    })
  } catch (error) {
    // console.error(error);
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}
const deleteAppointment = async (id) => {
  toastPayload.value = null

  try {
    const response = await axiosInstance.delete(`v1/scheduler/appointments/${id}`)
    getAppointments(1)
    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showAlert({
        title: toastPayload.value.toastMessage || 'Appointment Deleted successfully',
        icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        timer: 4000,
        timerProgressBar: true,
        showCancelButton: false,
        showConfirmButton: false
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Appointment Deleted successfully',
        icon: 'success'
      })
    }
  } catch (error) {
    // console.error(error);
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

// const errorMessage = ref('')

const restoreAppointment = async (id) => {
  // Reset toastPayload to an empty object
  toastPayload.value = {}
  // console.log("restore", toastPayload.value);

  try {
    // Make sure the correct HTTP method is used (assuming it's PUT for restoration)
    const response = await axiosInstance.delete(`v1/scheduler/appointments/${id}`)

    // Refresh the appointments after restoring
    getAppointments(1)

    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showAlert({
        title: toastPayload.value.toastMessage || 'Appointment Restored successfully',
        icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        timer: 4000,
        timerProgressBar: true,
        showCancelButton: false,
        showConfirmButton: false
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Appointment restored successfully',
        icon: 'success'
      })
    }
  } catch (error) {
    // Optionally log the error for debugging purposes
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

const confirmRestore = (id) => {
  // console.log("id", id);
  selectedAppointmentId.value = id
  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: 'You are about to RESTORE this Appointment. Do you want to proceed?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'RESTORE',
      cancelButtonText: 'cancel',
      confirmButtonColor: '#076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        restoreAppointment(id)
        getAppointments(1)
      }
    })
}

const getAppointments = async (page = 1) => {
  try {
    const params = {
      page: page, // Current page number
      'per-page': selectedPerPage.value // Items per page
    }

    // Include search query if it's not empty
    if (searchQuery.value) {
      params._search = searchQuery.value
    }

    const response = await axiosInstance.get('/v1/scheduler/appointments', { params })
    //check if data is array
    isArray.value = Array.isArray(response.data)
    tableData.value = response.data.dataPayload.data
    // Update pagination data
    currentPage.value = response.data.dataPayload.currentPage
    totalPages.value = response.data.dataPayload.totalPages
    perPage.value = response.data.dataPayload.perPage
  } catch (error) {
    let errorMessage = 'An error occurred'

    if (error.response && error.response.data.errorPayload) {
      // Check if errorPayload exists and has errors
      errors.value = error.response.data.errorPayload.errors
      console.log(errors)
      if (errors.value && errors.value.message) {
        errorMessage = errors.value.message // Use specific error message
      }
    }

    // Show toast notification for error
    proxy.$showToast({
      title: errorMessage, // Change title to be more indicative of an error
      text: errorMessage, // Show specific error message
      icon: 'error'
    })
  }
}

//watch seach query and call getAppointments
watch(searchQuery, () => {
  getAppointments(1)
})

//slots for time
const slotsData = ref({
  user_id: '',
  date: ''
})
const setUserId = () => {
  // set user id depending on the user role
  if (authStore.getRole() === 'su') {
    //get the user id from the owner of the appointment
    slotsData.value.user_id = appointmentDetails.value.user_id
    // console.log("user_id", slotsData.value.user_id);
  } else {
    slotsData.value.user_id = authStore.getUserId()
  }
}

const spaces = ref([]) // Initialize as an empty array
const meetingId = ref(null)
// create attendeesId array
const specialDate = ref('')

const attendeesId = ref([])

const selectedType = ref('')

const getAppointment = async (id) => {
  try {
    meetingId.value = id
    errorDetails.value = {}
    const response = await axiosInstance.get(`/v1/scheduler/appointments/${id}`)

    if (response.data.dataPayload && !response.data.errorPayload) {
      appointmentDetails.value = response.data.dataPayload.data
      attendees.value = response.data.dataPayload.data.attendees
      attendeesId.value = response.data.dataPayload.data.attendees.map((attendee) => attendee.attendee_id)
      appointmentDetails.value.attendees = attendeesId.value
      recordStatus.value = appointmentDetails.value.recordStatus
      space.value = appointmentDetails.value.space ?? 'no space here'
      selectedAppointmentId.value = id
      appointmentDetails.value.space_id = response.data.dataPayload.data.space?.id ?? 'no space'
      specialDate.value = response.data.dataPayload.data.appointment_date

      console.log('appointment type', appointmentDetails.value.appointment_type_id)
      console.log('type otyopm', appointmentTypeOptions.value)
      const selected = appointmentTypeOptions.value.find((type) => type.id === appointmentDetails.value.appointment_type_id)
      console.log('selected:', selected)
      selectedType.value = selected ? selected.type : 'nana'
      console.log('selectedType madafak:', selectedType.value)

      console.log('appointment date after special date', appointmentDetails.value.appointment_date)

      // Ensure spaces.value is an array
      if (!Array.isArray(spaces.value)) {
        spaces.value = [] // Initialize as an empty array if it's not already
      }
      console.log('Spaces here:', appointmentDetails.value.space)
      // Check if the space from the appointment exists in the spaces array
      if (appointmentDetails.value.space && !spaces.value.some((s) => s.id === appointmentDetails.value.space.id)) {
        // If the space doesn't exist in the spaces array, add it
        spaces.value.push(appointmentDetails.value.space)
      }

      // Set user id depending on the user role
      setUserId()
      downloadLink.value = appointmentDetails.value.attachment?.downloadLink || null
    }
  } catch (error) {
    console.log('error', error)
    const errorMessage =
      error.response?.data?.errorPayload?.errors?.message ||
      error.message || // Fallback to generic error message from the error object
      'An unknown error occurred there'

    proxy.$showToast({
      title: 'An error occurred 23',
      text: errorMessage,
      icon: 'error'
    })
  }
}

const getSpaces = async (userId) => {
  try {
    const response = await axiosInstance.get(`/v1/scheduler/spaces?user_id=${userId}`)
    // Ensure spaces.value is always an array
    spaces.value = Array.isArray(response.data.dataPayload.data) ? response.data.dataPayload.data : []
    console.log('Getting spaces...')

    console.log('Spaces data:', spaces.value)
  } catch (error) {
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

// Watch for changes in appointmentDetails.value.space
watch(
  () => appointmentDetails.value.space, // Watch the space object in appointmentDetails
  (newSpace) => {
    if (newSpace) {
      console.log('New found:', newSpace)
      console.log('Spaces1:', spaces.value)
      // Ensure spaces.value is an array
      if (!Array.isArray(spaces.value)) {
        spaces.value = [] // Initialize as an empty array if it's not already
      }
      console.log('Spaces2:', spaces.value)
      // Check if the new space already exists in the spaces array
      const spaceExists = spaces.value.some((space) => space.id === newSpace.id)
      console.log('Space exists:', spaceExists)
      // If the space doesn't exist, add it to the spaces array
      if (!spaceExists) {
        spaces.value.push(newSpace)
        console.log('New space added to spaces array:', spaces.value)
      }
    }
  },
  { immediate: true, deep: true } // immediate: true ensures the watcher runs on initialization
)

// Function to open modal
const selectedAppointmentId = ref('')
const openModal = async (id, userId) => {
  getSpaces(userId)
  await getAppointmentType()
  await getAppointment(id, userId)
  console.log('appointment deatils line 557', appointmentDetails.value.appointment_date)
  selectedAppointmentId.value = id
  myModal.value.show() // Open the modal
  console.log('appointment deatils line 565', appointmentDetails.value.appointment_date)
}

//submit attendeess signail
const submitSignal = ref('')

const updateAppointment = async () => {
  uploading.value = true
  console.log(appointmentDetails.value)
  //map only the attendee_id to the attendees array
  try {
    errorDetails.value = {}
    console.log('file dealer', fileInput.value)
    submitSignal.value = 'submit'
    const buttonText = ref(null)

    const response = await axiosInstance.put(`/v1/scheduler/appointments/${selectedAppointmentId.value}`, appointmentDetails.value)

    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload

      getAppointments(1)
      if (agenda.value !== null) {
        buttonText.value = 'Uploading File ...'
        uploadFile()
      } else {
        proxy.$showAlert({
          title: toastPayload.value.toastMessage,
          icon: toastPayload.value.toastTheme,
          showCancelButton: false,
          showConfirmButton: false,
          timer: 4000
        })

        // Close the modal
        handleModalClose()
        myModal.value.hide()
      }

      // Show toast notification using the response data
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Appointment Updated successfully',
        icon: 'success'
      })
    }
  } catch (error) {
    if (error.response && error.response.data.errorPayload) {
      errorDetails.value = error.response.data.errorPayload.errors
    } else {
      console.error('bug', error)
      const errorMessage = error.response?.data?.errorPayload?.errors?.message || 'An error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  } finally {
    uploading.value = false
  }
}

const suggestions = ref([])
const suggestedDate = ref('')
const timeSlots = ref([])

// Function to fetch suggested slots
const suggestSlots = async (id) => {
  try {
    id = selectedAppointmentId.value
    errorDetails.value = {}
    const response = await axiosInstance.get(`/v1/scheduler/slot-suggestion/${id}`)

    suggestions.value = response.data.dataPayload.data.suggestions

    suggestedDate.value = suggestions.value.date
    timeSlots.value = suggestions.value.slots
    appointmentDetails.value.appointment_date = suggestions.value.date

    //update date to suggessted date
    // appointmentDetails.value.appointment_date = suggestedDate.value
    console.log('suggested date', suggestedDate.value)

    // Show a success toast message
    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'success',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    }
  } catch (error) {
    console.log('error', error)
    // Handle errors
    errorDetails.value = error.response.data.errorPayload.errors
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

const toggleCheckIn = async (id) => {
  try {
    const response = await axiosInstance.put(`/v1/scheduler/checkin/${id}`)
    getAppointments(1)

    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showAlert({
        icon: toastPayload.value.toastTheme || 'success',
        text: toastPayload.value.toastMessage || 'Appointment Checked In successfully',
        timer: 4000,
        showCancelButton: false,
        showConfirmButton: false,
        timerProgressBar: true
      })
    } else {
      proxy.$showAlert({
        title: 'Appointment Checked In successfully',
        icon: 'success',
        showCancelButton: false,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
      })
    }
  } catch (error) {
    let errorMessage = 'An error occurred'

    if (error.response?.data.errorPayload?.errors?.message) {
      errorMessage = error.response.data.errorPayload.errors.message
    }

    proxy.$showToast({
      title: errorMessage,
      text: errorMessage,
      icon: 'error'
    })

    throw error // Re-throw the error so `onToggleCheckIn` can handle it
  }
}

const onToggleCheckIn = async (item) => {
  selectedAppointmentId.value = item.id

  const originalState = item.checked_in // Preserve the original state
  item.checked_in = !item.checked_in // Temporarily toggle the state

  try {
    await toggleCheckIn(item.id) // Call your backend function
  } catch (error) {
    item.checked_in = originalState // Revert state if the request fails
  }
}

const confirmCheckIn = (item) => {
  const originalState = item.checked_in // Preserve the original state
  item.checked_in = !item.checked_in // Temporarily toggle the state

  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: 'You are about to Check In this appointment. Do you want to proceed?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Check In',
      cancelButtonText: 'cancel',
      confirmButtonColor: '#076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        onToggleCheckIn(item)
      }
      item.checked_in = originalState
    })
}

// Function to handle actions after modal closes
const handleModalClose = () => {
  // Perform any actions you need after modal closes
  appointmentDetails.value = { ...InitialappointmentDetails }
  errors.value = {}
  space.value = ''
  getAppointments(1) // Refresh the appointments after closing the modal
  removeFile()
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

const apiResponse = ref([])

const getSlots = async () => {
  try {
    const response = await axiosInstance.post('/v1/scheduler/get-slots', slotsData.value)

    // Debug the response
    console.log(response.data)

    // Update the `apiResponse` ref with the response data (ensure it's an array)
    apiResponse.value = Array.isArray(response.data.dataPayload?.data?.slots) ? response.data.dataPayload.data.slots : []

    // Set all slots to `selected: false`
    const slotsWithSelected = apiResponse.value.map((slot) => ({
      ...slot,
      selected: false
    }))

    // Update `timeSlots`
    timeSlots.value = slotsWithSelected
  } catch (error) {
    console.error(error) // Log the error for debugging

    if (error.response && error.response.data.errorPayload) {
      errorDetails.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response?.data.errorPayload?.errors?.message || 'An error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

const debouncedGetSlots = debounce(() => {
  getSlots()
}, 300) // 300ms debounce

const handleDateChange = (newValue) => {
  if (!newValue) {
    console.warn('Invalid date value:', newValue)
    return
  }

  // Update the date in `slotsData`
  slotsData.value.date = newValue

  console.log('Date changed:', newValue)

  // Call the debounced getSlots
  debouncedGetSlots()
}

watch(
  () => appointmentDetails.value.appointment_date,
  (newValue, oldValue) => {
    console.log('appointment deatils line 852', appointmentDetails.value.appointment_date)
    if (newValue) {
      console.log(`Date changed from ${oldValue} to ${newValue}`)
      handleDateChange(newValue)
    }
  }
)

console.log('appointment date after the watch', appointmentDetails.value.appointment_date)

const truncatedSubject = computed(() => {
  const subject = appointmentDetails.value.subject || ''
  return subject.length > 12 ? subject.slice(0, 30) + '...' : subject
})

//control visibilty of timeSlot component in edit modalI
const timeSlotShow = ref(false)

const toggletimeSlotShow = () => {
  timeSlotShow.value = !timeSlotShow.value
}

//get attendees from component  via emits
const updateAttendees = (attendeesId) => {
  appointmentDetails.value.attendees = attendeesId
  console.log('form data attending mangai', appointmentDetails.value.attendees)
}

const showme = ref(false)

onMounted(async () => {
  //fetch appointments and slots and unavailable slots
  getAppointments(1)
})
</script>
<template>
  <b-col lg="12">
    <b-card>
      <b-row class="mb-4">
        <b-col lg="6">
          <div>
            <h2>Meetings</h2>
          </div>
        </b-col>
        <b-col lg="6" md="12" sm="12" class="mb-3">
          <div class="d-flex justify-content-end">
            <b-button variant="primary" @click="showModal"> New Meeting </b-button>
          </div>
        </b-col>
      </b-row>
      <b-row class="mb-3">
        <b-col lg="12" md="12" sm="12">
          <div class="d-flex justify-content-between align-items-stretch">
            <!-- Left Section: Items Per Page -->
            <div class="d-flex align-items-center">
              <select id="itemsPerPage" v-model="selectedPerPage" @change="updatePerPage" class="form-select form-select-sm h-100" style="width: auto">
                <option v-for="option in perPageOptions" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
            </div>

            <!-- Right Section: Search -->
            <div class="d-flex align-items-center" style="width: 180px">
              <b-input-group class="h-100">
                <!-- Search Input -->
                <b-form-input placeholder="Search..." aria-label="Search" v-model="searchQuery" class="h-100" />
                <!-- Search Button -->
                <b-input-group-append>
                  <b-button variant="primary" @click="getAppointments()" class="h-100">
                    <i class="fas fa-search"></i>
                  </b-button>
                </b-input-group-append>
              </b-input-group>
            </div>
          </div>
        </b-col>
      </b-row>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th @click="sortTable('start_time')">
                Subject
                <i class="fas fa-sort"></i>
              </th>
              <th v-if="role === 'su' || role === 'secretary'">ChairPerson</th>

              <th @click="sortTable('appointment_date')">
                Date
                <i class="fas fa-sort"></i>
              </th>
              <th @click="sortTable('start_time')">
                Time
                <i class="fas fa-sort"></i>
              </th>
              <th @click="sortTable('contact_name')">
                Contact Name
                <i class="fas fa-sort"></i>
              </th>
              <th>Status</th>
              <th>Checked In</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-striped">
            <template v-if="Array.isArray(tableData) && tableData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <td class="subject-column" :title="item.subject">{{ item.subject }}</td>
                <td v-if="role === 'su' || role === 'secretary'">{{ item.userName }}</td>
                <td>{{ item.appointment_date }}</td>
                <td>{{ item.start_time }} - {{ item.end_time }}</td>
                <td class="subject-column" :title="item.contact_name">{{ item.contact_name }}</td>

                <td>
                  <span :class="'badge bg-' + item.recordStatus.theme">{{ item.recordStatus.label }}</span>
                </td>

                <td>
                  <div v-if="item.recordStatus.label === 'ACTIVE'" class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked-{{ item.id }}" :checked="item.checked_in" :disabled="item.checked_in" @change="confirmCheckIn(item)" />
                    <label class="form-check-label" :for="'flexSwitchCheckChecked-' + item.id">
                      {{ item.checked_in === 1 ? 'Checked In' : 'Check In' }}
                    </label>
                  </div>

                  <div v-else class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDisabled" checked="" disabled="" />
                  </div>
                </td>

                <td>
                  <!-- Edit Button -->
                  <button v-if="item.recordStatus.label === 'ACTIVE'" class="btn btn-outline-primary btn-sm me-3" @click="openModal(item.id, item.user_id)" :disabled="item.checked_in">
                    <i class="fas fa-edit" title="Edit"></i>
                  </button>
                  <button v-else-if="item.recordStatus.label !== 'ACTIVE'" class="btn btn-outline-primary btn-sm me-3" @click="openModal(item.id)">
                    <i class="fas fa-eye" title="View"></i>
                  </button>

                  <!-- Cancel Button -->
                  <button v-if="(item.recordStatus.label !== 'CANCELLED' && item.recordStatus.label === 'ACTIVE') || item.recordStatus.label === 'RESCHEDULE'" class="btn btn-outline-warning btn-sm me-3" @click="confirmCancel(item.id)" :disabled="item.checked_in">
                    <i class="fas fa-cancel" title="Cancel"></i>
                  </button>
                  <button v-else-if="item.recordStatus.label !== 'ACTIVE'" class="btn btn-outline-warning btn-sm me-3" disabled>
                    <i class="fas fa-cancel" title="Cancel (Disabled)"></i>
                  </button>

                  <!-- Delete Button -->
                  <button v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'" class="btn btn-outline-danger btn-sm me-3" @click="confirmDelete(item.id)" :disabled="item.checked_in">
                    <i class="fas fa-trash" title="Delete"></i>
                  </button>
                  <button v-else-if="item.recordStatus.label === 'CANCELLED'" class="btn btn-outline-danger btn-sm me-3" disabled>
                    <i class="fas fa-trash" title="Delete (Disabled)"></i>
                  </button>

                  <!-- Restore Button -->
                  <button v-if="item.recordStatus.label === 'DELETED'" class="btn btn-outline-danger btn-sm" @click="confirmRestore(item.id)">
                    <i class="fas fa-undo" title="Restore"></i>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="6" class="text-center">No records found.</td>
              </tr>
            </template>
          </tbody>
        </table>
        <!-- Pagination -->
      </div>
      <b-col sm="12" lg="12" md="12" class="d-flex justify-content-end mt-5 mb-n5">
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-end">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link" @click="goToPage(currentPage - 1)" :disabled="currentPage === 1">Previous</button>
            </li>
            <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
              <button class="page-link" @click="goToPage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link" @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages">Next</button>
            </li>
          </ul>
        </nav>
      </b-col>
    </b-card>
  </b-col>
  <b-modal id="uppyModal" size="m" hide-header>
    <b-row>
      <b-col lg="12">
        <b-card no-body>
          <!-- Fixed Short Height -->
          <b-card no-body class="text-center">
            <uppy-dashboard id="drag-drop-area" />
          </b-card>
        </b-card>
      </b-col>
    </b-row>
  </b-modal>

  <b-modal ref="myModal" hide-footer size="xl" @hide="handleModalClose">
    <template #title>
      <div class="container-fluid" style="width: 90% !important; margin: 0 !important; font-size: 1rem !important">
        <div class="row align-items-center">
          <div class="mb-3 col-lg-3 col-md-6 col-sm-6"><span class="fw-bold">Date:</span> {{ appointmentDetails.appointment_date }}</div>

          <div class="mb-3 col-lg-4 col-md-6 col-sm-6"><span class="fw-bold">Subject:</span> {{ truncatedSubject }}</div>
          <div class="mb-3 col-lg-3 col-md-6 col-sm-6"><span class="fw-bold">Time:</span> {{ appointmentDetails.start_time }} to {{ appointmentDetails.end_time }}</div>
          <div class="mb-3 col-lg-2 col-md-6 col-sm-6">
            <span class="fw-bold">Status:</span>
            <b-badge :variant="recordStatus.theme">
              {{ recordStatus.label }}
            </b-badge>
          </div>
        </div>
      </div>
    </template>
    <b-row class="justify-content-center">
      <b-col lg="12" sm="12">
        <b-card-body>
          <b-form>
            <b-form-group>
              <b-row>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Subject</label>
                  <b-form-input v-model="appointmentDetails.subject" id="subject"></b-form-input>
                  <div v-if="errorDetails.subject" class="error">
                    {{ errorDetails.subject }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="date" class="form-label">Date</label>
                  <flat-pickr v-model="appointmentDetails.appointment_date" class="form-control" :config="flatPickrConfig" id="datePicker" />

                  <!-- <b-form-input v-model="appointmentDetails.appointment_date" id="input-107" type="date"></b-form-input> -->
                  <div v-if="errorDetails.appointment_date" class="error">
                    {{ errorDetails.appointment_date }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="time" class="form-label">Time</label>
                  <div class="d-flex">
                    <b-form-input v-model="appointmentDetails.start_time" id="starttime" type="time" class="me-2" @click="toggletimeSlotShow"></b-form-input>
                    <span class="align-self-center">to</span>
                    <b-form-input v-model="appointmentDetails.end_time" id="endtime" type="time" class="ms-2" @click="toggletimeSlotShow"></b-form-input>
                  </div>
                  <div v-if="errorDetails.start_time" class="error">
                    {{ errorDetails.start_time }}
                  </div>
                  <div v-if="errorDetails.end_time" class="error">
                    {{ errorDetails.end_time }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <!-- <b-form-group label="Meeting Type:" label-for="meeting type">
                    <select v-model="selectedType" name="service" class="form-select" id="addappointmenttype">
                      Default placeholder option
                      <option value="">Meeting Type</option>
                      Dynamically populated options from API
                      <option v-for="type in appointmentTypeOptions" :key="type" :value="type.id" @click="selectedType = type.type">
                        {{ type.type }}
                      </option>
                    </select>
                  </b-form-group> -->

                  <b-form-group label="Meeting Type:" label-for="input-1">
                    <div class="position-relative d-flex flex-column">
                      <b-form-input v-model="selectedType" placeholder="Meeting Type" @focus="showme = true" />

                      <ul v-if="appointmentTypeOptions.length && showme" class="list-group position-relative" role="listbox" style="max-height: 160px; overflow-y: auto" @mouseleave="showme = false">
                        <li v-for="type in appointmentTypeOptions" :key="type" class="list-group-item list-group-item-action" @click=";((appointmentDetails.appointment_type_id = type.id), (selectedType = type.type)), (showme = false)">{{ type.type }}</li>
                      </ul>
                      <span v-if="appointmentDetails.appointment_type_id" class="clear-btn" @click=";(appointmentDetails.appointment_type_id = ''), (selectedType = '')">
                        <i class="fas fa-times"></i>
                      </span>
                    </div>
                  </b-form-group>

                  <div v-if="errors.appointment_type_id" class="error" aria-live="polite">{{ errors.appointment_type_id }}</div>
                </b-col>
                <b-col lg="4" md="12" sm="12">
                  <label for="space" class="form-label">Space</label>
                  <select v-model="appointmentDetails.space_id" name="space" class="form-select" id="space">
                    <!-- Default option -->
                    <option :value="null" disabled>
                      {{ Array.isArray(spaces) && spaces.length ? 'Choose Space' : 'No Spaces Available' }}
                    </option>
                    <!-- Loop through spaces array -->
                    <option v-for="space in spaces" :key="space.id" :value="space.id" :disabled="space.is_locked">
                      {{ space.name }}
                    </option>
                  </select>
                  <div v-if="errorDetails.space" class="error">
                    {{ errorDetails.space }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="created_at" class="form-label">Booked on</label>
                  <b-form-input v-model="appointmentDetails.created_at" id="created_at" disabled></b-form-input>
                  <div v-if="errorDetails.created_at" class="error">
                    {{ errorDetails.created_at }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="contactPerson" class="form-label">Contact Person</label>
                  <b-form-input v-model="appointmentDetails.contact_name" id="contactPerson"></b-form-input>
                  <div v-if="errorDetails.contact_name" class="error">
                    {{ errorDetails.contact_name }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="phone" class="form-label">Phone Number</label>
                  <b-form-input v-model="appointmentDetails.mobile_number" id="phone"></b-form-input>
                  <div v-if="errorDetails.mobile_number" class="error">
                    {{ errorDetails.mobile_number }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <b-form-input v-model="appointmentDetails.email_address" id="email"></b-form-input>
                  <div v-if="errorDetails.email_address" class="error">
                    {{ errorDetails.email_address }}
                  </div>
                </b-col>

                <b-col md="12" lg="12" sm="12">
                  <TimeSlotComponent v-if="recordStatus.label === 'ACTIVE' && timeSlotShow" :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" @selectedSlotsTimes="handleSelectedSlotsTimes" class="mb-4" />
                </b-col>

                <!-- preview the pdf file using the pdf viewer -->
                <b-col lg="12" md="12" class="mb-2">
                  <label for="input-107" class="form-label">Agenda</label>
                  <div v-if="downloadLink" class="">
                    <div class="card-body position-relative d-flex align-items-start">
                      <a :href="downloadLink" target="_blank"><img src="@/assets/modules/file-manager/images/pdf.svg" alt="PDF Icon" class="card-img-left" style="width: 50px; height: 40px; object-fit: contain" /></a>
                    </div>
                  </div>
                  <div v-else>
                    <!-- <b-col cols="10"> -->
                    <!-- <input type="file" class="form-control" id="fileUpload" @change="handleFileUpload" aria-label="Small file input" /> -->
                    <div class="file-input-container position-relative d-flex align-items-center">
                      <input type="file" class="form-control" id="fileUpload" @change="handleFileUpload" aria-label="Small file input" ref="fileInput" />
                      <span v-if="fileName" class="clear-file ms-2" @click="removeFile">
                        <i class="fas fa-times"></i>
                      </span>
                    </div>
                    <p v-if="fileName" class="text-primary">{{ fileName }}</p>
                  </div>
                </b-col>
                <b-col lg="12" md="12" class="mb-3">
                  <label for="description" class="form-label">Description</label>
                  <b-form-textarea v-model="appointmentDetails.description" id="input-107" rows="3"></b-form-textarea>
                  <div v-if="errorDetails.notes" class="error">
                    {{ errorDetails.notes }}
                  </div>
                </b-col>
                <b-col lg="12" md="12" class="mb-3">
                  <AttendeesComponent :attendees="attendees" :meetingId="meetingId" @newAttendee="updateAttendees" :submitSignal="submitSignal" />
                </b-col>
                <b-col v-if="appointmentDetails.rejection_reason || appointmentDetails.rejection_reason" lg="12" md="12" class="mb-3">
                  <!-- //label for the rejection reason or the cancellation reason depending on the status of the appointment -->
                  <label for="description" class="form-label">Reason for {{ recordStatus.label === 'CANCELLED' ? 'Cancellation' : 'Rejection' }} </label>
                  <b-form-textarea v-model="appointmentDetails.rejection_reason" id="input-107" rows="3" disabled></b-form-textarea>
                </b-col>

                <b-row v-if="recordStatus.label === 'RESCHEDULE'">
                  <!-- //preview for the pdf file -->
                  <b-col lg="6" md="4" class="mb-3">
                    <label for="status-badge" class="form-label">Status</label>
                    <div id="status-badge" class="d-flex align-items-center">
                      <!-- <b-badge :variant="recordStatus.theme" class="me-3">
                        {{ recordStatus.label }}
                      </b-badge> -->

                      <b-button :variant="recordStatus.theme" @click="suggestSlots">Suggest Open slots</b-button>
                    </div>
                  </b-col>
                </b-row>
                <!-- <b-button variant="primary" v-b-modal.uppyModal>
                  <div class="d-flex justify-content-center">
                    <svg width="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path opacity="0.4" d="M16.191 2H7.81C4.77 2 3 3.78 3 6.83V17.16C3 20.26 4.77 22 7.81 22H16.191C19.28 22 21 20.26 21 17.16V6.83C21 3.78 19.28 2 16.191 2Z" fill="currentColor"></path>
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M8.07996 6.6499V6.6599C7.64896 6.6599 7.29996 7.0099 7.29996 7.4399C7.29996 7.8699 7.64896 8.2199 8.07996 8.2199H11.069C11.5 8.2199 11.85 7.8699 11.85 7.4289C11.85 6.9999 11.5 6.6499 11.069 6.6499H8.07996ZM15.92 12.7399H8.07996C7.64896 12.7399 7.29996 12.3899 7.29996 11.9599C7.29996 11.5299 7.64896 11.1789 8.07996 11.1789H15.92C16.35 11.1789 16.7 11.5299 16.7 11.9599C16.7 12.3899 16.35 12.7399 15.92 12.7399ZM15.92 17.3099H8.07996C7.77996 17.3499 7.48996 17.1999 7.32996 16.9499C7.16996 16.6899 7.16996 16.3599 7.32996 16.1099C7.48996 15.8499 7.77996 15.7099 8.07996 15.7399H15.92C16.319 15.7799 16.62 16.1199 16.62 16.5299C16.62 16.9289 16.319 17.2699 15.92 17.3099Z" fill="currentColor"></path>
                    </svg>
                    <p class="ms-3 mb-0">Add Document</p>
                  </div>
                </b-button> -->
                <b-col lg="12" class="mb-3">
                  <div v-if="appointmentDetails.status === 3">
                    <div v-if="timeSlots.length > 1">
                      <h3>{{ suggestedDate }}</h3>
                      <!-- Display the date from the API response -->
                      <b-col lg="12" md="12" sm="12">
                        <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" @selectedSlotsTimes="handleSelectedSlotsTimes" />
                      </b-col>
                    </div>
                    <div v-else>
                      <p>No available slots.</p>
                      <!-- Handle case where there are no suggestions -->
                    </div>
                  </div>
                </b-col>

                <b-row class="m-5">
                  <b-col>
                    <div class="d-flex justify-content-center">
                      <b-button v-if="uploading === false" variant="primary" class="me-3" @click="updateAppointment" ref="updateButton" :disabled="recordStatus.label !== 'ACTIVE' && recordStatus.label !== 'PENDING' && recordStatus.label !== 'RESCHEDULE'"> Update </b-button>
                      <button v-else class="btn btn-primary" type="button" disabled>
                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                        {{ buttonText.value !== null ? buttonText.value : 'Submitting...' }}
                      </button>
                    </div>
                  </b-col>
                </b-row>
              </b-row>
            </b-form-group>
          </b-form>
        </b-card-body>
      </b-col>
    </b-row>
  </b-modal>
</template>
<style scoped>
.error {
  color: red;
}
.modal-title {
  width: 100% !important;
}

.subject-column {
  max-width: 200px; /* Set the maximum width */
  white-space: nowrap; /* Prevent text from wrapping */
  overflow: hidden; /* Hide overflowed text */
  text-overflow: ellipsis; /* Show ellipses for overflowed text */
}

.file-input-container {
  display: flex;
  align-items: center;
}

.clear-file {
  cursor: pointer;
  color: #070707; /* Default icon color */
  font-size: 16px; /* Adjust icon size */
  margin-left: 10px; /* Space between input and icon */
}

.clear-file:hover {
  color: #3d4453; /* Icon color on hover */
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
</style>
