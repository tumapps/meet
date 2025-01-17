<script setup>
import { ref, getCurrentInstance, onMounted, computed, watch } from 'vue'
import AxiosInstance from '@/api/axios'

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
const perPageOptions = ref([10, 20, 50])
const searchQuery = ref('')
const errors = ref({})

const updatePerPage = async () => {
  await getPendingApprovals(selectedPerPage.value)
}

const goToPage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page
    getPendingApprovals(page)
  }
}

const performSearch = async () => {
  await getPendingApprovals(1)
}

const sortedData = computed(() => {
  return [...tableData.value].sort((a, b) => {
    let modifier = sortOrder.value === 'asc' ? 1 : -1
    if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier
    if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier
    return 0
  })
})
//get pending approvals

const getPendingApprovals = async (page) => {
  try {
    const params = {
      page: page, // Current page number
      'per-page': selectedPerPage.value // Items per page
    }

    // Include search query if it's not empty
    if (searchQuery.value) {
      params._search = searchQuery.value
    }

    const response = await axiosInstance.get('v1/scheduler/pending-appointments', { params })

    tableData.value = response.data.dataPayload.data
    console.log(response.data.dataPayload.data)
    currentPage.value = response.data.current_page
    totalPages.value = response.data.last_page
    perPage.value = response.data.per_page
    isArray.value = Array.isArray(response.data.data)
  } catch (error) {
    console.error(error)
    // Check if error.response exists before accessing its properties
    if (error.response && error.response.data && error.response.data.errorPayload) {
      errors.value = error.response.data.errorPayload.errors
    } else {
      // Provide a fallback error message if response or errorPayload is missing
      const errorMessage = error.message || 'An unknown error occurred'

      // Assuming `proxy.$showToast` is a function to display error messages
      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

watch(searchQuery, () => {
  getPendingApprovals(1)
})

const ApproveBooking = async (id) => {
  try {
    const response = await axiosInstance.put(`v1/scheduler/approve/${id}`)
    getPendingApprovals(1)

    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'operation successful',
        icon: toastPayload.value.toastTheme || 'success' // You can switch this back to use the theme from the response
        // icon: 'success',
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'operation successful',
        icon: 'success'
      })
    }
    getPendingApprovals(1)
  } catch (error) {
    // console.error(error);
    const errorMessage = error.response.data.errorPayload.errors?.message

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

const RejectBooking = async (id) => {
  try {
    const response = await axiosInstance.put(`v1/scheduler/reject/${id}`)

    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      // Show toast notification using the response data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'operation successful',
        icon: toastPayload.value.toastTheme || 'success' // You can switch this back to use the theme from the response
        // icon: 'success',
      })
    } else {
      // Fallback if toastPayload is not provided in the response
      proxy.$showToast({
        title: 'operation successful',
        icon: 'success'
      })
    }
    getPendingApprovals(1)
  } catch (error) {
    // console.error(error);
    if (error.response && error.response.data.errorPayload) {
      errors.value = error.response.data.errorPayload.errors
    } else {
      const errorMessage = error.response.data.errorPayload.errors?.message

      proxy.$showToast({
        title: 'An error occurred',
        text: errorMessage,
        icon: 'error'
      })
    }
  }
}

const confirmReject = (id) => {
  // selectedAvailability.value = id

  proxy
    .$showAlert({
      title: 'REJECT',
      text: 'By rejecting the meeting will not take place unless they get another space. Do you want to proceed?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Proceed',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        RejectBooking(id)
        getPendingApprovals(1)
      }
    })
}

onMounted(() => {
  getPendingApprovals(1)
})
</script>
<template>
  <b-col lg="12">
    <b-card>
      <div>
        <h2>Requests</h2>
      </div>
      <b-row class="mb-3">
        <b-col lg="12" class="mb-3">
          <!-- <div class="d-flex justify-content-end">
                        <b-button variant="primary" @click="showModal">
                            New Space
                        </b-button>
                    </div> -->
        </b-col>
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
            <div class="d-flex align-items-center">
              <b-input-group>
                <b-form-input placeholder="Search..." v-model="searchQuery" />
                <b-input-group-append>
                  <b-button variant="primary" @click="performSearch">Search</b-button>
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
              <th @click="sortTable('start_time')">Time <i class="fas fa-sort"></i></th>
              <th @click="sortTable('contact_name')">Contact Name <i class="fas fa-sort"></i></th>
              <th @click="sortTable('userName')">Recipient <i class="fas fa-sort"></i></th>
              <th @click="sortTable('email_address')">Email <i class="fas fa-sort"></i></th>
              <th @click="sortTable('mobile_number')">Mobile Number <i class="fas fa-sort"></i></th>
              <th @click="sortTable('subject')">Subject <i class="fas fa-sort"></i></th>
              <th @click="sortTable('appointment_type')">Appointment Type <i class="fas fa-sort"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="Array.isArray(tableData) && tableData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <td>{{ item.start_time }}- {{ item.end_time }}</td>
                <td>{{ item.contact_name }}</td>
                <td>{{ item.userName }}</td>
                <td>{{ item.email_address }}</td>
                <td>{{ item.mobile_number }}</td>
                <td>{{ item.subject }}</td>
                <td>{{ item.appointment_type }}</td>
                <td>
                  <!-- Actions -->
                  <button class="btn btn-outline-primary btn-sm me-3" @click="ApproveBooking(item.id)">
                    <i class="fas fa-edit" title="Approve"></i>
                  </button>
                  <button v-if="item.is_deleted !== '1'" class="btn btn-outline-danger btn-sm me-3" @click="confirmReject(item.id)">
                    <i class="fas fa-trash" title="REJECT"></i>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="9" class="text-center">No records found.</td>
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
</template>
