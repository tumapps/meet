<script setup>
import { ref, onMounted, watch } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import AxiosInstance from '@/api/axios'
import { useAuthStore } from '@/store/auth.store.js';

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

const selectedUser_id = ref(null); // To store the corresponding user_id


const UsersOptions = ref([]);
const selectedUsername = ref(''); // To hold the selected username

const getusers_booked = async () => {
    try {
        const response = await axiosInstance.get('/v1/auth/users');
        UsersOptions.value = response.data.dataPayload.data.profiles;
        // console.log("Users data:", UsersOptions.value);
    } catch (error) {
        proxy.$showToast({
            title: 'An error occurred',
            text: 'Oops! An error has occurred',
            icon: 'error',
        });
    }
};

watch(selectedUsername, (newUsername) => {
    const selectedUser = UsersOptions.value.find(user => user.first_name === newUsername);
    selectedUser_id.value = selectedUser ? selectedUser.user_id : null;
    userId.value = selectedUser_id.value ? selectedUser_id.value : authStore.getUserId();

});

// Function to handle date click
function handleDateClick(info) {
  const clickedDate = info.dateStr
  if (clickedDate < today) {
    alert('You cannot select a past date.')
  } else {
    alert(`Date clicked: ${clickedDate}`)
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
async function fetchEvents() {
  try {
    const response = await axiosInstance.get('/v1/scheduler/appointments')
    const apiData = response.data.dataPayload.data
    events.value = apiData.map((item) => {
      return {
        title: item.subject,
        start: `${item.appointment_date}T${item.start_time}`,
        end: `${item.appointment_date}T${item.end_time}`,
        extendedProps: {
          start_time: item.start_time,
          end_time: item.end_time,
          description: item.description,
          contact_name: item.contact_name,
          status: item.statusLabel
        }
      }
    })
    // console.log("Events fetched:", events.value)  // Log events after fetch
  } catch (error) {
    console.error('Error fetching events:', error)
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
  initialView: 'dayGridMonth',
  weekends: true,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  dateClick: handleDateClick,
  eventClick: handleEventClick,
  dayCellClassNames: handleDayCellClassNames,
  eventClassNames: (event) => {
    return event.extendedProps?.status === 'Active' ? 'event-active' : 'event-cancelled'
  }
})

watch(events, (newEvents) => {
  calendarOptions.value.events = [...newEvents]
  // console.log("Updated calendar options:", calendarOptions.value)
})

// Watch for changes in events and update the calendar
watch(events, (newEvents) => {
  // console.log("Events updated:", newEvents)  // Log the updated events

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
    // console.log("FullCalendar API:", calendarApi)
  }, 1000)  // Add a short delay to ensure the calendar is initialized

  getusers_booked();

  console.log("CBB", CBB.value)
})


</script>
<template>
  <!-- FullCalendar Component -->
  <b-row v-if="CBB === 0" class="align-items-center form-group">
                            <b-col cols="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <label for="addappointmenttype" class="col-form-label">
                                    <icon-component type="outlined" icon-name="user" :size="24"></icon-component>
                                </label>
                            </b-col>
                            <b-col cols="10" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <!-- Bind the select dropdown to selectedUsername -->
                                <select v-model="selectedUsername" name="service" class="form-select"
                                    id="addappointmenttype">
                                    <option value="">Recipient</option>
                                    <option v-for="user in UsersOptions" :key="user.user_id" :value="user.first_name">
                                        {{ user.first_name }}
                                    </option>
                                </select>
                                <!-- <div v-if="errors.user_id" class="error" aria-live="polite">
                                    {{ errors.user_id }}
                                </div> -->
                            </b-col>
                        </b-row>
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
</template>
