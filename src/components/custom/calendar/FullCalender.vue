<script setup>
import { ref, onMounted, watch } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import AxiosInstance from '@/api/axios'
import { useAuthStore } from '@/store/auth.store.js'
import BookAppointment from '@/components/modules/appointment/partials/BookAppointment.vue'
import { usePreferencesStore } from '../../../store/preferences'

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
    alert('You cannot select a past date.')
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
// Fetch events from API
const apiData = ref([])

const isLoading = ref(false)
const fetchError = ref(null)

async function fetchEvents() {
  isLoading.value = true
  fetchError.value = null
  try {
    const response = await axiosInstance.get('/v1/scheduler/appointments')
    apiData.value = response.data.dataPayload.data
    events.value = apiData.value.map((item) => {
      let backgroundColor
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
        start: `${item.appointment_date}T${item.start_time}`,
        end: `${item.appointment_date}T${item.end_time}`,
        backgroundColor: backgroundColor,
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

// Add class to grey out past dates
function handleDayCellClassNames(arg) {
  const cellDate = new Date(arg.date).toISOString().split('T')[0]
  if (cellDate < today) {
    return ['past-date']
  }
  return []
}

// FullCalendar options
const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  height: 'auto',
  events: events.value,
  weekends: preferences.weekend,
  dayMaxEvents: 3,
  slotMinTime: '08:00:00', // Start time of the day view
  slotMaxTime: '18:00:00', // End time of the day view
  dateClick: handleDateClick,
  eventClick: handleEventClick,
  dayCellClassNames: handleDayCellClassNames,
  // dayHeaderFormat: window.innerWidth < 768 ? { weekday : 'narrow' } : { weekday: 'short', month: 'numeric', day: 'numeric' },
  views: {
    timeGridWeek: {
      dayHeaderFormat: { weekday: 'narrow' } // Narrow format only for timeGridWeek
    }
  },
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },

  eventClassNames: (event) => {
    return event.extendedProps?.status === 'Active' ? 'event-active' : 'event-cancelled'
  },

  windowResize: function () {
    let resizeTimeout
    calendarOptions.value.windowResize = function () {
      clearTimeout(resizeTimeout)
      resizeTimeout = setTimeout(() => {
        if (window.innerWidth < 768) {
          calendarOptions.value.initialView = 'timeGridDay'
          calendarOptions.value.dayMaxEvents = 2
        } else {
          calendarOptions.value.initialView = 'dayGridMonth'
          calendarOptions.value.dayMaxEvents = 3
        }
      }, 300)
    }
  }
})

// Watch for changes in events and update the calendar
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

// Load events when the component is mounted
onMounted(() => {
  fetchEvents() // Fetch events from the API
})
</script>
<template>
  <BookAppointment ref="appointmentModal" :selectedDate="selectedDate" />
  <FullCalendar :options="calendarOptions" class="main-cont" />
  <!-- Modal for event details -->
  <b-modal v-model="isModalOpen" title="Event Details" dialog-class="centered-modal">
    <p><strong>Title:</strong> {{ selectedEvent.title }}</p>
    <p><strong>Start:</strong> {{ selectedEvent.start }} ({{ selectedEvent.start_time }})</p>
    <p><strong>End:</strong> {{ selectedEvent.end }} ({{ selectedEvent.end_time }})</p>
    <p><strong>Description:</strong> {{ selectedEvent.description }}</p>
    <p><strong>Contact Name:</strong> {{ selectedEvent.contact_name }}</p>
    <p><strong>Status:</strong> {{ selectedEvent.status }}</p>
    <template #footer>
      <b-button variant="warning" @click="isModalOpen = false">Close</b-button>
    </template>
  </b-modal>

  <div v-if="isLoading" class="loading-spinner">Loading events...</div>

  <!-- Modal for booking -->
  <!-- <Booking -->
</template>
<style scoped></style>
