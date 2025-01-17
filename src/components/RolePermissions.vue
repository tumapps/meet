<script setup>
import { ref, getCurrentInstance, onMounted, watch } from 'vue'
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
//get the role name from the last part of the url
const props = defineProps({
  roleName: {
    type: String,
    required: false
  },
  user_id: {
    type: Number,
    required: false
  }
})

const custompath = ref('')

const rolename = ref('')

// if (props.user_id && props.roleName) {
//   custompath.value = `role-to-user/${props.user_id}`
// } else if (props.roleName) {
//   custompath.value = `/v1/auth/assign?role=${props.roleName}`
// }

const onModalShow = () => {
  console.log('RolePermissions mounted heree', props.roleName)
  rolename.value = ref(props.roleName)

  if (props.user_id && props.roleName) {
    custompath.value = `role-to-user/${props.user_id}`
  } else if (props.roleName) {
    custompath.value = `/v1/auth/assign?role=${props.roleName}`
  }
}

const handleclose = () => {
  console.log('RolePermissions closed')
  //reset the selected items
  selectedItems.value.items = []
  //reset props
  rolename.value = ''
}
//selected items
const selectedItems = ref({
  items: []
})
const roleModal = ref(null) // Reference for <b-modal>

watch(
  () => props.roleName,
  (newRoleName, oldRoleName) => {
    rolename.value = newRoleName
    console.log('RoleName changed:', rolename.value)

    if (newRoleName !== oldRoleName && !props.user_id) {
      console.log('RoleName changed:', newRoleName)
      getRoleDetails()
    }
  }
)

const getRoleDetails = async () => {
  const response = await axiosInstance.get(`/v1/auth/manage-role?id=${props.roleName}`)
  availableRoles.value = response.data.dataPayload.data.available.roles
  availablePermissions.value = response.data.dataPayload.data.available.permissions
  assignedPermissions.value = response.data.dataPayload.data.assigned.permissions
  assignedRoles.value = response.data.dataPayload.data.assigned.roles
}

//move the selected item's name  to the assigned list
const moveToSelected = (name) => {
  //check if the item is already in the selected items and remove it
  if (selectedItems.value.items.includes(name)) {
    selectedItems.value.items = selectedItems.value.items.filter((item) => item !== name)
    return
  }
  selectedItems.value.items.push(name)
  console.log(selectedItems.value.items)
}

//check if the item is in the  selected items
const isSelected = (item) => {
  return selectedItems.value.items.includes(item.name)
}

//send the selected items to the backend
const updateRolePermissions = async () => {
  try {
    // Send the selected items to the backend
    const response = await axiosInstance.post(custompath.value, selectedItems.value)

    // Refresh the role details
    getRoleDetails()

    // Clear the selected items
    selectedItems.value.items = []

    // Check if a toast payload exists in the response
    if (response.data.toastPayload) {
      toastPayload.value = response.data.toastPayload

      // Show a toast notification with the provided data
      proxy.$showToast({
        title: toastPayload.value.toastMessage || 'Success',
        icon: toastPayload.value.toastTheme || 'success'
      })
    } else {
      // Fallback: show a default success notification
      proxy.$showToast({
        title: 'Success',
        icon: 'success'
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

const stripRolePermissions = async () => {
  try {
    const response = await axiosInstance.post(`/v1/auth/remove?id=${props.roleName}`, selectedItems.value)
    getRoleDetails()
    //clear the selected items
    selectedItems.value.items = []

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
    // console.error(error);
    errors.value = error.response.data.errorPayload.errors

    proxy.$showAlert({
      title: error.response.data.toastPayload.toastTheme,
      text: error.response.data.toastPayload.toastMessage,
      icon: error.response.data.toastPayload.toastTheme
    })
  }
}

onMounted(() => {
  console.log('RolePermissions mounted', props.roleName)
  if (!props.user_id) {
    getRoleDetails()
  }
})
</script>
<template>
  <b-modal ref="roleModal" @show="onModalShow" :title="rolename" class="modal-fullscreen my-modal rounded-modal" no-close-on-backdrop no-close-on-esc size="xl" hide-footer @hide="handleclose">
    <!-- Page Content -->
    <div class="row" style="max-height: 70vh; display: flex; overflow: hidden">
      <!-- Available Items Column -->
      <div class="col-md-5 border p-3" style="min-height: 80%; overflow-y: auto; max-height: 67vh">
        <h5>Available Items</h5>
        <div class="mb-3">
          <input type="text" class="form-control" v-model="availableItemsSearch" placeholder="Search Roles or Permissions..." />
        </div>
        <!-- Roles Segment -->
        <div>
          <button class="btn btn-link text-decoration-none">Roles</button>
          <ul class="list-group">
            <li v-for="item in availableRoles" :key="item.name" :class="['list-group-item', isSelected(item, 'role', 'available') ? 'active-item' : '']" @click="moveToSelected(item.name)">
              {{ item.name }}
            </li>
          </ul>
          <div v-if="!availableRoles || Object.keys(availableRoles).length === 0">No roles available</div>
        </div>

        <!-- Available Permissions -->
        <div class="mt-3">
          <button class="btn btn-link text-decoration-none">Permissions</button>
          <ul class="list-group mt-2">
            <li v-for="item in availablePermissions" :key="item.name" :class="['list-group-item', isSelected(item, 'permissions', 'available') ? 'active-item' : '']" @click="moveToSelected(item.name)">
              {{ item.data }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Control Buttons (Centered vertically and horizontally) -->
      <div class="col-md-2 d-flex flex-column justify-content-center align-items-center" style="position: sticky; top: 0; height: 70vh; background-color: white; z-index: 10">
        <button class="btn btn-primary mb-2" @click="updateRolePermissions">&gt;&gt;</button>
        <button class="btn btn-danger" @click="stripRolePermissions">&lt;&lt;</button>
      </div>

      <!-- Assigned Items Column -->
      <div class="col-md-5 border p-3" style="min-height: 80%; overflow-y: auto; max-height: 67vh">
        <h5>Assigned Items</h5>
        <!-- Search Bar for Assigned Items -->
        <div class="mb-3">
          <input type="text" class="form-control" v-model="assignedItemsSearch" placeholder="Search Assigned Roles or Permissions..." />
        </div>

        <!-- Roles Segment -->
        <div>
          <button class="btn btn-link text-decoration-none" @click="toggleCollapse('assignedRoles')">Roles</button>
          <ul class="list-group mt-2">
            <li v-for="item in assignedRoles" :key="item.name" :class="['list-group-item', isSelected(item, 'roles', 'assigned') ? 'active-item' : '']" @click="moveToSelected(item.name)">
              {{ item.name }}
            </li>
          </ul>
        </div>

        <!-- Permissions Segment -->
        <div class="mt-3">
          <button class="btn btn-link text-decoration-none">Permissions</button>
          <ul class="list-group mt-2">
            <li v-for="item in assignedPermissions" :key="item.name" :class="['list-group-item', isSelected(item, 'permissions', 'assigned') ? 'active-item' : '']" @click="moveToSelected(item.name)">
              {{ item.data }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </b-modal>
</template>

<style>
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
