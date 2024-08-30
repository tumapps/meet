<script setup>
import { ref, watch } from 'vue';

// Props
const props = defineProps({
  accountData: {
    type: Object,
    required: true,
  },
});

// Emit
const emit = defineEmits(['onClick', 'updateAccountData']);

// Local state for form inputs
const localAccountData = ref({ ...props.accountData });

// Watch for changes in the prop to update local state if necessary
watch(
  () => props.accountData,
  (newData) => {
    localAccountData.value = { ...newData };
  }
);

// Function to emit updated data to parent
const updateData = (key, value) => {
  localAccountData.value[key] = value;
  emit('updateAccountData', localAccountData.value); // Emit updated data
};

// Change the form tab and update the data
const changeTab = (value) => {
  emit('onClick', value);
};
</script>

<template>
  <fieldset>
    <div class="form-card text-start">
      <b-row>
        <div class="col-7">
          <h3 class="mb-4">Account Information:</h3>
        </div>
        <div class="col-5">
          <h2 class="steps">Step 1 - 3</h2>
        </div>
      </b-row>
      <b-row>
        <b-col md="6">
          <b-form-group label="Email: *">
            <b-form-input
              type="email"
              class="form-control"
              name="email"
              placeholder="Email Id"
              v-model="localAccountData.email_address"
              @input="updateData('email_address', localAccountData.email_address)"
            />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group label="Username: *">
            <b-form-input
              type="text"
              class="form-control"
              name="uname"
              placeholder="UserName"
              v-model="localAccountData.username"
              @input="updateData('username', localAccountData.username)"
            />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group label="Password: *" class="mt-3">
            <b-form-input
              type="password"
              class="form-control"
              name="new-password"
              placeholder="Password"
              v-model="localAccountData.password"
              @input="updateData('password', localAccountData.password)"
            />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group label="Confirm Password: *" class="mt-3 mb-3">
            <b-form-input
              type="password"
              class="form-control"
              name="confirm-new-password"
              placeholder="Confirm Password"
              v-model="localAccountData.confirm_password"
              @input="updateData('confirm_password', localAccountData.confirm_password)"
            />
          </b-form-group>
        </b-col>
      </b-row>
    </div>
    <a
      href="#personal"
      class="btn btn-primary next action-button float-end"
      @click="changeTab(2)"
      value="Next"
    >
      Next
    </a>
  </fieldset>
</template>
