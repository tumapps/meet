<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue'
import CreateAxiosInstance from '@/api/axios.js'

const axiosInstance = CreateAxiosInstance();
const errors = ref({ username: '', password: '', first_name: '', middle_name: '', last_name: '', mobile_number: '', email_address: '', confirm_password: '', oldPassword: '' });
const { proxy } = getCurrentInstance();
const username = ref("");
const password = ref("");
const confirm_password = ref("");
const first_name = ref("");
const middle_name = ref("");
const last_name = ref("");
const mobile_number = ref("");
const email_address = ref("");
const oldPassword = ref("");

onMounted(() => {
    getProfile();
});

const user_id = ref(212409004);

const getProfile = async () => {
    try {
        // add userid to request
    
        const response = await axiosInstance.get('/v1/auth/profile-view/');
        username.value = response.data.dataPayload.data.username;
        first_name.value = response.data.dataPayload.data.first_name;
        middle_name.value = response.data.dataPayload.data.middle_name;
        last_name.value = response.data.dataPayload.data.last_name;
        mobile_number.value = response.data.dataPayload.data.mobile_number;
        email_address.value = response.data.dataPayload.data.email_address;
    } catch (error) {
        proxy.$showAlert({
            title: 'An error occurred ',
            text: 'Ooops! an error has occured while fetching the profile!',
            icon: 'error',
        });
    }
}

const updateProfile = async () => {
    errors.value = { username: '', password: '', first_name: '', middle_name: '', last_name: '', mobile_number: '', email_address: '', confirm_password: '' };
    try {
        const response = await axiosInstance.put('v1/auth/profile-update', {
            username: username.value,
            password: password.value,
            confirm_password: confirm_password.value,
            first_name: first_name.value,
            middle_name: middle_name.value,
            last_name: last_name.value,
            mobile_number: mobile_number.value,
            email_address: email_address.value
        });

        if (response.data.dataPayload && !response.data.dataPayload.error) {
            proxy.$showAlert({
                title: 'Profile updated successfully',
                text: 'Profile has been updated successfully!',
                icon: 'success',
            });
        }

    } catch (error) {

        proxy.$showAlert({
            title: 'Ooops!',
            text: 'Ooops! an error has occured while updating the profile!',
            icon: 'error',
        });

        if (error.response && error.response.status === 422 && error.response.data.errorPayload) {

            const errorDetails = error.response.data.errorPayload.errors;

            errors.value.username = errorDetails.username || '';
            errors.value.password = errorDetails.password || '';
            errors.value.first_name = errorDetails.first_name || '';
            errors.value.middle_name = errorDetails.middle_name || '';
            errors.value.last_name = errorDetails.last_name || '';
            errors.value.mobile_number = errorDetails.mobile_number || '';
            errors.value.email_address = errorDetails.email_address || '';
            errors.value.confirm_password = errorDetails.confirm_password || '';

        }
    }
}

const updatePassword = async () => {
    errors.value = { password: '', confirm_password: '', oldPassword: '' };
    try {
        const response = await axiosInstance.post('v1/auth/update-password', {
            password: password.value,
            confirm_password: confirm_password.value,
            oldPassword: oldPassword.value
        });

        if (response.data.dataPayload && !response.data.dataPayload.error) {
            //set cookie
            proxy.$showAlert({
                title: 'Password updated successfully',
                text: 'Password has been updated successfully!',
                icon: 'success',
            });
        }
        console.log(response.data);
    } catch (error) {

        if (error.response && error.response.status === 422 && error.response.data.errorPayload) {

            const errorDetails = error.response.data.errorPayload.errors;
            console.log("Validation error details:", errorDetails);

            errors.value.password = errorDetails.password || '';
            errors.value.confirm_password = errorDetails.confirm_password || '';
            errors.value.oldPassword = errorDetails.oldPassword || '';

        }else{

        proxy.$showAlert({
            title: 'Ooops!',
            text: 'Ooops! an error has occured while updating the password!',
            icon: 'error',
        });
        }
    }
}


</script>

<template>

    <b-row>
        <div class="col-xl-12 col-lg-12">
            <div>
                <card-header class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Account Details</h4>
                    </div>
                </card-header>
                <b-card-body>
                    <div class="new-user-info pb-5">
                        <form @submit.prevent="updateProfile">
                            <b-row>
                                <b-col md="12" class="form-group">
                                    <label class="form-label" for="fname">First Name:</label>
                                    <input v-model="first_name" type="text" class="form-control" id="fname"
                                        placeholder="First Name" />
                                    <div v-if="errors.first_name" class="error" aria-live="polite">{{ errors.first_name
                                        }}</div>
                                </b-col>
                                <b-col md="12" class="form-group">
                                    <label class="form-label" for="cname">Middle Name:</label>
                                    <input v-model="middle_name" type="text" class="form-control" id="cname"
                                        placeholder="Middle Name" />
                                    <div v-if="errors.middle_name" class="error" aria-live="polite">{{
                                        errors.middle_name }}</div>
                                </b-col>
                                <b-col md="12" class="form-group">
                                    <label class="form-label" for="lname">Last Name:</label>
                                    <input v-model="last_name" type="text" class="form-control" id="lname"
                                        placeholder="Last Name" />
                                    <div v-if="errors.last_name" class="error" aria-live="polite">{{ errors.last_name }}
                                    </div>
                                </b-col>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="mobno">Mobile Number:</label>
                                    <input v-model="mobile_number" type="text" class="form-control" id="mobno"
                                        placeholder="Mobile Number" />
                                    <div v-if="errors.mobile_number" class="error" aria-live="polite">{{
                                        errors.mobile_number }}</div>
                                </b-col>
                                <b-col md="6" class="form-group">
                                    <label class="form-label" for="email">Email:</label>
                                    <input v-model="email_address" type="email" class="form-control" id="email"
                                        placeholder="Email" />
                                    <div v-if="errors.email_address" class="error" aria-live="polite">{{
                                        errors.email_address }}</div>
                                </b-col>
                            </b-row>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </b-card-body>
            </div>
        </div>
    </b-row>

    <b-row class="mt-5">
        <div class="col-xl-12 col-lg-12">
            <div>
                <card-header class="">
                    <div class="header-title">
                        <h4 class="mb-2 ">Change Password</h4>
                    </div>

                    <div>
                        <form @submit.prevent="updatePassword" class="mt-3">
                            <div class="row">
                                <b-col md="12" lg="12" class="form-group">
                                    <label class="form-label" for="pass"> Current Password:</label>
                                    <input v-model="oldPassword" type="password" class="form-control" id="pass"
                                        placeholder="Password" />
                                    <div v-if="errors.oldPassword" class="error" aria-live="polite">{{ errors.oldPassword
                                        }}</div>
                                </b-col>
                                <b-col md="12" class="form-group">
                                    <label class="form-label" for="pass">Password:</label>
                                    <input v-model="password" type="password" class="form-control" id="pass"
                                        placeholder="Password" />
                                    <div v-if="errors.password" class="error" aria-live="polite">{{ errors.password
                                        }}</div>
                                </b-col>
                                <b-col md="12" class="form-group">
                                    <label class="form-label" for="rpass">Repeat Password:</label>
                                    <input v-model="confirm_password" type="password" class="form-control" id="rpass"
                                        placeholder="Repeat Password " />
                                    <i class="bi bi-eye-slash" id="togglePassword"></i>
                                    <div v-if="errors.confirm_password" class="error" aria-live="polite">{{
                                        errors.confirm_password
                                    }}</div>
                                </b-col>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </card-header>
            </div>
        </div>
    </b-row>
</template>
<style scoped>
.error {
    color: red;
    font-size: 1.1em;
}
</style>