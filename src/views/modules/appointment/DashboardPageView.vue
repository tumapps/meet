<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth.store.js'
import Fullcalendar from '@/components/custom/calendar/FullCalender.vue'

const authStore = useAuthStore()
const username = ref('')
//change the events owner
const dashType = ref('user')
const role = ref('')
role.value = authStore.getRole()

if (role.value === 'secretary') {
  dashType.value = 'secretary'
}

onMounted(() => {
  username.value = localStorage.getItem('user.username')
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
    <b-col lg="12">
      <div class="full-calendar-container h-75">
        <!-- Ensures FullCalendar takes full height -->
        <Fullcalendar :dashType="dashType" />
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
