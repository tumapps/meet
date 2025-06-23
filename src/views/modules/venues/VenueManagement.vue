<script setup>
import { onMounted, ref, getCurrentInstance, computed, watch } from 'vue'
import AxiosInstance from '@/api/axios'
import FlatPickr from 'vue-flatpickr-component'
import { useAuthStore } from '@/store/auth.store.js'

const authStore = useAuthStore()
// Modal reference
const newSpace = ref(null)
const editSpace = ref(null)
const toastPayload = ref({})

const { proxy } = getCurrentInstance()
const axiosInstance = AxiosInstance()
const tableData = ref([])
const sortKey = ref('') // Active column being sorted
const sortOrder = ref('asc') // Sorting order: 'asc' or 'desc'
const isArray = ref(false)
const currentPage = ref(1) // The current page being viewed
const totalPages = ref(1) // Total number of pages from the API
const selectedPerPage = ref(20) // Number of items per page (from dropdown)
const perPageOptions = ref([20, 50])
const searchQuery = ref('')
const selectedAvailability = ref('')
const errors = ref({})
const isLoading = ref(false)

//get user id from session storage

const user_id = ref('')
user_id.value = authStore.getUserId()
const perPage = ref(50) // Number of items per page (from API response)

// Selected date
const InitialSpaceDetails = {
  name: '',
  level_id: '',
  opening_time: '',
  closing_time: '',
  capacity: ''
}

const SpaceDetails = ref({ ...InitialSpaceDetails })

const resetFormData = () => {
  SpaceDetails.value = { ...InitialSpaceDetails }
}

const handleClose = () => {
  resetFormData()
  errors.value = {}
}

const config2 = {
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i',
  time_24hr: true,
  minuteIncrement: 10
}

// Function to show the modal
const showModal = () => {
  if (newSpace.value) {
    newSpace.value.show()
  }
}

// watch(newSpace, (newSpace, old) =>{
//   if(newSpace !== )
// })

const openModal = (id) => {
  if (editSpace.value) {
    getSpace(id)
    editSpace.value.show()
  }
}

const sortTable = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

// const performSearch = async () => {
//   try {
//     const response = await axiosInstance.get(`v1/scheduler/availabilities?_search=${searchQuery.value}`)
//     isArray.value = Array.isArray(response.data)
//     tableData.value = response.data.dataPayload.data
//   } catch (error) {
//     // console.error(error);
//     proxy.$showToast({
//       title: 'An error occurred',
//       text: 'Ooops! an error occured',
//       icon: 'error'
//     })
//   }
// }

// const confirmCancel = (id) => {
//     selectedAvailability.value = id;

//     proxy.$showAlert({
//         title: 'Are you sure?',
//         text: 'You are about to DELETE this entry. Do you want to proceed?',
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonText: 'Yes, DELETE it!',
//         cancelButtonText: 'No, keep it',
//         confirmButtonColor: '#d33',
//         cancelButtonColor: '#097B3E',
//     }).then((result) => {
//         if (result.isConfirmed) {
//             cancelAvailability(id);
//             getSpaces(1);
//         }
//     });
// };

const getSpaces = async (page) => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/spaces?page=${page}&per-page=${selectedPerPage.value}&_search=${searchQuery.value}`)
    isArray.value = Array.isArray(response.data)
    tableData.value = response.data.dataPayload.data
    currentPage.value = response.data.dataPayload.currentPage
    totalPages.value = response.data.dataPayload.totalPages
    perPage.value = response.data.dataPayload.perPage

  } catch (error) {
    const errorMessage = error.response?.data?.errorPayload?.errors?.message || error.response?.data?.errorPayload?.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred ',
      text: errorMessage,
      icon: 'error'
    })
  }
}
watch(searchQuery, () => {
  getSpaces(1)
})

const getSpace = async (id) => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/space/${id}`)
    SpaceDetails.value = response.data.dataPayload.data
  } catch (error) {
    // console.error(error);
    const errorMessage = error?.response?.data?.errorPayload?.errors?.message || error?.response?.data?.errorPayload?.message || error?.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

const sortedData = computed(() => {
  return [...tableData.value].sort((a, b) => {
    let modifier = sortOrder.value === 'asc' ? 1 : -1
    if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier
    if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier
    return 0
  })
})

// Function to save settings (add API or local storage logic here)
const saveSpace = async () => {
  try {
    isLoading.value = true
    // Make the API call to save availability details
    const response = await axiosInstance.post('v1/scheduler/space', SpaceDetails.value)
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // Show toast notification using the response data
      //close modal
      newSpace.value.hide()
      proxy.$showAlert({
        title: toastPayload.value.toastTheme,
        icon: toastPayload.value.toastTheme, // You can switch this back to use the theme from the response
        text: toastPayload.value.toastMessage,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 4000
      })

      // Clear the form fields after successful submission
      SpaceDetails.value = {
        name: '',
        level_id: '',
        opening_time: '',
        closing_time: ''
      }

      errors.value = {}
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Created successfully',
        icon: 'success'
      })
    }

    // Refresh availabilities
    getSpaces(1)
  } catch (error) {
    // Safely handle the error response and check for errorPayload
    if (error.response && error.response.data && error.response.data.errorPayload) {
      errors.value = error.response.data.errorPayload.errors
    } else {
      // Display error notification if something went wrong
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: errorMessage,
        text: errorMessage,
        icon: 'error'
      })
    }
  } finally {
    isLoading.value = false
  }
}

const updateSpaceDetails = async (id) => {
  try {
    isLoading.value = true

    // Make the API call to update availability details
    const response = await axiosInstance.put(`v1/scheduler/space/${id}`, SpaceDetails.value)

    // Show success toast notification
    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      //close the modal
      getSpaces(1)
      editSpace.value.hide()
      toastPayload.value = response.data.toastPayload
      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Updated successfully',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showAlert({
        title: 'Updated successfully',
        icon: 'success',
        showCancelButton: false,
        showConfirmButton: false,
        timer: 4000
      })
    }
  } catch (error) {
    // Check if error.response exists to avoid TypeError
    if (error?.response && error.response?.data && error.response?.data.errorPayload) {
      // Extract and handle errors from server response
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  } finally {
    isLoading.value = false
  }
}

const updatePerPage = async () => {
  await getSpaces(selectedPerPage.value)
}

const goToPage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page
    getSpaces(page)
  }
}

const deleteSpace = async (id) => {
  toastPayload.value = null

  try {
    const response = await axiosInstance.delete(`v1/scheduler/space/${id}`)
    getSpace(id)
    getSpaces(1)
    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showAlert({
        title: toastPayload.value.toastMessage,
        icon: toastPayload.value.toastTheme, // You can switch this back to use the theme from the response
        showCancelButton: false,
        showConfirmButton: false,
        timer: 1500,
        showprogressBar: true
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'Space Deleted successfully',
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

const confirmAction = (id, action) => {
  selectedAvailability.value = id
  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: ' Do you want to' + ' ' + action + ' ' + 'this Space ?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: action,
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        deleteSpace(id)
        getSpaces(1)
      }
    })
}

const toggleLock = async (id) => {
  // Toggle the lock status
  // item.is_locked = !item.is_locked;

  try {
    // Optionally: Send an API request to update the lock status in the backend
    const response = await axiosInstance.put(`v1/scheduler/lock-space/${id}`)
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload

      //get spaces
      getSpaces(1)
      // Show toast notification using the response data

      proxy.$showAlert({
        title: toastPayload.value.toastTheme,
        text: toastPayload.value.toastMessage,
        icon: toastPayload.value.toastTheme, // You can switch this back to use the theme from the response
        showCancelButton: false,
        showConfirmButton: false,
        timer: 4000
      })
    }
  } catch (error) {
    const errorMessage = error?.response?.data?.errorPayload?.errors?.message || 'An unknown error occurred'

    proxy.$showAlert({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error',
      showCancelButton: true,
      showConfirmButton: false
    })

    console.error('Failed to update lock status:', error)
  }
}

onMounted(async () => {
  getSpaces(1)
})
</script>

<template>
  <!-- Modal Component -->

  <b-col lg="12">
    <b-card>
      <b-row>
        <b-col lg="6">
          <h3 class="mb-3">Space Management</h3>
        </b-col>
        <b-col lg="6" class="mb-3">
          <div class="d-flex justify-content-end">
            <b-button variant="primary" @click="showModal"> New Space </b-button>
          </div>
        </b-col>
      </b-row>

      <b-row class="mb-3">
        <b-col lg="12">
          <!-- Table Controls -->
          <div class="d-flex justify-content-between">
            <!-- Pagination Controls -->

            <div class="d-flex align-items-center">
              <select id="itemsPerPage" v-model="selectedPerPage" @change="updatePerPage" class="form-select form-select-sm">
                <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
              </select>
            </div>

            <!-- Search Box -->
            <div class="d-flex align-items-center" style="max-width: 180px">
              <b-input-group>
                <b-form-input placeholder="Search..." v-model="searchQuery" />
                <b-input-group-append>
                  <b-button variant="primary" @click="getSpaces()">
                    <i class="fas fa-search"></i>
                  </b-button>
                </b-input-group-append>
              </b-input-group>
            </div>
          </div>
        </b-col>
      </b-row>
      <!-- Table Data -->
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th @click="sortTable('description')">Name <i class="fas fa-sort"></i></th>
              <th @click="sortTable('start_date')">Opening Hours <i class="fas fa-sort"></i></th>
              <th @click="sortTable('end_date')">Closing Hours <i class="fas fa-sort"></i></th>
              <th @click="sortTable('start_time')">Capacity <i class="fas fa-sort"></i></th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="Array.isArray(tableData) && tableData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <td>{{ item.name }}</td>
                <td>{{ item.opening_time }}</td>
                <td>{{ item.closing_time }}</td>
                <td>{{ item.capacity }}</td>
                <td>
                  <span :class="item.is_locked ? 'badge bg-warning' : 'badge bg-success'" @click="toggleLock(item.id)">
                    {{ item.is_locked ? 'Locked' : 'Unlocked' }}
                  </span>
                </td>
                <td>
                  <!-- //info button -->
                  <!-- <button class="btn btn-outline-info btn-sm me-3" @click="viewDeatils(item.id)">
                    <i class="fas fa-info" title="View"></i>
                  </button> -->
                  <button class="btn btn-outline-primary btn-sm me-3" @click="openModal(item.id)">
                    <i class="fas fa-edit" title="Edit"></i>
                  </button>
                  <button v-if="item.is_deleted !== 1" class="btn btn-outline-danger btn-sm me-3" @click="confirmAction(item.id, 'Delete')">
                    <i class="fas fa-trash" title="Delete"></i>
                  </button>
                  <button v-if="item.is_deleted === 1" class="btn btn-outline-danger btn-sm" @click="confirmAction(item.id, 'Restore')">
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
      </div>
      <!-- Pagination -->
      <b-col sm="12" lg="12" class="mt-5 d-flex justify-content-end mb-n5">
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

  <!-- //modal -->
  <b-modal ref="newSpace" title="New Space" class="modal-fullscreen my-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleClose">
    <b-row>
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">Name</label>
          <input type="text" v-model="SpaceDetails.name" class="form-control" id="name" />
        </div>
        <div v-if="errors.name" class="error" aria-live="polite">{{ errors.name }}</div>
      </b-col>

      <!-- //add capacity -->
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="spacecapacity" class="form-label">Capacity</label>
          <input type="text" v-model="SpaceDetails.capacity" class="form-control" id="capacity" />
        </div>
        <div v-if="errors.capacity" class="error" aria-live="polite">{{ errors.capacity }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="6">
        <div class="mb-3">
          <label for="startTimePicker" class="form-label">Opening Hours</label>
          <flat-pickr v-model="SpaceDetails.opening_time" class="form-control" :config="config2" id="startTimePicker" />
        </div>
        <div v-if="errors.opening_time" class="error" aria-live="polite">{{ errors.opening_time }}</div>
      </b-col>
      <b-col md="5">
        <div class="mb-3">
          <label for="endTimePicker" class="form-label">Closing Hours</label>
          <flat-pickr v-model="SpaceDetails.closing_time" class="form-control" :config="config2" id="endTimePicker" />
        </div>
        <div v-if="errors.closing_time" class="error" aria-live="polite">{{ errors.closing_time }}</div>
      </b-col>
    </b-row>
    <div class="d-flex justify-content-center">
      <b-button v-if="isLoading === false" @click="saveSpace" variant="primary">Save</b-button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Saving...
      </button>
    </div>
  </b-modal>

  <b-modal ref="editSpace" title="Edit Space" class="modal-fullscreen my-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleClose">
    <b-row>
      <b-col md="12">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">Name</label>
          <input type="text" v-model="SpaceDetails.name" class="form-control" id="name" />
        </div>
        <div v-if="errors.name" class="error" aria-live="polite">{{ errors.name }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="6">
        <div class="mb-3">
          <label for="startTimePicker" class="form-label">Opening Hours</label>
          <flat-pickr v-model="SpaceDetails.opening_time" class="form-control" :config="config2" id="startTimePicker" />
        </div>
        <div v-if="errors.opening_time" class="error" aria-live="polite">{{ errors.opening_time }}</div>
      </b-col>
      <b-col md="6">
        <div class="mb-3">
          <label for="endTimePicker" class="form-label">Closing Hours</label>
          <flat-pickr v-model="SpaceDetails.closing_time" class="form-control" :config="config2" id="endTimePicker" />
        </div>
        <div v-if="errors.closing_time" class="error" aria-live="polite">{{ errors.closing_time }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="12">
        <div class="mb-3">
          <label for="capacity" class="form-label">Capacity</label>
          <input type="text" v-model="SpaceDetails.capacity" class="form-control" id="capacity" />
        </div>
        <div v-if="errors.capacity" class="error" aria-live="polite">{{ errors.capacity }}</div>
      </b-col>
    </b-row>
    <div class="d-flex justify-content-center mt-5">
      <b-button v-if="isLoading === false" @click="updateSpaceDetails(SpaceDetails.id)" variant="primary">Update</b-button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Updating...
      </button>
    </div>
  </b-modal>
</template>
<style scoped>
.error {
  color: red;
  font-size: 0.8rem;
}
</style>
