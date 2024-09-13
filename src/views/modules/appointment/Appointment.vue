<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue';
import AxiosInstance from '@/api/axios';

const axiosInstance = AxiosInstance();
const { proxy } = getCurrentInstance();
// Get route parameter using useRoute
const route = useRoute();
const router = useRouter();
// Access the global utility function using getCurrentInstance
const { appContext } = getCurrentInstance();
const globalUtils = appContext.config.globalProperties.$utils;

const errorDetails = ref({
    contact_name: '',
    email_address: '',
    mobile_number: '',
    appointment_date: '',
    start_time: '',
    end_time: '',
    subject: '',
    appointment_type: '',
    status: '',
    created_at: '',
    updated_at: '',
});

const appointmentDetails = ref({
    appointment_date: '',
    start_time: '',
    end_time: '',
    contact_name: '',
    email_address: '',
    mobile_number: '',
    subject: '',
    appointment_type: '',
    status: '',
    created_at: '',
    updated_at: '',
    statusLabel: '',
    user_id: '212409004',

});

// Method to update time slots
const updateTimeSlots = (updatedSlots) => {
    timeSlots.value = updatedSlots;
};
// Handle selected slots

const handleSelectedSlotsTimes = (selectedTimes) => {
    appointmentData.value.start_time = selectedTimes?.startTime || '';
    appointmentData.value.end_time = selectedTimes?.endTime || '';
};

const getAppointment = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.get(`/v1/scheduler/appointments/${route.params.id}`);

        if (response.data.dataPayload && !response.data.errorPayload) {

            appointmentDetails.value = response.data.dataPayload.data;

            //convert start created at to yyyy-mm-dd
            appointmentDetails.value.created_at = globalUtils.convertToDate(appointmentDetails.value.created_at);

        }

    } catch (error) {
        console.log(error);
        errorDetails.value = error.response.data.errorPayload.errors;
    }
}


// Update appointment
const updateAppointment = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.put(`/v1/scheduler/appointments/${route.params.id}`, appointmentDetails.value);

        if (response.data.dataPayload && !response.data.errorPayload) {
            proxy.$showAlert({
                title: 'Success',
                text: 'Appointment details updated successfully!',
                icon: 'success',
            });
            getAppointment();
        }

    } catch (error) {
        // proxy.$showAlert({
        //     title: 'Failed',
        //     text: 'It seems there was an error updating your appointment details. Please try again!',
        //     icon: 'error',
        // });
        errorDetails.value = error.response.data.errorPayload.errors;
    }
}

const timeSlots = ref([]);
const responseData = {
    "date": "2024-09-09",
    "slots": [
        { "startTime": "08:00", "endTime": "09:00" },
        { "startTime": "09:00", "endTime": "10:00" },
        { "startTime": "10:00", "endTime": "11:00" },
        { "startTime": "11:00", "endTime": "12:00" },
        { "startTime": "12:00", "endTime": "13:00" },
        { "startTime": "13:00", "endTime": "14:00" },
        { "startTime": "14:00", "endTime": "15:00" },
        { "startTime": "15:00", "endTime": "16:00" },
        { "startTime": "16:00", "endTime": "17:00" },
        { "startTime": "17:00", "endTime": "18:00" },
        { "startTime": "18:00", "endTime": "19:00" },
        { "startTime": "19:00", "endTime": "20:00" },
        { "startTime": "20:00", "endTime": "21:00" },
        { "startTime": "21:00", "endTime": "22:00" },
        { "startTime": "22:00", "endTime": "23:00" },
        { "startTime": "23:00", "endTime": "00:00" }
    ],
    "date": "2024-09-10",
    "slots": [
        { "startTime": "08:00", "endTime": "09:00" },
        { "startTime": "09:00", "endTime": "10:00" },
        { "startTime": "10:00", "endTime": "11:00" },
        { "startTime": "11:00", "endTime": "12:00" },
        { "startTime": "12:00", "endTime": "13:00" },
        { "startTime": "13:00", "endTime": "14:00" },
        { "startTime": "14:00", "endTime": "15:00" },
        { "startTime": "15:00", "endTime": "16:00" },
        { "startTime": "16:00", "endTime": "17:00" },
        { "startTime": "17:00", "endTime": "18:00" },
        { "startTime": "18:00", "endTime": "19:00" },
        { "startTime": "19:00", "endTime": "20:00" },
        { "startTime": "20:00", "endTime": "21:00" },
        { "startTime": "21:00", "endTime": "22:00" },
        { "startTime": "22:00", "endTime": "23:00" },
        { "startTime": "23:00", "endTime": "00:00" }
    ]
};

timeSlots.value = responseData.slots;

const dateSlots = ref([responseData]);


const suggestSlots = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.get(`/v1/scheduler/appointments/suggest-available-slots`);

        if (response.data.dataPayload && !response.data.errorPayload) {
        }

    } catch (error) {
        console.log(error);
        // errorDetails.value = error.response.data.errorPayload.errors;
    }
}

const cancelAppointment = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.delete(`/v1/scheduler/appointments/${route.params.id}/cancel`);

        if (response.data.dataPayload && !response.data.errorPayload) {
            proxy.$showAlert({
                title: 'Success',
                text: 'Appointment cancelled successfully!',
                icon: 'success',
            });
            getAppointment();
        }

    } catch (error) {
        proxy.$showAlert({
            title: 'Failed',
            text: 'It seems there was an error cancelling your appointment. Please try again!',
            icon: 'error',
        });
        errorDetails.value = error.response.data.errorPayload.errors;
    }
}

const restoreAppointment = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.put(`/v1/scheduler/appointments/${route.params.id}/restore`);

        if (response.data.dataPayload && !response.data.errorPayload) {
            proxy.$showAlert({
                title: 'Success',
                text: 'Appointment restored successfully!',
                icon: 'success',
            });
            getAppointment();
        }

    } catch (error) {
        proxy.$showAlert({
            title: 'Failed',
            text: 'It seems there was an error restoring your appointment. Please try again!',
            icon: 'error',
        });
        errorDetails.value = error.response.data.errorPayload.errors;
    }
}

const getBadgeVariant = (statusLabel) => {
    switch (statusLabel) {
        case 'Rescheduled':
            return 'warning';
        case 'Active':
            return 'success';
        case 'Cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
};

const appointmentType = ref([]);
const getAppointmentType =() =>{
    try{
        const response=axiosInstance.get('/v1/scheduler/appointmentTypes')
        appointmentType.value = response.data.dataPayload.data;

    }catch(error){

    }
}


onMounted(() => {
    getAppointment();

});

//go back to prvoius page
const goBack = () => {
    router.go(-1);
}

</script>
<template>
    <b-row class="justify-content-center w-100">
        <b-col lg="9" sm="12" class="">
            <b-card no-body class="m-2 w-100">
                <div class="d-flex flex-row-reverse">
                    <b-button variant="close" class="btn-xl m-5" @click="goBack"></b-button>
                </div>
                <b-card-header header-class="d-flex justify-content-between">
                    <div class="header-title">
                        <b-card-title>Input</b-card-title>
                    </div>
                </b-card-header>
                <b-card-body>
                    <b-form>
                        <b-form-group>

                            <b-row>

                                <b-col lg="12" md="12">
                                    <label for="input-107" class="form-label">Date </label>
                                    <b-form-input v-model="appointmentDetails.appointment_date" id="input-107"
                                        type="date"></b-form-input>
                                    <div v-if="errorDetails.appointment_date" class="error">
                                        {{ errorDetails.appointment_date }} </div>
                                </b-col>

                                <b-col lg="6" md="6">
                                    <label for="input-107" class="form-label">Start Time </label>
                                    <b-form-input v-model="appointmentDetails.start_time" id="input-107"></b-form-input>
                                    <div v-if="errorDetails.start_time" class="error">
                                        {{ errorDetails.start_time }} </div>
                                </b-col>
                                <b-col lg="6" md="6">
                                    <label for="input-107" class="form-label">End Time </label>
                                    <b-form-input v-model="appointmentDetails.end_time" id="input-107"></b-form-input>
                                    <div v-if="errorDetails.end_time" class="error">
                                        {{ errorDetails.end_time }} </div>
                                </b-col>

                                <b-col lg="12" md="12">
                                    <label for="input-107" class="form-label">Subject </label>
                                    <b-form-input v-model="appointmentDetails.subject" id="input-107"></b-form-input>
                                    <div v-if="errorDetails.subject" class="error">
                                        {{ errorDetails.subject }} </div>
                                </b-col>
                                <b-col lg="12" md="12">
                                    <label for="input-107" class="form-label">Appointment Type </label>
                                    <b-form-input v-model="appointmentDetails.appointment_type"
                                        id="input-107"></b-form-input>
                                    <div v-if="errorDetails.appointment_type" class="error">
                                        {{ errorDetails.appointment_type }} </div>
                                </b-col>

                                <b-col lg="12" md="12">
                                    <label for="input-107" class="form-label">Booked on </label>
                                    <b-form-input v-model="appointmentDetails.created_at" id="input-107"></b-form-input>
                                    <div v-if="errorDetails.created_at" class="error">
                                        {{ errorDetails.created_at }} </div>
                                </b-col>

                                <b-col lg="8" md="8">
                                    <label for="status-badge" class="form-label">Status</label>
                                    <!-- Status as a Badge -->
                                    <div id="status-badge" class="d-flex align-items-center">
                                        <b-badge :variant="getBadgeVariant(appointmentDetails.statusLabel)"
                                            class="me-3">
                                            {{ appointmentDetails.statusLabel }}
                                        </b-badge>
                                        <!-- Show Cancel button if status is greater than 0 -->
                                        <b-button v-if="appointmentDetails.status === 1" variant="danger"
                                            @click="cancelAppointment">
                                            Cancel
                                        </b-button>
                                        <b-button v-else="appointmentDetails.status === 2" variant="success"
                                            @click="restoreAppointment">Restore Appointment</b-button>
                                        <b-button v-else variant="success" @click="suggestSlots">Suggest Open
                                            slots</b-button>
                                    </div>
                                </b-col>
                                <b-col lg="5">
                                    <div v-if="appointmentDetails.status === 3">
                                        <div v-for="(dateEntry, index) in dateSlots" :key="index">
                                            <h3>{{ dateEntry.date }}</h3>
                                            <div>
                                                <TimeSlotComponent :timeSlots="timeSlots"
                                                    @update:timeSlots="updateTimeSlots"
                                                    @selectedSlotsTimes="handleSelectedSlotsTimes" />
                                            </div>
                                        </div>
                                    </div>
                                </b-col>
                                <b-row class="m-5">
                                    <b-col>
                                        <div class="d-flex justify-content-center">
                                            <b-button variant="primary" class="me-1"
                                                @click="updateAppointment">Update</b-button>
                                        </div>
                                    </b-col>
                                </b-row>
                            </b-row>
                        </b-form-group>
                    </b-form>
                </b-card-body>
            </b-card>
        </b-col>
    </b-row>
</template>
<style scoped></style>