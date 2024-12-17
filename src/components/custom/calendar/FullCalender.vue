<script setup>
import { ref, onMounted, watch, getCurrentInstance } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import AxiosInstance from '@/api/axios'
import { useAuthStore } from '@/store/auth.store.js'
import BookAppointment from '@/components/modules/appointment/partials/BookAppointment.vue'
import { usePreferencesStore } from '../../../store/preferences'
import { parse, format } from 'date-fns'

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

const appointmentModal = ref(null)

//modal
const showModal = () => {
  appointmentModal.value.$refs.appointmentModal.show()
}
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
      timer: 5000,
      showConfirmButton: false,
      showCancelButton: false,
      timerProgressBar: true
      // confirmButton:false,
    })
  } else {
    //show booking modal
    //pass clicked date to selecedDate
    selectedDate.value = clickedDate
    // console.log("Selected date:", selectedDate.value);
    // console.log(typeof(selectedDate.value));
    //pass the selected date to the modal

    showModal()
  }
}

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

function parseCustomDate(dateString) {
  // Parse the new format "14 Dec 2024"
  return parse(dateString, 'dd MMM yyyy', new Date())
}

async function fetchEvents() {
  isLoading.value = true
  fetchError.value = null
  try {
    const response = await axiosInstance.get('/v1/scheduler/appointments')

    apiData.value = response.data.dataPayload.data
    events.value = apiData.value
      .filter((item) => {
        // Ensure the event is ACTIVE and its date is today
        // const parsedDate = parse(item.appointment_date, 'yyyy-MM-dd', new Date())

        return item.recordStatus.label === 'ACTIVE'
      })
      .map((item) => {
        console.log('Item:', item)
        const parsedDate = parseCustomDate(item.appointment_date)
        const formattedDate = format(parsedDate, 'yyyy-MM-dd')
        let backgroundColor

        //this was used when i was displaying all events including cancelled now strictly showing active once
        // instead give diffrent colors to diffrent user id in secretary calendar view
        switch (item.recordStatus.label) {
          case 'ACTIVE':
            backgroundColor = '#86deb7'
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
        return {
          title: item.subject,
          start: `${formattedDate}T${item.start_time}`,
          end: `${formattedDate}T${item.end_time}`,
          backgroundColor: item.recordStatus.themeColor || backgroundColor,
          display: 'block',
          borderColor: 'transparent',
          extendedProps: {
            start_time: item.start_time,
            end_time: item.end_time,
            description: item.description,
            contact_name: item.contact_name,
            status: item.recordStatus.label
          }
        }
      })
  } catch (error) {
    fetchError.value = 'Failed to load events. Please try again later.'
  } finally {
    isLoading.value = false
  }
}

// this was replaced by the above function very slight changes to it on the date format handling
// async function fetchEvents() {
//   isLoading.value = true
//   fetchError.value = null

//   try {
//     const response = await axiosInstance.get('/v1/scheduler/appointments')
//     apiData.value = response.data.dataPayload.data
//     console.log('API Data:', apiData.value)
//     // apiData.value = dummyEvents

//     // Ensure events are set after apiData is updated
//     events.value = apiData.value.map((item) => {
//       console.log('Item:', item)
//       // Extract and format the date
//       // const appointmentDate = format(parseISO(item.appointment_date.split(',')[0]), 'yyyy-MM-dd')

//       // Determine background color
//       let backgroundColor
//       switch (item.recordStatus.label) {
//         case 'ACTIVE':
//           backgroundColor = '#86deb7'
//           break
//         case 'CANCELLED':
//           backgroundColor = '#E05263'
//           break
//         case 'DELETED':
//           backgroundColor = '#dc3545'
//           break
//         case 'PENDING':
//           backgroundColor = '#b8e1ff'
//           break
//         case 'RESCHEDULE':
//           backgroundColor = '#FFCAB1'
//           break
//         default:
//           backgroundColor = '#FFB2E6'
//       }
//       console.log('Appointment Date:', appointmentDate)

//       return {
//         title: item.subject || 'No Subject', // Fallback if subject is missing
//         start: `${appointmentDate}T${item.start_time}`, // Start date-time
//         end: `${appointmentDate}T${item.end_time}`,   // End date-time
//         backgroundColor: item.recordStatus.theme || backgroundColor,
//         display: 'block',
//         borderColor: 'transparent', // No border
//         extendedProps: {
//           start_time: item.start_time,
//           end_time: item.end_time,
//           description: item.description,
//           contact_name: item.contact_name,
//           status: item.recordStatus.label
//         }
//       }
//     })

//     // Log after assignment to ensure events have been updated
//     console.log('Events:', events.value)
//   } catch (error) {
//     fetchError.value = 'Failed to load events. Please try again later.'
//   } finally {
//     isLoading.value = false
//   }
// }

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

// FullCalendar options
const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  height: 'auto',
  events: events.value,
  weekends: preferences.weekend,
  dayMaxEvents: 3,
  slotDuration: '00:30:00', // Set time slot intervals to 30 minutes
  slotMinTime: '08:00:00', // Start time of the day view
  slotMaxTime: '18:00:00', // End time of the day view
  dateClick: handleDateClick,
  eventClick: handleEventClick,
  dayCellClassNames: handleDayCellClassNames,
  dayHeaderFormat: window.innerWidth < 768 ? { weekday: 'narrow' } : { weekday: 'short', month: 'numeric', day: 'numeric' },
  views: {
    dayGridMonth: {
      dayHeaderFormat: { weekday: 'short', month: 'numeric', day: 'numeric' }
    },
    timeGridDay: {
      dayHeaderFormat: { weekday: 'narrow' }
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
//   fetchEvents() // Fetch events from the API
//   console.log('events ata mount:', events.value)
// })

onMounted(async () => {
  await fetchEvents() // Wait for fetchEvents to complete
  console.log('events at mount:', apiData.value) // Log the events after the fetch is done
  console.log('events at mount:', events.value) // Log the events after the fetch is done
})
</script>
<template>
  <BookAppointment ref="appointmentModal" :selectedDate="selectedDate" />

  <!-- Skeleton Loader or Spinner -->
  <div v-if="isLoading" class="loading-spinner">
    <div class="skeleton-calendar">
      <div class="skeleton-header"></div>
      <div class="skeleton-day" v-for="n in 35" :key="n"></div>
    </div>
  </div>

  <!-- FullCalendar -->
  <FullCalendar v-else :options="calendarOptions" class="main-cont" />
  <!-- Modal for event details -->
  <b-modal v-model="isModalOpen" title="Appointment Details" dialog-class="centered-modal">
    <p><strong>Title:</strong> {{ selectedEvent.title }}</p>
    <p><strong>Start:</strong> {{ selectedEvent.start_time }}</p>
    <p><strong>End:</strong> {{ selectedEvent.end_time }}</p>
    <p><strong>Description:</strong> {{ selectedEvent.description }}</p>
    <p><strong>Contact Name:</strong> {{ selectedEvent.contact_name }}</p>
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
