
<script setup>
import { ref, watch } from 'vue';

// Props
const props = defineProps({
  personalData: {
    type: Object,
    required: true
  },
  onSubmit: Function

});

// Method to trigger submission
const handleSubmit = () => {
    if (props.onSubmit) {
        props.onSubmit();
    }
};

// Emit event
const emit = defineEmits(['updatePersonalData', 'onClick']);

// Local copy of personal data
const localPersonalData = ref({ ...props.personalData });


// Watch for changes in props to update local state
watch(
  () => props.personalData,
  (newData) => {
    localPersonalData.value = { ...newData };
  }, // Trigger watcher immediately to initialize localPersonalData
);

// Function to emit updated data to the parent
const updateData = (key, value) => {
  emit('updatePersonalData', localPersonalData.value); // Emit updated data
};


// Handle tab change
const changeTab = (value) => {
  emit('onClick', value);
};
</script>


<template>
  <fieldset>
    <div class="form-card text-start">
      <b-row>
        <div class="col-7">
          <h3 class="mb-4">Personal Information:</h3>
        </div>
        <div class="col-5">
          <h2 class="steps">Step 2 - 3</h2>
        </div>
      </b-row>
      <b-row>
        <b-col md="12" lg="12">
          <b-form-group label="First Name: *">
            <b-form-input
              type="text"
              class="form-control mb-3"
              name="fname"
              placeholder="First Name"
              v-model="localPersonalData.first_name"
              @input="updateData('first_name', localPersonalData.first_name)" required
            />
          </b-form-group>
        </b-col>
        <b-col md="12" lg="12">
          <b-form-group label="Last Name: *">
            <b-form-input
              type="text"
              class="form-control mb-3"
              name="lname"
              placeholder="Last Name"
              v-model="localPersonalData.last_name"
              @input="updateData('last_name', localPersonalData.last_name)" required
            />
          </b-form-group>
        </b-col>
        <b-col md="12" lg="12" class="mt-3">
          <b-form-group label="Contact No.: *">
            <b-form-input
              type="text"
              class="form-control mb-3"
              name="phno"
              placeholder="Contact No."
              v-model="localPersonalData.contact_no"
              @input="updateData('contact_no', localPersonalData.contact_no)" required
            />
          </b-form-group>
        </b-col>
        <b-col md="12" lg="12" class="mt-3">
          <b-form-group label="Middle name: *">
            <b-form-input
              type="text"
              class="form-control "
              name="phno_2"
              placeholder="middle name."
              v-model="localPersonalData.middle_name"
              @input="updateData('middle_name', localPersonalData.middle_name)" required
            />
          </b-form-group>
        </b-col>
      </b-row>
    </div>

    <!-- //submit button to send data to api  -->

    <button value="Submit" class="btn btn-primary action-button float-end mt-3" @click="handleSubmit">
  Submit
</button>

    <a
      href="#account"
      @click="changeTab(1)"
      class="btn btn-warning previous action-button-previous float-end me-1 mt-3"
      value="Previous"
    >
      Previous
    </a>
  </fieldset>
</template>
