<template>
    <b-row>
        <b-col lg="8">
            <b-container fluid class="iq-container">
                <b-row>
                    <b-col md="12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1>Good {Morning}!</h1>
                                <p>Experience a simple yet powerful way to build Dashboards with HOPE UI.</p>
                            </div>
                            <div>
                                <a href="" class="btn btn-link btn-primary-subtle">
                                    <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
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
            <div class="iq-header-img">
                <img src="@/assets/images/dashboard/top-header.png" alt="header"
                    class="theme-color-default-img img-fluid w-100 h-100" />
            </div>
            <b-card class="mt-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="profile-img position-relative me-3 mb-3 mb-lg-0 profile-logo profile-logo1">
                            <img src="@/assets/images/avatars/01.png" alt="User-Profile"
                                class="theme-color-default-img img-fluid rounded-pill avatar-100" loading="lazy" />
                            <img src="@/assets/images/avatars/avtar_1.png" alt="User-Profile"
                                class="theme-color-purple-img img-fluid rounded-pill avatar-100" loading="lazy" />
                            <img src="@/assets/images/avatars/avtar_2.png" alt="User-Profile"
                                class="theme-color-blue-img img-fluid rounded-pill avatar-100" loading="lazy" />
                            <img src="@/assets/images/avatars/avtar_4.png" alt="User-Profile"
                                class="theme-color-green-img img-fluid rounded-pill avatar-100" loading="lazy" />
                            <img src="@/assets/images/avatars/avtar_5.png" alt="User-Profile"
                                class="theme-color-yellow-img img-fluid rounded-pill avatar-100" loading="lazy" />
                            <img src="@/assets/images/avatars/avtar_3.png" alt="User-Profile"
                                class="theme-color-pink-img img-fluid rounded-pill avatar-100" loading="lazy" />
                        </div>
                        <div class="d-flex flex-wrap align-items-center mb-3 mb-sm-0">
                            <h4 class="me-2 h4">Austin Robertson</h4>
                            <span> - Web Developer</span>
                        </div>
                    </div>
                </div>
            </b-card>
        </b-col>
        <b-col lg="4" md="6" sm="12">
            <div class="card">
                <div class="flex-wrap card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4>Upcoming Appointments</h4>
                        <p class="mb-0">
                            <svg class="me-2" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="#17904b"
                                    d="M13,20H11V8L5.5,13.5L4.08,12.08L12,4.16L19.92,12.08L18.5,13.5L13,8V20Z"></path>
                            </svg>
                            16% this month
                        </p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex profile-media align-items-top" v-for="(item, index) in timeline" :key="index">
                        <div class="mt-1 profile-dots-pills border-primary"></div>
                        <div class="ms-4">
                            <h6 class="mb-1">
                                {{ item.name }} - <span class="text-primary">{{ item.position }}</span>
                            </h6>
                            <span class="mb-0">{{ item.time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </b-col>
    </b-row>
    <b-col lg="12">
        <b-card body-class="px-0">
            <div class="table-responsive">
                <table id="datatable" ref="inputTableRef" class="table dataTable" data-toggle="data-table">

                </table>
            </div>
        </b-card>
    </b-col>

</template>
<script setup>
import { ref, onMounted } from 'vue'
import AxiosInstance from '@/plugins/axios.js'
import useDataTable from '../../../hooks/useDatatable.js'



const timeline = ref([
    { name: 'Cameron Williamson', position: 'Dentist', time: '11 Jul 8:10 PM' },
    { name: 'Brooklyn Simmons', position: 'Orthopedic', time: '11 Jul 11 PM' },
    { name: 'Leslie Alexander', position: 'Neurology', time: '11 Jul 1:21 AM' },
    { name: 'Robbin Doe', position: 'ENT', time: '11 Jul 4:30 AM' },
    { name: 'Jane Cooper', position: 'Cardiologist', time: '11 Jul 4:50 AM' }
])



const tableData = ref([
    {
        name: 'Anne Hathaway',
        contact: 'Gastroenterologists',
        date: '02:00 PM',
        time: '06:00 PM',
        status: {
            color: 'danger',
            status: 'approved'
        },
    }
])

//get from api
onMounted(async () => {
    //fetch appointments and slots and unavailable slots
    getSlot();
    // console.log(appointments);
    modalInstance = new Modal(modalRef.value);  // Create a Bootstrap modal instance
});

const axiosInstance = AxiosInstance();
const appointments = ref([]);
const modalRef = ref(null);  // Reference to the modal element
 // Will hold the Bootstrap modal instance
//fetch appointments and slots and unavailable slots
const getSlot = async () => {
    try {
        const response = await axiosInstance.get('v1/scheduler/appointments');
        console.log(response.data);
        appointments.value = response.data.dataPayload.data;
        console.log("appointments", appointments);
    } catch (error) {
        console.error(error);
    }
};


const columns = ref([
    {
        title: 'Name',
        data: 'name',
        render: function (row) {
            return `<h6>${row}</h6>`
        }
    },
    {
        title: 'Contact',
        data: 'contact'
    },
    {
        title: 'Date',
        data: 'date'
    },
    {
        title: 'Time',
        data: 'time'
    },

    {
        title: 'Status',
        data: 'status',
        render: function (data) {
            return '<div class="bg-' + data.color + ' text-white badge rounded-pill">' + data.status + '</div>'
        }
    },
    {
        title: 'Action',
        data: '',
        render: function () {
            return `
        <button type="button" class="btn btn-primary" @click="viewItem"><i class="far fa-eye"></i></button>
        <button type="button" class="btn btn-warning" @click="editItem"><i class="fas fa-edit"></i></button>
        <button type="button" class="btn btn-danger" @click="deleteItem"><i class="far fa-trash-alt"></i></button>
      `;
        },
    }


])



const inputTableRef = ref(null)
useDataTable({
    tableRef: inputTableRef,
    columns: columns.value,
    data: tableData.value,
    isFilterColumn: true
})
// data table end
</script>

<style scoped>
.action-icons a {
    font-size: 18px;
    cursor: pointer;
}
</style>