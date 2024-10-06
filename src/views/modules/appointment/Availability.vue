<script setup>
import { onMounted, ref, getCurrentInstance, computed } from 'vue';
import AxiosInstance from '@/api/axios';
import FlatPickr from 'vue-flatpickr-component';


// Modal reference
const newAvailability = ref(null);
const updateAvailability = ref(null);

//get user id from session storage

const user_id = ref(sessionStorage.getItem('user_id'));
// Selected date
const availabilityDetails = ref({
    user_id: user_id.value,
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    description: ''
});
// Flatpickr config
const config = {
    enableTime: false,
    // noCalendar: true,
    dateFormat: 'Y-m-d',

};

const config2 = {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'H:i',
    time_24hr: true,
    minuteIncrement: 10,
};




// Function to show the modal
const showModal = () => {
    if (newAvailability.value) {
        newAvailability.value.show();
    }
};

const openModal = (id) => {
    if (updateAvailability.value) {
        getAvailability(id);
        updateAvailability.value.show();
    }
}


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
const selectedAvailability = ref('');
const errors = ref({});

const goToPage = (page) => {
    if (page > 0 && page <= totalPages.value) {
        currentPage.value = page;
        getAppointments(page);
    }
};

const updatePerPage = async () => {
    currentPage.value = 1;
    await getAppointments(1);
};

const sortTable = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

const performSearch = async () => {
    try {
        const response = await axiosInstance.get(`v1/scheduler/availabilities?search=${searchQuery.value}`);
        isArray.value = Array.isArray(response.data);
        tableData.value = response.data.dataPayload.data;
    } catch (error) {
        // console.error(error);
        proxy.$showToast({
            title: 'An error occurred ',
            text: 'Ooops! an error occured',
            icon: 'error',
        });
    }
};

onMounted(async () => {
    getAvailabilities(1);
});

const confirmCancel = (id) => {
    selectedAvailability.value = id;

    proxy.$showAlert({
        title: 'Are you sure?',
        text: 'You are about to cancel this entry. Do you want to proceed?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
    }).then((result) => {
        if (result.isConfirmed) {
            cancelAvailability(id);
            getAvailabilities(1);
        }
    });
};

const cancelAvailability = async (id) => {
    try {
        const response = await axiosInstance.delete(`v1/scheduler/availability/${id}`);
        proxy.$showToast({
            title: 'Appointment cancelled',
            icon: 'success',
        });
        getAvailabilities(1);
    } catch (error) {
        // console.error(error);
        proxy.$showToast({
            title: 'An error occurred ',
            text: 'Ooops! an error occured',
            icon: 'error',
        });
    }
};

const getAvailabilities = async (page = 1) => {
    try {
        const response = await axiosInstance.get(`v1/scheduler/availabilities?page=${page}&perPage=${selectedPerPage.value}`);
        isArray.value = Array.isArray(response.data);
        tableData.value = response.data.dataPayload.data;
        currentPage.value = response.data.dataPayload.currentPage;
        totalPages.value = response.data.dataPayload.totalPages;
        perPage.value = response.data.dataPayload.perPage;
    } catch (error) {
        // console.error(error);
        proxy.$showToast({
            title: 'An error occurred ',
            text: 'Ooops! an error occured',
            icon: 'error',
        });
    }
};

const getAvailability = async (id) => {
    try {
        const response = await axiosInstance.get(`v1/scheduler/availability/${id}`);
        availabilityDetails.value = response.data.dataPayload.data;

    } catch (error) {
        // console.error(error);
        proxy.$showToast({
            title: 'An error occurred ',
            text: 'Ooops! an error occured',
            icon: 'error',
        });
    }
};


const sortedData = computed(() => {
    return [...tableData.value].sort((a, b) => {
        let modifier = sortOrder.value === 'asc' ? 1 : -1;
        if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier;
        if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier;
        return 0;
    });
});

// Function to save settings (add API or local storage logic here)
const saveAvailability = async () => {
    try {
        const response = await axiosInstance.post('v1/scheduler/availability', availabilityDetails.value);

        proxy.$showToast({
            title: 'Updated',
            text: 'settings updated successfully!',
            icon: 'success',
        });

        getAvailabilities(1);
    } catch (error) {
// check for errorPayload in response
        if (error.response.data.errorPayload) {
            errors.value = error.response.data.errorPayload.errors;
            console.log(errors.value);
        } else {
            proxy.$showToast({
                title: 'Failed',
                text: 'an error occurred',
                icon: 'error',
            });
        }

    }
}

const updateAvailabilityDetails = async () => {
    try {
        const response = await axiosInstance.put(`v1/scheduler/availability/${id}`, availabilityDetails.value)
        // console.log(response);


        proxy.$showToast({
            title: 'success',
            text: 'updated successfully',
            icon: 'success'
        })
    } catch (error) {
        // check for errorPayload in response
        if (error.response.data.errorPayload) {
            errors.value = error.response.data.errorPayload.errors;
            console.log(errors.value);
        } else {
            proxy.$showToast({
            title: 'Failed',
            text: 'failed to update your settings',
            icon: 'error',
        });
        }

    }
}
</script>

<template>
    <!-- Modal Component -->

    <b-col lg="12">
        <b-card>
            <div>
                <h2>Availability</h2>
            </div>
            <b-row class="mb-3">
                <b-col lg="12" class="mb-3">
                    <div class="d-flex justify-content-end">
                        <b-button variant="primary" @click="showModal">
                            New Availability
                        </b-button>
                    </div>
                </b-col>
                <b-col lg="12">
                    <!-- Table Controls -->
                    <div class="d-flex justify-content-between">
                        <!-- Pagination Controls -->
                        <div class="d-flex align-items-center">
                            <label for="itemsPerPage" class="me-2">Items per page:</label>
                            <select id="itemsPerPage" v-model="selectedPerPage" @change="updatePerPage"
                                class="form-select form-select-sm">
                                <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}
                                </option>
                            </select>
                        </div>

                        <!-- Search Box -->
                        <div class="d-flex align-items-center">
                            <b-input-group>
                                <b-form-input placeholder="Search..." v-model="searchQuery" />
                                <b-input-group-append>
                                    <b-button variant="primary" @click="performSearch">Search</b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </div>
                    </div>
                </b-col>
            </b-row>
            <!-- Table Data -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th @click="sortTable('description')">Description <i class="fas fa-sort"></i></th>
                            <th @click="sortTable('start_date')">Start Date <i class="fas fa-sort"></i></th>
                            <th @click="sortTable('end_date')">End Date <i class="fas fa-sort"></i></th>
                            <th @click="sortTable('start_time')">Start Time <i class="fas fa-sort"></i></th>
                            <th @click="sortTable('end_time')">End Time <i class="fas fa-sort"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="Array.isArray(tableData) && tableData.length > 0">
                            <tr v-for="(item, index) in sortedData" :key="index">
                                <td>{{ item.description }}</td>
                                <td>{{ item.start_date }}</td>
                                <td>{{ item.end_date }}</td>
                                <td>{{ item.start_time }}</td>
                                <td>{{ item.end_time }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm me-3"
                                        @click="openModal(item.id)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm"
                                        @click="confirmCancel(item.id)">
                                        <i class="fas fa-cancel"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td colspan="6" class="text-center">No records found.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <b-col sm="12" lg="12" class="mt-5 d-flex justify-content-end mb-n5">
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

    <!-- //modal -->
    <b-modal ref="newAvailability" title="New Availability" class="modal-fullscreen my-modal" no-close-on-backdrop
        no-close-on-esc size="xl" hide-footer>
        <h4>Availability Settings</h4>
        <b-row>
            <b-col md="12">
                <div class="mb-3">
                    <label for="startDatePicker" class="form-label">Start Date</label>
                    <flat-pickr v-model="availabilityDetails.start_date" class="form-control" :config="config"
                        id="startDatePicker" />
                </div>
                <div v-if="errors.start_date" class="error" aria-live="polite">{{ errors.start_date }}</div>
            </b-col>
            <b-col md="12">
                <div class="mb-3">
                    <label for="endDatePicker" class="form-label">Last Date</label>
                    <flat-pickr v-model="availabilityDetails.end_date" class="form-control" :config="config"
                        id="endDatePicker" />
                </div>
                <div v-if="errors.end_date" class="error" aria-live="polite">{{ errors.end_date }}</div>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="6">
                <div class="mb-3">
                    <label for="startTimePicker" class="form-label">Start Time</label>
                    <flat-pickr v-model="availabilityDetails.start_time" class="form-control" :config="config2"
                        id="startTimePicker" />
                </div>
                <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
            </b-col>
            <b-col md="5">
                <div class="mb-3">
                    <label for="endTimePicker" class="form-label">End Time</label>
                    <flat-pickr v-model="availabilityDetails.end_time" class="form-control" :config="config2"
                        id="endTimePicker" />
                </div>
                <div v-if="errors.end_time" class="error" aria-live="polite">{{ errors.end_time }}</div>
            </b-col>
            <b-col md="12" class="mb-3">
                <b-form-group label="Description" label-for="descriptionField">
                    <b-form-input id="descriptionField" type="text"
                        v-model="availabilityDetails.description"></b-form-input>
                    <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
                </b-form-group>
            </b-col>
        </b-row>
        <div class="d-flex justify-content-end">
            <b-button @click="saveAvailability" variant="primary">Save</b-button>
        </div>
    </b-modal>

    <b-modal ref="updateAvailability" title="update Availability" class="modal-fullscreen my-modal" no-close-on-backdrop
        no-close-on-esc size="xl" hide-footer>
        <h4>Availability Settings</h4>
        <b-row>
            <b-col md="12">
                <div class="mb-3">
                    <label for="startDatePicker" class="form-label">Start Date</label>
                    <flat-pickr v-model="availabilityDetails.start_date" class="form-control" :config="config"
                        id="startDatePicker" />
                </div>
                <div v-if="errors.start_date" class="error" aria-live="polite">{{ errors.start_date }}</div>
            </b-col>
            <b-col md="12">
                <div class="mb-3">
                    <label for="endDatePicker" class="form-label">Last Date</label>
                    <flat-pickr v-model="availabilityDetails.end_date" class="form-control" :config="config"
                        id="endDatePicker" />
                </div>
                <div v-if="errors.end_date" class="error" aria-live="polite">{{ errors.end_date }}</div>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="6">
                <div class="mb-3">
                    <label for="startTimePicker" class="form-label">Start Time</label>
                    <flat-pickr v-model="availabilityDetails.start_time" class="form-control" :config="config2"
                        id="startTimePicker" />
                </div>
                <div v-if="errors.start_time" class="error" aria-live="polite">{{ errors.start_time }}</div>
            </b-col>
            <b-col md="5">
                <div class="mb-3">
                    <label for="endTimePicker" class="form-label">End Time</label>
                    <flat-pickr v-model="availabilityDetails.end_time" class="form-control" :config="config2"
                        id="endTimePicker" />
                </div>
                <div v-if="errors.end_time" class="error" aria-live="polite">{{ errors.end_time }}</div>
            </b-col>
            <b-col md="12" class="mb-3">
                <b-form-group label="Description" label-for="descriptionField">
                    <b-form-input id="descriptionField" type="text"
                        v-model="availabilityDetails.description"></b-form-input>
                    <div v-if="errors.description" class="error" aria-live="polite">{{ errors.description }}</div>
                </b-form-group>
            </b-col>
        </b-row>
        <div class="d-flex justify-content-end">
            <b-button @click="updateAvailabilityDetails" variant="primary">Update</b-button>
        </div>
    </b-modal>

</template>
<style scoped>
.error {
    color: red;
    font-size: 0.8rem;
}
</style>