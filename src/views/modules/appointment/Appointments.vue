<script setup>
import { onMounted, ref, getCurrentInstance, computed } from 'vue';
import AxiosInstance from '@/api/axios';
import BookAppointment from '@/components/modules/appointment/partials/BookAppointment.vue';


const appointmentModal = ref(null);
const showModal = () => {
    appointmentModal.value.$refs.appointmentModal.show();
};

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
    statusLabel: '',
    user_id: '212409004',

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
    appointmentData.value.start_time = selectedTimes?.startTime || '';
    appointmentData.value.end_time = selectedTimes?.endTime || '';
};

const timeSlots = ref([]);
const responseData = {
    "date": "2024-09-09",
    "slots": [
        { "startTime": "08:00", "endTime": "09:00" },
        { "startTime": "09:00", "endTime": "10:00" },
        { "startTime": "10:00", "endTime": "11:00" },
        { "startTime": "11:00", "endTime": "12:00" },
        { "startTime": "12:00", "endTime": "13:00" },
        { "startTime": "13:00", "endTime": "14:00" },
        { "startTime": "14:00", "endTime": "15:00" },
        { "startTime": "15:00", "endTime": "16:00" },
        { "startTime": "16:00", "endTime": "17:00" },
        { "startTime": "17:00", "endTime": "18:00" },
        { "startTime": "18:00", "endTime": "19:00" },
        { "startTime": "19:00", "endTime": "20:00" },
        { "startTime": "20:00", "endTime": "21:00" },
        { "startTime": "21:00", "endTime": "22:00" },
        { "startTime": "22:00", "endTime": "23:00" },
        { "startTime": "23:00", "endTime": "00:00" }
    ],
};

timeSlots.value = responseData.slots;

const dateSlots = ref([responseData]);

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
        default:
            return 'badge bg-secondary';
    }
};

const getBadgeVariant = (statusLabel) => {
    switch (statusLabel) {
        case 'Rescheduled':
            return 'warning';
        case 'Active':
            return 'success';
        case 'Cancelled':
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

const confirmCancel = (id) => {
    console.log("id", id);
    id = selectedAppointmentId.value;

    console.log("selectedAppointmentId", selectedAppointmentId.value);
    console.log("id2", id)

    proxy.$showAlert({
        title: 'Are you sure?',
        text: 'You are about to cancel this appointment. Do you want to proceed?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
    }).then((result) => {
        if (result.isConfirmed) {
            cancelAppointment(id);
        }
    });
};

const cancelAppointment = async (id) => {

    try {
        const response = await axiosInstance.delete(`v1/scheduler/appointments/${id}`);
        getAppointment(id);

        proxy.$showToast({
            title: 'Appointment cancelled',
            icon: 'success',
        })
    } catch (error) {
        console.error(error);
    }
};

const getAppointments = async (page = 1) => {
    try {
        const response = await axiosInstance.get('v1/scheduler/appointments?page=${page}&perPage=${selectedPerPage.value}');
        //check if data is array
        isArray.value = Array.isArray(response.data);
        tableData.value = response.data.dataPayload.data;

        // Update pagination data
        currentPage.value = response.data.dataPayload.currentPage;
        totalPages.value = response.data.dataPayload.totalPages;
        perPage.value = response.data.dataPayload.perPage;

    } catch (error) {
        console.error(error);
    }
};

const getAppointment = async (id) => {
    try {
        errorDetails.value = {};
        const response = await axiosInstance.get(`/v1/scheduler/appointments/${id}`);

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

// Function to open modal
const selectedAppointmentId = ref('');
const openModal = (id) => {
    const modal = proxy.$refs.myModal;  // Use proxy to access $refs
    getAppointment(id);
    selectedAppointmentId.value = id;
    modal.show();

    console.log("selectedAppointmentId", selectedAppointmentId.value);
};

const updateAppointment = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.put(`/v1/scheduler/appointments/${selectedAppointmentId.value}`, appointmentDetails.value);

        if (response.data.dataPayload && !response.data.errorPayload) {
            proxy.$showAlert({
                title: 'Success',
                text: 'Appointment updated successfully!',
                icon: 'success',
            });
            getAppointment(selectedAppointmentId.value);
        }

    } catch (error) {
        proxy.$showAlert({
            title: 'Failed',
            text: 'It seems there was an error updating your appointment. Please try again!',
            icon: 'danger',
        });
        errorDetails.value = error.response.data.errorPayload.errors;
    }
};

const restoreAppointment = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.put(`/v1/scheduler/appointments/`);

        if (response.data.dataPayload && !response.data.errorPayload) {
            proxy.$showAlert({
                title: 'Success',
                text: 'Appointment restored successfully!',
                icon: 'success',
            });
            getAppointment();
        }

    } catch (error) {
        proxy.$showAlert({
            title: 'Failed',
            text: 'It seems there was an error restoring your appointment. Please try again!',
            icon: 'danger',
        });
        errorDetails.value = error.response.data.errorPayload.errors;
    }
};

const suggestSlots = async () => {
    try {
        errorDetails.value = {};

        const response = await axiosInstance.get(`/v1/scheduler/appointments/suggest-available-slots`);

        if (response.data.dataPayload && !response.data.errorPayload) {
        }

    } catch (error) {
        console.log(error);
        // errorDetails.value = error.response.data.errorPayload.errors;
    }
}

</script>
<template>
    <div>
        <h2>Appointments</h2>
    </div>
    <b-col lg="12">
        <b-card>
            <div class="table-responsive">
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
                                <label for="itemsPerPage" class="me-2">Items per page:</label>
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
                                Time
                                <i class="fas fa-sort"></i>
                            </th>
                            <th>
                                Status
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
                                <td>{{ item.start_time }} - {{ item.end_time }}</td>
                                <td><span class="badge" :class="getStatusClass(item.statusLabel)">{{ item.statusLabel
                                        }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm me-3"
                                        @click="openModal(item.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" @click="confirmCancel(item.id)">
                                        <i class="fas fa-cancel"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Pagination -->
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

                        </template>
                        <tr v-else>
                            <td colspan="5" class="text-center">
                                No data to display
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </b-card>
    </b-col>
    <BookAppointment ref="appointmentModal" />

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

                            <b-col lg="6" md="6" class="mb-3">
                                <label for="input-107" class="form-label">Start Time</label>
                                <b-form-input v-model="appointmentDetails.start_time" id="input-107"></b-form-input>
                                <div v-if="errorDetails.start_time" class="error">
                                    {{ errorDetails.start_time }}</div>
                            </b-col>
                            
                            <b-col lg="6" md="6" class="mb-3">
                                <label for="input-107" class="form-label">End Time</label>
                                <b-form-input v-model="appointmentDetails.end_time" id="input-107"></b-form-input>
                                <div v-if="errorDetails.end_time" class="error">
                                    {{ errorDetails.end_time }}</div>
                            </b-col>

                            <b-col lg="12" md="12" class="mb-3">
                                <label for="input-107" class="form-label">Subject</label>
                                <b-form-input v-model="appointmentDetails.subject" id="input-107"></b-form-input>
                                <div v-if="errorDetails.subject" class="error">
                                    {{ errorDetails.subject }}</div>
                            </b-col>
                            
                            <b-col lg="12" md="12" class="mb-3">
                                <label for="input-107" class="form-label">Appointment Type</label>
                                <b-form-input v-model="appointmentDetails.appointment_type" id="input-107"></b-form-input>
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
                                    <b-badge :variant="getBadgeVariant(appointmentDetails.statusLabel)" class="me-3">
                                        {{ appointmentDetails.statusLabel }}
                                    </b-badge>
                                    <b-button v-if="appointmentDetails.status === 1" variant="danger" @click="confirmCancel">
                                        Cancel
                                    </b-button>
                                    <b-button v-else-if="appointmentDetails.status === 2" variant="success" @click="restoreAppointment">
                                        Restore Appointment
                                    </b-button>
                                    <b-button v-else variant="success" @click="suggestSlots">Suggest Open slots</b-button>
                                </div>
                            </b-col>
                            
                            <b-col lg="5" class="mb-3">
                                <div v-if="appointmentDetails.status === 3">
                                    <div v-for="(dateEntry, index) in dateSlots" :key="index">
                                        <h3>{{ dateEntry.date }}</h3>
                                        <div>
                                            <TimeSlotComponent :timeSlots="timeSlots" @update:timeSlots="updateTimeSlots"
                                                @selectedSlotsTimes="handleSelectedSlotsTimes" />
                                        </div>
                                    </div>
                                </div>
                            </b-col>
                            
                            <b-row class="m-5">
                                <b-col>
                                    <div class="d-flex justify-content-center">
                                        <b-button variant="primary" class="me-1" @click="updateAppointment">Update</b-button>
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