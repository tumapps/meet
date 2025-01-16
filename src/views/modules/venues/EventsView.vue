<script setup>
import { onMounted, ref, getCurrentInstance, computed, watch } from 'vue'
import AxiosInstance from '@/api/axios'
import FlatPickr from 'vue-flatpickr-component'

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
const errors = ref('')
const newSpace = ref(null)
const editevent = ref(null)

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

function handleClose() {
  resetErrors()
  resetFormData()
}
const config = {
  dateFormat: 'Y-m-d',
  altInput: true,
  altFormat: 'F j, Y',
  minDate: 'today'
}

const config2 = {
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i',
  time_24hr: true
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

const sortTable = (key) => {
  if (sortKey.value === key) {
    // Toggle the sort order if the same column is clicked again
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
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

const performSearch = async () => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/appointments?_search=${searchQuery.value}`)
    tableData.value = response.data.dataPayload.data
  } catch (error) {
    // console.error(error);
    const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

const showModal = () => {
  if (newSpace.value) {
    newSpace.value.show()
  }
}

const getEvent = async (id) => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/events/${id}`)
    eventDetails.value = response.data.dataPayload
  } catch (error) {
    // console.error(error);
    const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An unknown error occurred'

    proxy.$showToast({
      title: errorMessage,
      text: errorMessage,
      icon: 'error'
    })
  }
}

const openModal = (id) => {
  if (editevent.value) {
    getEvent(id)
    editevent.value.show()
  }
}

const AddEvent = async () => {
  try {
    const response = await axiosInstance.post('v1/scheduler/events', eventDetails.value)

    //get events
    getEvents(1)
    //close the modal
    newSpace.value.hide()
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showAlert({
        title: toastPayload.value.toastMessage,
        icon: toastPayload.value.toastTheme , // You can switch this back to use the theme from the response
        showCancelButton: false,
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'success',
        icon: 'success'
      })
    }
  } catch (error) {
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

const getEvents = async (page) => {
  try {
    const response = await axiosInstance.get('v1/scheduler/events')

    tableData.value = response.data.dataPayload.data

    currentPage.value = response.data.dataPayload.currentPage
    totalPages.value = response.data.dataPayload.totalPages
    perPage.value = response.data.dataPayload.perPage
  } catch (error) {
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'

    proxy.$showToast({
      title: errorMessage,
      // text: errorMessage,
      icon: 'error'
    })
  }
}

const deleteEvent = async (id) => {
  try {
    const response = await axiosInstance.delete(`v1/scheduler/events/${id}`)

    //get events
    getEvents(1)
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Deleted successfully',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'success',
        icon: 'success'
      })
    }
  } catch (error) {
    const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An error occurred'

    proxy.$showToast({
      title: errorMessage,
      // text: errorMessage,
      icon: 'error'
    })
  }
}

const cancelEvent = async (id) => {
  try {
    const response = await axiosInstance.put(`v1/scheduler/events/cancel/${id}`)

    //get events
    getEvents(1)
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Cancelled successfully',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'success',
        icon: 'success'
      })
    }
  } catch (error) {
    const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An error occurred'

    proxy.$showToast({
      title: errorMessage,
      // text: errorMessage,
      icon: 'error'
    })
  }
}

const restoreEvent = async (id) => {
  try {
    const response = await axiosInstance.put(`v1/scheduler/events/${id}/restore`)

    //get events
    getEvents(1)
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // console.log("toastPayload", toastPayload.value); // Log for debugging

      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Restored successfully',
        // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
        icon: 'success'
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'success',
        icon: 'success'
      })
    }
  } catch (error) {
    const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An error occurred'

    proxy.$showToast({
      title: errorMessage,
      // text: errorMessage,
      icon: 'error'
    })
  }
}
onMounted(async () => {
  //fetch appointments and slots and unavailable slots
  getEvents(1)
})
</script>
<template>
  <b-col lg="12">
    <b-card>
      <div>
        <h2>Events</h2>
      </div>
      <b-row class="mb-3">
        <b-col lg="12" md="12" sm="12" class="mb-3">
          <div class="d-flex justify-content-end">
            <b-button variant="primary" @click="showModal"> Add Event </b-button>
          </div>
        </b-col>
        <b-col lg="12" md="12" sm="12">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <!-- <label for="itemsPerPage" class="me-2">Items per page:</label> -->
              <select id="itemsPerPage" v-model="selectedPerPage" @change="updatePerPage" class="form-select form-select-sm" style="width: auto">
                <option v-for="option in perPageOptions" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
            </div>
            <div class="d-flex align-items-center">
              <b-input-group>
                <!-- Search Input -->
                <b-form-input placeholder="Search..." aria-label="Search" v-model="searchQuery" />
                <!-- Search Button -->
                <b-input-group-append>
                  <b-button variant="primary" @click="performSearch"> Search </b-button>
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
              <th @click="sortTable('title')">
                Title
                <i class="fas fa-sort"></i>
              </th>
              <th>Description</th>

              <th @click="sortTable('appointment_date')">
                Date
                <i class="fas fa-sort"></i>
              </th>

              <th @click="sortTable('start_time')">
                Time
                <i class="fas fa-sort"></i>
              </th>

              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="Array.isArray(tableData) && tableData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <td>{{ item.title }}</td>
                <td>{{ item.description }}</td>
                <td>{{ item.start_date }} - {{ item.end_date }}</td>
                <td>{{ item.start_time }} - {{ item.end_time }}</td>
                <td>
                  <span :class="'badge bg-' + item.recordStatus.theme">{{ item.recordStatus.label }}</span>
                </td>
                <td>
                  <button class="btn btn-outline-primary btn-sm me-3" @click="openModal(item.id)">
                    <i class="fas fa-edit" title="Edit"></i>
                  </button>
                  <!-- <button
                                        v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'"
                                        class="btn btn-outline-warning btn-sm me-3" @click="confirmCancel(item.id)">
                                        <i class="fas fa-cancel" title="Cancel"></i>
                                    </button>
                                    <button
                                        v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'"
                                        class="btn btn-outline-danger btn-sm me-3" @click="confirmDelete(item.id)">
                                        <i class="fas fa-trash" title="Delete"></i>
                                    </button>
                                    <button v-if="item.recordStatus.label === 'DELETED'"
                                        class="btn btn-outline-danger btn-sm" @click="confirmRestore(item.id)">
                                        <i class="fas fa-undo" title="Restore"></i>
                                    </button>
                                    <button v-if="item.recordStatus.label === 'CANCELLED'"
                                        class="btn btn-outline-exclamation-circle btn-sm">
                                        <i class="fas fa-undo"
                                            title="appointment has been cancelled no further action can be done"></i>
                                    </button> -->
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

  <b-modal ref="newSpace" title="Add Event" class="modal-fullscreen my-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleClose">
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
      <b-col md="5">
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
    <div class="d-flex justify-content-end">
      <b-button @click="AddEvent" variant="primary">Create</b-button>
    </div>
  </b-modal>

  <b-modal ref="editevent" title="Edit Event" class="modal-fullscreen my-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer>
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
          <flat-pickr v-model="eventDetails.event_date" class="form-control" :config="config" id="startDatePicker" />
        </div>
        <div v-if="errors.event_date" class="error" aria-live="polite">{{ errors.event_date }}</div>
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
      <b-col md="5">
        <div class="mb-3">
          <label for="endTimePicker" class="form-label">End Time</label>
          <flat-pickr v-model="eventDetails.end_time" class="form-control" :config="config2" id="endTimePicker" />
        </div>
        <div v-if="errors.end_time" class="error" aria-live="polite">{{ errors.end_time }}</div>
      </b-col>
    </b-row>
    <div class="d-flex justify-content-end">
      <b-button @click="UpdateEvent" variant="primary">Update</b-button>
    </div>
  </b-modal>
</template>
<style scoped>
.error {
  color: red;
  font-size: 0.8rem;
}
</style>
