<script setup>
import { ref, onMounted, computed, getCurrentInstance } from 'vue'
import AxiosInstance from '@/api/axios'
import Adduser from '@/components/AddUser.vue'

const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()
const errors = ref({})

// Data variables
const tableData = ref([])
const searchQuery = ref('')
const perPageOptions = [5, 10, 15, 20]
const selectedPerPage = ref(perPageOptions[0])
const currentPage = ref(1)
const totalPages = ref(1)
const isArray = ref(false)
const userData = ref({
  roles: [''],
  user_id: ''
})

// Fetch users from API with pagination
const getUsers = async (page = 1) => {
  try {
    const response = await axiosInstance.get(`/v1/auth/users?page=${page}&per-page=${selectedPerPage.value}`)

    if (response.data && response.data.dataPayload) {
      tableData.value = response.data.dataPayload.data
      currentPage.value = response.data.dataPayload.currentPage || 1
      totalPages.value = response.data.dataPayload.totalPages || 1
    }

    isArray.value = Array.isArray(tableData.value)

    console.log('tableData', tableData.value)
  } catch (error) {
    // console.error('Error fetching users:', error);
  }
}

// Handle items per page change
const updatePerPage = () => {
  getUsers(1) // Fetch data for the first page with the new perPage value
}

// Perform search (if search query is used)
const performSearch = async () => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/appointments?_search=${searchQuery.value}`)
    tableData.value = response.data.dataPayload.data
  } catch (error) {
    // console.error(error);
    proxy.$showToast({
      title: 'An error occurred ',
      text: 'Ooops! an error occured',
      icon: 'error'
    })
  }
}

// Pagination logic
const goToPage = (page) => {
  // console.log('page', page);
  if (page > 0 && page <= totalPages.value) {
    getUsers(page)
  }
}

// Computed data for sorting
const sortedData = computed(() => {
  return tableData.value.filter((item) => {
    return item.fullname.toLowerCase().includes(searchQuery.value.toLowerCase()) || item.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) || item.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  })
})

const addUserModal = ref(null)
const openAddUserModal = () => {
  addUserModal.value.toggleModal()
}

const getUser = async (id) => {
  try {
    const response = await axiosInstance.get(`/v1/auth/user?id=${id}`)

    if (response.data.dataPayload && !response.data.errorPayload) {
      userData.value = response.data.dataPayload.data.user
    }

    console.log('userData', userData.value)
  } catch (error) {
    // Check if error.response is defined before accessing it
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An unknown error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}
const ViewUser = ref(null)

// Function to open modal
const openModal = (id) => {
  if (ViewUser.value) {
    ViewUser.value.show() // Ensure you are calling the correct modal method
  }
  getUser(id)
}

//get roles from the api

const roles = ref([])
const rolesData = ref({})
const getRoles = async () => {
  try {
    const response = await axiosInstance.get('/v1/auth/roles')
    rolesData.value = Object.values(response.data.dataPayload.data)
    roles.value = rolesData.value.map((role) => role.name)
    console.log('roles', roles.value)
  } catch (error) {
    console.error(error)
  }
}

// Fetch users on mount
onMounted(() => {
  getUsers(1)
  getRoles()
})

const toastPayload = ref('')

const toggleStatus = async (id) => {
  console.log('id', id)
  try {
    const response = await axiosInstance.put(`/v1/auth/lock-account/${id}`)

    if (response.data.dataPayload) {
      toastPayload.value = response.data.dataPayload.data.message
      proxy.$showAlert({
        icon: toastPayload.value.toastTheme || 'success',
        text: toastPayload.value.toastMessage || 'Status updated',
        timer: 3000,
        showCancelButton: false,
        showConfirmButton: false,
        timerProgressBar: true
      })
    } else {
      proxy.$showAlert({
        title: 'success',
        icon: 'success',
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      })
    }

    getUsers(1)
    //close modal
    ViewUser.value.hide()
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
const confirmStatusChange = (id) => {
  const originalState = userData.value.status

  // Preserve the original state

  // Temporarily toggle the state
  if (userData.value.status === 10) {
    userData.value.status = 9
  } else {
    userData.value.status = 10
  }

  proxy
    .$showAlert({
      title: 'Are you sure?',
      text: 'You are about to change the status of this user. Do you want to proceed?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'yes',
      cancelButtonText: 'cancel',
      confirmButtonColor: '#076232',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        toggleStatus(id)
      } else {
        userData.value.status = originalState
        console.log('userData.value.status ori', userData.value.status)
      }
    })
}

//update user details
const UpdateUser = async () => {
  try {
    const response = await axiosInstance.put(`/v1/auth/update-user/${userData.value.user_id}`, userData.value)

    if (response.data.dataPayload) {
      proxy.$showAlert({
        title: response.data.dataPayload.data.toastTheme || 'success',
        text: response.data.dataPayload.data.Message || 'User updated',
        icon: response.data.dataPayload.data.toastTheme || 'success',
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      })
    }

    getUsers(1)
    //close modal
    ViewUser.value.hide()
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
</script>
<template>
  <b-col lg="12">
    <b-card class="h-100">
      <div>
        <h2>Users</h2>
      </div>
      <b-row class="mb-3">
        <b-col lg="12" md="12" sm="12" class="mb-3">
          <div class="d-flex justify-content-end">
            <button variant="primary" @click="openAddUserModal" class="btn btn-primary">Add User</button>
          </div>
        </b-col>
        <b-col lg="12" md="12" sm="12">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <label for="itemsPerPage" class="me-2">Items per page:</label>
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
        <!-- Table Section -->
        <table class="table table-hover">
          <thead>
            <tr>
              <th @click="sortTable('fullname')">FirstName <i class="fas fa-sort"></i></th>
              <th @click="sortTable('username')">Username <i class="fas fa-sort"></i></th>
              <th @click="sortTable('mobile_number')">Mobile Number <i class="fas fa-sort"></i></th>
              <th @click="sortTable('email')">Email <i class="fas fa-sort"></i></th>
              <th @click="sortTable('role')">Role <i class="fas fa-sort"></i></th>
              <th @click="sortTable('last_Activity')">Last Activity <i class="fas fa-sort"></i></th>
              <th @click="sortTable('status')">Status <i class="fas fa-sort"></i></th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="isArray && sortedData.length > 0">
              <tr v-for="(item, index) in sortedData" :key="index">
                <td>{{ item.fullname }}</td>
                <td>{{ item.username }}</td>
                <td>{{ item.mobile }}</td>
                <td>{{ item.email }}</td>
                <td>{{ item.roles[0] }}</td>
                <td>{{ item.last_Activity }}</td>
                <td>
                  <span :class="item.status === 10 ? 'badge bg-success' : 'badge bg-warning'">
                    {{ item.status === 10 ? 'Active' : 'Deactivated' }}
                  </span>
                </td>

                <td>
                  <b-dropdown right variant="link" toggle-class="text-dark" no-caret menu-class="custom-dropdown-menu">
                    <template #button-content>
                      <i class="fas fa-ellipsis-v"></i>
                      <!-- Font Awesome kebab icon -->
                    </template>

                    <!-- Dropdown menu items with icons -->
                    <b-dropdown-item @click="openModal(item.id)"> <i class="fas fa-eye mr-2"></i> View </b-dropdown-item>
                    <!-- <b-dropdown-item> <i class="fas fa-pen mr-2"></i> Edit </b-dropdown-item>
                    <b-dropdown-item> <i class="fas fa-trash mr-2"></i> Delete </b-dropdown-item> -->
                  </b-dropdown>
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
      <nav aria-label="Page navigation" class="mb-n5 mt-5">
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
    </b-card>
  </b-col>
  <Adduser ref="addUserModal" />

  <!-- modal to view and edit user details -->

  <!-- - //add new role modal -->
  <b-modal ref="ViewUser" :title="userData.username" class="modal custom-modal modal-lg fade" id="edit_user" hide-footer>
    <template #modal-header="{ close }">
      <h5 class="modal-title">User Details</h5>
      <b-button variant="light" @click="close" class="close"> &times; </b-button>
    </template>

    <b-row class="mb-5">
      <b-col cols="6" class="mb-5">
        <div class="profile-picture d-flex align-items-center">
          <!-- Profile Image -->
          <div class="col-lg-4 col-md-6 col-sm-12 d-flex justify-content-center text-center">
            <img id="blah2" class="avatar circular-img" src="@/assets/images/avatars/01.png" alt="profile-img" />
          </div>

          <!-- Badge -->
          <div class="ms-3 d-flex align-items-center">
            <!-- label for status -->
            <label class="px-3">Status:</label>
            <span :class="userData.status === 10 ? 'badge bg-success' : 'badge bg-warning'">
              {{ userData.status === 10 ? 'Active' : 'Deactivated' }}
            </span>
          </div>
        </div>
      </b-col>
      <b-col md="12" lg="6" class="mb-5 d-flex align-items-center">
        <!-- Form Switch -->
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" :checked="userData.status === 10" @change="confirmStatusChange(userData.user_id)" />
          <label class="form-check-label" :for="'statusSwitch'">
            {{ userData.status === 10 ? 'Lock Account' : 'Unlock Account' }}
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="errors.status" class="error ms-3" aria-live="polite">
          {{ errors.status }}
        </div>
      </b-col>

      <b-col md="12" lg="4">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">First Name</label>
          <b-form-input v-model="userData.first_name" id="startDatePicker" type="text" placeholder="Enter Role Name"></b-form-input>
        </div>
        <div v-if="errors.first_name" class="error" aria-live="polite">{{ errors.first_name }}</div>
      </b-col>
      <b-col md="12" lg="4">
        <div class="mb-3">
          <label for="startDatePicker" class="form-label">Last Name</label>
          <b-form-input v-model="userData.first_last" id="roledata" type="text" placeholder="Enter Role data"></b-form-input>
        </div>
        <div v-if="errors.first_last" class="error" aria-live="polite">{{ errors.first_last }}</div>
      </b-col>
      <b-col md="12" lg="4">
        <div class="mb-3">
          <label for="roledescription" class="form-label">Username</label>
          <b-form-input v-model="userData.username" id="roledescription" type="text" placeholder="Enter description"></b-form-input>
        </div>
        <div v-if="errors.username" class="error" aria-live="polite">{{ errors.username }}</div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="12" lg="4">
        <div class="mb-3">
          <label for="rolerulename" class="form-label">Email</label>
          <b-form-input v-model="userData.email" id="rolerulename" type="text" placeholder="Enter Role data"></b-form-input>
        </div>
        <div v-if="errors.email" class="error" aria-live="polite">{{ errors.email }}</div>
      </b-col>
      <b-col md="12" lg="4">
        <div class="mb-3">
          <label for="roledescription" class="form-label">Phone Number</label>
          <b-form-input v-model="userData.mobile_number" id="roledescription" type="text" placeholder="Enter description"></b-form-input>
        </div>
        <div v-if="errors.mobile_number" class="error" aria-live="polite">{{ errors.mobile_number }}</div>
      </b-col>
      <b-col md="12" lg="4">
        <div class="mb-3">
          <label for="roledescription" class="form-label">Role</label>
          <b-form-select v-model="userData.roles[0]" id="roledescription" :options="roles" placeholder="Select a role"></b-form-select>
        </div>
        <div v-if="errors.role" class="error" aria-live="polite">
          {{ errors.role }}
        </div>
      </b-col>
    </b-row>

    <div class="d-flex justify-content-end">
      <b-button @click="UpdateUser" variant="primary">Save</b-button>
    </div>
  </b-modal>
  <!--<b-modal ref="ViewUser" :title="userData.username" class="modal custom-modal modal-lg fade" id="edit_user">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <form action="users.html#">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="card-body">
                <div class="form-groups-item">
                  Profile Picture Section
                  <div class="profile-picture m-4">
                    <div class="profile-img">
                      <img id="blah2" class="avatar circular-img" src="@/assets/images/avatars/01.png" alt="profile-img" />
                    </div>
                    <div class="add-profile">
                      <span>Profile-pic.jpg</span>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                      <div class="input-block mb-3">
                        <label>First Name</label>
                        <input type="text" v-model="userData.first_name" class="form-control" placeholder="Enter First Name" />
                        <div v-if="errors.first_name" class="error" aria-live="polite">{{ errors.first_name }}</div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                      <div class="input-block mb-3">
                        <label>Last Name</label>
                        <input type="text" class="form-control" v-model="userData.first_last" placeholder="Enter Last Name" />
                        <div v-if="errors.first_last" class="error" aria-live="polite">{{ errors.first_last }}</div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                      <div class="input-block mb-3">
                        <label>User Name</label>
                        <input type="text" class="form-control" v-model="userData.username" placeholder="Enter User Name" />
                        <div v-if="errors.username" class="error" aria-live="polite">{{ errors.username }}</div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                      <div class="input-block mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" v-model="userData.email" placeholder="Enter Email Address" />
                        <div v-if="errors.email" class="error" aria-live="polite">{{ errors.email }}</div>
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                      <div class="input-block mb-3">
                        <label>Phone Number</label>
                        <input type="text" class="form-control" v-model="userData.mobile_number" placeholder="Enter Phone Number" />
                      </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                      <div class="mb-3">
                        <label for="roledescription" class="form-label">Role</label>
                        <b-form-select v-model="userData.roles[0]" :options="roles" id="roledescription" placeholder="Select a role" />
                      </div>
                      <div v-if="errors.role" class="error" aria-live="polite">
                        {{ errors.role }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </b-modal> -->

  <!-- Add User -->
</template>
<style>
/* Adjust the dropdown to fit items without scrolling */
.custom-dropdown-menu {
  max-height: none;
  /* Removes vertical scrolling constraint */
  min-width: 150px;
  /* Ensures adequate width for items with icons */
}

/* Styling for icons with margin for spacing */
.custom-dropdown-menu i {
  margin-right: 8px;
  /* Space between icon and text */
}

.circular-img {
  border-radius: 50%; /* Make the image circular */
  max-width: 60px; /* Adjust the size as needed */
  max-height: 60px; /* Maintain proportions */
  width: 100%; /* Ensure responsiveness */
  height: auto; /* Maintain aspect ratio */
  object-fit: contain; /* Ensure the image fills the circular frame */
}
</style>
