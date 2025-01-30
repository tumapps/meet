<script setup>
import { ref, watch, defineProps } from 'vue'
import createAxiosInstance from '@/api/axios'

// Props
const props = defineProps({
  attendees: {
    type: Array,
    default: () => []
  }
})

// Refs
const searchQuery = ref('')
const searchResults = ref([])
const attendees = ref([]) // Holds all attendees (from backend and added via search)
const rejectionReason = ref('')
const rejectionModal = ref(null)
const currentRemovalIndex = ref(null) // Tracks the index of the attendee being removed
const removedAttendees = ref([]) // Stores removed attendees and their reasons

// Axios Instance
const axiosInstance = createAxiosInstance()

// Watch for changes in props.attendees
watch(
  () => props.attendees,
  (newAttendees) => {
    // Add a `fromBackend` flag to distinguish backend-loaded attendees
    attendees.value = newAttendees.map((user) => ({
      ...user,
      fromBackend: true,
      removed: false // Initialize `removed` flag
    }))
  },
  { immediate: true }
)

// Fetch and filter search results
const handleSearch = async () => {
  const query = searchQuery.value.trim().toLowerCase()
  try {
    const response = await axiosInstance.get('/v1/auth/users')
    if (response.data?.dataPayload) {
      const allUsers = response.data.dataPayload.data
      // Filter out users already in the attendees list
      searchResults.value = allUsers.filter((user) => user.username.toLowerCase().includes(query) && !attendees.value.some((attendee) => attendee.id === user.id))
    }
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

// Add a search result to the table
const addAttendeeToTable = (user) => {
  // Add a flag `fromBackend` set to false for search-added attendees
  attendees.value.push({ ...user, fromBackend: false, removed: false })
  searchQuery.value = ''
  searchResults.value = []
}

// Remove an attendee from the table
const removeAttendee = (index, fromBackend) => {
  if (fromBackend) {
    // If attendee is from the backend, show the rejection modal
    currentRemovalIndex.value = index
    rejectionModal.value.show()
  } else {
    // If attendee is added via search, mark as removed directly
    attendees.value[index].removed = true
    removedAttendees.value.push({
      ...attendees.value[index],
      reason: 'No reason required (added via search)'
    })
  }
}

// Confirm rejection and mark the attendee as removed
const confirmRejection = () => {
  if (rejectionReason.value) {
    const removedUser = attendees.value[currentRemovalIndex.value]
    removedUser.removed = true
    removedAttendees.value.push({
      ...removedUser,
      reason: rejectionReason.value
    })
    rejectionReason.value = ''
    rejectionModal.value.hide()
  } else {
    alert('Please provide a reason for rejection.')
  }
}

// Restore a removed attendee
const restoreAttendee = (index) => {
  const restoredUser = attendees.value[index]
  restoredUser.removed = false
  // Remove the user from the `removedAttendees` array
  removedAttendees.value = removedAttendees.value.filter((user) => user.staff_id !== restoredUser.staff_id)
}

// Submit removed attendees
const submitRemovedAttendees = () => {
  console.log('Removed Attendees:', removedAttendees.value)
  alert('Removed attendees submitted successfully!')
  // You can now send `removedAttendees.value` to your backend API
}
</script>

<template>
  <div>
    <!-- Search and Add Attendees Section -->
    <b-col cols="12">
      <div class="search-form shadow-sm">
        <!-- Search Input -->
        <b-form-group label="Attendees:" label-for="input-1">
          <input v-model="searchQuery" @input="handleSearch" type="text" class="form-control mb-2" placeholder="Search username..." aria-label="Search for a username" />
        </b-form-group>

        <!-- Search Results -->
        <ul v-if="searchResults.length" class="list-group position-relative" role="listbox">
          <li v-for="result in searchResults" :key="result.id" class="list-group-item list-group-item-action" @click="addAttendeeToTable(result)">
            {{ result.username }}
          </li>
        </ul>

        <!-- No Results Message -->
        <p v-else-if="searchQuery && !searchResults.length" class="text-muted mt-2">No results found.</p>
      </div>
    </b-col>

    <!-- Attendees Table Section -->
    <div class="table-responsive mt-4">
      <table class="table table-striped table-bordered">
        <thead>
          <tr class="mytr">
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in attendees" :key="user.staff_id" class="custom-row" :class="{ 'text-muted': user.removed }">
            <td>
              <span :style="{ textDecoration: user.removed ? 'line-through' : 'none' }">
                {{ user.name }}
              </span>
            </td>
            <td>
              <span :style="{ textDecoration: user.removed ? 'line-through' : 'none' }">
                {{ user.email }}
              </span>
            </td>
            <td>
              <span :style="{ textDecoration: user.removed ? 'line-through' : 'none' }">
                {{ user.status }}
              </span>
            </td>
            <td>
              <!-- Remove Icon -->
              <i v-if="!user.removed" class="fas fa-trash-alt text-danger cursor-pointer" @click="removeAttendee(index, user.fromBackend)" title="Remove"></i>
              <!-- Restore Icon -->
              <i v-else class="fas fa-undo text-success cursor-pointer" @click="restoreAttendee(index)" title="Restore"></i>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Rejection Reasons Modal -->
    <b-modal ref="rejectionModal" title="Provide Rejection Reason" size="lg">
      <template #default>
        <div>
          <label for="rejectionReason">Reason for rejection:</label>
          <input v-model="rejectionReason" type="text" class="form-control mb-3" id="rejectionReason" placeholder="Enter rejection reason" />
        </div>
      </template>
      <template #footer>
        <b-button @click="confirmRejection" variant="danger" class="mr-2">Submit</b-button>
        <b-button @click="rejectionModal.hide()" variant="secondary">Close</b-button>
      </template>
    </b-modal>

    <!-- Submit Removed Attendees Button -->
    <div class="mt-4">
      <button type="button" class="btn btn-primary" @click="submitRemovedAttendees">Submit Removed Attendees</button>
    </div>
  </div>
</template>

<style scoped>
/* Add custom styles if needed */

.table tbody tr td {
  /* background-color: rgb(96, 96, 177) !important; */
  padding: 0px !important;
}

/* Add custom styles if needed */
.text-muted {
  color: #6c757d !important;
}
</style>
