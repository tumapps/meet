<script setup>
import { ref } from 'vue';
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue'; // Import the child component

// Define time slots
const timeSlots = ref([
  { startTime: '09:00', endTime: '09:30', booked: false, selected: false },
  { startTime: '09:30', endTime: '10:00', booked: true, selected: false },
  { startTime: '10:00', endTime: '10:30', booked: false, selected: false },
  { startTime: '10:30', endTime: '11:00', booked: false, selected: false },
  { startTime: '11:00', endTime: '11:30', booked: true, selected: false },
  { startTime: '11:30', endTime: '12:00', booked: false, selected: false },
  { startTime: '12:00', endTime: '12:30', booked: false, selected: false },
  { startTime: '12:30', endTime: '13:00', booked: false, selected: false },
  { startTime: '13:00', endTime: '13:30', booked: true, selected: false },
  { startTime: '13:30', endTime: '14:00', booked: false, selected: false },
  { startTime: '14:00', endTime: '14:30', booked: false, selected: false },
  { startTime: '14:30', endTime: '15:00', booked: false, selected: false },
  { startTime: '15:00', endTime: '15:30', booked: true, selected: false },
  { startTime: '15:30', endTime: '16:00', booked: false, selected: false },
  { startTime: '16:00', endTime: '16:30', booked: false, selected: false },
  { startTime: '16:30', endTime: '17:00', booked: false, selected: false }
]);

const updateTimeSlots = (updatedSlots) => {
  timeSlots.value = updatedSlots;
};
</script>
<template>
  <div>
    <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots" />
  </div>
</template>


