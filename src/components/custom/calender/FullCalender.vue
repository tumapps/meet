<script setup>
// Import required FullCalendar plugins
import FullCalendar from '@fullcalendar/vue3'
import InteractionPlugin from '@fullcalendar/interaction'
import DayGridPlugin from '@fullcalendar/daygrid'
import TimeGridPlugin from '@fullcalendar/timegrid'
import ListPlugin from '@fullcalendar/list'
import BootstrapPlugin from '@fullcalendar/bootstrap'

// Define props
const props = defineProps({
  events: {
    type: Array,
    default: () => []
  }
})

// Define emit function
const emit = defineEmits(['onDateSelect'])

// Event handlers
const handleDateSelect = (e) => {
  emit('onDateSelect', e)
}

const handleEventSelect = (e) => {
  emit('onDateSelect', e) // Consider renaming this to 'onEventSelect' for clarity
}

// FullCalendar options
const options = {
  plugins: [
    DayGridPlugin,
    TimeGridPlugin,
    InteractionPlugin, // Needed for dateClick
    ListPlugin,
    BootstrapPlugin
  ],
  timeZone: 'UTC',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
  },
  weekNumbers: false,
  initialView: 'dayGridMonth',
  editable: true,
  dayMaxEvents: true,
  select: handleEventSelect,
  dateClick: handleDateSelect,
  events: props.events
}
</script>

<template>
  <full-calendar :options="options"></full-calendar>
</template>

<style>
/* Custom styling for FullCalendar buttons */
.fc .fc-button {
  text-transform: capitalize;
}
</style>
