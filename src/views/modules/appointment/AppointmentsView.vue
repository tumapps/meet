<script setup>
import { onMounted, ref, getCurrentInstance, computed, watch, onUnmounted } from 'vue'
import AxiosInstance from '@/api/axios'
import BookAppointment from '@/components/modules/appointment/partials/BookAppointment.vue'
// import globalUtils from '@/utilities/globalUtils'
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue'
import { useAuthStore } from '@/store/auth.store.js'
import UppyDashboard from '@/components/custom/uppy/UppyDashboard.vue'
import FlatPickr from 'vue-flatpickr-component'
import { debounce } from 'lodash-es'

const authStore = useAuthStore()

const role = ref('')
role.value = authStore.getRole()
// const globalUtils = require('@/utils/globalUtils');
const appointmentModal = ref(null)
const showModal = () => {
  appointmentModal.value.$refs.appointmentModal.show()
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
  appointment_type: '',
  status: '',
  created_at: '',
  updated_at: '',
  statusLabel: '',
  user_id: '',
  space_id: ''
}

const appointmentDetails = ref({ ...InitialappointmentDetails })

const flatPickrConfig = {
  dateFormat: 'd M Y',
  altInput: true,
  altFormat: 'F j, Y',
  minDate: 'today'
}

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
    const response = await axiosInstance.put(`v1/scheduler/cancel/${id}`, { cancellation_reason: reason })
    getAppointment(id)
    getAppointments(1)
    toastPayload.value = response.data.toastPayload
    proxy.$showAlert({
      title: toastPayload.value.toastMessage || 'Appointment Deleted successfully',
      icon: toastPayload.value.toastTheme || 'success',
      timer: 3000,
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
    getAppointment(id)
    getAppointments(1)
    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showAlert({
        title: toastPayload.value.toastMessage || 'Appointment Deleted successfully',
        icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        timer: 3000,
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
    getAppointment(id)
    getAppointments(1)

    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showAlert({
        title: toastPayload.value.toastMessage || 'Appointment Restored successfully',
        icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        timer: 3000,
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
      text: 'You are about to RESTORE thirestoreAppointments appointment. Do you want to proceed?',
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

const getAppointment = async (id) => {
  try {
    errorDetails.value = {}
    const response = await axiosInstance.get(`/v1/scheduler/appointments/${id}`)

    if (response.data.dataPayload && !response.data.errorPayload) {
      appointmentDetails.value = response.data.dataPayload.data
      //convert start created at to yyyy-mm-dd
      //appointmentDetails.value.created_at = globalUtils.convertToDate(appointmentDetails.value.created_at)
      appointmentDetails.value.space_id = response.data.dataPayload.data.space?.id || null
      // console.log('space_id', appointmentDetails.value.space_id)
      recordStatus.value = appointmentDetails.value.recordStatus
      selectedAppointmentId.value = id

      //set user id depending on the user role
      setUserId()
      // console.log("user_id", slotsData.value.user_id);
      downloadLink.value = appointmentDetails.value.attachment?.downloadLink || null
      // const formattedAppointmentDate = computed({
      //   get() {
      //     if (!appointmentDetails.value.appointment_date) return null
      //     const date = new Date(appointmentDetails.value.appointment_date)
      //     if (isNaN(date)) return null // Handle invalid dates
      //     return date.toISOString().split('T')[0] // Returns 'YYYY-MM-DD'
      //   },
      //   set(value) {
      //     appointmentDetails.value.appointment_date = value // Set the input value
      //     console.log('formattedAppointmentDate', appointmentDetails.value.appointment_date)
      //   }
      // })

      // appointmentDetails.value.appointment_date = formattedAppointmentDate.value

      console.log('new date ', appointmentDetails.value.appointment_date)

      // Convert the date to 'YYYY-MM-DD' format
    }
  } catch (error) {
    console.log('error', error)
    // Check if error.response is defined before accessing it
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

// Function to open modal
const selectedAppointmentId = ref('')
const openModal = (id) => {
  const modal = proxy.$refs.myModal // Use proxy to access $refs
  getAppointment(id)
  selectedAppointmentId.value = id
  modal.show()

  // console.log("selectedAppointmentId", selectedAppointmentId.value);
}

const updateAppointment = async () => {
  console.log(appointmentDetails.value.checked_in)
  try {
    errorDetails.value = {}

    const response = await axiosInstance.put(`/v1/scheduler/appointments/${selectedAppointmentId.value}`, appointmentDetails.value)

    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload

      getAppointments(1)
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'success',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
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
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}
// }

// const performSearch = async () => {
//   try {
//     const response = await axiosInstance.get(`v1/scheduler/appointments?_search=${searchQuery.value}`)
//     tableData.value = response.data.dataPayload.data
//   } catch (error) {
//     // console.error(error);
//     const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

//     proxy.$showToast({
//       title: 'An error occurred',
//       text: errorMessage,
//       icon: 'error'
//     })
//   }
// }

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

    // Assign date and slots from the API response
    suggestedDate.value = suggestions.value.date
    timeSlots.value = suggestions.value.slots

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
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Operation successful',
        icon: 'success'
      })
    }
    //update date to suggessted date
    appointmentDetails.value.appointment_date = suggestedDate
  } catch (error) {
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

// const toggleCheckIn = async (id) => {
//   try {
//     const response = await axiosInstance.put(`/v1/scheduler/checkin/${id}`)
//     getAppointment(id)
//     getAppointments(1)

//     // Check if toastPayload exists in the response and update it
//     if (response.data.toastPayload) {
//       toastPayload.value = response.data.toastPayload
//       // console.log("toastPayload", toastPayload.value); // Log for debugging

//       // Show toast notification using the response data
//       proxy.$showAlert({
//         // title: toastPayload.value.toastMessage || 'Appointment Checked In successfully',
//         // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
//         icon: toastPayload.value.toastTheme || 'success',
//         showCancelButton: false,
//         showConfirmButton: false,
//         text: toastPayload.value.toastMessage || 'Appointment Checked In successfully',
//         timer: 3000,
//         timerProgressBar: true
//       })
//     } else {
//       // Fallback if toastPayload is not provided in the response
//       proxy.$showAlert({
//         title: 'Appointment Checked In successfully',
//         icon: 'success',
//         showCancelButton: false,
//         showConfirmButton: false,
//         timer: 1500,
//         timerProgressBar: true

//       })
//     }
//   } catch (error) {
//     // console.error(error);
//     let errorMessage = 'An error occurred'

//     if (error.response && error.response.data.errorPayload) {
//       // Check if errorPayload exists and has errors
//       const errors = error.response.data.errorPayload.errors
//       if (errors && errors.message) {
//         errorMessage = errors.message // Use specific error message
//       }
//     }

//     // Show toast notification for error
//     proxy.$showToast({
//       title: errorMessage, // Change title to be more indicative of an error
//       text: errorMessage, // Show specific error message
//       icon: 'error'
//     })
//   }
// }

const toggleCheckIn = async (id) => {
  try {
    const response = await axiosInstance.put(`/v1/scheduler/checkin/${id}`)
    getAppointment(id)
    getAppointments(1)

    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showAlert({
        icon: toastPayload.value.toastTheme || 'success',
        text: toastPayload.value.toastMessage || 'Appointment Checked In successfully',
        timer: 3000,
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
        timer: 3000,
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
  //   console.log('Modal has been closed');
  // appointmentDetails.value = { ...InitialappointmentDetails }
  errors.value = {}
  getAppointments(1) // Refresh the appointments after closing the modal
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

//watch for changes in appointmentDetails.appointment_date and call getSlots

watch(
  () => appointmentDetails.value.appointment_date,
  (newValue, oldValue) => {
    if (!newValue) {
      console.warn('appointment_date reset to null. Ignoring change.')
      return
    }

    if (newValue !== oldValue) {
      console.log(`Date changed from ${oldValue} to ${newValue}`)
      handleDateChange(newValue)
    }
  }
)

onMounted(async () => {
  //fetch appointments and slots and unavailable slots
  getAppointments(1)
  proxy.$addWebSocketListener((data) => {
    console.log('Received WebSocket message:', data)
  })
})

onUnmounted(() => {
  proxy.$removeWebSocketListener()
})
</script>
<template>
  <b-col lg="12">
    <b-card>
      <b-row class="mb-4">
        <b-col lg="6">
          <div>
            <h2>Appointments</h2>
          </div>
        </b-col>
        <b-col lg="6" md="12" sm="12" class="mb-3">
          <div class="d-flex justify-content-end">
            <b-button variant="primary" @click="showModal"> New Appointment </b-button>
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
              <th @click="sortTable('contact_name')">
                Name
                <i class="fas fa-sort"></i>
              </th>
              <th @click="sortTable('mobile_number')">
                Contact
                <i class="fas fa-sort"></i>
              </th>
              <th @click="sortTable('appointment_date')">
                Date
                <i class="fas fa-sort"></i>
              </th>
              <th @click="sortTable('start_time')">
                Subject
                <i class="fas fa-sort"></i>
              </th>
              <th @click="sortTable('email_address')">
                Email
                <i class="fas fa-sort"></i>
              </th>
              <th @click="sortTable('start_time')">
                Time
                <i class="fas fa-sort"></i>
              </th>
              <th v-if="role === 'su'">Recipient</th>
              <th>Status</th>
              <th>Checked In</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="Array.isArray(tableData) && tableData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <td>{{ item.contact_name }}</td>
                <td>{{ item.mobile_number }}</td>
                <td>{{ item.appointment_date }}</td>
                <td>{{ item.subject }}</td>
                <td>{{ item.email_address }}</td>
                <td>{{ item.start_time }} - {{ item.end_time }}</td>
                <td v-if="role === 'su'">{{ item.userName }}</td>
                <td>
                  <span :class="'badge bg-' + item.recordStatus.theme">{{ item.recordStatus.label }}</span>
                </td>

                <td>
                  <div v-if="item.recordStatus.label === 'ACTIVE'" class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked-{{ item.id }}" :checked="item.checked_in" :disabled="item.checked_in" @change="confirmCheckIn(item)" />
                    <label class="form-check-label" :for="'flexSwitchCheckChecked-' + item.id">
                      {{ item.checked_in ? 'Checked In' : 'Check In' }}
                    </label>
                  </div>

                  <div v-else class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDisabled" checked="" disabled="" />
                  </div>
                </td>

                <td>
                  <!-- Edit Button -->
                  <button v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'" class="btn btn-outline-primary btn-sm me-3" @click="openModal(item.id)" :disabled="item.checked_in">
                    <i class="fas fa-edit" title="Edit"></i>
                  </button>
                  <button v-else-if="item.recordStatus.label === 'CANCELLED'" class="btn btn-outline-primary btn-sm me-3" disabled>
                    <i class="fas fa-edit" title="Edit (Disabled)"></i>
                  </button>

                  <!-- Cancel Button -->
                  <button v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'" class="btn btn-outline-warning btn-sm me-3" @click="confirmCancel(item.id)" :disabled="item.checked_in">
                    <i class="fas fa-cancel" title="Cancel"></i>
                  </button>
                  <button v-else-if="item.recordStatus.label === 'CANCELLED'" class="btn btn-outline-warning btn-sm me-3" disabled>
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
            <tr v-else>
              <td colspan="5" class="text-center">No data to display</td>
            </tr>
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
  <BookAppointment ref="appointmentModal" @close="handleModalClose" @appointment-created="getAppointments(1)" />
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

  <!-- <b-modal ref="myModal" hide-footer :title="'Contact:' + '' + appointmentDetails.contact_name + ' ' + 'RE:' + ' ' + appointmentDetails.subject" size="xl">-->

  <b-modal ref="myModal" hide-footer size="xl" @hide="handleModalClose">
    <template #title>
      <div class="custom-modal-title">
        <!-- <span class="contact-name">{{ appointmentDetails.contact_name }}</span> -->
        <span class="subject">RE: {{ appointmentDetails.subject }}</span>
        <span class="date"> Date: {{ appointmentDetails.appointment_date }}</span>
        <span class="time"> Time:{{ appointmentDetails.start_time }} - {{ appointmentDetails.end_time }}</span>
        <span class="status"
          >Status:
          <b-badge :variant="recordStatus.theme" class="me-3">
            {{ recordStatus.label }}
          </b-badge>
        </span>
      </div>
    </template>

    <b-row class="justify-content-center">
      <b-col lg="12" sm="12">
        <b-card-body>
          <b-form>
            <b-form-group>
              <b-row>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Name</label>
                  <b-form-input v-model="appointmentDetails.contact_name" id="input-107"></b-form-input>
                  <div v-if="errorDetails.contact_name" class="error">
                    {{ errorDetails.contact_name }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Date</label>
                  <flat-pickr v-model="appointmentDetails.appointment_date" class="form-control" :config="flatPickrConfig" id="datePicker" />

                  <!-- <b-form-input v-model="appointmentDetails.appointment_date" id="input-107" type="date"></b-form-input> -->
                  <div v-if="errorDetails.appointment_date" class="error">
                    {{ errorDetails.appointment_date }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Time</label>
                  <div class="d-flex">
                    <b-form-input v-model="appointmentDetails.start_time" id="input-107" type="time" class="me-2"></b-form-input>
                    <span class="align-self-center">to</span>
                    <b-form-input v-model="appointmentDetails.end_time" id="input-108" type="time" class="ms-2"></b-form-input>
                  </div>
                  <div v-if="errorDetails.start_time" class="error">
                    {{ errorDetails.start_time }}
                  </div>
                  <div v-if="errorDetails.end_time" class="error">
                    {{ errorDetails.end_time }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Phone Number</label>
                  <b-form-input v-model="appointmentDetails.mobile_number" id="input-107"></b-form-input>
                  <div v-if="errorDetails.mobile_number" class="error">
                    {{ errorDetails.mobile_number }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Email</label>
                  <b-form-input v-model="appointmentDetails.email_address" id="input-107"></b-form-input>
                  <div v-if="errorDetails.email_address" class="error">
                    {{ errorDetails.email_address }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Subject</label>
                  <b-form-input v-model="appointmentDetails.subject" id="input-107"></b-form-input>
                  <div v-if="errorDetails.subject" class="error">
                    {{ errorDetails.subject }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Appointment Type</label>
                  <b-form-input v-model="appointmentDetails.appointment_type" id="input-107"></b-form-input>
                  <div v-if="errorDetails.appointment_type" class="error">
                    {{ errorDetails.appointment_type }}
                  </div>
                </b-col>
                <b-col lg="4" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Booked on</label>
                  <b-form-input v-model="appointmentDetails.created_at" id="input-107" disabled></b-form-input>
                  <div v-if="errorDetails.created_at" class="error">
                    {{ errorDetails.created_at }}
                  </div>
                </b-col>
                <!-- preview the pdf file using the pdf viewer -->
                <b-col lg="4" md="12" class="mb-5">
                  <label for="input-107" class="form-label">Attachment</label>
                  <div v-if="downloadLink" class="d-flex align-items-center">
                    <div class="card-body position-relative d-flex align-items-center">
                      <img src="@/assets/modules/file-manager/images/pdf.svg" alt="PDF Icon" class="card-img-left" style="width: 50px; height: 40px; object-fit: contain" />
                      <a :href="downloadLink" target="_blank" class="btn btn-primary btn-sm">View Attachment</a>
                    </div>
                  </div>
                  <div v-else>
                    <p>No attachment</p>
                  </div>
                </b-col>
                <b-col lg="12" md="12" class="mb-3">
                  <label for="'attendees'" class="form-label">Attendees</label>
                  <div>
                    <!-- //show tags of attendees -->
                    <b-form-tags v-model="appointmentDetails.attendees" id="attendees" tag-variant="primary" tag-pills>
                      <template #tag="{ tag, removeTag }">
                        <b-badge variant="primary" class="me-1">
                          {{ tag.staff_id }}
                          <b-icon @click="removeTag" icon="x" />
                        </b-badge>
                      </template>
                    </b-form-tags>
                  </div>
                  <div v-if="errorDetails.attendees" class="error">
                    {{ errorDetails.attendees }}
                  </div>
                </b-col>
                <b-col lg="12" md="12" class="mb-3">
                  <label for="input-107" class="form-label">Description</label>
                  <b-form-textarea v-model="appointmentDetails.description" id="input-107" rows="3"></b-form-textarea>
                  <div v-if="errorDetails.notes" class="error">
                    {{ errorDetails.notes }}
                  </div>
                </b-col>

                <b-col md="12" lg="12" sm="12">
                  <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" @selectedSlotsTimes="handleSelectedSlotsTimes" />
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

                <b-col lg="5" class="mb-3">
                  <div v-if="appointmentDetails.status === 3">
                    <div v-if="timeSlots.length > 1">
                      <h3>{{ suggestedDate }}</h3>
                      <!-- Display the date from the API response -->
                      <!-- <div>
                        <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" @selectedSlotsTimes="handleSelectedSlotsTimes" />
                      </div> -->
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
                      <b-button variant="primary" class="me-3" @click="updateAppointment" ref="updateButton"> Update </b-button>
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

.custom-modal-title {
  display: flex;
  align-items: center; /* Vertically aligns items */
  justify-content: flex-start; /* Ensures items are aligned to the start */
  gap: 4rem; /* Adds spacing between each item */
  width: 100%; /* Makes the title span the full width */
  padding: 0.5rem; /* Adds some padding for better appearance */
}

.custom-modal-title .status {
  margin-left: auto; /* Pushes the status badge to the far right */
}

.custom-modal-title span {
  flex-shrink: 0; /* Prevents items from shrinking if the content overflows */
}

.custom-modal-title .contact-name,
.custom-modal-title .subject,
.custom-modal-title .date,
.custom-modal-title .time {
  color: #333; /* Custom text color */
}
</style>
