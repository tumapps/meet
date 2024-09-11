<script setup>
import { onMounted, ref, getCurrentInstance } from 'vue';
import AxiosInstance from '@/api/axios';
import { useRoute } from 'vue-router';

const axiosInstance = AxiosInstance();
const {proxy} = getCurrentInstance();
const route = useRoute();

onMounted(() => {
  fetchSettings();
});
//errors 
const errors = ref({ start_time: '', end_time: '', booking_window: '', slot_duration: '', advanced_booking: '' });
// Time options for select dropdown
const timeOptions = ref([
  '08:00 ', '09:00 ', '10:00 ', '11:00', '12:00', '13:00', '14:00',
  '15:00', '16:00', '17:00', '18:00'
]);

const user_id = ref('212409004');
const appointment_id = ref('');
// Initial settings data
const settings = ref({
  user_id: user_id.value,
  start_time: '',
  end_time: '',
  booking_window: '',
  slot_duration: '',
  advanced_booking: '',
});

//fetch user settings
const fetchSettings = async () => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/settings/${user_id.value}`);
    console.log(response.data.dataPayload.data);

    settings.value = {
      start_time: response.data.dataPayload.data.start_time,
      end_time: response.data.dataPayload.data.end_time,
      booking_window: response.data.dataPayload.data.booking_window,
      slot_duration: response.data.dataPayload.data.slot_duration,
      advanced_booking: response.data.dataPayload.data.advanced_booking,
    };
    appointment_id.value = response.data.dataPayload.data.id;

  } catch (error) {
    proxy.$showToast({
      title: 'An error occurred ',
      text: 'an error has occured while getting your settings!',
      icon: 'success',
    });
  }
}

// Function to save settings (add API or local storage logic here)
const saveSettings = async () => {
  try {
    const response = await axiosInstance.put(`v1/scheduler/settings/${appointment_id.value}`, settings.value);
      proxy.$showToast({
        title: 'Updated',
        text: 'settings updated successfully!',
        icon: 'success',
    });
  } catch (error) {
    proxy.$showToast({
        title: 'Failed',
        text: 'failed to update your settings',
        icon: 'error',
      });
  }
}
</script>
<template>
  <div>
    <b-card class="">
      <h2 class="text-center mb-2">Scheduler Settings</h2>
      <b-form @submit.prevent="saveSettings">
        <!-- Section: Working Hours -->
        <b-card>
          <h4 class="text-green">Working Hours</h4>
          <b-row class="">
            <b-col md="12">
              <b-form-group label="Start Time" label-for="working-hours-start">
                <input id="working-hours-start" v-model="settings.start_time" type="time" required>
                <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
              </b-form-group>
            </b-col>
            <b-col md="6">
              <b-form-group label="End Time" label-for="working-hours-end">
                <input id="working-hours-end" v-model="settings.end_time" :options="timeOptions" required>
                <div v-if="errors.end_time" class="error" aria-live="polite">{{ errors.end_time }}</div>
              </b-form-group>
            </b-col>
          </b-row>
        </b-card>
        <!-- Section: Booking Settings -->
        <b-card>
          <h4 class="text-green">Booking Settings</h4>
          <b-row>
            <b-col md="12">
              <b-form-group label="Booking Window (in months)" label-for="booking-window">
                <b-form-input id="booking-window" type="number" v-model="settings.booking_window" min="1" max="12"
                  required></b-form-input>
                <div v-if="errors.booking_window" class="error" aria-live="polite">{{ errors.booking_window }}</div>
              </b-form-group>
            </b-col>

            <b-col md="12">
              <b-form-group label="Slot Duration (in minutes)" label-for="slot-duration">
                <b-form-input id="slot-duration" type="number" v-model="settings.slot_duration" min="15" max="60"
                  required></b-form-input>
                <div v-if="errors.slot_duration" class="error" aria-live="polite">{{ errors.slot_duration }}</div>
              </b-form-group>
            </b-col>
          </b-row>
        </b-card>
        <!-- Section: Buffer Time -->
        <b-card class="mb-3 p-3 shadow">
          <h4 class="text-green">Buffer Time</h4>
          <b-row>
            <b-col md="12">
              <b-form-group label="Buffer Time (in minutes)" label-for="buffer-time">
                <b-form-input id="buffer-time" type="number" v-model="settings.advanced_booking" min="10"
                  required></b-form-input>
                <div v-if="errors.advanced_booking" class="error" aria-live="polite">{{ errors.advanced_booking }}</div>
              </b-form-group>
            </b-col>
          </b-row>
        </b-card>

        <!-- Save Button -->
        <div class="text-center">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </b-form>
    </b-card>
  </div>
</template>



<style scoped>


/* Green theme for section titles */
.text-green {
  color: black;
}
</style>
