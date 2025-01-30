<template>
  <b-card>
    <b-card-header>
      <h4 class="card-title">App Settings</h4>
    </b-card-header>
    <b-card-body>
      <b-row>
        <b-col md="6">
          <b-form-group label="App Name" label-for="app_name" invalid-feedback="errors.app_name">
            <b-form-input id="app_name" v-model="settings.app_name" :state="errors.app_name ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group label="System Email" label-for="system_email" invalid-feedback="errors.system_email">
            <b-form-input id="system_email" v-model="settings.system_email" :state="errors.system_email ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group label="Category" label-for="category" invalid-feedback="errors.category">
            <b-form-input id="category" v-model="settings.category" :state="errors.category ? false : null" />
          </b-form-group>
        </b-col>


        <b-col md="6">
          <b-form-group label="Email Scheme" label-for="email_scheme" invalid-feedback="errors.email_scheme">
            <b-form-input id="email_scheme" v-model="settings.email_scheme" :state="errors.email_scheme ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group label="Email SMTPs" label-for="email_smtps" invalid-feedback="errors.email_smtps">
            <b-form-input id="email_smtps" v-model="settings.email_smtps" :state="errors.email_smtps ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group label="Email Port" label-for="email_port" invalid-feedback="errors.email_port">
            <b-form-input id="email_port" v-model="settings.email_port" :state="errors.email_port ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group label="Email Encryption" label-for="email_encryption" invalid-feedback="errors.email_encryption">
            <b-form-input id="email_encryption" v-model="settings.email_encryption" :state="errors.email_encryption ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group label="Email Password" label-for="email_password" invalid-feedback="errors.email_password">
            <b-form-input id="email_password" v-model="settings.email_password" :state="errors.email_password ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group label="Email Username" label-for="email_username" invalid-feedback="errors.email_username">
            <b-form-input id="email_username" v-model="settings.email_username" :state="errors.email_username ? false : null" />
          </b-form-group>
        </b-col>

        <b-col md="12">
          <b-form-group label="Description" label-for="description" invalid-feedback="errors.description">
            <b-form-textarea id="description" v-model="settings.description" :state="errors.description ? false : null" rows="5"/>
          </b-form-group>
        </b-col>

      </b-row>
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
  description: '',
  email_username: ''
})

const errors = ref({})
const loading = ref(false)
const message = ref(null)

// Fetch settings on page mount
const fetchSettings = async () => {
  try {
    const response = await axiosInstance.get('/v1/scheduler/system-settings')
    settings.value = response.data.data // Adjust based on your API response structure
  } catch (error) {
    console.error('Failed to fetch settings:', error)
    message.value = {
      type: 'error',
      text: 'Failed to fetch settings. Please try again.'
    }
  }
}

// Update settings
const updateSettings = async () => {
  loading.value = true
  errors.value = {}
  message.value = null

  try {
    const response = await axiosInstance.put('/v1/scheduler/system-settings', settings.value)

    message.value = {
      type: 'success',
      text: 'Settings updated successfully!'
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
// onMounted(fetchSettings)
</script>

<style>
.settings-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.card {
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.card-header {
  padding: 15px;
  border-bottom: 1px solid #ddd;
}

.card-body {
  padding: 20px;
}

.form-label {
  font-weight: 500;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 14px;
}

.alert {
  padding: 10px;
  border-radius: 4px;
  margin-top: 20px;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
}
</style>
