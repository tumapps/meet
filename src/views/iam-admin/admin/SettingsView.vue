<template>
  <div class="settings-page container mt-5">
    <h1 class="mb-4">App Settings</h1>

    <!-- Card for General Settings -->
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">General Settings</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- App Name -->
          <div class="col-md-6 mb-3">
            <label for="app_name" class="form-label">App Name</label>
            <input type="text" id="app_name" v-model="settings.app_name" class="form-control" :class="{ 'is-invalid': errors.app_name }" />
            <div v-if="errors.app_name" class="invalid-feedback">
              {{ errors.app_name[0] }}
            </div>
          </div>

          <!-- System Email -->
          <div class="col-md-6 mb-3">
            <label for="system_email" class="form-label">System Email</label>
            <input type="email" id="system_email" v-model="settings.system_email" class="form-control" :class="{ 'is-invalid': errors.system_email }" />
            <div v-if="errors.system_email" class="invalid-feedback">
              {{ errors.system_email[0] }}
            </div>
          </div>

          <!-- Category -->
          <div class="col-md-6 mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" id="category" v-model="settings.category" class="form-control" :class="{ 'is-invalid': errors.category }" />
            <div v-if="errors.category" class="invalid-feedback">
              {{ errors.category[0] }}
            </div>
          </div>

          <!-- Description -->
          <div class="col-md-12 mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" v-model="settings.description" class="form-control" :class="{ 'is-invalid': errors.description }" rows="3"></textarea>
            <div v-if="errors.description" class="invalid-feedback">
              {{ errors.description[0] }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card for Email Settings -->
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">Email Settings</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Email Scheme -->
          <div class="col-md-4 mb-3">
            <label for="email_scheme" class="form-label">Email Scheme</label>
            <input type="text" id="email_scheme" v-model="settings.email_scheme" class="form-control" :class="{ 'is-invalid': errors.email_scheme }" />
            <div v-if="errors.email_scheme" class="invalid-feedback">
              {{ errors.email_scheme[0] }}
            </div>
          </div>

          <!-- Email SMTP Server -->
          <div class="col-md-4 mb-3">
            <label for="email_smtps" class="form-label">Email SMTP Server</label>
            <input type="text" id="email_smtps" v-model="settings.email_smtps" class="form-control" :class="{ 'is-invalid': errors.email_smtps }" />
            <div v-if="errors.email_smtps" class="invalid-feedback">
              {{ errors.email_smtps[0] }}
            </div>
          </div>

          <!-- Email Port -->
          <div class="col-md-4 mb-3">
            <label for="email_port" class="form-label">Email Port</label>
            <input type="number" id="email_port" v-model="settings.email_port" class="form-control" :class="{ 'is-invalid': errors.email_port }" />
            <div v-if="errors.email_port" class="invalid-feedback">
              {{ errors.email_port[0] }}
            </div>
          </div>

          <!-- Email Encryption -->
          <div class="col-md-4 mb-3">
            <label for="email_encryption" class="form-label">Email Encryption</label>
            <input type="text" id="email_encryption" v-model="settings.email_encryption" class="form-control" :class="{ 'is-invalid': errors.email_encryption }" />
            <div v-if="errors.email_encryption" class="invalid-feedback">
              {{ errors.email_encryption[0] }}
            </div>
          </div>

          <!-- Email Username -->
          <div class="col-md-4 mb-3">
            <label for="email_username" class="form-label">Email Username</label>
            <input type="text" id="email_username" v-model="settings.email_username" class="form-control" :class="{ 'is-invalid': errors.email_username }" />
            <div v-if="errors.email_username" class="invalid-feedback">
              {{ errors.email_username[0] }}
            </div>
          </div>

          <!-- Email Password -->
          <div class="col-md-4 mb-3">
            <label for="email_password" class="form-label">Email Password</label>
            <input type="password" id="email_password" v-model="settings.email_password" class="form-control" :class="{ 'is-invalid': errors.email_password }" />
            <div v-if="errors.email_password" class="invalid-feedback">
              {{ errors.email_password[0] }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="text-end">
      <button type="submit" class="btn btn-primary" @click="updateSettings" :disabled="loading">
        {{ loading ? 'Updating...' : 'Update Settings' }}
      </button>
    </div>

    <!-- Success/Error Message -->
    <div v-if="message" :class="['alert', message.type === 'success' ? 'alert-success' : 'alert-danger']" class="mt-4">
      {{ message.text }}
    </div>
  </div>
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
onMounted(fetchSettings)
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
