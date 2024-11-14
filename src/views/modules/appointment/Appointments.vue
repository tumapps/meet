<script setup>
import { onMounted, ref, getCurrentInstance, computed, watch } from 'vue';
import AxiosInstance from '@/api/axios';
import BookAppointment from '@/components/modules/appointment/partials/BookAppointment.vue';
import globalUtils from '@/utilities/globalUtils';
import TimeSlotComponent from '@/components/modules/appointment/partials/TimeSlotComponent.vue';
import { useAuthStore } from '@/store/auth.store.js';

const authStore = useAuthStore();

const CBB = ref('')
CBB.value = authStore.getCanBeBooked();
// const globalUtils = require('@/utils/globalUtils');
const appointmentModal = ref(null);
const showModal = () => {
    appointmentModal.value.$refs.appointmentModal.show();
};

const toastPayload = ref('');
const { proxy } = getCurrentInstance();
const axiosInstance = AxiosInstance();
const tableData = ref([]);
const sortKey = ref('');  // Active column being sorted
const sortOrder = ref('asc');  // Sorting order: 'asc' or 'desc'
const isArray = ref(false);
const currentPage = ref(1);  // The current page being viewed
const totalPages = ref(1);   // Total number of pages from the API
const perPage = ref(20);     // Number of items per page (from API response)
const selectedPerPage = ref(20);  // Number of items per page (from dropdown)
const perPageOptions = ref([10, 20, 50, 100]);
const searchQuery = ref('');

const errorDetails = ref({});

//get user_id from session storage
// const userId = authStore.getUserId();

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
    user_id: '',

});

const goToPage = (page) => {
    // Ensure the page is within the valid range
    if (page > 0 && page <= totalPages.value) {
        currentPage.value = page;
        getAppointments(page);  // Fetch appointments for the selected page
    }
};

const updatePerPage = async () => {
    currentPage.value = 1;  // Reset to first page when changing items per page
    await getAppointments(1);  // Fetch appointments with the new perPage value
};


// Method to update time slots
const updateTimeSlots = (updatedSlots) => {
    timeSlots.value = updatedSlots;
};
// Handle selected slots

const handleSelectedSlotsTimes = (selectedTimes) => {
    appointmentDetails.value.start_time = selectedTimes?.startTime || '';
    appointmentDetails.value.end_time = selectedTimes?.endTime || '';
};


const sortTable = (key) => {
    if (sortKey.value === key) {
        // Toggle the sort order if the same column is clicked again
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};
// Get class for status badges
const getStatusClass = (status) => {
    switch (status) {
        case 'Reschedule':
            return 'badge bg-warning';
        case 'Active':
            return 'badge bg-success';
        case 'Cancelled':
            return 'badge bg-danger';
        case 'Resheduled':
            return 'primary';
        default:
            return 'badge bg-secondary';
    }
};

const getBadgeVariant = (statusLabel) => {
    switch (statusLabel) {
        case 'Reschedule':
            return 'warning';
        case 'Active':
            return 'success';
        case 'Cancelled':
            return 'warning';
        case 'Deleted':
            return 'danger';
        default:
            return 'secondary';
    }
};

// Sort the data based on the current sort key and order
const sortedData = computed(() => {
    return [...tableData.value].sort((a, b) => {
        let modifier = sortOrder.value === 'asc' ? 1 : -1;
        if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier;
        if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier;
        return 0;
    });
});


onMounted(async () => {
    //fetch appointments and slots and unavailable slots
    getAppointments(1);
});

const confirmDelete = (id) => {
    // console.log("id", id);
    selectedAppointmentId.value = id;
    proxy.$showAlert({
        title: 'Are you sure?',
        text: 'You are about to Delete this appointment. Do you want to proceed?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete it!',
        cancelButtonText: 'No, keep it',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#076232',
    }).then((result) => {
        if (result.isConfirmed) {
            deleteAppointment(id);
            getAppointments(1);
        }
    });
};

const confirmCancel = (id) => {
    selectedAppointmentId.value = id;
    proxy.$showAlert({
        title: 'Are you sure?',
        text: 'You are about to CANCEL this appointment. Do you want to proceed?',
        icon: 'warning',
        input: 'textarea', // Adding input field
        inputLabel: 'Reason for Cancellation',
        inputPlaceholder: 'Please type your reason here...',
        inputAttributes: {
            'aria-label': 'Please type your reason here'
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, CANCEL it!',
        cancelButtonText: 'No, keep it',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#097B3E',
        preConfirm: (inputValue) => {
            if (!inputValue) {
                // Display error message if input is empty
                proxy.$showAlert({
                    title: 'Ooops!',
                    text: 'You need to provide a reason for cancellation.',
                    icon: 'info',
                    // background: '#d33',
                });
                return false; // Prevents closing the alert
            }
            return inputValue; // Passes the input value to the `then` block
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = result.value; // Access the input value here
            // console.log('Cancellation reason:', reason); // Optional: log the reason
            // Proceed with the cancellation using the provided reason
            cancelAppointment(id, reason);
            getAppointments(1);
        }
    });
};



const cancelAppointment = async (id, reason) => {
    try {
        // console.log("reasinsnius", reason);
        const response = await axiosInstance.put(`v1/scheduler/cancel/${id}`, { cancellation_reason: reason });
        getAppointment(id);
        getAppointments(1);
        toastPayload.value = response.data.toastPayload;
        proxy.$showToast({
            title: toastPayload.value.toastMessage || 'Appointment Deleted successfully2',
            // icon: toastPayload.value.toastTheme || 'success',
            icon: 'success',
        });


    } catch (error) {
        // console.error(error);
        const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An error occurred';

        proxy.$showToast({
            title: 'An error occurred',
            text: errorMessage,
            icon: 'error',
        });
    }
};
const deleteAppointment = async (id) => {
    toastPayload.value = null;

    try {
        const response = await axiosInstance.delete(`v1/scheduler/appointments/${id}`);
        getAppointment(id);
        getAppointments(1);
        // Check if toastPayload exists in the response and update it
        if (response.data.toastPayload) {
            toastPayload.value = response.data.toastPayload;
            // console.log("toastPayload", toastPayload.value); // Log for debugging

            // Show toast notification using the response data
            proxy.$showToast({
                title: toastPayload.value.toastMessage || 'Appointment Deleted successfully',
                // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
                icon: 'success',
            });
        } else {
            // Fallback if toastPayload is not provided in the response
            proxy.$showToast({
                title: 'Appointment Deleted successfully',
                icon: 'success',
            });
        }


    } catch (error) {
        // console.error(error);
        const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An unknown error occurred';

        proxy.$showToast({
            title: 'An error occurred',
            text: errorMessage,
            icon: 'error',
        });

    }
};

const errorMessage = ref('');

const restoreAppointment = async (id) => {
    // Reset toastPayload to an empty object
    toastPayload.value = {};
    // console.log("restore", toastPayload.value);

    try {
        // Make sure the correct HTTP method is used (assuming it's PUT for restoration)
        const response = await axiosInstance.delete(`v1/scheduler/appointments/${id}`);

        // Refresh the appointments after restoring
        getAppointment(id);
        getAppointments(1);

        // Check if toastPayload exists in the response and update it
        if (response.data.toastPayload) {
            toastPayload.value = response.data.toastPayload;
            // console.log("toastPayload", toastPayload.value); // Log for debugging

            // Show toast notification using the response data
            proxy.$showToast({
                title: toastPayload.value.toastMessage || 'Appointment Restored successfully',
                // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
                icon: 'success',
            });
        } else {
            // Fallback if toastPayload is not provided in the response
            proxy.$showToast({
                title: 'Appointment restored successfully',
                icon: 'success',
            });
        }

    } catch (error) {
        // Optionally log the error for debugging purposes
        const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An error occurred';

        proxy.$showToast({
            title: 'An error occurred',
            text: errorMessage,
            icon: 'error',
        });
    }
};


const confirmRestore = (id) => {
    // console.log("id", id);
    selectedAppointmentId.value = id;
    proxy.$showAlert({
        title: 'Are you sure?',
        text: 'You are about to RESTORE thirestoreAppointments appointment. Do you want to proceed?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'RESTORE',
        cancelButtonText: 'No, keep it',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#076232',
    }).then((result) => {
        if (result.isConfirmed) {
            restoreAppointment(id);
            getAppointments(1);
        }
    });
};

const getAppointments = async (page = 1) => {
    try {
        // console.log("selected", selectedPerPage.value)
        const response = await axiosInstance.get(`/v1/scheduler/appointments?page=${page}&per-page=${selectedPerPage.value}`);
        //check if data is array
        isArray.value = Array.isArray(response.data);
        tableData.value = response.data.dataPayload.data;
        // Update pagination data
        currentPage.value = response.data.dataPayload.currentPage;
        totalPages.value = response.data.dataPayload.totalPages;
        perPage.value = response.data.dataPayload.perPage;

    } catch (error) {
        const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An error occurred';

        proxy.$showToast({
            title: 'An error occurred',
            text: errorMessage,
            icon: 'error',
        });
    }
};

//slots for time
const slotsData = ref({
    user_id: '',
    date: '',
});
const setUserId = () => {
    // set user id depending on the user role
    if (authStore.getCanBeBooked() === 0) {
        //get the user id from the owner of the appointment
        slotsData.value.user_id = appointmentDetails.value.user_id;
        // console.log("user_id", slotsData.value.user_id);

    } else {
        slotsData.value.user_id = authStore.getUserId();
    }
}


const getAppointment = async (id) => {
    try {

        errorDetails.value = {};
        const response = await axiosInstance.get(`/v1/scheduler/appointments/${id}`);

        if (response.data.dataPayload && !response.data.errorPayload) {
            appointmentDetails.value = response.data.dataPayload.data;
            //convert start created at to yyyy-mm-dd
            appointmentDetails.value.created_at = globalUtils.convertToDate(appointmentDetails.value.created_at);
            selectedAppointmentId.value = id;

            //set user id depending on the user role
            setUserId();
            // console.log("user_id", slotsData.value.user_id);
        }


    } catch (error) {
        // Check if error.response is defined before accessing it
        const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An unknown error occurred';

        proxy.$showToast({
            title: 'An error occurred',
            text: errorMessage,
            icon: 'error',
        });
    }
}

// Function to open modal
const selectedAppointmentId = ref('');
const openModal = (id) => {
    const modal = proxy.$refs.myModal;  // Use proxy to access $refs
    getAppointment(id);
    selectedAppointmentId.value = id;
    modal.show();

    // console.log("selectedAppointmentId", selectedAppointmentId.value);
};

const updateAppointment = async () => {
console.log(appointmentDetails.value.checked_in);
    if (appointmentDetails.value.checked_in) {
        //show toast
        proxy.$showToast({
            title: 'Cannot update: Appointment is already checked in',
            // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
            icon: 'info',
        });

    } else {
        // Call the update function if checked_in is false
        try {
            errorDetails.value = {};

            const response = await axiosInstance.put(`/v1/scheduler/appointments/${selectedAppointmentId.value}`, appointmentDetails.value);

            // Check if toastPayload exists in the response and update it
            if (response.data.toastPayload) {
                toastPayload.value = response.data.toastPayload;

                getAppointments();
                // console.log("toastPayload", toastPayload.value); // Log for debugging

                // Show toast notification using the response data
                proxy.$showToast({
                    title: toastPayload.value.toastMessage || 'Appointment Updated successfully',
                    // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
                    icon: 'success',
                });
            } else {
                // Fallback if toastPayload is not provided in the response
                proxy.$showToast({
                    title: 'Appointment Updated successfully',
                    icon: 'success',
                });
            }

        } catch (error) {
            if (error.response && error.response.data.errorPayload) {
                errorDetails.value = error.response.data.errorPayload.errors;
            } else {
                const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An error occurred';

                proxy.$showToast({
                    title: 'An error occurred',
                    text: errorMessage,
                    icon: 'error',
                });
            }
        }
    }

};

const performSearch = async () => {
    try {
        const response = await axiosInstance.get(`v1/scheduler/appointments?_search=${searchQuery.value}`);
        tableData.value = response.data.dataPayload.data;
    } catch (error) {
        // console.error(error);
        const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An unknown error occurred';

        proxy.$showToast({
            title: 'An error occurred',
            text: errorMessage,
            icon: 'error',
        });
    }
};

// const UsersOptions = ref([]);
// const selectedUsername = ref(''); // To hold the selected username

const suggestions = ref([]);
const suggestedDate = ref('');
const timeSlots = ref([]); ``

// Function to fetch suggested slots
const suggestSlots = async (id) => {
    try {
        id = selectedAppointmentId.value;
        errorDetails.value = {};
        const response = await axiosInstance.get(`/v1/scheduler/slot-suggestion/${id}`);

        suggestions.value = response.data.dataPayload.data.suggestions;

        // Assign date and slots from the API response
        suggestedDate.value = suggestions.value.date;
        timeSlots.value = suggestions.value.slots;

        // Show a success toast message
        // Check if toastPayload exists in the response and update it
        if (response.data.toastPayload) {
            toastPayload.value = response.data.toastPayload;
            // console.log("toastPayload", toastPayload.value); // Log for debugging

            // Show toast notification using the response data
            proxy.$showToast({
                title: toastPayload.value.toastMessage || 'success',
                // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
                icon: 'success',
            });
        } else {
            // Fallback if toastPayload is not provided in the response
            proxy.$showToast({
                title: 'Operation successful',
                icon: 'success',
            });
        }
        //update date to suggessted date 
        appointmentDetails.value.appointment_date = suggestedDate;

    } catch (error) {
        // Handle errors
        errorDetails.value = error.response.data.errorPayload.errors;
        const errorMessage = response.data.errorPayload.errors?.message || errorPayload.message || 'An unknown error occurred';

        proxy.$showToast({
            title: 'An error occurred',
            text: errorMessage,
            icon: 'error',
        });

    }
};

const toggleCheckIn = async (id) => {
    try {
        const response = await axiosInstance.put(`/v1/scheduler/checkin/${id}`);
        getAppointment(id);
        getAppointments(1);

        // Check if toastPayload exists in the response and update it
        if (response.data.toastPayload) {
            toastPayload.value = response.data.toastPayload;
            // console.log("toastPayload", toastPayload.value); // Log for debugging

            // Show toast notification using the response data
            proxy.$showToast({
                title: toastPayload.value.toastMessage || 'Appointment Checked In successfully',
                // icon: toastPayload.value.toastTheme || 'success', // You can switch this back to use the theme from the response
                icon: 'success',
            });
        } else {
            // Fallback if toastPayload is not provided in the response
            proxy.$showToast({
                title: 'Appointment Checked In successfully',
                icon: 'success',
            });
        }

    } catch (error) {
        // console.error(error);
        let errorMessage = 'An error occurred';

        if (error.response && error.response.data.errorPayload) {
            // Check if errorPayload exists and has errors
            const errors = error.response.data.errorPayload.errors;
            if (errors && errors.message) {
                errorMessage = errors.message; // Use specific error message
            }
        }

        // Show toast notification for error
        proxy.$showToast({
            title: errorMessage, // Change title to be more indicative of an error
            text: errorMessage, // Show specific error message
            icon: 'error',
        });
    }
};

const confirmCheckIn = (id) => {
    selectedAppointmentId.value = id;
    proxy.$showAlert({
        title: 'Are you sure?',
        text: 'You are about to Check In this appointment. Do you want to proceed?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Check In!',
        cancelButtonText: 'close',
        confirmButtonColor: '#076232',
        cancelButtonColor: '#d33',
    }).then((result) => {
        if (result.isConfirmed) {
            toggleCheckIn(id);
            getAppointments(1);
        }
    });
};

// Function to handle actions after modal closes
const handleModalClose = () => {
    // Perform any actions you need after modal closes
    //   console.log('Modal has been closed');

    getAppointments(1);  // Refresh the appointments after closing the modal
};

// const getSlots = async () => {
//     try {
//         const response = await axiosInstance.post('/v1/scheduler/get-slots', slotsData.value);
//         // console.log("user_id", userId.value)
//         // Update the `apiResponse` ref with the response data
//         apiResponse.value = response.data.dataPayload.data.slots;
//         // Set all slots to `selected: false`
//         const slotsWithSelected = apiResponse.value.map(slot => ({
//             ...slot,
//             selected: false
//         }));
//         // Update `timeSlots`
//         timeSlots.value = slotsWithSelected;
//     } catch (error) {
//         // console.error('Error getting slots:', error);
//         if (error.response && error.response.data.errorPayload) {
//             errorDetails.value = error.response.data.errorPayload.errors;
//         } else {
//             const errorMessage = error.response?.data.errorPayload?.errors?.message || errorPayload?.message || 'An error occurred';

//             proxy.$showToast({
//                 title: 'An error occurred',
//                 text: errorMessage,
//                 icon: 'error',
//             });
//         }
//     }
// };

const apiResponse = ref([]);

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
        console.error(error);        // Handle error response if available
        if (error.response && error.response.data.errorPayload) {
            errorDetails.value = error.response.data.errorPayload.errors;
        } else {
            // Handle general error with a fallback message
            const errorMessage = error.response?.data.errorPayload?.errors?.message || 'An error occurred';

            proxy.$showToast({
                title: 'An error occurred',
                text: errorMessage,
                icon: 'error',
            });
        }
    }
};

const handleDateChange = (newValue, oldValue) => {
    slotsData.value.date = newValue;
    console.log('date changed:', newValue);

    // console.log('date changed:', newValue);
    getSlots(); searchQuery
};

//watch for changes in appointmentDetails.appointment_date and call getSlots

watch(() => appointmentDetails.value.appointment_date, (newValue, oldValue) => {
    // console.log('date changed:', newValue);
    handleDateChange(newValue, oldValue);
});

</script>
<template>

    <b-col lg="12">
        <b-card>
            <div>
                <h2>Appointments</h2>
            </div>
            <b-row class="mb-3">
                <b-col lg="12" md="12" sm="12" class="mb-3">
                    <div class="d-flex justify-content-end">
                        <b-button variant="primary" @click="showModal">
                            New Appointment
                        </b-button>
                    </div>
                </b-col>
                <b-col lg="12" md="12" sm="12">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <!-- <label for="itemsPerPage" class="me-2">Items per page:</label> -->
                            <select id="itemsPerPage" v-model="selectedPerPage" @change="updatePerPage"
                                class="form-select form-select-sm" style="width: auto;">
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center">
                            <b-input-group>
                                <!-- Search Input -->
                                <b-form-input placeholder="Search..." aria-label="Search" v-model="searchQuery" />
                                <!-- Search Button -->
                                <b-input-group-append>
                                    <b-button variant="primary" @click="performSearch">
                                        Search
                                    </b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </div>
                    </div>
                </b-col>
            </b-row>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th @click="sortTable('contact_name')">
                                Name
                                <i class="fas fa-sort"></i>
                            </th>
                            <th @click="sortTable('mobile_number')">
                                Contact
                                <i class="fas fa-sort"></i>
                            </th>
                            <th @click="sortTable('appointment_date')">
                                Date
                                <i class="fas fa-sort"></i>
                            </th>
                            <th @click="sortTable('start_time')">
                                Subject
                                <i class="fas fa-sort"></i>
                            </th>
                            <th @click="sortTable('email_address')">
                                Email
                                <i class="fas fa-sort"></i>
                            </th>
                            <th @click="sortTable('start_time')">
                                Time
                                <i class="fas fa-sort"></i>
                            </th>
                            <th v-if="CBB === 0">
                                Recipient
                            </th>
                            <th>
                                Status
                            </th>
                            <th>
                                Checked In
                            </th>
                            <th>
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="Array.isArray(tableData) && tableData.length > 0">
                            <tr v-for="(item, index) in sortedData" :key="index">
                                <td>{{ item.contact_name }}</td>
                                <td>{{ item.mobile_number }}</td>
                                <td>{{ item.appointment_date }}</td>
                                <td>{{ item.subject }}</td>
                                <td>{{ item.email_address }}</td>
                                <td>{{ item.start_time }} - {{ item.end_time }}</td>
                                <td v-if="CBB === 0">{{ item.userName }}</td>
                                <td><span :class="'badge bg-' + item.recordStatus.theme">{{ item.recordStatus.label
                                        }}</span></td>

                                <td>
                                    <div v-if="item.recordStatus.label === 'ACTIVE'" class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            id="flexSwitchCheckChecked-{{ item.id }}" :checked="item.checked_in"
                                            :disabled="item.checked_in" @change="confirmCheckIn(item.id)" />
                                        <label class="form-check-label" :for="'flexSwitchCheckChecked-' + item.id">
                                            {{ item.checked_in ? 'Checked In' : 'Check In' }}
                                        </label>
                                    </div>

                                    <div v-else="item.recordStatus.label !== 'ACTIVE'" class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckDisabled"
                                            checked="" disabled="">

                                    </div>
                                </td>

                                <td>
                                    <button
                                        v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'"
                                        class="btn btn-outline-primary btn-sm me-3" @click="openModal(item.id)">
                                        <i class="fas fa-edit" title="Edit"></i>
                                    </button>
                                    <button
                                        v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'"
                                        class="btn btn-outline-warning btn-sm me-3" @click="confirmCancel(item.id)"
                                        :disabled="item.checked_in">
                                        <i class="fas fa-cancel" title="Cancel"></i>
                                    </button>
                                    <button
                                        v-if="item.recordStatus.label !== 'DELETED' && item.recordStatus.label !== 'CANCELLED'"
                                        class="btn btn-outline-danger btn-sm me-3" @click="confirmDelete(item.id)">
                                        <i class="fas fa-trash" title="Delete"></i>
                                    </button>
                                    <button v-if="item.recordStatus.label === 'DELETED'"
                                        class="btn btn-outline-danger btn-sm" @click="confirmRestore(item.id)">
                                        <i class="fas fa-undo" title="Restore"></i>
                                    </button>
                                    <button v-if="item.recordStatus.label === 'CANCELLED'"
                                        class="btn btn-outline-exclamation-circle btn-sm">
                                        <i class="fas fa-undo"
                                            title="appointment has been cancelled no further action can be done"></i>
                                    </button>

                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="5" class="text-center">
                                No data to display
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination -->

            </div>
            <b-col sm="12" lg="12" md="12" class="d-flex justify-content-end mt-5 mb-n5">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end">
                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <button class="page-link" @click="goToPage(currentPage - 1)"
                                :disabled="currentPage === 1">Previous</button>
                        </li>
                        <li v-for="page in totalPages" :key="page" class="page-item"
                            :class="{ active: currentPage === page }">
                            <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                        </li>
                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                            <button class="page-link" @click="goToPage(currentPage + 1)"
                                :disabled="currentPage === totalPages">Next</button>
                        </li>
                    </ul>
                </nav>
            </b-col>
        </b-card>
    </b-col>
    <BookAppointment ref="appointmentModal" @close="handleModalClose" />

    <b-modal ref="myModal" hide-footer title="Appointment Details" size="xl">
        <b-row class="justify-content-center">
            <b-col lg="12" sm="12">
                <b-card-body>
                    <b-form>
                        <b-form-group>
                            <b-row>
                                <b-col lg="12" md="12" class="mb-3">
                                    <label for="input-107" class="form-label">Date</label>
                                    <b-form-input v-model="appointmentDetails.appointment_date" id="input-107"
                                        type="date"></b-form-input>
                                    <div v-if="errorDetails.appointment_date" class="error">
                                        {{ errorDetails.appointment_date }}</div>
                                </b-col>

                                <b-col md="12" lg="12" sm="12">
                                    <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots"
                                        @selectedSlotsTimes="handleSelectedSlotsTimes" />
                                </b-col>

                                <b-col lg="12" md="12" class="mb-3">
                                    <label for="input-107" class="form-label">Subject</label>
                                    <b-form-input v-model="appointmentDetails.subject" id="input-107"></b-form-input>
                                    <div v-if="errorDetails.subject" class="error">
                                        {{ errorDetails.subject }}</div>
                                </b-col>

                                <b-col lg="12" md="12" class="mb-3">
                                    <label for="input-107" class="form-label">Appointment Type</label>
                                    <b-form-input v-model="appointmentDetails.appointment_type"
                                        id="input-107"></b-form-input>
                                    <div v-if="errorDetails.appointment_type" class="error">
                                        {{ errorDetails.appointment_type }}</div>
                                </b-col>

                                <b-col lg="12" md="12" class="mb-3">
                                    <label for="input-107" class="form-label">Booked on</label>
                                    <b-form-input v-model="appointmentDetails.created_at" id="input-107"></b-form-input>
                                    <div v-if="errorDetails.created_at" class="error">
                                        {{ errorDetails.created_at }}</div>
                                </b-col>

                                <b-col lg="8" md="8" class="mb-3">
                                    <label for="status-badge" class="form-label">Status</label>
                                    <div id="status-badge" class="d-flex align-items-center">
                                        <b-badge :variant="getBadgeVariant(appointmentDetails.statusLabel)"
                                            class="me-3">
                                            {{ appointmentDetails.statusLabel }}
                                        </b-badge>

                                        <b-button v-if="appointmentDetails.status === 3" variant="success"
                                            @click="suggestSlots">Suggest Open
                                            slots</b-button>
                                    </div>
                                </b-col>

                                <b-col lg="5" class="mb-3">
                                    <div v-if="appointmentDetails.status === 3">
                                        <div v-if="timeSlots.length > 1">
                                            <h3>{{ suggestedDate }}</h3> <!-- Display the date from the API response -->
                                            <div>
                                                <TimeSlotComponent :timeSlots="timeSlots"
                                                    @update:timeSlots="updateTimeSlots"
                                                    @selectedSlotsTimes="handleSelectedSlotsTimes" />
                                            </div>
                                        </div>
                                        <div v-else>
                                            <p>No available slots.</p>
                                            <!-- Handle case where there are no suggestions -->
                                        </div>
                                    </div>
                                </b-col>


                                <b-row class="m-5">
                                    <b-col>
                                        <div class="d-flex justify-content-center">
                                            <b-button variant="primary" class="me-3" @click="updateAppointment"
                                                ref="updateButton">
                                                Update
                                            </b-button>
                                        </div>

                                    </b-col>
                                </b-row>
                            </b-row>
                        </b-form-group>
                    </b-form>
                </b-card-body>
            </b-col>
        </b-row>
    </b-modal>

</template>
<style scoped>
.error {
    color: red;
}
</style>