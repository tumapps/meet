<script setup>
import { ref, getCurrentInstance, watch, defineExpose } from 'vue'
import AxiosInstance from '@/api/axios'
import { defineProps } from 'vue'

const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()
const errors = ref({})
const toastPayload = ref({})
//  data for roles and permissions
const availableRoles = ref({})
const availablePermissions = ref([])
const assignedRoles = ref([])
const assignedPermissions = ref([])
const availableItemsSearch = ref('')
const assignedItemsSearch = ref('')
//get the role name from the last part of the url
const props = defineProps({
  user_id: {
    type: Number,
    required: false
  },
  name: {
    type: String,
    required: false
  }
})

defineExpose({
  show: () => {
    if (assignModal.value) {
      assignModal.value.show() // ✅ This allows the parent to trigger show()
    }
  }
})

const resetState = () => {
  errors.value = {}
  toastPayload.value = {}
  availableRoles.value = {}
  availablePermissions.value = []
  assignedRoles.value = []
  assignedPermissions.value = []
}

const secretaryid = ref(null)
const secretaryname = ref(null)
const i = ref(0)
const isModalProcessing = ref(false)

const onModalShow = async () => {
  if (isModalProcessing.value) return
  isModalProcessing.value = true

  // Wait until props are populated
  while (!props.user_id || !props.name) {
    await new Promise((resolve) => setTimeout(resolve, 50)) // Small delay to wait for props
  }

  secretaryid.value = props.user_id
  secretaryname.value = props.name

  //console.log('Modal opened:', i.value++, secretaryid.value, secretaryname.value)

  getUnderSecretary()

  setTimeout(() => {
    isModalProcessing.value = false // Reset flag after delay
  }, 500)
}

const handleclose = () => {
  resetState() // Reset state
  assignedItemsSearch.value = ''
  availableItemsSearch.value = ''
}
//selected items
const assignModal = ref(null) // Reference for <b-modal>

//move the selected item's name  to the assigned list
const selectedUser_id = ref(null)

const getUnderSecretary = async () => {
  //console.log('here i go', secretaryid.value, secretaryname.value)
  const response = await axiosInstance.get(`/v1/scheduler/get-assigned-users?id=${secretaryid.value}`)
  availableRoles.value = response.data.dataPayload.data.available
  assignedRoles.value = response.data.dataPayload.data.assigned
}

//send the selected items to the backend
const updateUnderSecretary = async () => {
  try {
    // Send the selected items to the backend
    const response = await axiosInstance.post('/v1/scheduler/managed-users', {
      user_id: selectedUser_id.value,
      secretary_id: secretaryid.value
    })

    getUnderSecretary()

    // Check if a toast payload exists in the response
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayloadI
      // Show a toast notification with the provided data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Success',
        icon: toastPayload.value.toastTheme || 'success'
      })
    }
  } catch (error) {
    // Handle errors
    const errorMessage = error?.response?.data?.errorPayload?.errors?.message || error?.response?.data?.toastPayload?.toastMessage
    const theme = error?.response?.data?.toastPayload?.toastTheme || 'error'

    // Show a toast notification for the error
    proxy.$showToast({
      title: errorMessage,
      text: errorMessage,
      icon: theme
    })
  }
}

const stripUnderSecretary = async () => {
  try {
    // const response = await axiosInstance.post(`/v1/auth/remove?id=${props.roleName}`, selectedItems.value)
    const response = await axiosInstance.delete(`/v1/scheduler/managed-users/${secretaryid.value}/${selectedUser_id.value}`)

    getUnderSecretary()
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'success',
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
    const errorMessage = error?.response?.data?.errorPayload?.errors?.message || error?.response?.data?.toastPayload?.toastMessage
    const theme = error?.response?.data?.toastPayload?.toastTheme || 'error'

    // Show a toast notification for the error
    proxy.$showToast({
      title: errorMessage,
      text: errorMessage,
      icon: theme
    })
  }
}

const flag = ref('')

watch(
  () => availableItemsSearch.value,
  (newVal) => {
    performSearch(props.roleName, newVal)
  }
)

watch(
  () => assignedItemsSearch.value,
  (newVal) => {
    flag.value = 'assinged'
    performSearch(props.roleName, newVal)
  }
)
const performSearch = async (role, searchQuery) => {
  try {
    const response = await axiosInstance.get(`v1/auth/manage-role?id=${role}&_search=${searchQuery}`)

    if (flag.value === 'assinged') {
      assignedRoles.value = response.data.dataPayload.data.assigned.roles
      assignedPermissions.value = response.data.dataPayload.data.assigned.permissions
    } else {
      availableRoles.value = response.data.dataPayload.data.available.roles
      availablePermissions.value = response.data.dataPayload.data.available.permissions
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
</script>
<template>
  <!-- //button to open the modal -->

  <b-modal ref="assignModal" @show="onModalShow" :title="secretaryname" class="modal-fullscreen modal rounded-3" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleclose">
    <!-- Page Content -->
    <div class="row" style="max-height: 70vh; display: flex; overflow: hidden">
      <!-- Available Items Column -->
      <div class="col-md-5 border p-3" style="min-height: 80%; overflow-y: auto; max-height: 67vh">
        <h5>Available Items</h5>
        <div class="mb-3">
          <input type="text" class="form-control" v-model="availableItemsSearch" placeholder="Search users..." />
        </div>
        <!-- Roles Segment -->
        <div>
          <ul class="list-group">
            <li v-for="item in availableRoles" :key="item.user_id" class="list-group-item" :class="{ active: selectedUser_id === item.user_id }" @click="selectedUser_id = item.user_id">
              {{ item.fullname }}
            </li>
          </ul>
          <div v-if="!availableRoles || Object.keys(availableRoles).length === 0">No Users available</div>
        </div>
      </div>

      <!-- Control Buttons (Centered vertically and horizontally) -->
      <div class="col-md-2 d-flex flex-column justify-content-center align-items-center" style="position: sticky; top: 0; height: 70vh; background-color: white; z-index: 10">
        <button class="btn btn-primary mb-2" @click="updateUnderSecretary">&gt;&gt;</button>
        <button class="btn btn-danger" @click="stripUnderSecretary">&lt;&lt;</button>
      </div>

      <!-- Assigned Items Column -->
      <div class="col-md-5 border p-3" style="min-height: 80%; overflow-y: auto; max-height: 67vh">
        <h5>Assigned Items</h5>
        <!-- Search Bar for Assigned Items -->
        <div class="mb-3">
          <input type="text" class="form-control" v-model="assignedItemsSearch" placeholder="Search Assigned Users..." />
        </div>

        <!-- Roles Segment -->
        <div>
          <ul class="list-group mt-2">
            <li v-for="item in assignedRoles" :key="item.user_id" class="list-group-item" :class="{ active: selectedUser_id === item.user_id }" @click="selectedUser_id = item.user_id">
              {{ item.fullname }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </b-modal>
</template>

<style>
.modal .modal-content {
  border-radius: 8px !important; /* Ensures the modal content is also rounded */
}

/* Styling for borders */
.border {
  border: 2px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
}

/* Styling for list-group items */
.list-group-item {
  cursor: pointer;
}

.list-group-item:hover {
  background-color: #1bcc97;
}

/* Active item styling */
.active-item {
  background-color: #1bcc97;
  color: white;
  font-weight: bold;
}

/* Styling for collapsed buttons */
.btn-link {
  font-size: 16px;
  font-weight: bold;
  text-align: left;
}
</style>
