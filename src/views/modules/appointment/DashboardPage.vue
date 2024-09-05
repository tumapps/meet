<script setup>
import { ref, onMounted, computed } from 'vue'
import AxiosInstance from '@/api/axios'
import CountUp from 'vue-countup-v3'
import { useRouter } from 'vue-router';

const router = useRouter();
const axiosInstance = AxiosInstance();
const tableData = ref([]);
const sortKey = ref('');  // Active column being sorted
const sortOrder = ref('asc');  // Sorting order: 'asc' or 'desc'
const isArray = ref(false);
const addAppointment = () => {
    router.push('/book-appointment');
}

// Handle sorting
const sortTable = (key) => {
    if (sortKey.value === key) {
        // Toggle the sort order if the same column is clicked again
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

// Dummy methods for the action buttons


// Get class for status badges
const getStatusClass = (status) => {
    switch (status) {
        case 'Pending':
            return 'badge bg-warning';
        case 'Active':
            return 'badge bg-success';
        case 'Cancelled':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
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
    getAppointment();
});

const timeline = ref([])
//get from api
const getAppointment = async () => {
    try {
        const response = await axiosInstance.get('v1/scheduler/appointments');
        //check if data is array
        isArray.value = Array.isArray(response.data);
        tableData.value = response.data.dataPayload.data;
        timeline.value = response.data.dataPayload.data;
        console.log("data", tableData.value);
    } catch (error) {
        console.error(error);
    }

};
//cancel appointment
const cancelAppointment = async (id) => {
    try {
        const response = await axiosInstance.delete(`v1/scheduler/appointments/${id}`);
        console.log(response);
        getAppointment();
        alert('Appointment cancelled successfully');
    } catch (error) {
        console.error(error);
    }
};

const username = localStorage.getItem('user.username');
const goToPage = (id) => {
    router.push({ name: 'appointment', params: { id } }); // Navigate with dynamic id
};
// data table end
</script>
<template>
    <b-row>
        <b-col lg="8" md="8">
            <b-container fluid class="iq-container">
                <b-row>
                    <b-col md="12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1 class="mb-3">Hello!</h1>
                                <p>Experience a simple yet powerful way to build Dashboards with HOPE UI.</p>
                            </div>
                            <div>
                                <a href="#" class="btn btn-primary">
                                    <svg class="icon-20 me-1" width="20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.8251 15.2171H12.1748C14.0987 15.2171 15.731 13.985 16.3054 12.2764C16.3887 12.0276 16.1979 11.7713 15.9334 11.7713H14.8562C14.5133 11.7713 14.2362 11.4977 14.2362 11.16C14.2362 10.8213 14.5133 10.5467 14.8562 10.5467H15.9005C16.2463 10.5467 16.5263 10.2703 16.5263 9.92875C16.5263 9.58722 16.2463 9.31075 15.9005 9.31075H14.8562C14.5133 9.31075 14.2362 9.03619 14.2362 8.69849C14.2362 8.35984 14.5133 8.08528 14.8562 8.08528H15.9005C16.2463 8.08528 16.5263 7.8088 16.5263 7.46728C16.5263 7.12575 16.2463 6.84928 15.9005 6.84928H14.8562C14.5133 6.84928 14.2362 6.57472 14.2362 6.23606C14.2362 5.89837 14.5133 5.62381 14.8562 5.62381H15.9886C16.2483 5.62381 16.4343 5.3789 16.3645 5.13113C15.8501 3.32401 14.1694 2 12.1748 2H11.8251C9.42172 2 7.47363 3.92287 7.47363 6.29729V10.9198C7.47363 13.2933 9.42172 15.2171 11.8251 15.2171Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.4"
                                            d="M19.5313 9.82568C18.9966 9.82568 18.5626 10.2533 18.5626 10.7823C18.5626 14.3554 15.6186 17.2627 12.0005 17.2627C8.38136 17.2627 5.43743 14.3554 5.43743 10.7823C5.43743 10.2533 5.00345 9.82568 4.46872 9.82568C3.93398 9.82568 3.5 10.2533 3.5 10.7823C3.5 15.0873 6.79945 18.6413 11.0318 19.1186V21.0434C11.0318 21.5715 11.4648 22.0001 12.0005 22.0001C12.5352 22.0001 12.9692 21.5715 12.9692 21.0434V19.1186C17.2006 18.6413 20.5 15.0873 20.5 10.7823C20.5 10.2533 20.066 9.82568 19.5313 9.82568Z"
                                            fill="currentColor"></path>
                                    </svg>
                                    Announcements
                                </a>
                            </div>
                        </div>
                    </b-col>
                </b-row>
            </b-container>
            <div class="iq-header-img mt-3">
                <img src="@/assets/images/dashboard/top-header.png" alt="header" class="img-fluid w-100 h-100" />
            </div>
            <b-card>
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="profile-img position-relative me-3 mb-3 mb-lg-0 profile-logo profile-logo1">
                            <img src="@/assets/images/avatars/01.png" alt="User-Profile"
                                class="img-fluid rounded-pill avatar-100" />
                        </div>
                        <div class="d-flex flex-wrap align-items-center">
                            <h4 class="me-2 h4 mb-0">{{ username }}</h4>
                            <span class="text-muted"> - Web Developer</span>
                        </div>
                    </div>
                </div>
            </b-card>
            <b-row class="d-flex flex-nowrap  overflow-auto">
                <b-card class="me-2 statistics-card">
                    <div class="text-center">AVG Engagements Rate</div>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div>
                            <h2><vue3-autocounter ref="counter" :startAmount='0' :endAmount='2.648' :duration="3" decimalSeparator='.' :decimals='2' :autoinit='true'/></h2>

                        </div>
                        <div class="border bg-info-subtle rounded p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <b-progress :max="0" class="progress bg-info-subtle shadow-none w-100" height="6px">
                            <b-progress-bar :value="70" variant="info"></b-progress-bar>
                        </b-progress>
                    </div>
                </b-card>

                <b-card class="me-2 statistics-card">
                    <div class="text-center">AVG Engagements Rate</div>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div>
                            <h2><vue3-autocounter ref="counter" :startAmount='0' :endAmount='2.648' :duration="3" decimalSeparator='.' :decimals='2' :autoinit='true'/></h2>
                        </div>
                        <div class="border bg-info-subtle rounded p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <b-progress :max="0" class="progress bg-info-subtle shadow-none w-100" height="6px">
                            <b-progress-bar :value="70" variant="info"></b-progress-bar>
                        </b-progress>
                    </div>
                </b-card>
                <b-card class="me-2 statistics-card">
                    <div class="text-center">Cancelled </div>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div>
                            <h2><vue3-autocounter ref="counter" :startAmount='0' :endAmount='2.648' :duration="3" decimalSeparator='.' :decimals='2' :autoinit='true'/></h2>
                        </div>
                        <div class="border bg-info-subtle rounded p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <b-progress :max="0" class="progress bg-info-subtle shadow-none w-100" height="6px">
                            <b-progress-bar :value="70" variant="info"></b-progress-bar>
                        </b-progress>
                    </div>
                </b-card>

            </b-row>
        </b-col>

        <b-col lg="4" md="4" sm="12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4>Upcoming Appointments</h4>
                        <p class="mb-0 text-muted">
                            <svg class="me-2" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#17904b"
                                    d="M13,20H11V8L5.5,13.5L4.08,12.08L12,4.16L19.92,12.08L18.5,13.5L13,8V20Z"></path>
                            </svg>
                        </p>
                    </div>
                </div>
                <div class="card-body card-body-scrollable">
                    <div class="d-flex profile-media align-items-top mb-3" v-for="(item, index) in timeline"
                        :key="index">
                        <div class="profile-dots-pills border-primary"></div>
                        <div class="ms-4">
                            <h6 class="mb-1">
                                {{ item.contact_name }} - <span class="text-primary">{{ item.appointment_type }}</span>
                            </h6>
                            <span class="text-muted">{{ item.start_time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </b-col>
    </b-row>
    <!-- <b-row class="m-3 w-100">
        <b-col lg="12">
            <div class="mb-5 mt-3 bg-white h-75 rounded">
                <div class="d-flex justify-content-between align-items-center py-5">
                    <!-- Search Box with Icon -
                    <div class="flex mx-auto position-relative w-25">
                        <input type="text" v-model="searchQuery" class="form-control rounded-pill ps-5"
                            placeholder="Search..." />
                        <i
                            class="fas fa-search position-absolute top-50 start-0 translate-middle-y text-muted ps-3"></i>
                    </div>

                    <!-- Add Appointment Button --
                    <button class="btn btn-outline-primary rounded-pill mx-5" @click="addAppointment">
                        + Appointment
                    </button>
                </div>
            </div>
        </b-col>
    </b-row>
    <b-col lg="12">
        <b-card body-class="px-0">
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
                                    <button type="button" class="btn btn-outline-primary btn-sm" @click="goToPage(item.id)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <!-- <button class="btn btn-outline-warning btn-sm" @click="editAppointment(item)">
                                        <i class="fas fa-edit"></i>
                                    </button> --
                                    <button class="btn btn-outline-danger btn-sm" @click="cancelAppointment(item.id)">
                                        <i class="fas fa-trash"></i>
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
</div>
</b-card>

</b-col> -->
</template>

<style scoped>
.action-icons a {
    font-size: 18px;
    cursor: pointer;
}

.borderless-btn {
    border: none !important;
    box-shadow: none;
}

h1 {
    font-size: 2rem;
}

.profile-img img {
    width: 100px;
    height: 100px;
}

.card-header h4 {
    font-size: 1.25rem;
}

.table th {
    cursor: pointer;
}

.table td {
    vertical-align: middle;
}

.btn {
    margin-right: 0.5rem;
    /* Adds gap between buttons */
}

.card-body-scrollable {
    overflow-y: auto;
}

/* For extra large screens (xl) */
@media (min-width: 1200px) {
    .card-body-scrollable {
        max-height: 49vh !important;
    }
}

/* For large screens (lg) */
@media (min-width: 992px) {
    .card-body-scrollable {
        max-height: 49vh!important;
    }
}

/* For medium screens (md) */
@media (min-width: 768px) {
    .card-body-scrollable {
        max-height: 52vh!important;
    }
}
.statistics-card {
    max-width: 33%;
}
</style>
