<script setup>
import { ref, getCurrentInstance } from 'vue';
import AccountComponent from '@/components/widgets/wizard/AccountComponent.vue';
import PersonalComponent from '@/components/widgets/wizard/PersonalComponent.vue';
import FinishComponent from '@/components/widgets/wizard/FinishComponent.vue';
import AxiosInstance from '@/api/axios'

const axiosInstance = AxiosInstance();
const currentindex = ref(1);
const { proxy } = getCurrentInstance();

const formData = ref({
    username: '',
    password: '',
    confirm_password: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    mobile_number: '',
    email_address: '',
});

// Methods
const changeTab = (val) => {
    currentindex.value = val;
};

// Handle the emitted updated account data from AccountComponent
const updateAccountData = (updatedData) => {
    formData.value = { ...formData.value, ...updatedData }; // Update formData with new account data
};

//Handle the emitted updated personal data from PersonalComponent
const updatePersonalData = (updatedData) => {
    formData.value = { ...formData.value, ...updatedData }; // Update formData with new personal data
};

const submitForm = async () => {
    try {
        const response = await axiosInstance.post('/v1/auth/register', formData.value);
        // Handle success
        proxy.$showAlert({
            title: 'Account created successfully',
            text: 'account has been created successfully!',
            icon: 'success',
        });
        //move to next tab
        currentindex.value = 3;
    } catch (error) {
        // Handle error
        // console.error('Error submitting form:', error);
        proxy.$showToast({
            title: 'An error occurred ',
            text: 'Ooops! an error has occured while creating the account!',
            icon: 'error',
        });
    }
};

</script>

<template>
    <b-row class="justify-content-center align-items-center" style="min-height: 100vh;">
        <!-- <b-col lg="12" class="background-image">
    </b-col> -->

    <b-col sm="12" lg="12">
    <b-card no-body>
        <b-card-header class="d-flex justify-content-between">
            <div class="header-title">
                <b-card-title>Account info</b-card-title>
            </div>
        </b-card-header>
        <b-card-body>
            <b-form id="form-wizard1" class="text-center mt-3">
                <ul id="top-tab-list" class="p-0 row list-inline">
                    <li :class="[
                        'col-lg-3',
                        'col-md-6',
                        'mb-3', 
                        'text-start',
                        currentindex == 1 ? 'active' : '',
                        currentindex > 1 ? 'done active' : ''
                    ]" id="account">
                        <a href="javascript:void(0);">
                            <div class="iq-icon me-3">
                                <!-- SVG Icon -->
                                <svg class="icon-20 svg-icon" width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.4"
                                        d="M8.23918 8.70907V7.36726C8.24934 5.37044 9.92597 3.73939 11.9989 3.73939C13.5841 3.73939 15.0067 4.72339 15.5249 6.19541C15.6976 6.65262 16.2057 6.89017 16.663 6.73213C16.8865 6.66156 17.0694 6.50253 17.171 6.29381C17.2727 6.08508 17.293 5.84654 17.2117 5.62787C16.4394 3.46208 14.3462 2 11.9786 2C8.95048 2 6.48126 4.41626 6.46094 7.38714V8.91084L8.23918 8.70907Z"
                                        fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.7688 8.71118H16.2312C18.5886 8.71118 20.5 10.5808 20.5 12.8867V17.8246C20.5 20.1305 18.5886 22.0001 16.2312 22.0001H7.7688C5.41136 22.0001 3.5 20.1305 3.5 17.8246V12.8867C3.5 10.5808 5.41136 8.71118 7.7688 8.71118ZM11.9949 17.3286C12.4928 17.3286 12.8891 16.941 12.8891 16.454V14.2474C12.8891 13.7703 12.4928 13.3827 11.9949 13.3827C11.5072 13.3827 11.1109 13.7703 11.1109 14.2474V16.454C11.1109 16.941 11.5072 17.3286 11.9949 17.3286Z"
                                        fill="currentColor"></path>
                                </svg>
                            </div>
                            <span> Account</span>
                        </a>
                    </li>
                    <li :class="[
                        'col-lg-3',
                        'col-md-6',
                        'mb-3', 
                        'text-start',
                        currentindex == 2 ? 'active' : '',
                        currentindex > 1 ? 'done active' : ''
                    ]" id="personal">
                        <a href="javascript:void(0);">
                            <div class="iq-icon me-3">
                                <!-- SVG Icon -->
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M11.997 15.1746C7.684 15.1746 4 15.8546 4 18.5746C4 21.2956 7.661 21.9996 11.997 21.9996C16.31 21.9996 19.994 21.3206 19.994 18.5996C19.994 15.8786 16.334 15.1746 11.997 15.1746Z"
                                        fill="currentColor"></path>
                                    <path opacity="0.4"
                                        d="M11.9971 12.5838C14.9351 12.5838 17.2891 10.2288 17.2891 7.29176C17.2891 4.35476 14.9351 1.99976 11.9971 1.99976C9.06008 1.99976 6.70508 4.35476 6.70508 7.29176C6.70508 10.2288 9.06008 12.5838 11.9971 12.5838Z"
                                        fill="currentColor"></path>
                                </svg>
                            </div>
                            <span class="dark-wizard"> Personal</span>
                        </a>
                    </li>
                    <li :class="[
                        'col-lg-3',
                        'col-md-6',
                        'mb-3', 
                        'text-start',
                        currentindex == 3 ? 'active' : '',
                        currentindex > 3 ? 'done' : ''
                    ]" id="confirm">
                        <a href="javascript:void(0);">
                            <div class="iq-icon me-3">
                                <!-- SVG Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="dark-wizard"> Finish</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Components -->
                <div :class="currentindex == 1 ? 'show' : 'd-none'">
                    <account-component @onClick="changeTab" :accountData="formData"
                        @updateAccountData="updateAccountData"></account-component>
                </div>

                <div :class="currentindex == 2 ? 'show' : 'd-none'">
                    <personal-component @onClick="changeTab" :personalData="formData"
                        @updatePersonalData="updatePersonalData" @Submit="submitForm"></personal-component>
                </div>

                <div id="confirm" :class="currentindex == 3 ? 'show' : 'd-none'">
                    <finish-component></finish-component>
                </div>
            </b-form>
        </b-card-body>
    </b-card>
</b-col>

    </b-row>
</template>
<style scoped>
.background-image {
    height: 200px;
    background-image: url('@/assets/images/tum.jpg');
    background-size: cover;
    background-position: center;
}
</style>
