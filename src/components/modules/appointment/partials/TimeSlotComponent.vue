<script setup>
import { ref } from 'vue'

// Define props
const props = defineProps({
  timeSlots: {
    type: Array,
    required: true
  }
})

// Define emits
const emit = defineEmits(['update:timeSlots', 'selectedSlotsTimes'])

// Local state for selected slots
const selectedSlots = ref([])

// Function to select a slot
const selectSlot = (index) => {
  const slot = props.timeSlots[index]

  // Ignore if the slot is booked (grayed out)
  if (slot.booked) return

  // If the slot is already selected, unselect it
  if (slot.selected) {
    slot.selected = false
    selectedSlots.value = selectedSlots.value.filter((i) => i !== index) // Remove the slot from selected slots
  } else {
    // Check if there are already selected slots
    if (selectedSlots.value.length === 0) {
      // If no slots are selected, select the clicked slot
      slot.selected = true
      selectedSlots.value.push(index)
    } else {
      const lastSelected = selectedSlots.value[selectedSlots.value.length - 1]

      if (index < lastSelected) {
        // If the clicked slot is earlier than the last selected one, deselect all and select the current one
        props.timeSlots.forEach((s) => (s.selected = false))
        selectedSlots.value = []
        slot.selected = true
        selectedSlots.value.push(index)
      } else if (index === lastSelected + 1) {
        // Only allow selection of adjacent slots
        slot.selected = true
        selectedSlots.value.push(index)
      } else {
        // Deselect all previously selected slots and select the current slot
        props.timeSlots.forEach((s) => (s.selected = false))
        selectedSlots.value = []
        slot.selected = true
        selectedSlots.value.push(index)
      }
    }
  }

  // Emit the updated time slots to parent
  emit('update:timeSlots', props.timeSlots)

  // Emit selected slots times
  const selectedTimes = selectedSlots.value.map((i) => ({
    startTime: props.timeSlots[i].startTime,
    endTime: props.timeSlots[i].endTime
  }))

  // Calculate start and end time
  let startTime = ''
  let endTime = ''

  if (selectedTimes.length > 0) {
    startTime = selectedTimes[0].startTime
    endTime = selectedTimes[selectedTimes.length - 1].endTime
  }

  emit('selectedSlotsTimes', { startTime, endTime })

  // console.log(selectedSlots.value);
}
</script>

<template>
  <div class="timeslots-container justify-content-center">
    <!-- Loop through time slots and display them -->
    <div v-for="(slot, index) in timeSlots" :key="slot.startTime" :class="['timeslot', { booked: slot.booked, selected: slot.selected }]" @click="selectSlot(index)">
      <div class="d-flex flex-column justify-content-center align-items-center h-100">
        <div class="px-3 py-2 text-center w-100">{{ slot.startTime }}</div>
        <div class="px-3 py-2 text-center w-100">{{ slot.endTime }}</div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.timeslots-container {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
}

.timeslot {
  width: 80px;
  height: 80px;
  margin: 7px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #fff;
  color: black;
  cursor: pointer;
  border: 1px solid #ddd;
  border-radius: 5px;
  padding: 5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s;
}

.timeslot.selected {
  background-color: #4caf50;
  color: white;
}

.timeslot.booked {
  background-color: #d89837;
  color: white;
  pointer-events: none;
}
</style>
