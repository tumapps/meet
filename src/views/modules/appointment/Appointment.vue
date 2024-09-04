<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import { useRoute } from 'vue-router';
import AxiosInstance from '@/api/axios';
// import globalUtils from '../../../utilities/globalUtils';

const axiosInstance = AxiosInstance();
// Get route parameter using useRoute
const route = useRoute();
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

});

const updatedAppointmentDetails = ref({
    appointment_date: '',
    start_time: '',
    end_time: '',
    contact_name: '',
    email_address: '',
    mobile_number: '',
    subject: '',
    appointment_type: '',
    user_id: '212409004',


});

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

        const response = await axiosInstance.put(`/v1/scheduler/appointments/${route.params.id}`, updatedAppointmentDetails.value);

        if (response.data.dataPayload && !response.data.errorPayload) {
            globalUtils.showSuccessToast('Appointment updated successfully');
        }

    } catch (error) {
        console.log(error);
        errorDetails.value = error.response.data.errorPayload.errors;
    }
}

onMounted(() => {
    getAppointment();
    

});


</script>
<template>
    <b-row class="justify-content-center w-100">
        <b-col lg="9" sm="12" class="">
            <b-card no-body class="m-2 w-100">
                <b-card-header header-class="d-flex justify-content-between">
                    <div class="header-title">
                        <b-card-title>Input</b-card-title>
                    </div>
                </b-card-header>
                <b-card-body>
                    <b-form>
                        <b-form-group>
                            <b-card class=" pb-3">
                                <b-row class="gap-5">
                                    <b-col lg="4" md="4" sm="12"> <label class="form-label">First Name </label>
                                        <b-form-input v-model="appointmentDetails.contact_name" id="input-101"
                                            type="text" placeholder="first name"></b-form-input>
                                    </b-col>
                                    <div v-if="errorDetails.contact_name" class="error">
                                        {{ errorDetails.contact_name }} </div>
                                </b-row>
                                <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="3" md="4">
                                        <label for="input-104" class="form-label">Mobile number</label>
                                        <b-form-input v-model="appointmentDetails.mobile_number" id="input-104"
                                            type="tel" placeholder="+254 555-555-555"></b-form-input>
                                        <div v-if="errorDetails.mobile_number" class="error">
                                            {{ errorDetails.mobile_number }} </div>
                                    </b-col>
                                    <b-col lg="3" md="4">
                                        <label for="input-102" class="form-label">Email</label>
                                        <b-form-input v-model="appointmentDetails.email_address" id="input-102"
                                            type="email" placeholder="markjhon@gmail.com"></b-form-input>
                                        <div v-if="errorDetails.email_address" class="error">
                                            {{ errorDetails.email_address }} </div>
                                    </b-col>
                                </b-row>
                            </b-card>
                            <b-card class="pb-3 mt-5">
                                <b-row class="gap-5">

                                    <b-col lg="6" md="4">
                                        <label for="input-107" class="form-label">Date </label>
                                        <b-form-input v-model="appointmentDetails.appointment_date" id="input-107"
                                            type="date"></b-form-input>
                                        <div v-if="errorDetails.appointment_date" class="error">
                                            {{ errorDetails.appointment_date }} </div>
                                    </b-col>
                                    </b-row>    
                                    <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="3" md="6">
                                        <label for="input-107" class="form-label">Start Time </label>
                                        <b-form-input v-model="appointmentDetails.start_time" id="input-107" 
                                        ></b-form-input>
                                        <div v-if="errorDetails.start_time" class="error">
                                            {{ errorDetails.start_time }} </div>
                                    </b-col>
                                    <b-col lg="3" md="6">
                                        <label for="input-107" class="form-label">End Time </label>
                                        <b-form-input v-model="appointmentDetails.end_time" id="input-107" 
                                            ></b-form-input>
                                        <div v-if="errorDetails.end_time" class="error">
                                            {{ errorDetails.end_time }} </div>
                                    </b-col>
                                </b-row>
                                <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="4" md="4">
                                        <label for="input-107" class="form-label">Subject </label>
                                        <b-form-input v-model="appointmentDetails.subject"
                                            id="input-107"></b-form-input>
                                        <div v-if="errorDetails.subject" class="error">
                                            {{ errorDetails.subject }} </div>
                                    </b-col>

                                    <b-col lg="4" md="4">
                                        <label for="input-107" class="form-label">Appointment Type </label>
                                        <b-form-input v-model="appointmentDetails.appointment_type"
                                            id="input-107"></b-form-input>
                                        <div v-if="errorDetails.appointment_type" class="error">
                                            {{ errorDetails.appointment_type }} </div>
                                    </b-col>

                                </b-row>

                                <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="4" md="4">
                                        <label for="input-107" class="form-label">Booked on </label>
                                        <b-form-input v-model="appointmentDetails.created_at"
                                            id="input-107"></b-form-input>
                                        <div v-if="errorDetails.created_at" class="error">
                                            {{ errorDetails.created_at }} </div>
                                    </b-col>
                                    <b-col lg="8" md="4">
                                    </b-col>
                                    <b-col lg="12" v-if="1 > 0" class="d-flex justify-content-end">
                                        <b-button variant="warning" class="me-1">Cancel</b-button>
                                    </b-col>
                                    <b-col lg="12" v-if="1 > 1" class="d-flex justify-content-end ">
                                        <b-button variant="danger" class="button">Cancel</b-button>
                                    </b-col>
                                </b-row>
                            </b-card>
                        </b-form-group>
                        <div class="d-flex justify-content-center">
                            <b-button variant="primary" class="me-1" @click="updateAppointment">Update</b-button>
                            <b-button variant="danger">Cancel</b-button>
                        </div>
                    </b-form>
                </b-card-body>
            </b-card>
        </b-col><template>
    <b-row class="justify-content-center w-100">
        <b-col lg="9" sm="12" class="">
            <b-card no-body class="m-2 w-100">
                <b-card-header header-class="d-flex justify-content-between">
                    <div class="header-title">
                        <b-card-title>Input</b-card-title>
                    </div>
                </b-card-header>
                <b-card-body>
                    <b-form>
                        <b-form-group>
                            <b-card class=" pb-3">
                                <b-row class="gap-5">
                                    <b-col lg="4" md="4" sm="12"> <label class="form-label">First Name </label>
                                        <b-form-input v-model="appointmentDetails.contact_name" id="input-101"
                                            type="text" placeholder="first name"></b-form-input>
                                    </b-col>
                                    <div v-if="errorDetails.contact_name" class="error">
                                        {{ errorDetails.contact_name }} </div>
                                </b-row>
                                <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="3" md="4">
                                        <label for="input-104" class="form-label">Mobile number</label>
                                        <b-form-input v-model="appointmentDetails.mobile_number" id="input-104"
                                            type="tel" placeholder="+254 555-555-555"></b-form-input>
                                        <div v-if="errorDetails.mobile_number" class="error">
                                            {{ errorDetails.mobile_number }} </div>
                                    </b-col>
                                    <b-col lg="3" md="4">
                                        <label for="input-102" class="form-label">Email</label>
                                        <b-form-input v-model="appointmentDetails.email_address" id="input-102"
                                            type="email" placeholder="markjhon@gmail.com"></b-form-input>
                                        <div v-if="errorDetails.email_address" class="error">
                                            {{ errorDetails.email_address }} </div>
                                    </b-col>
                                </b-row>
                            </b-card>
                            <b-card class="pb-3 mt-5">
                                <b-row class="gap-5">

                                    <b-col lg="6" md="4">
                                        <label for="input-107" class="form-label">Date </label>
                                        <b-form-input v-model="appointmentDetails.appointment_date" id="input-107"
                                            type="date"></b-form-input>
                                        <div v-if="errorDetails.appointment_date" class="error">
                                            {{ errorDetails.appointment_date }} </div>
                                    </b-col>
                                    </b-row>    
                                    <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="3" md="6">
                                        <label for="input-107" class="form-label">Start Time </label>
                                        <b-form-input v-model="appointmentDetails.start_time" id="input-107" 
                                        ></b-form-input>
                                        <div v-if="errorDetails.start_time" class="error">
                                            {{ errorDetails.start_time }} </div>
                                    </b-col>
                                    <b-col lg="3" md="6">
                                        <label for="input-107" class="form-label">End Time </label>
                                        <b-form-input v-model="appointmentDetails.end_time" id="input-107" 
                                            ></b-form-input>
                                        <div v-if="errorDetails.end_time" class="error">
                                            {{ errorDetails.end_time }} </div>
                                    </b-col>
                                </b-row>
                                <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="4" md="4">
                                        <label for="input-107" class="form-label">Subject </label>
                                        <b-form-input v-model="appointmentDetails.subject"
                                            id="input-107"></b-form-input>
                                        <div v-if="errorDetails.subject" class="error">
                                            {{ errorDetails.subject }} </div>
                                    </b-col>

                                    <b-col lg="4" md="4">
                                        <label for="input-107" class="form-label">Appointment Type </label>
                                        <b-form-input v-model="appointmentDetails.appointment_type"
                                            id="input-107"></b-form-input>
                                        <div v-if="errorDetails.appointment_type" class="error">
                                            {{ errorDetails.appointment_type }} </div>
                                    </b-col>

                                </b-row>

                                <b-row class="gap-5 mt-5 pt-3">
                                    <b-col lg="4" md="4">
                                        <label for="input-107" class="form-label">Booked on </label>
                                        <b-form-input v-model="appointmentDetails.created_at"
                                            id="input-107"></b-form-input>
                                        <div v-if="errorDetails.created_at" class="error">
                                            {{ errorDetails.created_at }} </div>
                                    </b-col>
                                    <b-col lg="8" md="4">
                                    </b-col>
                                    <b-col lg="12" v-if="1 > 0" class="d-flex justify-content-end">
                                        <b-button variant="warning" class="me-1">Cancel</b-button>
                                    </b-col>
                                    <b-col lg="12" v-if="1 > 1" class="d-flex justify-content-end ">
                                        <b-button variant="danger" class="button">Cancel</b-button>
                                    </b-col>
                                </b-row>
                            </b-card>
                        </b-form-group>
                        <div class="d-flex justify-content-center">
                            <b-button variant="primary" class="me-1">Submit</b-button>
                            <b-button variant="danger">Cancel</b-button>
                        </div>
                    </b-form>
                </b-card-body>
            </b-card>
        </b-col>
    </b-row>
</template>
    </b-row>
</template>

<style scoped></style>