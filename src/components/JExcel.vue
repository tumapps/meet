<script setup>
import { ref } from 'vue'
import Swal from 'sweetalert2'
import 'jsuites/dist/jsuites.css'
import 'jspreadsheet-ce/dist/jspreadsheet.css'

// Sample attendee data
const attendees = ref([
  { staff_id: 212501015, email: 'keke@jk.com', name: 'KBlock Ken', removed: false },
  { staff_id: 212501011, email: 'limyvy@mailinator.com', name: 'Tucker Mcbride', removed: false },
  { staff_id: 212501020, email: 'john@doe.com', name: 'John Doe', removed: false },
  { staff_id: 212501021, email: 'jane@doe.com', name: 'Jane Doe', removed: false }
])

// Reason storage (key-value pair: { userId: reason })
const removalReasons = ref({})

// Function to show SweetAlert for removal confirmation
const confirmRemoveAttendee = (index) => {
  const attendee = attendees.value[index]

  Swal.fire({
    title: 'Are you sure?',
    text: `You are about to remove ${attendee.name}. Please provide a reason.`,
    icon: 'warning',
    input: 'textarea',
    inputLabel: 'Reason for Removal',
    inputPlaceholder: 'Please type your reason here...',
    inputAttributes: {
      'aria-label': 'Please type your reason here'
    },
    showCancelButton: true,
    confirmButtonText: 'Yes, Remove',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
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

      // Update attendee as removed and save the reason
      attendees.value[index].removed = true
      removalReasons.value[attendee.staff_id] = reason

      // Show confirmation toast
      Swal.fire({
        title: 'Removed!',
        text: `${attendee.name} has been removed.`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    }
  })
}

// Function to undo removal for a specific attendee
const undoRemoveAttendee = (index) => {
  const attendee = attendees.value[index]

  // Mark attendee as not removed and delete reason
  attendees.value[index].removed = false
  delete removalReasons.value[attendee.staff_id]

  Swal.fire({
    title: 'Undo Successful!',
    text: `${attendee.name} has been restored.`,
    icon: 'success',
    timer: 2000,
    showConfirmButton: false
  })
}
</script>

<template>
  <div class="p-4">
    <!-- Table to display attendees -->
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th v-for="(value, key) in attendees[0]" :key="key">
            {{ key.charAt(0).toUpperCase() + key.slice(1) }}
            <!-- Capitalize first letter of key -->
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(attendee, index) in attendees" :key="attendee.staff_id" :class="{ 'text-muted': attendee.removed }">
          <!-- <td>
            <span :style="{ textDecoration: attendee.removed ? 'line-through' : 'none' }">
              {{ index + 1 }}
            </span>
          </td> -->
          <td>
            <span :style="{ textDecoration: attendee.removed ? 'line-through' : 'none' }">
              {{ attendee.staff_id }}
            </span>
          </td>
          <td>
            <span :style="{ textDecoration: attendee.removed ? 'line-through' : 'none' }">
              {{ attendee.email }}
            </span>
          </td>
          <td>
            <span :style="{ textDecoration: attendee.removed ? 'line-through' : 'none' }">
              {{ attendee.name }}
            </span>
          </td>
          <td>
            <!-- Toggle Remove/Undo Button -->
            <button v-if="!attendee.removed" class="btn btn-sm btn-danger" @click="confirmRemoveAttendee(index)"><i class="fas fa-trash"></i> Remove</button>
            <button v-else class="btn btn-sm btn-success" @click="undoRemoveAttendee(index)"><i class="fas fa-undo"></i> Undo</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style>
table td,
table th {
  padding: 5px 10px; /* Adjust padding here */
}

.text-muted {
  color: #6c757d !important; /* Optional muted styling for removed rows */
}
table tbody tr {
  height: 20px; /* Reduce row height */
}
</style>
