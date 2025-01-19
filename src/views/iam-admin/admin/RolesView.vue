<script setup>
import { ref, getCurrentInstance, onMounted, computed, watch } from 'vue'
import AxiosInstance from '@/api/axios'
// import { useRouter } from 'vue-router'
import RolePermissions from '@/components/RolePermissions.vue'

// const router = useRouter()
const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()
const sortOrder = ref('asc') // Sorting order: 'asc' or 'desc's
const sortKey = ref('') // Active column being sorted
const searchQuery = ref('')
// const isArray = ref(false)
const currentPage = ref(1) // The current page being viewed
const totalPages = ref(1) // Total number of pages from the API
const selectedPerPage = ref(20) // Number of items per page (from dropdown)
const perPageOptions = ref([10, 20, 50, 100])
// const appointmentModal = ref(null)

const AddRole = ref(null)
const InitialRoleDetails = {
  name: '',
  description: '',
  type: '1',
  data: '',
  ruleName: ''
}

const RoleDetails = ref({ ...InitialRoleDetails })

const resetFormData = () => {
  RoleDetails.value = { ...InitialRoleDetails.value }
}
// const ViewRole = ref(null)

const tableData = ref([])

const errors = ref({})

const resetErrors = () => {
  errors.value = {}
}
const toastPayload = ref(null)

const handleClose = () => {
  resetErrors()
  resetFormData()
}

const showModal = () => {
  if (AddRole.value) {
    AddRole.value.show()
  }
}

//pagination navigation
const goToPage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page
    getRoles(page)
  }
}

// create a new role api
const NewRole = async () => {
  toastPayload.value = null

  try {
    const response = await axiosInstance.post('v1/auth/role', RoleDetails.value)

    // Check if toastPayload exists in the response and update it
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload

      //close the modal
      AddRole.value.hide()

      //get the data
      getRoles()
      // Show toast notification using the response data
      proxy.$showAlert({
        title: toastPayload.value.toastTheme,
        icon: toastPayload.value.toastTheme, // You can switch this back to use the theme from the response
        text: toastPayload.value.toastMessage,
        timer: 2000,
        showConfirmButton: false,
        showCancelButton: false
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
    errors.value = error.response.data.errorPayload.errors
    const errorMessage = error.response.data.errorPayload.errors?.message || error.response.errorPayload.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

//get roles
const getRoles = async (page = currentPage.value) => {
  try {
    const response = await axiosInstance.get(`/v1/auth/roles?page=${page}&per-page=${selectedPerPage.value}&_search=${searchQuery.value}`)
    tableData.value = Object.values(response.data.dataPayload.data)
    console.log(tableData.value)
  } catch (error) {
    console.error(error)
  }
}
watch(searchQuery, () => {
  // getRoles(1)

  if (searchQuery.value === '') {
    getRoles()
  } else {
    performSearch()
  }
})

const roleModal = ref(null)
const roleName = ref('')
const showRoleModal = (role) => {
  roleName.value = role
  console.log('1', roleName.value)
  roleModal.value.$refs.roleModal.show()
  //pass the role name to the modal as value for roleName
}

// Format timestamp to a readable date
function formatDate(timestamp) {
  return new Date(timestamp * 1000).toLocaleString()
}

const sortedData = computed(() => {
  return [...tableData.value].sort((a, b) => {
    let modifier = sortOrder.value === 'asc' ? 1 : -1
    if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier
    if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier
    return 0
  })
})

const updatePerPage = async () => {
  await getRoles(1)
}

// search
const performSearch = async () => {
  try {
    const response = await axiosInstance.get(`v1/auth/manage-role?_search=${searchQuery.value}`)
    tableData.value = Object.values(response.data.dataPayload.data)
  } catch (error) {
    // console.error(error);
    errors.value = error.response.data.errorPayload.errors
    const errorMessage = error.response.data.errorPayload.errors?.message || error.response.errorPayload.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}

onMounted(() => {
  getRoles()
})
</script>
<template>
  <b-col lg="12">
    <b-card>
      <div>
        <h2>Roles</h2>
      </div>
      <b-row class="mb-3">
        <b-col lg="12" class="mb-3">
          <div class="d-flex justify-content-end">
            <b-button variant="primary" @click="showModal"> New Role </b-button>
          </div>
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
                  <b-button variant="primary" @click="getRoles(1)">Search</b-button>
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
              <th @click="sortTable('description')">ID <i class="fas fa-sort"></i></th>
              <th @click="sortTable('start_date')">Role Name <i class="fas fa-sort"></i></th>
              <th>Description</th>
              <th>Data</th>
              <th @click="sortTable('end_date')">Created At<i class="fas fa-sort"></i></th>
              <th>Updated At<i class="fas fa-sort"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="Array.isArray(tableData) && tableData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <td>{{ index }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.description }}</td>
                <td>{{ item.data }}</td>
                <td>{{ formatDate(item.createdAt) }}</td>
                <td>{{ formatDate(item.updatedAt) }}</td>
                <td>
                  <button type="button" class="btn btn-outline-primary btn-sm me-3" @click="showRoleModal(item.name)">
                    <i class="fa-solid fa-shield" style="color: #076232"></i>
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm" @click="confirmDelete(item.id)">
                    <i class="fa-solid fa-trash"></i>
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

  <!-- //add new role modal -->
  <b-modal ref="AddRole" title="Add Role" class="my-modal taller-modal modal-fullscreen" no-close-on-esc size="xl" hide-footer centered hide-header-close="false" @hide="handleClose">
    <template #modal-header="{ close }">
      <!-- Custom header with title and close button -->
      <h5 class="modal-title">Add Role</h5>
      <b-button variant="light" @click="close" class="close"> &times; </b-button>
    </template>

    <b-row>
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">Role Name</label>
          <b-form-input v-model="RoleDetails.name" id="startDatePicker" type="text" placeholder="Enter Role Name"></b-form-input>
        </div>
        <div v-if="errors.name" class="error" aria-live="polite">{{ errors.name }}</div>
      </b-col>
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">Role Data</label>
          <b-form-input v-model="RoleDetails.data" id="roledata" type="text" placeholder="Enter Role data"></b-form-input>
        </div>
        <div v-if="errors.data" class="error" aria-live="polite">{{ errors.data }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="roledescription" class="form-label">Description</label>
          <b-form-input v-model="RoleDetails.description" id="roledescription" type="text" placeholder="Enter description"></b-form-input>
        </div>
        <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
      </b-col>
      <b-col md="12" lg="6">
        <div class="mb-3">
          <label for="rolerulename" class="form-label">Rule name</label>
          <b-form-input v-model="RoleDetails.ruleName" id="rolerulename" type="text" placeholder="Enter Role data"></b-form-input>
        </div>
        <div v-if="errors.ruleName" class="error" aria-live="polite">{{ errors.ruleName }}</div>
      </b-col>
    </b-row>

    <div class="d-flex justify-content-end">
      <b-button @click="NewRole" variant="primary">Save</b-button>
    </div>
  </b-modal>

  <!-- //view role its Permissions and child roles -->

  <RolePermissions ref="roleModal" :roleName="roleName" />
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
