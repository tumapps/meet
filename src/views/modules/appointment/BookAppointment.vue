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
        <full-calender :events="events" @onDateSelect="dateSelected"></full-calender>
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
                <label for="addtitle" class="col-form-label">Name</label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addtitle" class="form-control" />
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addtitle" class="col-form-label">RE:</label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addtitle" class="form-control" placeholder="what the meeting is about">
              </b-col>
            </b-row>

            <!-- TODO: Add time picker -->
            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label class="col-form-label">
                  <icon-component type="outlined" icon-name="watch" :size="24"></icon-component>
                </label>
              </b-col>
              <b-col cols="10">
                <!-- TODO: get the slot from the API -->
                <select v-model="Timeslot" name="service" class="form-select" id="service">
                  <option value="">10-12</option>
                  <option value="in-person">14-16</option>
                  <option value="group">8-10</option>
                  <option value="virtual">11-13</option>
                </select>
              </b-col>
            </b-row>



            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label class="col-form-label">
                  <icon-component type="outlined" icon-name="pencil" :size="24"></icon-component>
                </label>
              </b-col>
              <b-col cols="10">
                <select v-model="meetingType" name="service" class="form-select" id="service">
                  <option value="">meeting type</option>
                  <option value="in-person">In-Person</option>
                  <option value="group">Group</option>
                  <option value="virtual">Virtual</option>
                </select>
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label for="addtitle" class="col-form-label">
                  <icon-component type="outlined" icon-name="location" :size="24"></icon-component>

                </label>
              </b-col>
              <b-col cols="10">
                <input type="text" id="addtitle" class="form-control" />
              </b-col>
            </b-row>
            <b-row class="g-3 align-items-center form-group">
              <b-col cols="2">
                <label class="col-form-label">
                  <icon-component type="outlined" icon-name="pencil-alt" :size="24"></icon-component>
                </label>
              </b-col>
              <b-col cols="10">
                <textarea name="description" id="description" class="form-control" rows="3" placeholder="Description"
                  spellcheck="false"></textarea>
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
      <button type="button" class="btn btn-primary" data-bs-dismiss="modal" name="save" @click="save()">Save</button>
    </div>
  </b-modal>

</template>

<script setup>
import { ref, onMounted } from 'vue'
import moment from 'moment'

// Library Components
import flatpickr from 'vue-flatpickr-component'

// Components
import PageHeader from '@/components/custom/header/PageHeader.vue'
import FullCalender from '@/components/custom/calender/FullCalender.vue'
const show = ref(false)
const dateSelected = () => {
  show.value = true
}
const save = () => {
  // document.writeln('Form submitted')
  show.value = false
}
const close = () => {
  show.value = false
}
const startdate = ref('')
const selectService = ref('')
const enddate = ref('')
const startTime = ref('')
const endTime = ref('')
// const show = ref(false)
const dateOption = ref({
  mode: 'range'
})
const timeOption = ref({
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i'
})




// TODO: GET FROM API 
const events = ref([
  {
    title: 'Click for Google',
    url: 'http://google.com/',
    start: moment(new Date(), 'YYYY-MM-DD').add(-20, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  },
  {
    title: 'Long Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(-18, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    color: 'purple'
  },
  {
    title: 'Long Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(-16, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    end: moment(new Date(), 'YYYY-MM-DD').add(-13, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    color: 'red',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)',
    // display: 'background'
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(-14, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    color: '#047685',
    backgroundColor: 'rgba(4,118,133,0.2)',
    textColor: 'rgba(4,118,133,1)',
    borderColor: 'rgba(4,118,133,1)'
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(-12, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(235,153,27,0.2)',
    textColor: 'rgba(235,153,27,1)',
    borderColor: 'rgba(235,153,27,1)'
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(-10, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(206,32,20,0.2)',
    textColor: 'rgba(206,32,20,1)',
    borderColor: 'rgba(206,32,20,1)'
  },
  {
    title: 'Birthday Party',
    start: moment(new Date(), 'YYYY-MM-DD').add(-8, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  },
  {
    title: 'Meeting',
    start: moment(new Date(), 'YYYY-MM-DD').add(-6, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  },
  {
    title: 'Birthday Party',
    start: moment(new Date(), 'YYYY-MM-DD').add(-5, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(235,153,27,0.2)',
    textColor: 'rgba(235,153,27,1)',
    borderColor: 'rgba(235,153,27,1)'
  },
  {
    title: 'Birthday Party',
    start: moment(new Date(), 'YYYY-MM-DD').add(-2, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(235,153,27,0.2)',
    textColor: 'rgba(235,153,27,1)',
    borderColor: 'rgba(235,153,27,1)'
  },

  {
    title: 'Meeting',
    start: moment(new Date(), 'YYYY-MM-DD').add(0, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgb(206, 32, 20)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  },
  {
    title: 'Click for Google',
    url: 'http://google.com/',
    start: moment(new Date(), 'YYYY-MM-DD').add(0, 'days').format('YYYY-MM-DD') + 'T06:30:00.000Z',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(0, 'days').format('YYYY-MM-DD') + 'T07:30:00.000Z',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  },
  {
    title: 'Birthday Party',
    start: moment(new Date(), 'YYYY-MM-DD').add(0, 'days').format('YYYY-MM-DD') + 'T08:30:00.000Z',
    backgroundColor: 'rgba(235,153,27,0.2)',
    // textColor: 'rgba(235,153,27,1)',
    borderColor: 'rgba(235,153,27,1)',
    color: 'yellow',   // an option!
    textColor: 'black' // an option!
  },
  {
    title: 'Doctor Meeting',
    start: moment(new Date(), 'YYYY-MM-DD').add(0, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(235,153,27,0.2)',
    textColor: 'rgba(235,153,27,1)',
    borderColor: 'rgba(235,153,27,1)',
  },
  {
    title: 'All Day Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(1, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)',
    
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(8, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  },
  {
    groupId: '999',
    title: 'Repeating Event',
    start: moment(new Date(), 'YYYY-MM-DD').add(10, 'days').format('YYYY-MM-DD') + 'T05:30:00.000Z',
    backgroundColor: 'rgba(58,87,232,0.2)',
    textColor: 'rgba(58,87,232,1)',
    borderColor: 'rgba(58,87,232,1)'
  }
],
)
</script>

<style></style>
