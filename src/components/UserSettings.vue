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
const errors = ref({})
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
  errors.value = {}
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

const saveSettings = async () => {
  errors.value = {}
  try {
    const response = await axiosInstance.put(`v1/scheduler/settings/${appointment_id.value}`, settings.value)

    if (response.data.toastPayload) {
      proxy.$showAlert({
        title: response.data.toastPayload.toastTheme,
        text: response.data.toastPayload.toastMessage,
        icon: response.data.toastPayload.toastTheme,
        showCancelButton: false,
        showConfirmButton: false,
        timer: 2000
      })
    }
  } catch (error) {
    console.log('error', error)
    errors.value = error.response.data.errorPayload?.errors
    // const errorMessage = error.response.data.errorPayload.errors?.message
    // status code !== 422
    if (error.response.status !== 422) {
      proxy.$showAlert({
        title: 'Failed',
        text: error.response.data.errorPayload.message,
        icon: 'error'
      })
    }
  }
}
</script>
<template>
  <div>
    <b-card>
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
                <b-form-input id="slot-duration" type="number" v-model="settings.slot_duration"></b-form-input>
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
                <b-form-input id="buffer-time" type="number" v-model="settings.advanced_booking"></b-form-input>
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
.error {
  color: red;
  font-size: 1rem;
}
</style>
