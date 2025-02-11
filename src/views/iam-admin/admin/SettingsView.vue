<template>
  <b-card>
    <b-card-header>
      <h4 class="card-title">App Settings</h4>
    </b-card-header>
    <b-card-body>
      <!-- Message Display -->
      <b-alert v-if="message" :variant="message.type" show>
        {{ message.text }}
      </b-alert>

      <!-- Form Fields -->
      <b-row>
        <!-- App Name -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="app_name" class="form-label">App Name</label>
            <b-form-input id="app_name" v-model="settings.app_name" type="text" placeholder="Enter App Name"></b-form-input>
          </div>
          <div v-if="errors.app_name" class="error" aria-live="polite">
            {{ errors.app_name }}
          </div>
        </b-col>

        <!-- System Email -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="system_email" class="form-label">System Email</label>
            <b-form-input id="system_email" v-model="settings.system_email" type="text" placeholder="Enter System Email"></b-form-input>
          </div>
          <div v-if="errors.system_email" class="error" aria-live="polite">
            {{ errors.system_email }}
          </div>
        </b-col>

        <!-- Category -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <b-form-input id="category" v-model="settings.category" type="text" placeholder="Enter Category"></b-form-input>
          </div>
          <div v-if="errors.category" class="error" aria-live="polite">
            {{ errors.category }}
          </div>
        </b-col>

        <!-- Email Scheme -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="email_scheme" class="form-label">Email Scheme</label>
            <b-form-input id="email_scheme" v-model="settings.email_scheme" type="text" placeholder="Enter Email Scheme"></b-form-input>
          </div>
          <div v-if="errors.email_scheme" class="error" aria-live="polite">
            {{ errors.email_scheme }}
          </div>
        </b-col>

        <!-- Email SMTPs -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="email_smtps" class="form-label">Email SMTPs</label>
            <b-form-input id="email_smtps" v-model="settings.email_smtps" type="text" placeholder="Enter Email SMTPs"></b-form-input>
          </div>
          <div v-if="errors.email_smtps" class="error" aria-live="polite">
            {{ errors.email_smtps }}
          </div>
        </b-col>

        <!-- Email Port -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="email_port" class="form-label">Email Port</label>
            <b-form-input id="email_port" v-model="settings.email_port" type="text" placeholder="Enter Email Port"></b-form-input>
          </div>
          <div v-if="errors.email_port" class="error" aria-live="polite">
            {{ errors.email_port }}
          </div>
        </b-col>

        <!-- Email Encryption -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="email_encryption" class="form-label">Email Encryption</label>
            <b-form-input id="email_encryption" v-model="settings.email_encryption" type="text" placeholder="Enter Email Encryption"></b-form-input>
          </div>
          <div v-if="errors.email_encryption" class="error" aria-live="polite">
            {{ errors.email_encryption }}
          </div>
        </b-col>

        <!-- Email Password -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="email_password" class="form-label">Email Password</label>
            <b-form-input id="email_password" v-model="settings.email_password" type="password" placeholder="Enter Email Password"></b-form-input>
          </div>
          <div v-if="errors.email_password" class="error" aria-live="polite">
            {{ errors.email_password }}
          </div>
        </b-col>

        <!-- Email Username -->
        <b-col md="12" lg="6">
          <div class="mb-3">
            <label for="email_username" class="form-label">Email Username</label>
            <b-form-input id="email_username" v-model="settings.email_username" type="text" placeholder="Enter Email Username"></b-form-input>
          </div>
          <div v-if="errors.email_username" class="error" aria-live="polite">
            {{ errors.email_username }}
          </div>
        </b-col>

        <!-- Description -->
        <b-col md="12">
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <b-form-textarea id="description" v-model="settings.description" placeholder="Enter Description" rows="5"></b-form-textarea>
          </div>
          <div v-if="errors.description" class="error" aria-live="polite">
            {{ errors.description }}
          </div>
        </b-col>
      </b-row>

      <!-- Save Button -->
      <div class="text-center mt-5">
        <b-button @click="updateSettings" :disabled="loading" variant="primary">
          <b-spinner v-if="loading" small />
          <span v-else>Save</span>
        </b-button>
      </div>
    </b-card-body>
  </b-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AxiosInstance from '@/api/axios'

const axiosInstance = AxiosInstance()

// Reactive state
const settings = ref({
  app_name: '',
  system_email: '',
  category: '',
  email_scheme: '',
  email_smtps: '',
  email_port: '',
  email_encryption: '',
  email_password: '',
  email_username: '',
  description: ''
})

const errors = ref({})
const loading = ref(false)
const message = ref(null)

// Form fields configuratio
// Fetch settings on page mount
const fetchSettings = async () => {
  loading.value = true
  try {
    const response = await axiosInstance.get('/v1/scheduler/system-settings')

    console.log('API Response:', response.data) // Debugging

    if (response.data?.dataPayload?.data?.length) {
      settings.value = response.data.dataPayload.data[0] // Extract first object
    } else {
      console.warn('Unexpected API response structure:', response.data)
      message.value = {
        type: 'error',
        text: 'Unexpected API response structure. Please contact support.'
      }
    }
  } catch (error) {
    console.error('Failed to fetch settings:', error)
    message.value = {
      type: 'error',
      text: 'Failed to fetch settings. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Update settings
const updateSettings = async () => {
  loading.value = true
  errors.value = {}
  message.value = null

  try {
    const response = await axiosInstance.put('/v1/scheduler/system-settings', settings.value)

    if (response.data?.dataPayload?.data) {
      settings.value = {
        ...settings.value,
        ...response.data.dataPayload.data
      }

      message.value = {
        type: 'success',
        text: 'Settings updated successfully!'
      }
    } else {
      console.warn('Unexpected API response structure:', response.data)
      message.value = {
        type: 'error',
        text: 'Unexpected API response structure. Please contact support.'
      }
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      // Validation errors
      errors.value = error.response.data.errors
    } else {
      console.error('Failed to update settings:', error)
      message.value = {
        type: 'error',
        text: 'Failed to update settings. Please try again.'
      }
    }
  } finally {
    loading.value = false
  }
}

// Fetch settings when the page is mounted
onMounted(fetchSettings)
</script>

<style>
.error {
  color: red;
}
</style>
