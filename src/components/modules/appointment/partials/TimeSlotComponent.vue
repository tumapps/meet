<!-- TimeSlotComponent.vue -->
<template>
    <div class="timeslots-container">
        <!-- Loop through time slots and display them -->
        <div v-for="(slot, index) in timeSlots" :key="slot.startTime"
            :class="['timeslot', { 'booked': slot.booked, 'selected': slot.selected }]" @click="selectSlot(index)">
            {{ slot.startTime }} - {{ slot.endTime }}
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';

// Define props
const props = defineProps({
    timeSlots: {
        type: Array,
        required: true
    }
});

// Define emits
const emit = defineEmits(['update:timeSlots']);

// Local state for selected slots
const selectedSlots = ref([]);

// Function to select a slot
const selectSlot = (index) => {
    const slot = props.timeSlots[index];

    // Ignore if the slot is booked (grayed out)
    if (slot.booked) return;

    // If the slot is already selected, unselect it
    if (slot.selected) {
        slot.selected = false;
        selectedSlots.value = selectedSlots.value.filter(i => i !== index); // Remove the slot from selected slots
    } else {
        // Check if there are already selected slots
        if (selectedSlots.value.length === 0) {
            // If no slots are selected, select the clicked slot
            slot.selected = true;
            selectedSlots.value.push(index);
        } else {
            const lastSelected = selectedSlots.value[selectedSlots.value.length - 1];

            if (index < lastSelected) {
                // If the clicked slot is earlier than the last selected one, deselect all and select the current one
                props.timeSlots.forEach(s => s.selected = false);
                selectedSlots.value = [];
                slot.selected = true;
                selectedSlots.value.push(index);
            } else if (index === lastSelected + 1) {
                // Only allow selection of adjacent slots
                slot.selected = true;
                selectedSlots.value.push(index);
            } else {
                // Deselect all previously selected slots and select the current slot
                props.timeSlots.forEach(s => s.selected = false);
                selectedSlots.value = [];
                slot.selected = true;
                selectedSlots.value.push(index);
            }
        }
    }

    // Emit the updated time slots to parent
    emit('update:timeSlots', props.timeSlots);
};
</script>

<style scoped>
.timeslots-container {
    display: flex;
    flex-wrap: wrap;
}

.timeslot {
    width: 60px;
    height: 60px;
    margin: 5px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f0f0f0;
    cursor: pointer;
    border: 1px solid #ddd;
}

.timeslot.selected {
    background-color: #4caf50;
    color: white;
}

.timeslot.booked {
    background-color: #ccc;
    color: white;
    pointer-events: none;
}
</style>