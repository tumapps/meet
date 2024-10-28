<script setup>
import { ref, getCurrentInstance, watch, onMounted } from 'vue'
import createAxiosInstance from '@/api/axios';
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue'; // Import the child component
import FlatPickr from 'vue-flatpickr-component';
import IconComponent from '@/components/icons/IconComponent.vue'
import { useAuthStore } from '@/store/auth.store.js';

const authStore = useAuthStore();
const axiosInstance = createAxiosInstance();
const { proxy } = getCurrentInstance();
//get can be booked status from store
const CBB = ref('')
CBB.value = authStore.getCanBeBooked();
const userId = ref('');


const errors = ref({
    contact_name: '',
    email_address: '',
    start_time: '',
    mobile_number: '',
    subject: '',
    appointment_type: '',
    description: ''
});

const props = defineProps({
    selectedDate: String
});

const timeSlots = ref([]);
const apiResponse = ref([]);
const selectedDate = ref(null);

const config = {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'F j, Y',
    minDate: 'today',
};


const today = ref(new Date().toLocaleDateString());
// console.log(today.value)
const selectedUser_id = ref(null); // To store the corresponding user_id
const selectedPriority = ref(null); // To store the corresponding priority

//get user id from session storage
userId.value = authStore.getUserId();


const slotsData = ref({
    user_id: userId,
    date: '',
});


// Define your form data
const appointmentData = ref({
    user_id: userId,
    appointment_date: selectedDate.value,
    start_time: "",
    end_time: "",
    contact_name: "",
    email_address: "",
    mobile_number: "",
    subject: "",
    description: "",
    appointment_type: "",
    priority: "",
});
//handle date change 
const handleDateChange = (newValue, oldValue) => {
    appointmentData.value.appointment_date = newValue;
    slotsData.value.date = newValue;
    // console.log('date changed:', newValue);
    getSlots();
};

//watcher for date change
watch(selectedDate, (newValue, oldValue) => {
    // console.log("new date??", newValue)

    handleDateChange(newValue, oldValue);
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
        // console.log("user_id", userId.value)
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
        // console.error('Error getting slots:', error);
        proxy.$showToast({
            title: 'An error occurred ',
            text: 'Ooops! an error has occured ',
            icon: 'error',
        });

    }
};

// Function to send the POST request
const submitAppointment = async () => {
    //reset errors
    errors.value = {};
    try {
        const response = await axiosInstance.post('/v1/scheduler/appointments', appointmentData.value);
        proxy.$showAlert({
            title: 'Success',
            text: 'Appointment booked successfully',
            icon: 'success',
        });
        // Reset the form
        appointmentData.value = {};
    } catch (error) {

        if (error.response && error.response.status === 422 && error.response.data.errorPayload) {
            errors.value = error.response.data.errorPayload.errors;

        }
    }
};

const appointmentTypeOptions = ref([]);
const getAppointmentType = async () => {
    try {
        const response = await axiosInstance.get('/v1/scheduler/types');
        appointmentTypeOptions.value = response.data.dataPayload.data.types;
    } catch (error) {
        // console.error('Error fetching appointment types:', error);
        proxy.$showToast({
            title: 'An error occurred ',
            text: 'Ooops! an error has occured',
            icon: 'error',
        });
    }
};


const UsersOptions = ref([]);
const PriorityOptions = ref([]);
const selectedUsername = ref(''); // To hold the selected username

const getusers_booked = async () => {
    try {
        const response = await axiosInstance.get('/v1/auth/users');
        UsersOptions.value = response.data.dataPayload.data.profiles;
        // console.log("Users data:", UsersOptions.value);
    } catch (error) {
        proxy.$showToast({
            title: 'An error occurred',
            text: 'Oops! An error has occurred',
            icon: 'error',
        });
    }
};

const getPriority = async () => {
    try {
        const response = await axiosInstance.get('/v1/scheduler/priorities');
        PriorityOptions.value = response.data.dataPayload.data;
        // console.log("Priority data:", PriorityOptions.value);
    } catch (error) {
        proxy.$showToast({
            title: 'An error occurred',
            text: 'Oops! An error has occurred',
            icon: 'error',
        });
    }
};

// Watch for changes in the selected username to update selectedUser_id
watch(selectedUsername, (newUsername) => {
    const selectedUser = UsersOptions.value.find(user => user.first_name === newUsername);
    selectedUser_id.value = selectedUser ? selectedUser.user_id : null;
    userId.value = selectedUser_id.value ? selectedUser_id.value : authStore.getUserId();
});

watch(selectedPriority, (newPriority) => {
    const selectedPriority = PriorityOptions.value.find(priority => priority.code === newPriority);
    appointmentData.value.priority = selectedPriority ? selectedPriority.code : null;
});


const close = () => {
    //close the modal 
};

onMounted(() => {
    slotsData.value.date = today.value;
    // getSlots();
    getAppointmentType();
    getAppointmentType();
    getusers_booked();
    getPriority();
});

</script>
<template>

    <b-modal ref="appointmentModal" title="Book Appointment" class="modal-fullscreen my-modal rounded-modal"
        no-close-on-backdrop no-close-on-esc size="xl" hide-footer>
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
                        <b-row class="align-items-center form-group">
                            <b-col cols="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <label for="addphonenumber" class="col-form-label">Phone</label>
                            </b-col>
                            <b-col cols="10" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <input type="text" id="addphonenumber" v-model="appointmentData.mobile_number"
                                    class="form-control" />
                                <div v-if="errors.mobile_number" class="error" aria-live="polite">{{
                                    errors.mobile_number }}</div>
                            </b-col>

                            <b-col cols="2" lg="1">
                                <label for="addemail" class="col-form-label">Email</label>
                            </b-col>
                            <b-col cols="10" lg="5">
                                <input type="text" id="addemail" v-model="appointmentData.email_address"
                                    class="form-control" />
                                <div v-if="errors.email_address" class="error" aria-live="polite">{{
                                    errors.email_address }}</div>
                            </b-col>
                        </b-row>

                        <b-row v-if="CBB === 0" class="align-items-center form-group">
                            <b-col cols="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <label for="addappointmenttype" class="col-form-label">
                                    <icon-component type="outlined" icon-name="user" :size="24"></icon-component>
                                </label>
                            </b-col>
                            <b-col cols="10" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <!-- Bind the select dropdown to selectedUsername -->
                                <select v-model="selectedUsername" name="service" class="form-select"
                                    id="addappointmenttype">
                                    <option value="">Recipient</option>
                                    <option v-for="user in UsersOptions" :key="user.user_id" :value="user.first_name">
                                        {{ user.first_name }}
                                    </option>
                                </select>
                                <div v-if="errors.user_id" class="error" aria-live="polite">
                                    {{ errors.user_id }}
                                </div>
                            </b-col>
                        </b-row>


                        <b-row class="align-items-center form-group">
                            <b-col cols="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <label for="addappointmenttype" class="col-form-label">
                                    Priority
                                </label>
                            </b-col>
                            <b-col cols="10" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <!-- Bind the select dropdown to selectedPriority -->
                                <select v-model="selectedPriority" name="priority" class="form-select"
                                    id="addappointmenttype">
                                    <option value="">Select Priority</option>
                                    <option v-for="priority in PriorityOptions" :key="priority.code"
                                        :value="priority.code">
                                        {{ priority.label }}
                                    </option>
                                </select>
                                <div v-if="errors.priority" class="error" aria-live="polite">
                                    {{ errors.priority }}
                                </div>
                            </b-col>

                            <b-col cols="2" lg="1" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <label for="addsubject" class="col-form-label">RE:</label>
                            </b-col>
                            <b-col cols="10" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <input type="text" id="addsubject" v-model="appointmentData.subject"
                                    class="form-control" placeholder="what the meeting is about" />
                                <div v-if="errors.subject" class="error" aria-live="polite">{{ errors.subject }}</div>
                            </b-col>
                        </b-row>
                        <b-row class="g-3 align-items-center form-group">
                            <b-col cols="2">
                                <label for="addphonenumber" class="col-form-label">Notes</label>
                            </b-col>
                            <b-col cols="10">
                                <input type="text" id="addphonenumber" v-model="appointmentData.description"
                                    class="form-control" />
                                <div v-if="errors.description" class="error" aria-live="polite">{{
                                    errors.description }}</div>
                            </b-col>
                        </b-row>
                        <b-row class="align-items-center form-group">
                            <b-col cols="2" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <label for="addappointmenttype" class="col-form-label">
                                    <icon-component type="outlined" icon-name="pencil" :size="24"></icon-component>
                                </label>
                            </b-col>
                            <b-col cols="10" lg="4" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <select v-model="appointmentData.appointment_type" name="service" class="form-select"
                                    id="addappointmenttype">
                                    <!-- Default placeholder option -->
                                    <option value="">Appointment Type</option>
                                    <!-- Dynamically populated options from API -->
                                    <option v-for="type in appointmentTypeOptions" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                                <div v-if="errors.appointment_type" class="error" aria-live="polite">{{
                                    errors.appointment_type }}</div>
                            </b-col>
                            <b-col cols="2" lg="1" class="mb-sm-3 mb-md-3 mb-lg-0">
                                <label for="addappointmenttype" class="col-form-label">
                                    <icon-component type="outlined" icon-name="calendar" :size="24"></icon-component>
                                </label>
                            </b-col>
                            <b-col cols="10" lg="5" class="mb-sm-3 mb-md-3 mb-lg-0">

                                <flat-pickr v-model="selectedDate" class="form-control" :config="config"
                                    id="datePicker" />



                            </b-col>
                        </b-row>
                        <b-row class="g-3 align-items-center form-group mt-3 mb-5 p-2">
                            <b-row>
                                <b-col md="12" lg="12" sm="12">
                                    <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots"
                                        @selectedSlotsTimes="handleSelectedSlotsTimes" />
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
<style>
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

/* Add rounded corners to the modal */
.modal-fullscreen .modal-content {
    border-radius: 5px !important;
    /* You can adjust the radius value */
}
</style>
