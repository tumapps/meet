<script setup>
import { ref, defineEmits, defineProps, computed } from 'vue'

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
// Group time slots with original index preserved
const groupedSlots = computed(() => {
  return props.timeSlots.map((slot, index) => ({
    ...slot,
    originalIndex: index
  }))
})

// Filter slots by time of day
const morningSlots = computed(() =>
  groupedSlots.value.filter((slot) => {
    const hour = parseInt(slot.startTime.split(':')[0])
    return hour >= 6 && hour < 12
  })
)

const afternoonSlots = computed(() =>
  groupedSlots.value.filter((slot) => {
    const hour = parseInt(slot.startTime.split(':')[0])
    return hour >= 12 && hour < 17
  })
)

const eveningSlots = computed(() =>
  groupedSlots.value.filter((slot) => {
    const hour = parseInt(slot.startTime.split(':')[0])
    return hour >= 17 || hour < 6
  })
)

// Format time to AM/PM
const formatTime = (time) => {
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const period = hour >= 12 ? 'PM' : 'AM'
  const displayHour = hour % 12 || 12
  return `${displayHour}:${minutes} ${period}`
}

// Function to select a slot
const selectSlot = (index) => {
  const slot = props.timeSlots[index]
  console.log('slot selected:', slot)
  console.log(props.timeSlots)

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
<!-- //deepseek made sure the styles are not overriden by the parent component chatgpt broke existing onces😂😂😂😂😂😂😂😂😂😂 -->
<template>
  <div class="timeslots-container">
    <!-- Morning Group -->
    <div v-if="morningSlots.length" class="time-group">
      <h3 class="group-header">Morning</h3>
      <div class="group-slots">
        <div v-for="slot in morningSlots" :key="slot.startTime" :class="['timeslot', { booked: slot.booked, selected: slot.selected, expired: slot.is_expired }]" @click="selectSlot(slot.originalIndex)">
          <div class="timeslot-content">
            <div class="timeslot-time">{{ formatTime(slot.startTime) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Afternoon Group -->
    <div v-if="afternoonSlots.length" class="time-group">
      <h3 class="group-header">Afternoon</h3>
      <div class="group-slots">
        <div v-for="slot in afternoonSlots" :key="slot.startTime" :class="['timeslot', { booked: slot.booked, selected: slot.selected, expired: slot.is_expired }]" @click="selectSlot(slot.originalIndex)">
          <div class="timeslot-content">
            <div class="timeslot-time">{{ formatTime(slot.startTime) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Evening Group -->
    <div v-if="eveningSlots.length" class="time-group">
      <h3 class="group-header">Evening</h3>
      <div class="group-slots">
        <div v-for="slot in eveningSlots" :key="slot.startTime" :class="['timeslot', { booked: slot.booked, selected: slot.selected, expired: slot.is_expired }]" @click="selectSlot(slot.originalIndex)">
          <div class="timeslot-content">
            <div class="timeslot-time">{{ formatTime(slot.startTime) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.timeslots-container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  max-width: 800px;
  margin: 0 auto;
}

.time-group {
  margin-bottom: 24px;
}

.group-header {
  color: #2c3e50;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 12px;
  padding-bottom: 6px;
  border-bottom: 1px solid #e0e6ed;
}

.group-slots {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.timeslot {
  width: 70px;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #ffffff;
  color: #34495e;
  cursor: pointer;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
  font-size: 0.85rem;
  font-weight: 500;
}

.timeslot:hover {
  border-color: #3498db;
  transform: translateY(-2px);
  box-shadow: 0 2px 6px rgba(52, 152, 219, 0.2);
}

.timeslot.selected {
  background-color: #097b3e;
  color: white;
  border-color: #097b3e;
}

.timeslot.booked {
  background-color: #d89837;
  color: white;
  cursor: not-allowed;
  pointer-events: none;
  /* opacity: 0.7; */
}

.timeslot.expired {
  background-color: #95a5a6;
  color: #bdc3c7;
  pointer-events: none;
  text-decoration: line-through;
}

.timeslot-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100%;
}

.timeslot-time {
  text-align: center;
}
</style>
