<script setup>
import { ref, getCurrentInstance, onMounted, computed, watch } from 'vue'
import AxiosInstance from '@/api/axios'

const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()
const sortOrder = ref('asc') // Sorting order: 'asc' or 'desc'
const sortKey = ref('') // Active column being sorted
const searchQuery = ref('')
// const isArray = ref(false)
const currentPage = ref(1) // The current page being viewed
const totalPages = ref(1) // Total number of pages from the API
const selectedPerPage = ref(20) // Number of items per page (from dropdown)
const perPage = ref(20)
const perPageOptions = ref([10, 20, 50, 100])

// const AddPermission = ref(null)
// const PermissionDetails = ref({
//   name: '',
//   description: '',
//   type: '1',
//   data: '',
//   ruleName: ''
// })
// const ViewPermission = ref(null)

const tableData = ref([])

const errors = ref({})
// const toastPayload = ref(null)

// const showModal = () => {
//   if (AddPermission.value) {
//     AddPermission.value.show()
//   }
// }

//pagination navigation
const goToPage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page
    getPermissions(page)
  }
}

// create a new role api
// const NewPermission = async () => {
//   toastPayload.value = null

//   try {
//     const response = await axiosInstance.post('v1/auth/permission', PermissionDetails.value)

//     // Check if toastPayload exists in the response and update it
//     if (response.data.toastPayload) {
//       toastPayload.value = response.data.toastPayload

//       //close the modal
//       AddPermission.value.hide()

//       //get the data
//       getPermissions()
//       // Show toast notification using the response data
//       proxy.$showToast({
//         title: toastPayload.value.toastMessage || 'success',
//         // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
//         icon: 'success'
//       })
//     } else {
//       // Fallback if toastPayload is not provided in the response
//       proxy.$showToast({
//         title: 'Appointment Deleted successfully',
//         icon: 'success'
//       })
//     }
//   } catch (error) {
//     console.error(error)
//     errors.value = error.response.data.errorPayload.errors
//     const errorMessage = error.response.data.errorPayload.errors?.message || error.response.errorPayload.message || 'An unknown error occurred'

//     proxy.$showToast({
//       title: 'An error occurred',
//       text: errorMessage,
//       icon: 'error'
//     })
//   }
// }

//get roles
const getPermissions = async (page = currentPage.value) => {
  try {
    const response = await axiosInstance.get(`/v1/auth/permissions?page=${page}&per-page=${selectedPerPage.value}&search=${searchQuery.value}`)
    tableData.value = Object.values(response.data.dataPayload.data)
    console.log(tableData.value)

    currentPage.value = response.data.dataPayload.currentPage
    totalPages.value = response.data.dataPayload.totalPages
    perPage.value = response.data.dataPayload.perPage
  } catch (error) {
    console.error(error)
  }
}

watch(searchQuery, () => {
  getPermissions(1)
})

//edit roles

// get single role
// const getPermission = async (name) => {
//   const names = name
//   try {
//     const response = await axiosInstance.get('v1/auth/permission', names.value)
//     PermissionDetails.value = response.data.dataPayload.data
//   } catch (error) {
//     console.error(error)
//   }
// }
// const openModal = async (name) => {
//   if (ViewPermission.value) {
//     ViewPermission.value.show()
//   }
//   await getPermission(name)
// }

// Format timestamp to a readable date
// function formatDate(timestamp) {
//   return new Date(timestamp * 1000).toLocaleString()
// }

const sortedData = computed(() => {
  return [...tableData.value].sort((a, b) => {
    let modifier = sortOrder.value === 'asc' ? 1 : -1
    if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier
    if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier
    return 0
  })
})

const updatePerPage = async () => {
  await getPermissions(1)
}

//search
// const performSearch = async () => {
//   try {
//     const response = await axiosInstance.get(`v1/auth/permission?_search=${searchQuery.value}`)
//     isArray.value = Array.isArray(response.data)
//     tableData.value = response.data.dataPayload.data
//   } catch (error) {
//     // console.error(error);
//     errors.value = error.response.data.errorPayload.errors
//     const errorMessage = error.response.data.errorPayload.errors?.message || error.response.data.errorPayload.message || 'An unknown error occurred'

//     proxy.$showToast({
//       title: 'An error occurred',
//       text: errorMessage,
//       icon: 'error'
//     })
//   }
// }

const SyncPermissions = async () => {
  try {
    const response = await axiosInstance.get('v1/auth/sync-permissions')
    proxy.$showToast({
      title: response.data.toastPayload.toastMessage || 'success',
      icon: 'success'
    })
  } catch (error) {
    console.error(error)
    errors.value = error.response.data.errorPayload.errors
    const errorMessage = error.response.data.errorPayload.errors?.message || error.response.data.errorPayload.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

onMounted(() => {
  getPermissions()
})
</script>
<template>
  <b-col lg="12">
    <b-card>
      <b-row class="mb-5">
        <b-col lg="6"
          ><div>
            <h2>Permissions</h2>
          </div></b-col
        >
        <b-col lg="6"
          ><div class="d-flex justify-content-end">
            <button class="btn btn-primary" @click="SyncPermissions">Sync Permissions</button>
          </div></b-col
        >
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
            <div class="d-flex align-items-center">
              <b-input-group>
                <b-form-input placeholder="Search..." v-model="searchQuery" />
                <b-input-group-append>
                  <b-button variant="primary" @click="getPermissions(1)">Search</b-button>
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
              <!-- <th @click="sortTable('description')">ID <i class="fas fa-sort"></i></th> -->
              <th @click="sortTable('start_date')">Name <i class="fas fa-sort"></i></th>
              <th>Description</th>
              <!-- <th>Data</th> -->
              <!-- <th>RuleName</th> -->
              <!-- <th @click="sortTable('end_date')">Created At<i class="fas fa-sort"></i></th>
              <th>Updated At<i class="fas fa-sort"></i></th> -->
              <!-- <th>Actions</th> -->
            </tr>
          </thead>
          <tbody>
            <template v-if="Array.isArray(tableData) && tableData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <!-- <td>{{ index }}</td> -->
                <td>{{ item.name }}</td>
                <td>{{ item.data }}</td>
                <!-- <td>{{ item.description }}</td> -->

                <!-- <td>{{ item.ruleName }}</td> -->
                <!-- <td>{{ formatDate(item.createdAt) }}</td>
                <td>{{ formatDate(item.updatedAt) }}</td> -->
                <!-- <td>
                  <button type="button" class="btn btn-outline-primary btn-sm me-3" @click="openModal(item.name)">
                    <i class="fas fa-edit" title="edit entry"></i>
                  </button> 
                  <button type="button" class="btn btn-outline-danger btn-sm" @click="confirmDelete(item.id)">
                    <i class="fas fa-trash" title="delete entry"></i>
                  </button>
                </td> -->
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
</template>
<style>
/* Custom CSS to increase modal height */
.taller-modal .modal-dialog {
  max-height: 90vh;
  /* Set a maximum height of 90% of the viewport */
  width: 800px;
  /* Set desired width if needed */
}

.taller-modal .modal-content {
  height: 100%;
  /* Ensures content takes full height of modal-dialog */
  overflow-y: auto;
  /* Adds scrolling if content overflows */
  display: flex;
  flex-direction: column;
}

/* //on small screens  */
@media (max-width: 768px) {
  .taller-modal .modal-dialog {
    max-height: 100vh;
    /* Set a maximum height of 90% of the viewport */
    width: 100%;
    /* Set desired width if needed */
  }
}

.error {
  color: red;
  font-size: 0.9rem;
}
</style>
