<script setup>
import { ref, computed, watch } from 'vue'

import { useSetting } from '@/store/pinia'

import { getVariableColor } from '@/utilities/root-var'
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css'
import FullCalender from '@/components/custom/calender/FullCalender.vue'

import AnalyticsWidget from '@/components/modules/appointment/widgets/AnalyticsWidget.vue'

const themeColor = computed(() => store.getters['setting/theme_color'].colors);
const date = ref(null);

const timeline = ref([
  { name: 'Cameron Williamson', position: 'Dentist', time: '11 Jul 8:10 PM' },
  { name: 'Brooklyn Simmons', position: 'Orthopedic', time: '11 Jul 11 PM' },
  { name: 'Leslie Alexander', position: 'Neurology', time: '11 Jul 1:21 AM' },
  { name: 'Robbin Doe', position: 'ENT', time: '11 Jul 4:30 AM' },
  { name: 'Jane Cooper', position: 'Cardiologist', time: '11 Jul 4:50 AM' }
])
const list = ref([
  { img: require('@/assets/modules/appointment/images/avatars/02.png'), name: 'Jonathan Bradley', email: 'kenzi.lawson@example.com', contact: '(319) 555-0115' },
  { img: require('@/assets/modules/appointment/images/avatars/04.png'), name: 'Logan Wolfe', email: 'georgia.young@example.com', contact: '(603) 555-0123' },
  { img: require('@/assets/modules/appointment/images/avatars/01.png'), name: 'Elnora obrien', email: 'sara.cruz@example.com', contact: '(270) 555-0117' },
  { img: require('@/assets/modules/appointment/images/avatars/03.png'), name: 'Katherine Romero', email: 'felicia.reid@example.com', contact: '(629) 555-0129' },
  { img: require('@/assets/modules/appointment/images/avatars/05.png'), name: 'Oscar Phelps', email: 'alma.lawson@example.com', contact: '(307) 555-0133' }
])
</script>


<template>
  <b-row>
    <b-col>
      <h3 class="mb-5">Appointments Overview</h3>
      <!-- <b-row class=" row-cols-1 row-cols-md-2 row-cols-lg-5  w-100 justify-content-center align-items-center"> -->
      <b-row class="row-cols-sm-4 row-cols-md-5 row-cols-lg-5 g-4 w-100 justify-content-center align-items-center">
        <b-col cols="12" md="6">
          <b-card class="">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-success-subtle p-2 rounded">
                <svg class="icon-32" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M3 16.8701V9.25708H21V16.9311C21 20.0701 19.0241 22.0001 15.8628 22.0001H8.12733C4.99561 22.0001 3 20.0301 3 16.8701ZM7.95938 14.4101C7.50494 14.4311 7.12953 14.0701 7.10977 13.6111C7.10977 13.1511 7.46542 12.7711 7.91987 12.7501C8.36443 12.7501 8.72997 13.1011 8.73985 13.5501C8.7596 14.0111 8.40395 14.3911 7.95938 14.4101ZM12.0198 14.4101C11.5653 14.4311 11.1899 14.0701 11.1701 13.6111C11.1701 13.1511 11.5258 12.7711 11.9802 12.7501C12.4248 12.7501 12.7903 13.1011 12.8002 13.5501C12.82 14.0111 12.4643 14.3911 12.0198 14.4101ZM16.0505 18.0901C15.596 18.0801 15.2305 17.7001 15.2305 17.2401C15.2206 16.7801 15.5862 16.4011 16.0406 16.3911H16.0505C16.5148 16.3911 16.8902 16.7711 16.8902 17.2401C16.8902 17.7101 16.5148 18.0901 16.0505 18.0901ZM11.1701 17.2401C11.1899 17.7001 11.5653 18.0611 12.0198 18.0401C12.4643 18.0211 12.82 17.6411 12.8002 17.1811C12.7903 16.7311 12.4248 16.3801 11.9802 16.3801C11.5258 16.4011 11.1701 16.7801 11.1701 17.2401ZM7.09989 17.2401C7.11965 17.7001 7.49506 18.0611 7.94951 18.0401C8.39407 18.0211 8.74973 17.6411 8.72997 17.1811C8.72009 16.7311 8.35456 16.3801 7.90999 16.3801C7.45554 16.4011 7.09989 16.7801 7.09989 17.2401ZM15.2404 13.6011C15.2404 13.1411 15.596 12.7711 16.0505 12.7611C16.4951 12.7611 16.8507 13.1201 16.8705 13.5611C16.8804 14.0211 16.5247 14.4011 16.0801 14.4101C15.6257 14.4201 15.2503 14.0701 15.2404 13.6111V13.6011Z"
                    fill="currentColor"></path>
                  <path opacity="0.4"
                    d="M3.00293 9.25699C3.01577 8.66999 3.06517 7.50499 3.15803 7.12999C3.63224 5.02099 5.24256 3.68099 7.54442 3.48999H16.4555C18.7376 3.69099 20.3677 5.03999 20.8419 7.12999C20.9338 7.49499 20.9832 8.66899 20.996 9.25699H3.00293Z"
                    fill="currentColor"></path>
                  <path
                    d="M8.30465 6.59C8.73934 6.59 9.06535 6.261 9.06535 5.82V2.771C9.06535 2.33 8.73934 2 8.30465 2C7.86996 2 7.54395 2.33 7.54395 2.771V5.82C7.54395 6.261 7.86996 6.59 8.30465 6.59Z"
                    fill="currentColor"></path>
                  <path
                    d="M15.6953 6.59C16.1201 6.59 16.456 6.261 16.456 5.82V2.771C16.456 2.33 16.1201 2 15.6953 2C15.2606 2 14.9346 2.33 14.9346 2.771V5.82C14.9346 6.261 15.2606 6.59 15.6953 6.59Z"
                    fill="currentColor"></path>
                </svg>
              </div>
              <div>
                <h4 class="d-none d-md-block">12/4</h4>
                <span class="text-dark d-none d-md-block">Today</span>
              </div>
            </div>
          </b-card>
        </b-col>
        <b-col cols="12" md="6">
          <analytics-widget :value="1206" description="All" variant="primary" icon="document"></analytics-widget>
        </b-col>
        <b-col cols="12" md="6">
          <analytics-widget :value="2455" description="New" variant="primary" icon="users"></analytics-widget>
        </b-col>
        <b-col cols="12" md="6">
          <analytics-widget :value="1200" description="Rescheduled" variant="warning" icon="users">
          </analytics-widget>
        </b-col>
        <b-col cols="12" md="6">
          <analytics-widget :value="1200" description="Cancelled" variant="danger" icon="missed-call">
          </analytics-widget>
        </b-col>
      </b-row>
      <b-card>
        <template #header>
          <div class="flex-wrap d-flex justify-content-between align-items-center">
            <div class="header-title">
              <h4 class="card-title">Appointments</h4>
            </div>
            <div class="d-flex align-items-center align-self-center mt-2 mt-sm-0">
              <div class="d-flex align-items-center text-primary">
                <icon-component type="solid" icon-name="circle" :size="12"></icon-component>
                <div class="ms-2">
                  <span class="text-gray">Booked</span>
                </div>
              </div>
              <div class="d-flex align-items-center ms-3 text-info">
                <icon-component type="solid" icon-name="circle" :size="12"></icon-component>
                <div class="ms-2">
                  <span class="text-gray">Cancelled</span>
                </div>
              </div>
            </div>
            <b-dropdown variant="none px-0 py-0" id="dropdown-1" text="This year ">
              <b-dropdown-item variant="none">Year</b-dropdown-item>
              <b-dropdown-item variant="none">Month</b-dropdown-item>
              <b-dropdown-item variant="none">Week</b-dropdown-item>
            </b-dropdown>
          </div>

        </template>
      </b-card>
    </b-col>
    <b-col lg="7 calendar-s">
      <b-card>
        <full-calender :events="events" @onDateSelect="dateSelected"></full-calender>
      </b-card>
    </b-col>
  </b-row>
  <b-row>
    <b-col lg="8" md="6" sm="12">
      <b-card no-body>
        <b-card-header class="flex-wrap d-flex justify-content-between">
          <b-card-title>
            <h4>Doctors</h4>
          </b-card-title>
        </b-card-header>
        <b-card-body class="px-0">
          <div class="table-responsive">
            <b-table-simple id="doctors-table" striped class="py-3 table-hover">
              <b-thead class="bg-soft-primary">
                <b-tr class="text-dark">
                  <th class="sorting_asc">User</th>
                  <th class="sorting_asc">Email</th>
                  <th class="sorting_asc">Contact no.</th>
                  <th class="text-center">More Details</th>
                </b-tr>
              </b-thead>
              <b-tbody>
                <b-tr v-for="(item, index) in list" :key="index">
                  <td>
                    <div class="d-flex align-items-center">
                      <img class="rounded img-fluid avatar-40 me-3" :src="item.img" alt="profile" loading="lazy" />
                      <h6 class="mb-0">{{ item.name }}</h6>
                    </div>
                  </td>
                  <td>{{ item.email }}</td>
                  <td>{{ item.contact }}</td>
                  <td class="text-center">
                    <a href="#">View</a>
                  </td>
                </b-tr>
              </b-tbody>
              <b-tfoot>
                <b-tr>
                  <th title="User"></th>
                  <th title="Email"></th>
                  <th title="Contact-Number"></th>
                  <th title="More Details"></th>
                </b-tr>
              </b-tfoot>
            </b-table-simple>
          </div>
        </b-card-body>
      </b-card>
    </b-col>
    <b-col lg="4" md="6" sm="12">
      <div class="card">
        <div class="flex-wrap card-header d-flex justify-content-between">
          <div class="header-title">
            <h4>Upcoming Appointments</h4>
            <p class="mb-0">
              <svg class="me-2" width="24" height="24" viewBox="0 0 24 24">
                <path fill="#17904b" d="M13,20H11V8L5.5,13.5L4.08,12.08L12,4.16L19.92,12.08L18.5,13.5L13,8V20Z"></path>
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
</template>
