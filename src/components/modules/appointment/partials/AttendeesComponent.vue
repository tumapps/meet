<script setup>
import { ref, defineProps, watch, defineEmits } from 'vue'
import createAxiosInstance from '@/api/axios'

const attendeeModal = ref(null) // Reference for <b-modal>
const attendees = ref([]) // To store the attendees from the API

const props = defineProps({
  showModal: {
    type: Boolean, // Use Boolean for direct binding
    required: true
  }
})
const emit = defineEmits(['update:showModal']) // Emit event to sync with parent

const axiosInstance = createAxiosInstance()
const availableUsers = ref([])
const searchQuery = ref('')
const searchResults = ref([])
const selectedItems = ref([])

// Watch for prop updates
watch(
  () => props.showModal,
  (newVal) => {
    console.log('Child modal showModal updated:', newVal)
  }
)

// Sample user data
const users = ref([
  { id: 1, name: 'John Doe', email: 'john@example.com', status: 'Active' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', status: 'Inactive' },
  { id: 3, name: 'Alice Johnson', email: 'alice@example.com', status: 'Active' },
  { id: 4, name: 'Bob Brown', email: 'kk@hk.com', status: 'Active' }
])

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
  searchResults.value = availableUsers.value.filter((attendee) => attendee.username.toLowerCase().includes(query))
}

function addToAttendees() {
  const ids = selectedItems.value.map((item) => item.id) // Extract IDs
  attendees.value = [...attendees.value, ...ids] // Add to attendees
  console.log('Updated attendees: ', attendees.value)
}

const addSelectedItem = (item) => {
  // Avoid duplicates
  if (!selectedItems.value.find((selected) => selected.id === item.id)) {
    selectedItems.value.push(item)
  }
  addToAttendees()
  // Clear the search field and results
  searchQuery.value = ''
  searchResults.value = []

  console.log('added to list ', selectedItems.value)
}

const removeSelectedItem = (index) => {
  selectedItems.value.splice(index, 1)
}

const closeModal = () => {
  emit('update:showModal', false) // Notify parent to hide modal
}
</script>

<template>
  <b-modal ref="attendeeModal" title="Child Modal" hide-footer @hide="closeModal" class="child-modal" style="z-index: 1055">
    <!-- Modal content -->
    <b-col cols="10">
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
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
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
          </tr>
        </tbody>
      </table>
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
