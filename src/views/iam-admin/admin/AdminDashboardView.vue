<template>
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-3">
    <div class="d-flex flex-column">
      <h3>Quick Insights</h3>
      <p class="text-primary mb-0">Meetings Dashboard</p>
    </div>
    <div class="d-flex justify-content-between align-items-center rounded flex-wrap gap-3">
      <div class="form-group mb-0">
        <flat-picker v-model="date" :config="{ mode: 'range', minDate: 'today', dateFormat: 'Y-m-d' }" name="date" placeholder="24 Jan 2022 to 23 Feb 2022" class="form-control"></flat-picker>
      </div>
      <b-button variant="primary">Analytics</b-button>
    </div>
  </div>
  <b-row class="row-cols-2 row-cols-md-2 row-cols-lg-4">
    <b-col>
      <analytics-widget :value="1206" description="All" Iconcolor="#3788D8" icon="file-lines"></analytics-widget>
    </b-col>
    <b-col>
      <analytics-widget :value="2455" description="New" Iconcolor="#097B3E" icon="users"></analytics-widget>
    </b-col>
    <b-col>
      <analytics-widget :value="1200" description="Active" Iconcolor="#097B3E" icon="phone"></analytics-widget>
    </b-col>
    <b-col>
      <analytics-widget :value="1200" description="Cancelled" Iconcolor="#d33" icon="phone-slash"></analytics-widget>
    </b-col>
  </b-row>

  <b-row>
    <b-col lg="8" md="6" sm="12">
      <b-card no-body>
        <b-card-header class="flex-wrap d-flex justify-content-between">
          <b-card-title><h4>Doctors</h4></b-card-title>
        </b-card-header>
        <b-card-body class="px-0">
          <div class="table-responsive">
            <b-table-simple id="doctors-table" striped class="py-3">
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

  <b-row>
    <b-col lg="4" md="6">
      <b-card class="card-block card-stretch card-height">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <p class="mb-0 text-dark">Total Meetings</p>
          <b-badge tag="a" variant=" bg-primary-subtle" class="badge rounded-pill" href="javascript:void(0);"> View </b-badge>
        </div>
        <small class="mb-2">This Week.</small>

        <div class="mb-3">
          <h2 class="counter">
            <Vue3autocounter ref="counter" separator="," :duration="3" :startAmount="0" :endAmount="124" />
          </h2>
        </div>
        <div>
          <apexchart height="100%" type="bar" :options="grossVolume.options" :series="grossVolume.series" />
        </div>
      </b-card>
    </b-col>

    <b-col lg="8" md="12">
      <b-card class="card-block card-stretch card-height">
        <template #header>
          <div class="d-flex justify-content-between flex-wrap">
            <h4 class="card-title">Meetings Distribution</h4>
            <div class="dropdown">
              <b-dropdown variant="none px-0 py-0" id="dropdown-1" text="This year">
                <b-dropdown-item variant="none">Year</b-dropdown-item>
                <b-dropdown-item variant="none">Month</b-dropdown-item>
                <b-dropdown-item variant="none">Week</b-dropdown-item>
              </b-dropdown>
            </div>
          </div>
        </template>
        <apexchart height="100%" type="line" class="dashboard-line-chart" :options="netVolumeSale.options" :series="netVolumeSale.series" />
      </b-card>
    </b-col>
  </b-row>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
// Pinia Store
import { useSetting } from '@/store/pinia'
import AnalyticsWidget from '@/components/widgets/AnalyticsWidget.vue'
import Vue3autocounter from 'vue3-autocounter'
import FlatPicker from 'vue-flatpickr-component'
import { getVariableColor } from '@/utilities/root-var'

const date = ref('')
// const imgGroup = ref([require('@/assets/images/table/1.png'), require('@/assets/images/table/2.png'), require('@/assets/images/table/3.png'), require('@/assets/images/table/4.png'), require('@/assets/images/table/5.png')])

const store = useSetting()
// const themeColor = computed(() => store.getters['setting/theme_color'].colors)
const themeColor = computed(() => store.theme_color)
const colors = ref(null)
const colorsChange = () => {
  const newColors = getVariableColor()
  colors.value = [newColors.primary, newColors.warning]
}
colorsChange()
watch(
  themeColor,
  () => {
    colorsChange()
    grossVolume.value = {
      ...grossVolume.value,
      options: {
        ...grossVolume.value.options,
        colors: colors.value
      }
    }
  },
  { deep: true }
)
watch(
  themeColor,
  () => {
    colorsChange()
    netVolumeSale.value = {
      ...netVolumeSale.value,
      options: {
        ...netVolumeSale.value.options,
        colors: colors.value,
        markers: {
          ...netVolumeSale.value.options.markers,
          strokeColors: colors.value
        }
      }
    }
  },
  { deep: true }
)

const timeline = ref([
  { name: 'Cameron Williamson', position: 'Dentist', time: '11 Jul 8:10 PM' },
  { name: 'Brooklyn Simmons', position: 'Orthopedic', time: '11 Jul 11 PM' },
  { name: 'Leslie Alexander', position: 'Neurology', time: '11 Jul 1:21 AM' },
  { name: 'Robbin Doe', position: 'ENT', time: '11 Jul 4:30 AM' },
  { name: 'Jane Cooper', position: 'Cardiologist', time: '11 Jul 4:50 AM' }
])

const grossVolume = ref({
  series: [
    {
      name: 'Successful deals',
      data: [30, 50, 35, 60, 40, 60, 60]
    },
    {
      name: 'Failed deals',
      data: [40, 50, 55, 50, 30, 80, 30]
    }
  ],
  options: {
    chart: {
      type: 'bar',
      height: '100%',
      stacked: true,
      toolbar: {
        show: false
      }
    },
    colors: colors.value,
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '28%',
        endingShape: 'rounded',
        borderRadius: 3
      }
    },
    legend: {
      show: false
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      show: true,
      width: 3,
      colors: ['transparent']
    },
    grid: {
      show: true,
      strokeDashArray: 7
    },
    xaxis: {
      categories: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
      labels: {
        minHeight: 20,
        maxHeight: 20,
        style: {
          colors: '#8A92A6'
        }
      }
    },
    yaxis: {
      title: {
        text: ''
      },
      labels: {
        minWidth: 20,
        maxWidth: 20,
        style: {
          colors: '#8A92A6'
        }
      }
    },
    fill: {
      opacity: 1
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return '$ ' + val + ' thousands'
        }
      }
    },
    responsive: [
      {
        breakpoint: 1025,
        options: {
          chart: {
            height: 130
          }
        }
      }
    ]
  }
})
const netVolumeSale = ref({
  series: [
    {
      name: 'Sales',
      data: [10, 82, 75, 68, 47, 60, 49, 91, 108, 200]
    }
  ],
  options: {
    colors: colors.value,
    chart: {
      height: '100%',
      type: 'line',
      toolbar: {
        show: false
      }
    },
    forecastDataPoints: {
      count: 3
    },
    stroke: {
      width: 3
    },
    grid: {
      show: true,
      strokeDashArray: 7
    },
    markers: {
      size: 6,
      colors: '#FFFFFF',
      strokeColors: colors.value,
      strokeWidth: 2,
      strokeOpacity: 0.9,
      strokeDashArray: 0,
      fillOpacity: 0,
      shape: 'circle',
      radius: 2,
      offsetX: 0,
      offsetY: 0
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      axisBorder: {
        show: false
      },
      axisTicks: {
        show: false
      },
      tooltip: {
        enabled: false
      }
    }
  }
})
</script>
