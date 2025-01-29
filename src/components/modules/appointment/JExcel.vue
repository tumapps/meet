<template>
  <div ref="spreadsheet"></div>
</template>

<script>
import 'jspreadsheet-ce/dist/jspreadsheet.css'
import 'jsuites/dist/jsuites.css'
import jspreadsheet from 'jspreadsheet-ce'
import Swal from 'sweetalert2'

export default {
  name: 'Spreadsheet',
  data() {
    return {
      attendees: [
        { staff_id: 212501015, email: 'keke@jk.com', name: 'KBlock Ken', removed: false },
        { staff_id: 212501011, email: 'limyvy@mailinator.com', name: 'Tucker Mcbride', removed: false },
        { staff_id: 212501020, email: 'john@doe.com', name: 'John Doe', removed: false },
        { staff_id: 212501021, email: 'jane@doe.com', name: 'Jane Doe', removed: false }
      ],
      removalReasons: {}
    }
  },
  computed: {
    // Convert attendees data into a format suitable for jExcel
    jExcelData() {
      return this.attendees.map(attendee => [
        attendee.staff_id,
        attendee.email,
        attendee.name,
        attendee.removed ? 'Removed' : 'Active'
      ])
    }
  },
  mounted() {
    // Initialize jExcel
    const spreadsheet = jspreadsheet(this.$refs.spreadsheet, {
      data: this.jExcelData,
      columns: [
        { title: 'Staff ID', width: 120, type: 'text', readOnly: true },
        { title: 'Email', width: 200, type: 'text', readOnly: true },
        { title: 'Name', width: 150, type: 'text', readOnly: true },
        { title: 'Status', width: 100, type: 'text', readOnly: true },
        {
          title: 'Action',
          width: 120,
          type: 'html',
          render: (instance, cell, colIndex, rowIndex) => {
            const attendee = this.attendees[rowIndex]
            if (attendee.removed) {
              return `<button class="btn btn-sm btn-success" onclick="undoRemoveAttendee(${rowIndex})"><i class="fas fa-undo"></i> Undo</button>`
            } else {
              return `<button class="btn btn-sm btn-danger" onclick="confirmRemoveAttendee(${rowIndex})"><i class="fas fa-trash"></i> Remove</button>`
            }
          }
        }
      ],
      rowHeight: 30 // Set the row height to 30px (or any desired value)
    })

    // Attach global functions for button actions
    window.confirmRemoveAttendee = this.confirmRemoveAttendee
    window.undoRemoveAttendee = this.undoRemoveAttendee
  },
  methods: {
    // Function to show SweetAlert for removal confirmation
    confirmRemoveAttendee(index) {
      const attendee = this.attendees[index]

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
          this.attendees[index].removed = true
          this.removalReasons[attendee.staff_id] = reason
          console.log(this.removalReasons)

          // Refresh jExcel data
          this.$refs.spreadsheet.jexcel.setData(this.jExcelData)

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
    },

    // Function to undo removal for a specific attendee
    undoRemoveAttendee(index) {
      const attendee = this.attendees[index]

      // Mark attendee as not removed and delete reason
      this.attendees[index].removed = false
      delete this.removalReasons[attendee.staff_id]

      // Refresh jExcel data
      this.$refs.spreadsheet.jexcel.setData(this.jExcelData)

      Swal.fire({
        title: 'Undo Successful!',
        text: `${attendee.name} has been restored.`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    }
  }
}
</script>

<style scoped>
/* Add custom styles if needed */
</style>