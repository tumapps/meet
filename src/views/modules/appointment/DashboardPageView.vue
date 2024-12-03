<script setup>
import { ref, onMounted, watch, getCurrentInstance } from 'vue'
import AxiosInstance from '@/api/axios'
import { useAuthStore } from '@/store/auth.store.js'
import Fullcalendar from '@/components/custom/calendar/FullCalender.vue'

const { proxy } = getCurrentInstance()
const axiosInstance = AxiosInstance()

const authStore = useAuthStore()
const username = ref('')
//change the events owner
const role = ref('')
role.value = authStore.getRole()
const userId = ref('')
// const searchQuery = ref('');

const selectedUser_id = ref(null) // To store the corresponding user_id

const UsersOptions = ref([])
const selectedUsername = ref('') // To hold the selected username

//filter appointments by user
const getusers_booked = async () => {
  try {
    const response = await axiosInstance.get('/v1/auth/users')
    UsersOptions.value = response.data.dataPayload.data.profiles
    // console.log("Users data:", UsersOptions.value);
    // console.log("Users data:", UsersOptions.value);
  } catch (error) {
    proxy.$showToast({
      title: 'An error occurred',
      text: 'Oops! An error has occurred',
      icon: 'error'
    })
  }
}

// const performSearch = async () => {
//     try {
//         const response = await axiosInstance.get(`v1/scheduler/appointments?_search=${searchQuery.value}`);
//         tableData.value = response.data.dataPayload.data;
//     } catch (error) {
//         // console.error(error);
//         const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An unknown error occurred';

//         proxy.$showToast({
//             title: 'An error occurred',
//             text: errorMessage,
//             icon: 'error',
//         });
//     }
// };

//watch for selected user name
watch(selectedUsername, (newUsername) => {
  const selectedUser = UsersOptions.value.find((user) => user.first_name === newUsername)
  selectedUser_id.value = selectedUser ? selectedUser.user_id : null
  userId.value = selectedUser_id.value ? selectedUser_id.value : authStore.getUserId()
  // console.log("Selected User ID:", selectedUser_id.value);
})

onMounted(() => {
  username.value = localStorage.getItem('user.username')
  getusers_booked()
})
// data table end
</script>
<template>
  <b-row>
    <b-col lg="12" md="12">
      <div class="headerimage">
        <!-- <img src="@/assets/images/tum.jpg" alt="header" class="w-100 h-25" /> -->
      </div>
      <b-card>
        <div class="d-flex flex-wrap align-items-center justify-content-between">
          <div class="d-flex flex-wrap align-items-center">
            <div class="profile-img position-relative me-3 mb-3 mb-lg-0 profile-logo profile-logo1">
              <img src="@/assets/images/avatars/01.png" alt="User-Profile" class="img-fluid rounded-pill avatar-100" />
            </div>
            <div class="d-flex flex-wrap align-items-center">
              <h4 class="me-2 h4 mb-0">{{ username }}</h4>
              <!-- <span class="text-muted"> - Director</span> -->
            </div>
          </div>
        </div>
      </b-card>
    </b-col>
  </b-row>
  <b-row>
    <b-row class="mb-3 align-items-center">
      <!-- Left Column (Dropdown) -->
      <b-col lg="2" class="d-flex justify-content-lg-end mb-3 mb-lg-0">
        <div v-if="role === 'su'" class="w-100 w-lg-auto">
          <div class="dropdown w-100 w-lg-auto" style="float: right">
            <select v-model="selectedUsername" name="service" class="form-select form-select-sm" id="addappointmenttype">
              <option value="">All</option>
              <option v-for="user in UsersOptions" :key="user.user_id" :value="user.first_name">
                {{ user.first_name }}
              </option>
            </select>
          </div>
        </div>
      </b-col>

      <!-- Right Column (Badges) -->
      <b-col lg="10" class="d-flex justify-content-end align-items-end">
        <span class="badge bg-success me-2">Active</span>
        <span class="badge bg-warning me-2">Canceled</span>
        <span class="badge bg-danger me-2">Deleted</span>
        <!-- <span class="badge bg-warning me-2">Rescheduled</span> -->
      </b-col>
    </b-row>

    <b-col lg="12">
      <div class="full-calendar-container h-75">
        <!-- Ensures FullCalendar takes full height -->
        <Fullcalendar />
      </div>
    </b-col>
  </b-row>
</template>

<style scoped>
.full-calendar-container {
  width: 100%;
}

.full-calendar-container .fc {
  width: 100%;
  height: 50%;
  /* Ensures calendar fills the container */
}

h1 {
  font-size: 2rem;
}

.profile-img img {
  width: 100px;
  height: 100px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.headerimage {
  height: 20vh;
  background-image: url(@/assets/images/tum.jpg);
}

.headerimage img {
  height: 100%;
  width: 100%;
  object-fit: cover;
}

b-card {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}
</style>
