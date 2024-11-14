<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import AxiosInstance from '@/api/axios'
import { useAuthStore } from '@/store/auth.store.js';
import BookAppointment from '@/components/modules/appointment/partials/BookAppointment.vue';

const authStore = useAuthStore();
const axiosInstance = AxiosInstance();
// Reactive variables
const events = ref([])
const selectedEvent = ref({})
const isModalOpen = ref(false)
const today = new Date().toISOString().split('T')[0];

//change the events owner
const CBB = ref('');
CBB.value = authStore.getCanBeBooked();
const userId = ref('');


const appointmentModal = ref(null);

//modal
const showModal = () => {
  appointmentModal.value.$refs.appointmentModal.show();
};
const selectedDate = ref('');


// Function to handle date click
function handleDateClick(info) {
  const clickedDate = info.dateStr
  if (clickedDate < today) {
    alert('You cannot select a past date.')
  } else {
    //show booking modal
    //pass clicked date to selecedDate
    selectedDate.value = clickedDate;
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
const apiData = ref([]);

async function fetchEvents() {
  try {
    const response = await axiosInstance.get('/v1/scheduler/appointments')
    apiData.value = response.data.dataPayload.data
    events.value = apiData.map((item) => {

      let backgroundColor;

      // Determine the background color based on the statusLabel
      switch (item.recordStatus.label) {
        case 'ACTIVE':
          backgroundColor = '#199F52';  // Active is red
          break;
        case 'CANCELLED':
          backgroundColor = 'orange'; // Cancelled is green
          break;

        case 'DELETED':
          backgroundColor = '#dc3545'; // Different shade of red for deleted
          break;

        default:
          backgroundColor = 'gray'; // Default color if no match
      }

      return {
        title: item.subject,
        start: `${item.appointment_date}T${item.start_time}`,
        end: `${item.appointment_date}T${item.end_time}`,
        backgroundColor: backgroundColor,
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
    // console.error('Error fetching events:', error)
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
  weekends: true,
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

  windowResize: function (view) {
    // Switch to a simpler view for small screens
    if (window.innerWidth < 768) {
      calendarOptions.value.initialView = 'timeGridDay';
      calendarOptions.value.height = 'auto';
      calendarOptions.value.dayMaxEvents = 2;
    } else {
      calendarOptions.value.initialView = 'dayGridMonth';
      calendarOptions.value.height = 650;
    }
  },
})

// Watch for changes in events and update the calendar
watch(events, (newEvents) => {
  // console.log("Events updated:", newEvents)  // Log the updated events
  calendarOptions.value.events = [...newEvents]

  const calendarApi = calendarOptions.value?.getApi?.()
  if (calendarApi) {
    // Remove existing events and add the new ones
    calendarApi.removeAllEvents()
    newEvents.forEach(event => {
      calendarApi.addEvent(event)
    })

    // Force the calendar to re-render (update the size)
    calendarApi.updateSize()
  }
})

// Load events when the component is mounted
onMounted(() => {
  fetchEvents()  // Fetch events from the API

  // Log FullCalendar API on mount
  setTimeout(() => {
    const calendarApi = calendarOptions.value?.getApi?.()
  }, 1000)  // Add a short delay to ensure the calendar is initialized

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

  <!-- Modal for booking -->
  <!-- <Booking -->
</template>

<style scoped>
.fc.v-event {
  background-color: black !important;
  border: 1px solid red !important;
}
</style>
