<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
import AxiosInstance from '@/api/axios'

const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()

const meetingTypes = ref([])
const loading = ref(false)
const selectedPerPage = ref(20) // Number of items per page (from dropdown)
const currentPage = ref(1) // The current page being viewed
const searchQuery = ref('') // Search query
const perPageOptions = ref([20, 50])
const totalPages = ref(1) // Total number of pages from the API
const perPage = ref(20) // Number of items per page (from API response)
const typeDetails = ref({})
const errors = ref({})

//get meeting types
const getMeetingTypes = async (page) => {
  try {
    const response = await axiosInstance.get(`/v1/scheduler/meeting-types?page=${page}&perPage=${selectedPerPage.value}&_search=${searchQuery.value}`)

    meetingTypes.value = response.data.dataPayload.data
    currentPage.value = response.data.dataPayload.currentPage
    totalPages.value = response.data.dataPayload.totalPages
    perPage.value = response.data.dataPayload.perPage
  } catch (error) {
    console.log(error)
  }
}

const getMeetingType = async (id) => {
  try {
    const response = await axiosInstance.get(`/v1/scheduler/meeting-type/${id}`)
    typeDetails.value = response.data.dataPayload.data
  } catch (error) {
    console.log(error)
    if (error.response.status !== 422) {
      proxy.$showAlert({
        title: error.response.data.errorPayload.toastTheme,
        text: error.response.data.errorPayload.toastMessage,
        icon: error.response.data.errorPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000
      })
    }
  }
}

const newType = async () => {
  loading.value = true
  try {
    const response = await axiosInstance.post('/v1/scheduler/meeting-type', typeDetails.value)
    if (response.data.toastPayload) {
      proxy.$refs.modal2.hide()
      getMeetingTypes(1)
      proxy.$showAlert({
        title: response.data.toastPayload.toastTheme,
        text: response.data.toastPayload.toastMessage,
        icon: response.data.toastPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000
      })
    }
  } catch (error) {
    console.log(error)
    errors.value = error.response.data.errorPayload?.errors
    if (error.response.status !== 422) {
      proxy.$showAlert({
        title: error.response.data.toastPayload.toastTheme,
        text: error.response.data.toastPayload.toastMessage,
        icon: error.response.data.toastPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000
      })
    }
  } finally {
    loading.value = false
  }
}

const updateType = async () => {
  loading.value = true
  try {
    const response = await axiosInstance.put(`/v1/scheduler/meeting-type/${typeDetails.value.id}`, typeDetails.value)
    if (response.data.toastPayload) {
      proxy.$refs.modal.hide()
      proxy.$showAlert({
        title: response.data.toastPayload.toastTheme,
        text: response.data.toastPayload.toastMessage,
        icon: response.data.toastPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000
      })
    }
    getMeetingTypes(1)
  } catch (error) {
    console.log(error)
    errors.value = error.response.data.errorPayload?.errors

    if (error.response.status !== 422) {
      proxy.$showAlert({
        title: error.response.data.errorPayload.toastTheme,
        text: error.response.data.errorPayload.toastMessage,
        icon: error.response.data.errorPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000
      })
    }
  } finally {
    loading.value = false
  }
}

const deleteAction = async (id) => {
  try {
    const response = await axiosInstance.delete(`/v1/scheduler/meeting-type/${id}`)
    if (response.data.toastPayload) {
      getMeetingTypes(1)
      proxy.$showAlert({
        title: response.data.toastPayload.toastTheme,
        text: response.data.toastPayload.toastMessage,
        icon: response.data.toastPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000
      })
    }
  } catch (error) {
    console.log(error)
    if (error.response.status !== 422) {
      proxy.$showAlert({
        title: error.response.data.errorPayload.toastTheme,
        text: error.response.data.errorPayload.toastMessage,
        icon: error.response.data.errorPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000
      })
    }
  }
}

// confirm before delete alerts
const confirmDelete = (id, status) => {
  proxy
    .$showAlert({
      icon: 'warning',
      title: 'Are you sure?',
      text: 'Are you sure you want to' + ' ' + [status ? 'restore' : 'delete'] + ' this meeting type?',
      showCancelButton: true,
      showConfirmButton: true,
      confirmButtonText: 'Submit',
      confirmButtonColor: '#097B3E',
      cancelButtonColor: '#d33'
    })
    .then((result) => {
      if (result.isConfirmed) {
        deleteAction(id)
      }
    })
}

const updatePerPage = async () => {
  currentPage.value = 1 // Reset to first page when changing items per page
  await getMeetingTypes(1) // Fetch appointments with the new perPage value
}

const goToPage = (page) => {
  // Ensure the page is within the valid range
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page
    getMeetingTypes(page) // Fetch appointments for the selected page
  }
}
//open modal
const openModal = (id) => {
  proxy.$refs.modal.show()
  getMeetingType(id)
}

const openModal2 = () => {
  proxy.$refs.modal2.show()
}

onMounted(() => {
  getMeetingTypes(1)
})
</script>
<template>
  <b-col lg="12">
    <b-card>
      <b-row class="mb-5">
        <b-col lg="6">
          <h2>Meeting Types</h2>
        </b-col>
        <b-col lg="6" class="d-flex justify-content-end">
          <!-- create new meeting type -->
          <b-button variant="primary" @click="openModal2()"> New Meeting type </b-button>
        </b-col>
      </b-row>

      <b-row clas="mb-3">
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
                  <b-button variant="primary" @click="openModal2()"> Search </b-button>
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
              <th scope="col">Name</th>
              <!-- <th scope="col">Description</th> -->
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="meetingTypes.length > 0">
              <tr v-for="meetingType in meetingTypes" :key="meetingType.id">
                <td>{{ meetingType.type }}</td>
                <!-- <td>{{ meetingType.description }}</td> -->
                <td>
                  <button class="btn btn-outline-info btn-sm me-3" :disabled="meetingType.is_deleted" size="sm" @click="openModal(meetingType.id)">
                    <i :class="['fas', meetingType.is_deleted ? 'fa-eye' : 'fa-edit']"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm me-3" size="sm" @click="confirmDelete(meetingType.id, meetingType.is_deleted)">
                    <i :class="['fas', meetingType.is_deleted ? 'fa-undo' : 'fa-trash']"></i>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td class="text-center">No meeting types found</td>
              </tr>
            </template>
          </tbody>
        </table>
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

  <!-- Modal -->
  <b-modal ref="modal" id="modal" title="Edit Meeting Type" class="p-4 custom-modal-body" no-close-on-backdrop no-close-on-esc size="l" hide-footer @hide="handleClose">
    <b-form-group label="Name" label-for="name" class="mb-3">
      <b-form-input id="name" v-model="typeDetails.type" />
      <div v-if="errors.type" class="error">{{ errors.type }}</div>
    </b-form-group>
    <!-- <b-form-group label="Description" label-for="description">
        <b-form-textarea id="description" v-model="meetingType.description" />
        </b-form-group> -->
    <div class="d-flex justify-content-center">
      <b-button v-if="loading === false" @click="updateType" variant="primary" :disabled="is_deleted">Update</b-button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
        submitting...
      </button>
    </div>
  </b-modal>

  <!-- //modal 2 -->
  <b-modal ref="modal2" id="modal2" title="New Meeting Type" class="p-4 custom-modal-body" no-close-on-backdrop no-close-on-esc size="l" hide-footer @hide="handleClose">
    <b-form-group label="name" label-for="name" class="mb-3">
      <b-form-input id="name" v-model="typeDetails.type" />
      <div v-if="errors.type" class="error">{{ errors.type }}</div>
    </b-form-group>
    <div class="d-flex justify-content-center">
      <b-button v-if="loading === false" @click="newType" variant="primary">Save</b-button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
        submitting...
      </button>
    </div>
  </b-modal>
</template>
