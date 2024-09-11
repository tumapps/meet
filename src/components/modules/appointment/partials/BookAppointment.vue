<script setup>
import { ref, getCurrentInstance, watch, onMounted } from 'vue'
import createAxiosInstance from '@/api/axios';
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue'; // Import the child component
import FlatPickr from 'vue-flatpickr-component';

const axiosInstance = createAxiosInstance();
const { proxy } = getCurrentInstance();
const errors = ref({
    contact_name: '',
    email_address: '',
    start_time: '',
    mobile_number: '',
    subject: '',
    appointment_type: '',
    description: ''
});

const timeSlots = ref([]);
const apiResponse = ref([]);
const selectedDate = ref(null);

const config = {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'F j, Y',
};


const today = ref(new Date().toLocaleDateString());
console.log(today.value)

const slotsData = ref({
    user_id: "212409004",
    date: '',
});


// Define your form data
const appointmentData = ref({
    user_id: "212409004",
    appointment_date: selectedDate.value,
    start_time: "",
    end_time: "",
    contact_name: "",
    email_address: "",
    mobile_number: "",
    subject: "",
    appointment_type: ""
});
//handle date change 
const handleDateChange = (newValue, oldValue) => {
    getSlots();
};

//watcher for date change
watch(selectedDate, (newValue, oldValue) => {
    handleDateChange(newValue, oldValue);
    console.log('selectedDate:', selectedDate.value);
    slotsData.value.date = selectedDate.value;
    getSlots();
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

// Function to fetch slots from the API
const getSlots = async () => {
    try {
        const response = await axiosInstance.post('/v1/scheduler/get-slots', slotsData.value);
        // Update the `apiResponse` ref with the response data
        apiResponse.value = response.data.dataPayload.data.slots;
        // Set all slots to `selected: false`
        const slotsWithSelected = apiResponse.value.map(slot => ({
            ...slot,
            selected: false
        }));
        // Update `timeSlots`
        timeSlots.value = slotsWithSelected;
    } catch (error) {
        console.error('Error getting slots:', error);
    }
};

// Function to send the POST request
const submitAppointment = async () => {
    try {
        // isLoading.value = true;
        const response = await axiosInstance.post('/v1/scheduler/appointments', appointmentData.value);

        // the sweetalert plugin
        proxy.$showAlert({
            title: 'Booked Successfully',
            text: 'Your appointment has been booked successfully!',
            icon: 'success',
        });
        closeModal();
        // Handle the success response here (e.g., showing a success message)
    } catch (error) {
        proxy.$showToast({
            title: 'Failed',
            text: 'An error occurred while booking the appointment. Please try again!',
            icon: 'error',
        });
        if (error.response && error.response.status === 422 && error.response.data.errorPayload) {
            errors.value = error.response.data.errorPayload.errors;
        }
    }
};

const close = () => {
  // Logic to close modal
};

onMounted(() => {
    slotsData.value.date = today.value;
    getSlots();
});

</script>
<template>
    
    <b-modal ref="appointmentModal" title="Book Appointment" class="modal-fullscreen my-modal" no-close-on-backdrop
        no-close-on-esc size="xl" hide-footer>
        <form id="add-form" action="javascript:void(0)" method="post">
            <div class="d-flex flex-column align-items-start">
                <input type="hidden" name="id" />
                <input type="hidden" name="appointment_type" />
                <div class="w-100" id="v-pills-tabContent">
                    <div class="fade active show m-5" id="-3">
                        <b-row class=" align-items-center form-group">
                            <b-col cols="2">
                                <label for="addcontactname" class="col-form-label">Name</label>
                            </b-col>
                            <b-col cols="10">
                                <input type="text" id="addcontactname" v-model="appointmentData.contact_name"
                                    class="form-control" />
                                <div v-if="errors.contact_name" class="error" aria-live="polite">{{ errors.contact_name
                                    }}</div>
                            </b-col>
                        </b-row>
                        <b-row class="g-3 align-items-center form-group">
                            <b-col cols="2">
                                <label for="addphonenumber" class="col-form-label">Phone</label>
                            </b-col>
                            <b-col cols="10">
                                <input type="text" id="addphonenumber" v-model="appointmentData.mobile_number"
                                    class="form-control" />
                                <div v-if="errors.mobile_number" class="error" aria-live="polite">{{
                                    errors.mobile_number }}</div>
                            </b-col>
                        </b-row>
                        <b-row class="g-3 align-items-center form-group">
                            <b-col cols="2">
                                <label for="addemail" class="col-form-label">Email</label>
                            </b-col>
                            <b-col cols="10">
                                <input type="text" id="addemail" v-model="appointmentData.email_address"
                                    class="form-control" />
                                <div v-if="errors.email_address" class="error" aria-live="polite">{{
                                    errors.email_address }}</div>
                            </b-col>
                        </b-row>

                        <b-row class="g-3 align-items-center form-group">
                            <b-col cols="2">
                                <label for="addsubject" class="col-form-label">RE:</label>
                            </b-col>
                            <b-col cols="10">
                                <input type="text" id="addsubject" v-model="appointmentData.subject"
                                    class="form-control" placeholder="what the meeting is about" />
                                <div v-if="errors.subject" class="error" aria-live="polite">{{ errors.subject }}</div>
                            </b-col>
                        </b-row>
                        <b-row class="g-3 align-items-center form-group">
                            <b-col cols="2">
                                <label for="addappointmenttype" class="col-form-label">
                                    <icon-component type="outlined" icon-name="pencil" :size="24"></icon-component>
                                </label>
                            </b-col>
                            <b-col cols="10">
                                <select v-model="appointmentData.appointment_type" name="service" class="form-select"
                                    id="addappointmenttype">
                                    <option value="">meeting type</option>
                                    <option value="in-person">In-Person</option>
                                    <option value="group">Group</option>
                                    <option value="virtual">Virtual</option>
                                </select>
                                <div v-if="errors.appointment_type" class="error" aria-live="polite">{{
                                    errors.appointment_type }}</div>
                            </b-col>
                        </b-row>
                        <b-row>
                            <div class="container mt-3">
                                <div class="mb-3">
                                    <label for="datePicker" class="form-label">Select a Date</label>
                                    <flat-pickr v-model="selectedDate" class="form-control" :config="config"
                                        id="datePicker" />
                                </div>

                            </div>
                        </b-row>
                        <b-row class="g-3 align-items-center form-group mt-3 mb-5 p-2">
                            <b-col cols="12" class="d-flex justify-content-center align-content-center mb-2">
                                <h3>Time slots</h3>
                                <!-- make a key for the colors of the time slots -->
                            </b-col>

                            <b-row>
                                <b-col md="12" lg="12" sm="12">
                                    <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots"
                                        @selectedSlotsTimes="handleSelectedSlotsTimes" />
                                </b-col>
                                <b-col lg="12">
                                    <div class="d-flex align-items-center justify-content-center form-check">
                                        <input type="checkbox" class="form-check-input" />
                                        <label class="form-check-label ms-2" for="addconfirm2">Available slots</label>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center form-check">
                                        <input type="checkbox" class="form-check-input bg-warning" />
                                        <label class="form-check-label ms-2" for="addconfirm2">Booked slots</label>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center form-check">
                                        <input type="checkbox" class="form-check-input bg-primary" />
                                        <label class="form-check-label ms-2" for="addconfirm2">Selected slots</label>
                                    </div>
                                </b-col>
                            </b-row>
                            <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
                        </b-row>
                    </div>
                </div>
            </div>
        </form>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="close()">Discard
                Changes</button>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" name="save"
                @click="submitAppointment">Save</button>
        </div>
    </b-modal>

</template>
<style scoped>
.error {
    color: red;
    font-size: 0.9em;
}

.dot {
    height: 10px;
    width: 10px;
    border-radius: 50%;
    display: inline-block;
}
</style>
