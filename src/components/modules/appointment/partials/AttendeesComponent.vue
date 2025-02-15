<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue'
import createAxiosInstance from '@/api/axios'
import Swal from 'sweetalert2'

const emits = defineEmits(['newAttendee'])

// add props

// Props
const props = defineProps({
  attendees: {
    type: Array,
    default: () => []
  },
  meetingId: {
    type: Number,
    required: true
  },
  submitSignal: {
    type: String,
    required: true
  }
})

// Refs
const searchQuery = ref(null)
const searchResults = ref([])
const attendees = ref([]) // Holds all attendees (from backend and added via search)
const currentRemovalIndex = ref(null) // Tracks the index of the attendee being removed
const removedAttendees = ref({
  attendees: {} // This will hold the key-value pairs for attendees
}) // Stores removed attendees and their reasons

// Axios Instance
const axiosInstance = createAxiosInstance()

watch(
  () => props.attendees,
  (newAttendees) => {
    attendees.value = newAttendees.map((user) => ({
      ...user,
      id: user.attendee_id ?? user.id, // Normalize attendee_id to id
      fromBackend: true,
      removed: false
    }))
  },
  { immediate: true }
)

//watch for changes in ptrops.meetingId
const meetingId = ref(null)
watch(
  () => props.meetingId,
  (newMeetingId) => {
    // Add a `fromBackend` flag to distinguish backend-loaded attendees
    meetingId.value = newMeetingId
  },
  { immediate: true }
)
// watch searchQuery and call handleSearch
watch(searchQuery, (newSearchQuery) => {
  console.log('searchQuery:', newSearchQuery)
  console.log('tyoeof:', typeof newSearchQuery)
  console.log('searchResults:', searchResults.value)
  if (!newSearchQuery) {
    searchResults.value = []
    console.log('searchResults indde:', searchResults.value)
    return // Stop execution to prevent unnecessary `handleSearch()`
  }
  handleSearch()
})

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

const attendeesId = ref([])

watch(attendeesId, (newValue) => {
  emits('newAttendee', newValue)
  console.log('watcher atterarera', newValue)
})

// emits('attendeesId', attendeesId.value)

// Add a search result to the table
const addAttendeeToTable = (user) => {
  // Add a flag `fromBackend` set to false for search-added attendees

  const duplicate = attendees.value.some((attendee) => attendee.attendee_id === user.id)

  if (!duplicate) {
    attendees.value.push({ ...user, fromBackend: false, removed: false })
    //push only id from attendees to attendeesId
    attendeesId.value = attendees.value.map((attendee) => attendee.id)
    searchResults.value = ''
  } else {
    Swal.fire('Error!', 'Attendee already added.', 'error')
  }

  console.log('final attendees', attendees.value)
  console.log('final attendeesId', attendeesId.value)

  searchQuery.value = ''
  searchResults.value = []
}

// Remove an attendee from the table
const removeAttendee = (index, fromBackend) => {
  if (fromBackend) {
    // If attendee is from the backend, show the Removal modal
    currentRemovalIndex.value = index
    confirmRemoval()
  } else {
    attendees.value.splice(index, 1)
    attendeesId.value = attendees.value.map((attendee) => attendee.id)
    console.log('final attendees removed from table', attendees.value)
  }
}

const confirmRemoval = () => {
  Swal.fire({
    title: 'Provide a reason for Removal',
    input: 'textarea',
    inputLabel: 'Removal Reason',
    inputPlaceholder: 'Please type your reason here...',
    inputAttributes: {
      'aria-label': 'Please type your reason here'
    },
    showCancelButton: true,
    confirmButtonText: 'Confirm',
    cancelButtonText: 'Cancel',
    confirmButtonColor: 'primary',
    cancelButtonColor: '#d33',
    preConfirm: (inputValue) => {
      if (!inputValue) {
        Swal.showValidationMessage('Reason is required!')
        return false // Prevents the alert from closing
      }
      return inputValue // Pass the input value to the `then` block
    }
  }).then((result) => {
    if (result.isConfirmed) {
      const reason = result.value
      console.log('Removal Reason:', attendees.value[currentRemovalIndex.value])
      // Proceed with the removal process
      const removedUser = attendees.value[currentRemovalIndex.value]
      attendees.value.splice(currentRemovalIndex.value, 1)
      attendeesId.value = attendees.value.map((attendee) => attendee.id)
      removedUser.removed = true
      removedAttendees.value.attendees[removedUser.attendee_id] = reason
      //remove id of  user being removed from the attendeesid
      // attendeesId.value.splice
      console.log('Removed Attendees:', removedAttendees.value)
      console.log('new attendeesids', attendeesId.value)
      //backedn call to remove the user
    }
  })
}

// Restore a removed attendee
const restoreAttendee = (index) => {
  const restoredUser = attendees.value[index]
  restoredUser.removed = false
  // Remove the user from the `removedAttendees` array
  removedAttendees.value.attendees = removedAttendees.value.attendees.filter((user) => user.staff_id !== restoredUser.staff_id)
}

// Submit removed attendees
const submitRemovedAttendees = async () => {
  if (Object.keys(removedAttendees.value.attendees).length === 0) {
    console.warn('No attendees to remove') // Optional: Log a warning
    return // Stop the function if no attendees
  }
  try {
    const response = await axiosInstance.put(`/v1/scheduler/remove-attendee/${meetingId.value}`, removedAttendees.value)
    if (response.data?.dataPayload) {
      Swal.fire('Success!', 'Removed attendees have been submitted successfully.', 'success')
    }
    console.log('Removed Attendees:', removedAttendees.value)
  } catch (error) {
    console.log('meetingId:', meetingId.value)

    console.error('Error submitting removed attendees:', error)
  }
}

watch(
  () => props.submitSignal,
  (newSubmitSignal) => {
    if (newSubmitSignal === 'submit') {
      submitRemovedAttendees()
    }
  },
  { immediate: true }
)
</script>

<template>
  <div>
    <!-- Search and Add Attendees Section -->
    <b-col cols="12">
      <div class="search-form">
        <!-- Search Input -->
        <b-form-group label="Attendees:" label-for="input-1">
          <input v-model="searchQuery" type="text" class="form-control mb-2" placeholder="Search using username..." aria-label="Search for a username" />
        </b-form-group>

        <!-- Search Results -->
        <ul v-if="searchResults.length > 0" class="list-group position-relative" role="listbox">
          <li v-for="result in searchResults" :key="result.id" class="list-group-item list-group-item-action" @click="addAttendeeToTable(result)">
            {{ result.username }}
          </li>
        </ul>
        <!-- No Results Message -->
        <p v-else-if="searchQuery && !searchResults.length" class="text-muted mt-2">No results found.</p>
      </div>
    </b-col>

    <!-- Attendees Table Section -->
    <div v-if="attendees.length > 0" class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Names</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in attendees" :key="user.staff_id" class="custom-row" :class="{ 'text-muted': user.removed }">
            <td>
              <span :style="{ textDecoration: user.removed ? 'line-through' : 'none' }">
                {{ user.fullname }}
              </span>
            </td>
            <td>
              <span :style="{ textDecoration: user.removed ? 'line-through' : 'none' }">
                {{ user.email }}
              </span>
            </td>
            <td>
              <span :style="{ textDecoration: user.removed ? 'line-through' : 'none' }">
                {{ user.status === 9 ? 'Pending' : 'Active' }}
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
  </div>
</template>

<style scoped>
/* Add custom styles if needed */
.text-muted {
  color: #6c757d !important;
}

.table thead tr th {
  /* padding: 1px !important; */
  padding: 0.5px 8px 0 22px !important;
}
</style>
