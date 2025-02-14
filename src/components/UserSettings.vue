<script setup>
import { watch, ref, getCurrentInstance, defineProps } from 'vue'
import AxiosInstance from '@/api/axios'
// import { useRoute } from 'vue-router';
// import FlatPickr from 'vue-flatpickr-component';
import { useAuthStore } from '@/store/auth.store.js'
import { usePreferencesStore } from '@/store/preferences'

const props = defineProps({
  user_id: {
    type: String,
    required: true
  }
})

watch(
  () => props.user_id,
  async () => {
    await fetchSettings()
  }
)

const authStore = useAuthStore()
const preferences = usePreferencesStore()

const axiosInstance = AxiosInstance()
const { proxy } = getCurrentInstance()
// const route = useRoute();

//errors
const errors = ref({ start_time: '', end_time: '', booking_window: '', slot_duration: '', advanced_booking: '' })
// Time options for select dropdown
const timeOptions = ref(['08:00 ', '09:00 ', '10:00 ', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'])

//get user id from store
const user_id = ref('')
user_id.value = authStore.getUserId()
const appointment_id = ref('')
// Initial settings data
const settings = ref({
  user_id: user_id,
  start_time: '',
  end_time: '',
  booking_window: '',
  slot_duration: '',
  advanced_booking: ''
})

function toggleWeekend() {
  const newWeekendState = !preferences.weekend
  preferences.setWeekendPreference(newWeekendState)
}

//fetch user settings
const fetchSettings = async () => {
  try {
    const response = await axiosInstance.get(`v1/scheduler/settings/${user_id.value}`)
    // console.log(response.data.dataPayload.data);

    settings.value = {
      start_time: response.data.dataPayload.data.start_time,
      end_time: response.data.dataPayload.data.end_time,
      booking_window: response.data.dataPayload.data.booking_window,
      slot_duration: response.data.dataPayload.data.slot_duration,
      advanced_booking: response.data.dataPayload.data.advanced_booking
    }
    appointment_id.value = response.data.dataPayload.data.id
  } catch (error) {
    proxy.$showToast({
      title: 'An error occurred ',
      text: 'an error has occured while getting your settings!',
      icon: 'success'
    })
  }
}

// Flatpickr config
// const config = {
//   enableTime: false,
//   // noCalendar: true,
//   dateFormat: 'Y-m-d',

// };

// const config2 = {
//   enableTime: true,
//   noCalendar: true,
//   dateFormat: 'H:i',
//   time_24hr: true,
//   minuteIncrement: 10,
// };

// Selected date
// const start_date = ref('');
// const end_date = ref('');

//selected time
// const start_Time = ref('');
// const end_Time = ref('');
// const description = ref('');

// // Function to save settings (add API or local storage logic here)
// const saveAvailability = async () => {
//   try {
//     const response = await axiosInstance.post('v1/scheduler/availability', {
//       user_id: user_id.value,
//       start_date: start_date.value,
//       end_date: end_date.value,
//       start_time: start_Time.value,
//       end_time: end_Time.value,
//       description:description.value
//     });

//     proxy.$showToast({
//       title: 'Updated',
//       text: 'settings updated successfully!',
//       icon: 'success',
//     });
//   } catch (error) {
//     proxy.$showToast({
//       title: 'Failed',
//       text: 'failed to update your settings',
//       icon: 'error',
//     });
//   }
// }

// Function to save settings (add API or local storage logic here)
const saveSettings = async () => {
  try {
    await axiosInstance.put(`v1/scheduler/settings/${appointment_id.value}`, settings.value)
    proxy.$showToast({
      title: 'Updated',
      text: 'settings updated successfully!',
      icon: 'success'
    })
  } catch (error) {
    const errorMessage = error.response.data.errorPayload.errors?.message || 'An error occurred'

    proxy.$showToast({
      title: 'An error occurred',
      text: errorMessage,
      icon: 'error'
    })
  }
}
</script>
<template>
  <div>
    <b-card>
      <!-- <h2 class=" mb-2">Scheduler Settings</h2> -->
      <b-form @submit.prevent="saveSettings">
        <!-- Section: Working Hours -->
        <b-card class="mb-3 p-3 shadow">
          <h4 class="text-green">Working Hours</h4>
          <b-row class="">
            <b-col md="12">
              <b-form-group label="Start Time" label-for="working-hours-start">
                <b-form-input id="working-hours-start" v-model="settings.start_time" type="time"></b-form-input>
                <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
              </b-form-group>
            </b-col>
            <b-col md="12">
              <b-form-group label="End Time" label-for="working-hours-end">
                <b-form-input id="working-hours-end" v-model="settings.end_time" :options="timeOptions"></b-form-input>
                <div v-if="errors.end_time" class="error" aria-live="polite">{{ errors.end_time }}</div>
              </b-form-group>
            </b-col>
            <b-col md="12">
              <b-form-group label="Enable Weekends" label-for="enable-weekends-toggle">
                <b-form-checkbox id="enable-weekends-toggle" :checked="preferences.weekend" @change="toggleWeekend" switch>
                  {{ preferences.weekend ? 'Enabled' : 'Disabled' }}
                </b-form-checkbox>
              </b-form-group>
            </b-col>
          </b-row>
        </b-card>

        <!-- availability -->
        <!-- <b-card>
          <h4>Availability Settings</h4>
          <b-row>
            <b-col md="6">
              <div class="mb-3">
                <label for="datePicker" class="form-label">Start Date</label>
                <flat-pickr v-model="start_date" class="form-control" :config="config" id="datePicker" />
              </div>
            </b-col>
            <b-col md="5">
              <div class="mb-3">
                <label for="datePicker" class="form-label">Last Date</label>
                <flat-pickr v-model="end_date" class="form-control" :config="config" id="datePicker" />
              </div>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="6">
              <div class="mb-3">
                <label for="datePicker" class="form-label">Start Time</label>
                <flat-pickr v-model="start_Time" class="form-control" :config="config2" id="datePicker" />
              </div>
            </b-col>
            <b-col md="5">
              <div class="mb-3">
                <label for="datePicker" class="form-label">End Time</label>
                <flat-pickr v-model="end_Time" class="form-control" :config="config2" id="datePicker" />
              </div>
            </b-col>
            <b-col md="12" class="mb-3">
              <b-form-group label="Description" label-for="description">
                <b-form-input id="description" type="text" v-model="description"></b-form-input>
                <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
              </b-form-group>
            </b-col>
          </b-row>
          <div class="d-flex justify-content-end">
            <b-button @click="saveAvailability" variant="primary">Save</b-button>
          </div>
        </b-card> -->
        <!-- Section: Booking Settings -->
        <b-card class="mb-3 p-3 shadow">
          <h4>Booking Settings</h4>
          <b-row>
            <b-col md="12" class="mb-4">
              <b-form-group label="Booking Window (in months)" label-for="booking-window">
                <b-form-input id="booking-window" type="number" v-model="settings.booking_window"></b-form-input>
                <div v-if="errors.booking_window" class="error" aria-live="polite">{{ errors.booking_window }}</div>
              </b-form-group>
            </b-col>

            <b-col md="12">
              <b-form-group label="Slot Duration (in minutes)" label-for="slot-duration">
                <b-form-input id="slot-duration" type="number" v-model="settings.slot_duration" min="15" max="60"></b-form-input>
                <div v-if="errors.slot_duration" class="error" aria-live="polite">{{ errors.slot_duration }}</div>
              </b-form-group>
            </b-col>
          </b-row>
        </b-card>
        <!-- Section: Buffer Time -->
        <b-card class="mb-3 p-3 shadow">
          <h4>Buffer Time</h4>
          <b-row>
            <b-col md="12">
              <b-form-group label="Buffer Time (in minutes)" label-for="buffer-time">
                <b-form-input id="buffer-time" type="number" v-model="settings.advanced_booking" min="10"></b-form-input>
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

<style scoped></style>
