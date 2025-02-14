<script setup>
import { ref, onMounted, watch, getCurrentInstance, defineProps, computed } from 'vue'
import { useRouter } from 'vue-router'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import AxiosInstance from '@/api/axios'
import { useAuthStore } from '@/store/auth.store.js'
import { usePreferencesStore } from '../../../store/preferences'
import { format } from 'date-fns' // read comment on line 107

const router = useRouter()

const props = defineProps({
  dashType: {
    type: String,
    required: true
  }
})
//wooah

console.log('dashType:', props.dashType)

const { proxy } = getCurrentInstance()
const preferences = usePreferencesStore()
const authStore = useAuthStore()
const axiosInstance = AxiosInstance()
// Reactive variables
const events = ref([])
const selectedEvent = ref({})
const isModalOpen = ref(false)
const today = new Date().toISOString().split('T')[0]

//change the events owner
const role = ref('')
role.value = authStore.getRole()
// const userId = ref('');

// const appointmentModal = ref(null)

//modal
// const showModal = () => {
//   // appointmentModal.value.$refs.appointmentModal.show()
//   if (props.dashType === 'user') {
//     appointmentModal.value.$refs.appointmentModal.show()
//   } else {
//     //emit a create event
//     emit('createEvent')
//   }
// }
const selectedDate = ref('')

// Function to handle date click
function handleDateClick(info) {
  const clickedDate = info.dateStr
  if (clickedDate < today) {
    // proxyR
    proxy.$showAlert({
      title: 'Info',
      text: 'Please Click Today or a later Date',
      icon: 'info',
      timer: 4000,
      showConfirmButton: false,
      showCancelButton: true,
      timerProgressBar: true
      // confirmButton:false,
    })
  } else {
    //show booking modal
    //pass clicked date to selecedDate
    selectedDate.value = clickedDate
    console.log('Selected date:', selectedDate.value)
    //push to booking
    if (props.dashType === 'Registrar') {
      router.push({ name: 'all-events' })
    } else {
      router.push({ name: 'booking', query: { date: selectedDate.value } })
    }
    // console.log(typeof(selectedDate.value));
    //pass the selected date to the modal
  }
}

const Dashtype = computed(() => props.dashType)

// Function to handle event click and open modal
function handleEventClick(info) {
  selectedEvent.value = {
    title: info.event.title,
    start: info.event.startStr,
    end: info.event.endStr || 'N/A',
    start_time: info.event.extendedProps?.start_time || 'N/A',
    end_time: info.event.extendedProps?.end_time || 'N/A',
    description: info.event.extendedProps?.description || 'N/A',
    contact_name: info.event.extendedProps?.contact_name || 'N/A',
    status: info.event.extendedProps?.status || 'N/A'
  }
  isModalOpen.value = true
}
// Fetch events from API

// array to store the fetched data
const apiData = ref([])
// const apiData = ref()

const isLoading = ref(false)
const fetchError = ref(null)

//update on this .. i stoppee dusing it since date coming form the backend changed format to yyyy-MM-dd

// function parseCustomDate(dateString) {
//   // Parse the new format "14 Dec 2024"
//   return parse(dateString, 'dd MMM yyyy', new Date())
// }

// async function fetchAppointments() {
//   isLoading.value = true
//   fetchError.value = null
//   try {
//     const response = await axiosInstance.get('/v1/scheduler/appointments')

//     apiData.value = response.data.dataPayload.data
//     events.value = apiData.value
//       .filter((item) => {
//         // Ensure the event is ACTIVE and its date is today
//         // const parsedDate = parse(item.appointment_date, 'yyyy-MM-dd', new Date())

//         return item.recordStatus.label === 'ACTIVE'
//       })
//       .map((item) => {
//         console.log('Item:', item)
//         const parsedDate = parseCustomDate(item.appointment_date)
//         const formattedDate = format(parsedDate, 'yyyy-MM-dd')
//         let backgroundColor

//         //this was used when i was displaying all events including cancelled now strictly showing active once
//         // instead give diffrent colors to diffrent user id in secretary calendar view
//         switch (item.recordStatus.label) {
//           case 'ACTIVE':
//             backgroundColor = '#86deb7'
//             break
//           case 'CANCELLED':
//             backgroundColor = '#E05263'
//             break
//           case 'DELETED':
//             backgroundColor = '#dc3545'
//             break
//           case 'PENDING':
//             backgroundColor = '#b8e1ff'
//             break
//           case 'RESCHEDULE':
//             backgroundColor = '#FFCAB1'
//             break
//           default:
//             backgroundColor = '#FFB2E6'
//         }
//         return {
//           title: item.subject,
//           start: `${formattedDate}T${item.start_time}`,
//           end: `${formattedDate}T${item.end_time}`,
//           backgroundColor: item.recordStatus.themeColor || backgroundColor,
//           display: 'block',
//           borderColor: 'transparent',
//           extendedProps: {
//             start_time: item.start_time,
//             end_time: item.end_time,
//             description: item.description,
//             contact_name: item.contact_name,
//             status: item.recordStatus.label
//           }

//           console.log('tittle:', item.subject)

//         }
//       })
//   } catch (error) {
//     fetchError.value = 'Failed to load events. Please try again later.'
//   } finally {
//     isLoading.value = false
//   }
// }

async function fetchAppointments() {
  isLoading.value = true
  fetchError.value = null

  try {
    const params = {}

    // Include search query if it's not empty
    if (selectedUser.value) {
      params._search = selectedUser.value
    }

    const response = await axiosInstance.get('/v1/scheduler/appointments', { params })

    apiData.value = response.data.dataPayload.data
    console.log('Raw API Data:', typeof apiData.value) // Debugging to check API response
    if (Array.isArray(apiData.value)) {
      events.value = apiData.value
        .filter((item) => {
          // Ensure the event is ACTIVE
          return item.recordStatus?.label === 'ACTIVE'
        })
        .map((item) => {
          console.log('Item:', item)

          // const parsedDate = parseCustomDate(item.appointment_date)
          const formattedDate = format(item.appointment_date, 'yyyy-MM-dd')
          let backgroundColor

          // Assign background color based on recordStatus.label
          switch (item.recordStatus.label) {
            case 'ACTIVE':
              backgroundColor = '#C056C6'
              break
            case 'CANCELLED':
              backgroundColor = '#E05263'
              break
            case 'DELETED':
              backgroundColor = '#dc3545'
              break
            case 'PENDING':
              backgroundColor = '#b8e1ff'
              break
            case 'RESCHEDULE':
              backgroundColor = '#FFCAB1'
              break
            default:
              backgroundColor = '#FFB2E6'
          }

          // Construct the event object
          const event = {
            title: item.subject,
            start: `${formattedDate}T${item.start_time}`,
            end: `${formattedDate}T${item.end_time}`,
            backgroundColor: item.recordStatus.themeColor || backgroundColor,
            display: 'block',
            borderColor: 'transparent',
            initialView: 'timeGridWeek',

            extendedProps: {
              start_time: item.start_time,
              end_time: item.end_time,
              description: item.description,
              contact_name: item.contact_name,
              status: item.recordStatus.label
            }
          }

          // Log the constructed event
          console.log('Event main Action :', event)

          return event
        })
    }
  } catch (error) {
    fetchError.value = 'Failed to load events. Please try again later.'
    console.error('Error fetching appointments:', error)
  } finally {
    isLoading.value = false
  }
}

//function to get events

async function fetchEvents() {
  isLoading.value = true
  fetchError.value = null

  try {
    const response = await axiosInstance.get('/v1/scheduler/events')

    // Ensure apiData is always an array
    apiData.value = Array.isArray(response.data.dataPayload.data) ? response.data.dataPayload.data : []

    console.log('Raw API Data:', typeof apiData.value) // Debugging to check API response

    // Filter and map API data to FullCalendar's required format
    if (Array.isArray(apiData.value)) {
      events.value = apiData.value
        .filter((item) => item?.recordStatus?.label === 'ACTIVE') // Ensure safe access
        .map((item) => {
          const start = `${item.start_date}T${item.start_time}`
          const end = `${item.end_date}T${item.end_time}`

          return {
            title: item.title,
            start,
            end,
            backgroundColor: item.recordStatus?.theme || '#d33', // Default color
            display: 'block',
            borderColor: 'transparent',
            initialView: 'timeGridMonth',
            extendedProps: {
              start_time: item.start_time,
              end_time: item.end_time,
              description: item.description,
              status: item.recordStatus?.label || 'UNKNOWN' // Default status
            }
          }
        })
    }

    console.log('Mapped Events:', events.value) // Debugging to verify mapped data
  } catch (error) {
    fetchError.value = 'Failed to load events. Please try again later.'
    console.error('Error fetching events:', error)
  } finally {
    isLoading.value = false
  }
}

// Add class to grey out past dates
function handleDayCellClassNames(arg) {
  const cellDate = new Date(arg.date).toISOString().split('T')[0]
  if (cellDate < today) {
    return ['past-date']
  }
  return []
}

watch(
  () => preferences.weekend,
  (newWeekend) => {
    calendarOptions.value.weekends = newWeekend
  }
)

// Watch for dashType changes and update initialView
watch(
  () => props.dashType,
  (newDashType) => {
    calendarOptions.value.initialView = newDashType === 'Registrar' ? 'timeGridMonth' : 'timeGridWeek'
  }
)

const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: props.dashType === 'Registrar' ? 'dayGridMonth' : 'timeGridWeek',
  height: 'auto',
  events: events.value,
  weekends: preferences.weekend,
  dayMaxEvents: 3,
  slotDuration: '00:30:00', // Set time slot intervals to 30 minutes
  slotMinTime: '08:00:00', // Start time of the day view
  slotMaxTime: '21:00:00', // End time of the day view
  dateClick: handleDateClick,
  eventClick: handleEventClick,
  dayCellClassNames: handleDayCellClassNames,
  dayHeaderFormat: window.innerWidth < 768 ? { weekday: 'narrow' } : { weekday: 'short', month: 'numeric', day: 'numeric' },
  views: {
    dayGridMonth: {
      dayHeaderFormat: { weekday: 'short' }
    },
    timeGridWeek: {
      dayHeaderFormat: { weekday: 'short' }
    },
    timeGridDay: {
      dayHeaderFormat: { weekday: 'long' }
    }
  },
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },

  eventClassNames: (event) => {
    return `event-${event.extendedProps?.status?.toLowerCase()}`
  }

  // windowResize: function () {
  //   let resizeTimeout
  //   calendarOptions.value.windowResize = function () {
  //     clearTimeout(resizeTimeout)
  //     resizeTimeout = setTimeout(() => {
  //       if (window.innerWidth < 768) {
  //         calendarOptions.value.initialView = 'timeGridDay'
  //         calendarOptions.value.dayMaxEvents = 2
  //       } else {
  //         calendarOptions.value.initialView = 'dayGridMonth'
  //         calendarOptions.value.dayMaxEvents = 3
  //       }
  //     }, 300)
  //   }
  // }
})

// Watch for changes in events and update the calendar

// watch(events, (newEvents) => {
//   calendarOptions.value.events = [...newEvents]
// })

watch(events, (newEvents) => {
  // console.log("Events updated:", newEvents)  // Log the updated events
  calendarOptions.value.events = [...newEvents]

  const calendarApi = calendarOptions.value?.getApi?.()
  if (calendarApi) {
    // Remove existing events and add the new ones
    calendarApi.removeAllEvents()
    newEvents.forEach((event) => {
      calendarApi.addEvent(event)
    })

    // Force the calendar to re-render (update the size)
    calendarApi.updateSize()
  }
})

// // Load events when the component is mounted
// onMounted(() => {
//   fetchAppointments() // Fetch events from the API
//   console.log('events ata mount:', events.value)
// })

//filter meetings for secretary
// const searchQuery = ref('');

const UsersOptions = ref([])
const selectedUser = ref('') // To hold the selected username

//filter appointments by user
const getusers_booked = async () => {
  try {
    const response = await axiosInstance.get('/v1/auth/users')
    UsersOptions.value = response.data.dataPayload.data
    console.log('Users data:', UsersOptions.value)
    // console.log("Users data:", UsersOptions.value);
  } catch (error) {
    proxy.$showToast({
      title: 'An error occurred',
      text: 'Oops! An error has occurred',
      icon: 'error'
    })
  }
}

watch(selectedUser, (newselectedUser) => {
  if (newselectedUser === '') {
    fetchAppointments()
  } else {
    // fetchAppointmentsByUser(selectedUser.value)
    console.log('Selected User:', selectedUser.value)
    fetchAppointments()
  }
})

onMounted(async () => {
  if (props.dashType === 'user') {
    await fetchAppointments()
  } else if (props.dashType === 'secretary') {
    await getusers_booked()
  } else if (props.dashType === 'Registrar') {
    await fetchEvents()
  }
})
</script>
<template>
  <b-row>
    <b-col v-if="Dashtype !== 'Registrar'" lg="2" class="d-flex justify-content-lg-end mb-3 mb-5">
      <div v-if="role === 'su' || role === 'secretary'" class="w-100 w-lg-auto">
        <div class="dropdown w-100 w-lg-auto" style="float: right">
          <select v-model="selectedUser" name="service" class="form-select form-select-sm" id="addappointmenttype">
            <option value="">All</option>
            <option v-for="user in UsersOptions" :key="user.user_id" :value="user.id">
              {{ user.fullname }}
            </option>
          </select>
        </div>
      </div>
    </b-col>
    <!-- FullCalendar -->
    <FullCalendar :options="calendarOptions" class="main-cont" />
  </b-row>
  <!-- Modal for event details -->
  <b-modal v-model="isModalOpen" title="Summary" dialog-class="centered-modal">
    <p><strong>Title:</strong> {{ selectedEvent.title }}</p>
    <p><strong>Start:</strong> {{ selectedEvent.start_time }}</p>
    <p><strong>End:</strong> {{ selectedEvent.end_time }}</p>
    <p><strong>Description:</strong> {{ selectedEvent.description }}</p>
    <p v-if(selectedEvent.contact_name)><strong>Contact Name:</strong> {{ selectedEvent.contact_name }}</p>
    <p><strong>Status:</strong> {{ selectedEvent.status }}</p>
    <template #footer>
      <b-button variant="warning" @click="isModalOpen = false">Close</b-button>
    </template>
  </b-modal>

  <!-- Modal for booking -->
  <!-- <Booking -->
</template>
<style scoped>
.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  font-size: 1.2em;
  color: #777;
}

/* Example skeleton styles */
.skeleton-calendar {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
  max-width: 500px;
  margin: auto;
}

.skeleton-header {
  grid-column: span 7;
  height: 20px;
  background: #ddd;
  border-radius: 4px;
}

.skeleton-day {
  height: 50px;
  background: #eee;
  border-radius: 4px;
}
</style>
