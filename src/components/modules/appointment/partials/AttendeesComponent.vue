<script setup>
import { ref, defineProps, watch, defineEmits, computed } from 'vue'
import createAxiosInstance from '@/api/axios'

// References
const attendeeModal = ref(null)
const attendees = ref([])
// const finalAttendees = ref([]);
const selectedItems = ref([])
const uploading = ref(false)
const rejects = ref([])
const reasons = ref({})
const rejectedUsers = ref({})
const showModal = ref(false)

// Props and Emit
const props = defineProps({
  users: {
    type: Array,
    default: () => [] // Default empty array
  }
})
const emit = defineEmits(['update:showModal']) // Sync with parent

// Axios Instance
const axiosInstance = createAxiosInstance()
const availableUsers = ref([])
const searchQuery = ref('')
const searchResults = ref([])

// Use a computed property to make `props.users` reactive
const users = computed(() => props.users)

// Watch attendeeModal
watch(
  () => attendeeModal.value,
  (newVal) => {
    if (newVal) {
      console.log('Modal opened', attendeeModal.value)
    }
  }
)

// Remove a user
const removeUser = (index) => {
  rejects.value.push(users.value[index])
  console.log('Rejects: ', rejects.value)
  users.value.splice(index, 1) // Make sure to copy `users` properly if directly mutating.
  console.log('Updated users: ', users.value)
}

// Fetch and Search Users
const handleSearch = async () => {
  const query = searchQuery.value.trim().toLowerCase()
  try {
    const response = await axiosInstance.get('/v1/auth/users')
    if (response.data?.dataPayload) {
      availableUsers.value = response.data.dataPayload.data
    }
  } catch (error) {
    console.error('Error fetching users:', error)
  }
  searchResults.value = availableUsers.value.filter((attendee) => attendee.username?.toLowerCase().includes(query))
}

// Add a selected item
const addSelectedItem = (item) => {
  if (!selectedItems.value.some((selected) => selected.id === item.id)) {
    selectedItems.value.push(item)
  }
  searchQuery.value = ''
  searchResults.value = []
  console.log('Added to selectedItems: ', selectedItems.value)
}

// Remove a selected item
const removeSelectedItem = (index) => {
  selectedItems.value.splice(index, 1)
}

// Push attendees
const pushToAttendees = () => {
  attendees.value = [...new Set([...props.users, ...selectedItems.value])]
  selectedItems.value = []
  // openModal()
  rejectedUsers.value = {}
  console.log('Final attendees: ', attendees.value)
}

// Reset and close modal
const closeModal = () => {
  selectedItems.value = []
  searchQuery.value = ''
  searchResults.value = []
  emit('update:showModal', false)
}

// Open modal
// const openModal = () => {
//   showModal.value?.show()
// }

// Submit rejections
const submitRejections = () => {
  Object.entries(rejectedUsers.value).forEach(([userId, reason]) => {
    if (reason) {
      console.log(`User ${userId} rejected for reason: ${reason}`)
    }
  })
  reasons.value = {}
  rejectedUsers.value = {}
  showModal.value = false
  alert('Rejections submitted successfully!')
}
</script>

<template>
  <b-modal ref="attendeeModal" title="Child Modal" hide-footer @hide="closeModal" class="child-modal" style="z-index: 1055" size="xl">
    <!-- Modal content -->
    <b-row>
      <b-col cols="10" lg="10">
        <div class="search-form shadow-sm">
          <!-- Selected Items -- -->
          <div v-if="selectedItems.length" class="mb-2">
            <span v-for="(item, index) in selectedItems" :key="item.id" class="badge bg-primary text-white me-2 p-2" @click="removeSelectedItem(index)"> {{ item.username }} ✖ </span>
          </div>

          <!-- Search Input -- -->
          <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control mb-2" placeholder="Search to add attendees...." aria-label="Search for a username" />

          <!-- Search Results  -->
          <ul v-if="searchResults.length" class="list-group position-relative" role="listbox">
            <li v-for="result in searchResults" :key="result.id" class="list-group-item list-group-item-action" @click="addSelectedItem(result)">
              {{ result.username }}
            </li>
          </ul>

          <!-- No Results Message  -->
          <p v-else-if="searchQuery && !searchResults.length" class="text-muted mt-2">No results found.</p>
        </div>
      </b-col>
      <b-col cols="2" lg="2" class="d-flex align-items-center justify-content-center">
        <button type="button" class="btn btn-primary" @click="pushToAttendees">Add</button>
      </b-col>
    </b-row>
    <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr class="mytr">
          <th>Name</th>
          <th>Email</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.staff_id" class="custom-row">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <button type="button" class="btn btn-outline-danger" @click="removeUser(user.staff_id)">
              Remove
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
    <!-- //button to submit the selected items -->
    <div class="modal-footer border-0">
      <button v-if="uploading === false" type="button" class="btn btn-primary" data-bs-dismiss="modal" name="save" @click="pushToAttendees">Submit</button>
      <button v-else class="btn btn-primary" type="button" disabled>
        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
        Loading...
      </button>
      <button v-if="uploading === false" type="button" class="btn btn-outline-warning" data-bs-dismiss="modal" @click="closeModal()">Close</button>
    </div>
    <!-- <button @click="openModal" class="btn btn-warning mb-4">
      Open Rejection Modal
    </button> -->

    <div v-if="Object.keys(rejectedUsers).length > 0">
      <h3>Rejected Users:</h3>
      <ul>
        <li v-for="(reason, userId) in rejectedUsers" :key="userId">User {{ userId }}: {{ reason }}</li>
      </ul>
    </div>
  </b-modal>

  <!-- //reasons  -->
  <!-- Modal for Rejection Reason -->
  <b-modal ref="showModal" title="Provide Rejection Reasons" size="lg">
    <template #default>
      <div>
        <div v-for="userId in rejects" :key="userId">
          <label :for="`reason-${userId}`">Reason for rejecting User {{ userId }}:</label>
          <input v-model="reasons[userId]" type="text" class="form-control mb-3" :id="`reason-${userId}`" placeholder="Enter rejection reason" />
        </div>
      </div>
    </template>
    <template #footer>
      <b-button @click="submitRejections" variant="danger" class="mr-2"> Submit Rejections </b-button>
      <b-button @click="showModal = false" variant="secondary"> Close </b-button>
    </template>
  </b-modal>
</template>
<style>
/* Additional styles for table and modal */
.table {
  margin: 0;
}

.table tbody tr td{
  /* background-color: rgb(96, 96, 177) !important; */
  padding: 8px !important;
}


.badge-success {
  background-color: #28a745;
}

.badge-danger {
  background-color: #dc3545;
}

.child-modal {
  z-index: 1055 !important; /* Default Bootstrap modal z-index is 1050 */
}


</style>
