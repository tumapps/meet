
<script setup>
import { ref, onMounted } from 'vue'
import createAxiosInstance from '@/api/axios';
import PageHeader from '@/components/custom/header/PageHeader.vue'
import FullCalender from '@/components/custom/calender/FullCalender.vue'
import moment from 'moment'

const axiosInstance = createAxiosInstance();
const errors = ref({ contact_name: '', email_address: '', start_time: '', mobile_number: '', subject: '', appointment_type: '', description: '' });
const events = ref([]);
const show = ref(false)
const clickedDate = ref('');
// Function to handle the dateClick event

const formatDate = (date) => {
  if (!date) return '';

  const year = date.getFullYear().toString(); // Get last 2 digits of the year
  const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are zero-based
  const day = date.getDate().toString().padStart(2, '0'); // Day of the month

  return `${year}-${month}-${day}`;
};

const handleDateClick = (info) => {
  // `info.date` gives the clicked date
  clickedDate.value = info.date;
  console.log('Clicked date:', clickedDate);

  // Format the date and update appointmentData
  const formattedDate = formatDate(clickedDate.value);
  appointmentData.value.appointment_date = formattedDate;
console.log('Formatted date:', formattedDate);
  // Perform any other action, e.g., open a modal or add an event
  show.value = true;
};

const close = () => {
  show.value = false
}
// const show = ref(false)
const dateOption = ref({
  mode: 'range'
})
const timeOption = ref({
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i'
})
// Local variable for the selected time range
const selectedTime = ref('');
// Function to update start_time and end_time based on the selected option
const updateTimes = () => {
  if (selectedTime.value) {
    const times = selectedTime.value.split('-');
    appointmentData.value.start_time = `${times[0]}:00`;  // Set the start time (e.g., "10:00")
    appointmentData.value.end_time = `${times[1]}:00`;    // Set the end time (e.g., "12:00")
  } else {
    appointmentData.value.start_time = null;
    appointmentData.value.end_time = null;
  }
};
// Define your form data
const appointmentData = ref({
  user_id: "212408001",
  appointment_date: "",
  start_time: "",
  end_time: "",
  contact_name: "",
  email_address: "",
  mobile_number: "",
  subject: "",
  appointment_type: ""
});

//time range as slots for the appointment from the api
const slots = ref([]);

onMounted(() => {
  getBookedSlots();
});

// Function to send the POST request
const submitAppointment = async () => {
  try {
    // isLoading.value = true;
    console.log('Appointment data:', appointmentData.value);
    const response = await axiosInstance.post('/v1/scheduler/appointments', appointmentData.value);
    console.log('Response:', response.data);
    alert(response.data.dataPayload.data.message);

    close();
    // Handle the success response here (e.g., showing a success message)
  } catch (error) {
    console.error('Error submitting appointment:');
    if (error.response && error.response.status === 422 && error.response.data.errorPayload) {

      const errorDetails = error.response.data.errorPayload.errors;
      // console.log("Validation error details:", errorDetails);
      console.log('Errors:', errors.value.email_address);
      console.log('Errors:', errors.value.start_time);
      errors.value.contact_name = errorDetails.contact_name || '';
      errors.value.email_address = errorDetails.email_address || '';
      errors.value.mobile_number = errorDetails.mobile_number || '';
      errors.value.subject = errorDetails.subject || '';
      errors.value.appointment_type = errorDetails.appointment_type || '';
      errors.value.description = errorDetails.description || '';
      errors.value.start_time = errorDetails.start_time || '';
    }
    // console.error('Error submitting appointment:', error);
    // Handle the error response here (e.g., showing an error message)
  }
};
//function to get booked slots
//run the function on page load
const getBookedSlots = async () => {
  try {
    const response = await axiosInstance.get('/v1/scheduler/appointments');
    slots.value = response.data.dataPayload.data;
    console.log('Slots:', slots.value);


    console.log("rime", response)
    // Handle the success response here (e.g., showing a success message)
  } catch (error) {
    console.error('Error submitting appointment:', error);
    // Handle the error response here (e.g., showing an error message)
  }
};
// console.log('Events2:', events3);
</script>


<template>
  <b-row>
    <b-col md="12">
      <page-header title="Book Appointment">
        <b-button variant="primary me-1">Back</b-button>
        <b-button variant="warning ms-2" @click="show = true">
          <icon-component type="solid" icon-name="plus" :size="20"></icon-component>
          <span class="ms-1">Book Appointment</span>
        </b-button>
      </page-header>
    </b-col>

    <b-col lg="7 calendar-s">
      <b-card>
        <full-calender :events="events" @onDateSelect="handleDateClick"></full-calender>
      </b-card>
    </b-col>
    <b-col lg="5">
      <b-card>
        <template #header>
          <h4 class="mb-0">Available Slots</h4>
        </template>
        <div class="d-flex pb-4 gap-3">
          <div>
            <img class="img-fluid rounded" src="@/assets/modules/appointment/images/users/01.png" alt="users img"
              loading="lazy" />
          </div>
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <div>
              <h5 class="mb-0">Ross Geller</h5>
              <span>Anesthetics</span>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon-24" width="24" viewBox="0 0 24 24" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M13.1043 4.17701L14.9317 7.82776C15.1108 8.18616 15.4565 8.43467 15.8573 8.49218L19.9453 9.08062C20.9554 9.22644 21.3573 10.4505 20.6263 11.1519L17.6702 13.9924C17.3797 14.2718 17.2474 14.6733 17.3162 15.0676L18.0138 19.0778C18.1856 20.0698 17.1298 20.8267 16.227 20.3574L12.5732 18.4627C12.215 18.2768 11.786 18.2768 11.4268 18.4627L7.773 20.3574C6.87023 20.8267 5.81439 20.0698 5.98724 19.0778L6.68385 15.0676C6.75257 14.6733 6.62033 14.2718 6.32982 13.9924L3.37368 11.1519C2.64272 10.4505 3.04464 9.22644 4.05466 9.08062L8.14265 8.49218C8.54354 8.43467 8.89028 8.18616 9.06937 7.82776L10.8957 4.17701C11.3477 3.27433 12.6523 3.27433 13.1043 4.17701Z"
                  fill="#FFD329" />
              </svg>
              <p class="text-primary mb-0">5.0</p>
            </div>
          </div>
        </div>
        <div class="d-flex gap-3">
          <div>
            <img class="img-fluid rounded" src="@/assets/modules/appointment/images/users/02.png" alt="users img"
              loading="lazy" />
          </div>
          <div class="d-flex justify-content-between align-items-center flex-grow-1">
            <div>
              <h5 class="mb-0">Ted Mosby</h5>
              <span>Cardiology</span>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon-24" width="24" viewBox="0 0 24 24" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M13.1043 4.17701L14.9317 7.82776C15.1108 8.18616 15.4565 8.43467 15.8573 8.49218L19.9453 9.08062C20.9554 9.22644 21.3573 10.4505 20.6263 11.1519L17.6702 13.9924C17.3797 14.2718 17.2474 14.6733 17.3162 15.0676L18.0138 19.0778C18.1856 20.0698 17.1298 20.8267 16.227 20.3574L12.5732 18.4627C12.215 18.2768 11.786 18.2768 11.4268 18.4627L7.773 20.3574C6.87023 20.8267 5.81439 20.0698 5.98724 19.0778L6.68385 15.0676C6.75257 14.6733 6.62033 14.2718 6.32982 13.9924L3.37368 11.1519C2.64272 10.4505 3.04464 9.22644 4.05466 9.08062L8.14265 8.49218C8.54354 8.43467 8.89028 8.18616 9.06937 7.82776L10.8957 4.17701C11.3477 3.27433 12.6523 3.27433 13.1043 4.17701Z"
                  fill="#FFD329" />
              </svg>
              <p class="text-primary mb-0">4.5</p>
            </div>
          </div>
        </div>
      </b-card>
    </b-col>
  </b-row>
  <b-modal v-model="show" title="Add Appointment" hide-footer>
    <form id="add-form" action="javascript:void(0)" method="post">
      <div class="d-flex flex-column align-items-start">
        <input type="hidden" name="id" />
        <input type="hidden" name="appointment_type" />
        <div class="w-100" id="v-pills-tabContent">
          <div class="fade active show" id="-3">
            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addcontactname" class="col-form-label">Name</label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addcontactname" v-model="appointmentData.contact_name" class="form-control" />
                <div v-if="errors.contact_name" class="error" aria-live="polite">{{ errors.contact_name }}</div>
              </b-col>
            </b-row>

            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addphonenumber" class="col-form-label">Phone</label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addphonenumber" v-model="appointmentData.mobile_number" class="form-control" />
                <div v-if="errors.mobile_number" class="error" aria-live="polite">{{ errors.mobile_number }}</div>
              </b-col>
            </b-row>

            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addemail" class="col-form-label">Email</label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addemail" v-model="appointmentData.email_address" class="form-control" />
                <div v-if="errors.email_address" class="error" aria-live="polite">{{ errors.email_address }}</div>
              </b-col>
            </b-row>

            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addsubject" class="col-form-label">RE:</label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addsubject" v-model="appointmentData.subject" class="form-control"
                  placeholder="what the meeting is about" />
                <div v-if="errors.subject" class="error" aria-live="polite">{{ errors.subject }}</div>
              </b-col>
            </b-row>

            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addtime" class="col-form-label">Time</label>
              </b-col>
              <b-col cols="10">
                <select v-model="selectedTime" @change="updateTimes" name="service" class="form-select" id="addtime">
                  //loop through the slots to get the time range
                  <option value="">Select Time</option>
                  <option v-for="slot in slots" :value="slot.time_range">{{ slot.time_range }}</option>
                </select>
                <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
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
                <div v-if="errors.appointment_type" class="error" aria-live="polite">{{ errors.appointment_type }}</div>
              </b-col>
            </b-row>

            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addlocation" class="col-form-label">
                  <icon-component type="outlined" icon-name="location" :size="24"></icon-component>
                </label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addlocation" v-model="appointmentData.location" class="form-control" />
                <div v-if="errors.location" class="error" aria-live="polite">{{ errors.location }}</div>
              </b-col>
            </b-row>

            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="adddescription" class="col-form-label">
                  <icon-component type="outlined" icon-name="pencil-alt" :size="24"></icon-component>
                </label>
              </b-col>
              <b-col cols="10">
                <textarea name="description" id="adddescription" v-model="appointmentData.description"
                  class="form-control" rows="3" placeholder="Description" spellcheck="false"></textarea>
                <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
              </b-col>
            </b-row>
          </div>
        </div>
      </div>
    </form>

    <div class="d-flex align-items-center justify-content-center form-check">
      <input type="checkbox" class="form-check-input m-0" id="addconfirm2" name="confirm" value="Bike" />
      <label class="form-check-label ms-2" for="addconfirm2">Confirm Information</label>
    </div>
    <div class="modal-footer border-0">
      <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="close()">Discard Changes</button>
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
</style>
