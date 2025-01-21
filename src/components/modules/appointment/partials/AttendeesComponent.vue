<script setup>
import { ref, defineProps, watch, defineEmits } from 'vue'
import createAxiosInstance from '@/api/axios'

const attendeeModal = ref(null) // Reference for <b-modal>
const attendees = ref([]) // To store attendees being added
const finalAttendees = ref([]) // To store the final attendees
const selectedItems = ref([]) // Temporary list of selected attendees
const uploading = ref(false) // Loading state

const props = defineProps({
  users: {
    type: Array,
    default: () => [] // Default to an empty array
  }
})

const emit = defineEmits(['update:showModal']) // Emit event to sync with parent

const axiosInstance = createAxiosInstance()
const availableUsers = ref([]) // All users from the API
const searchQuery = ref('')
const searchResults = ref([])

// Reactive copy of props.users
const users = ref([])

watch(
  () => props.users,
  (newUsers) => {
    users.value = [...newUsers] // Ensure reactivity
  },
  { immediate: true }
)

// Remove a user from `users`
const removeUser = (index) => {
  users.value.splice(index, 1)
  console.log('Updated users: ', users.value)
}

// Fetch and search users
const handleSearch = async () => {
  const query = searchQuery.value.trim().toLowerCase()
  try {
    const response = await axiosInstance.get('/v1/auth/users')
    if (response.data && response.data.dataPayload) {
      availableUsers.value = response.data.dataPayload.data
    }
  } catch (error) {
    console.error('Error fetching users:', error)
  }
  searchResults.value = availableUsers.value.filter((attendee) => attendee.username && attendee.username.toLowerCase().includes(query))
}

// Add a selected user to `selectedItems`
const addSelectedItem = (item) => {
  if (!selectedItems.value.find((selected) => selected.id === item.id)) {
    selectedItems.value.push(item)
  }

  // Clear search input and results
  searchQuery.value = ''
  searchResults.value = []
  console.log('Added to selectedItems: ', selectedItems.value)
}

// Remove a user from `selectedItems`
const removeSelectedItem = (index) => {
  selectedItems.value.splice(index, 1)
}

// Push users and selected attendees to `attendees`
const pushToAttendees = () => {
  attendees.value = [...new Set([...users.value, ...selectedItems.value])]
  users.value = [...attendees.value]
  finalAttendees.value = [...attendees.value]
  console.log('Final attendees: ', finalAttendees.value)
}

// Reset temporary states and emit close
const closeModal = () => {
  selectedItems.value = []
  searchQuery.value = ''
  searchResults.value = []
  emit('update:showModal', false)
}
</script>

<template>
  <b-modal ref="attendeeModal" title="Child Modal" hide-footer @hide="closeModal" class="child-modal" style="z-index: 1055" size="xl">
    <!-- Modal content -->
    <b-col cols="10" lg="10">
      <div class="search-form shadow-sm">
        <!-- Selected Items -- -->
        <div v-if="selectedItems.length" class="mb-2">
          <span v-for="(item, index) in selectedItems" :key="item.id" class="badge bg-primary text-white me-2 p-2" @click="removeSelectedItem(index)"> {{ item.username }} ✖ </span>
        </div>

        <!-- Search Input -- -->
        <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control mb-2" placeholder="Search username..." aria-label="Search for a username" />

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
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span
                class="badge"
                :class="{
                  'badge-success': user.status === 'Active',
                  'badge-danger': user.status === 'Inactive'
                }">
                {{ user.status }}
              </span>
            </td>
            <td>
              <button type="button" class="btn btn-danger" @click="removeUser(index)">Remove</button>
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
  </b-modal>
</template>
<style>
/* Additional styles for table and modal */
.table {
  margin: 0;
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
