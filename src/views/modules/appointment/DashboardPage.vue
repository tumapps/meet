<script setup>
import { ref, onMounted } from 'vue'
import AxiosInstance from '@/api/axios'
import { get } from 'jquery';

const axiosInstance = AxiosInstance();


const timeline = ref([])
//get from api

const upmessage = ref();
const getUpcomingAppointment = async () => {
    try {
        const response = await axiosInstance.get('v1/scheduler/upcoming');
        if(response.data.dataPayload.data.message){
            upmessage.value = response.data.dataPayload.data.message
        }else{
timeline.value = response.data.dataPayload.data.appointments
        }


    } catch (error) {
        console.error(error);
    }
};

const activeAppointments = ref();
const getActiveAppointments = async () => {
    try {
        const response = await axiosInstance.get('v1/scheduler/active');
        activeAppointments.value = response.data.dataPayload.data.appointments

    } catch (error) {
        console.error(error);
    }
};
const allAppointments = ref();
const getAllAppointments = async () => {
    try {
        const response = await axiosInstance.get('v1/scheduler/all');
        allAppointments.value = response.data.dataPayload.data
    } catch (error) {
        console.error(error);
    }
};
const reschedule = ref();
const getReschedule = async () => {
    try {
        const response = await axiosInstance.get('v1/scheduler/reschedule');
        reschedule.value = response.data.dataPayload.data.appointments
    } catch (error) {
        console.error(error);
    }
};
onMounted(async () => {
    //fetch appointments and slots and unavailable slots
    getAllAppointments();
    getUpcomingAppointment();
    getActiveAppointments();
    getReschedule();
});

const username = localStorage.getItem('user.username');
// data table end
</script>
<template>
    <b-row>
        <b-col lg="8" md="8">
            <div class="iq-header-img mt-3">
                <img src="@/assets/images/dashboard/top-header.png" alt="header" class="img-fluid w-100 h-100" />
            </div>
            <b-card>
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="profile-img position-relative me-3 mb-3 mb-lg-0 profile-logo profile-logo1">
                            <img src="@/assets/images/avatars/01.png" alt="User-Profile"
                                class="img-fluid rounded-pill avatar-100" />
                        </div>
                        <div class="d-flex flex-wrap align-items-center">
                            <h4 class="me-2 h4 mb-0">{{ username }}</h4>
                            <span class="text-muted"> - Director</span>
                        </div>
                    </div>
                </div>
            </b-card>
            <b-row class="d-flex flex-nowrap  overflow-auto">
                <b-card class="me-2 statistics-card p-3 shadow rounded">
    <div class="text-center mb-2">
        <h4 class="mb-0 text-nowrap">Upcoming</h4>
    </div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="mb-0">
                <vue3-autocounter ref="counter" :startAmount='0' :endAmount="activeAppointments" :duration="1" :autoinit='true' />
            </h2>
        </div>
        <div class="border bg-info-subtle rounded p-2 d-none d-md-block">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </div>
    </div>
    <div>
        <b-progress :max="100" class="progress bg-info-subtle shadow-none rounded" height="8px">
            <b-progress-bar :value="70" variant="info"></b-progress-bar>
        </b-progress>
    </div>
</b-card>

<b-card class="me-2 statistics-card p-3 shadow rounded">
    <div class="text-center mb-2">
        <h4 class="mb-0 text-nowrap">Reschedule</h4>
    </div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="mb-0">
                <vue3-autocounter ref="counter" :startAmount='0' :endAmount="reschedule" :duration="1" :autoinit='true' />
            </h2>
        </div>
        <div class="border bg-info-subtle rounded p-2 d-none d-md-block">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </div>
    </div>
    <div>
        <b-progress :max="100" class="progress bg-info-subtle shadow-none rounded" height="8px">
            <b-progress-bar :value="70" variant="info"></b-progress-bar>
        </b-progress>
    </div>
</b-card>

<b-card class="me-2 statistics-card p-3 shadow rounded">
    <div class="text-center mb-2">
        <h4 class="mb-0 text-nowrap">All Appointments</h4>
    </div>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="mb-0">
                <vue3-autocounter ref="counter" :startAmount='0' :endAmount="activeAppointments" :duration="1" :autoinit='true' />
            </h2>
        </div>
        <div class="border bg-info-subtle rounded p-2 d-none d-md-block">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </div>
    </div>
    <div>
        <b-progress :max="100" class="progress bg-info-subtle shadow-none rounded" height="8px">
            <b-progress-bar :value="70" variant="info"></b-progress-bar>
        </b-progress>
    </div>
</b-card>


            </b-row>
        </b-col>
        <b-col lg="4" md="4" sm="12" class="upcoming">
            <div class="card-body">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4>Upcoming Appointments</h4>
                        <p class="mb-0 text-muted">
                            <svg class="me-2" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#17904b"
                                    d="M13,20H11V8L5.5,13.5L4.08,12.08L12,4.16L19.92,12.08L18.5,13.5L13,8V20Z"></path>
                            </svg>
                        </p>
                    </div>
                </div>
                <div>
                    <div v-if="upmessage">{{ upmessage }}</div>
                    <div class="d-flex profile-media align-items-top mb-3" v-for="(item, index) in timeline"
                        :key="index">
                        <div class="profile-dots-pills border-primary"></div>
                        <div class="ms-4">
                            <h6 class="mb-1">
                                {{ item.contact_name }} - <span class="text-primary">{{ item.appointment_type }}</span>
                                {{ item.subject }}
                            </h6>
                            <span class="text-muted">{{ item.start_time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </b-col>
    </b-row>

</template>

<style scoped>
h1 {
    font-size: 2rem;
}

.upcoming {
    max-height: 50%;
}

.profile-img img {
    width: 100px;
    height: 100px;
}

.card-header h4 {
    font-size: 1.25rem;
}

.card-body {
    overflow-y: hidden;
    max-height: 50%;
}


.statistics-card {
    max-width: 32%;
}
</style>
