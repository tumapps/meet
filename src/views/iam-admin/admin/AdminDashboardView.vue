<script setup>
import { ref, computed, watch, onMounted } from 'vue'
// Pinia Store
import { useSetting } from '@/store/pinia'
import AnalyticsWidget from '@/components/widgets/AnalyticsWidget.vue'
import Vue3autocounter from 'vue3-autocounter'
// import FlatPicker from 'vue-flatpickr-component'
import { getVariableColor } from '@/utilities/root-var'
import AxiosInstance from '@/api/axios'
import UpcomingEvents from '@/components/upcomingEvents.vue'
import TableView from '@/components/spacesMiniTable.vue'

const axiosInstance = AxiosInstance()

// const date = ref('')
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

// get stats from backend
const analytics = ref({})
const getAnalytics = async () => {
  const response = await axiosInstance.get('/v1/scheduler/reports')

  console.log('API Response:', response.data.dataPayload.data)
  analytics.value = response.data.dataPayload.data
  // get all the analytics data from the backend
}
const weeklyStats = ref({})
const getWeeklyStats = async () => {
  const response = await axiosInstance.get('v1/scheduler/weekly-meeting-summary')

  console.log('API Response:', response.data.dataPayload.data)
  weeklyStats.value = response.data.dataPayload.data.weekly_meeting_summary
}

const annualStats = ref({})
const getAnnualStats = async () => {
  const response = await axiosInstance.get('/v1/scheduler/yearly-meeting-summary')

  console.log('API Response:', response.data.dataPayload.data)
  annualStats.value = response.data.dataPayload.data.yearly_meeting_summary
}

const getEvents = async () => {
  const response = await axiosInstance.get('/v1/scheduler/upcoming-events')

  console.log('API Response:', response.data.dataPayload.data)
  if (Array.isArray(response.data.dataPayload.data)) {
    if (response.data.dataPayload.data.length > 0) {
      timeline.value = response.data.dataPayload.data
    }
  }
}
const timeline = ref([])

const grossVolume = computed(() => {
  const dates = Object.keys(weeklyStats.value || {}).sort() // Sort dates
  return {
    series: [
      {
        name: 'Successful meetings',
        data: dates.map((date) => weeklyStats.value[date]?.attended_meetings)
      },
      {
        name: 'Failed meetings',
        data: dates.map((date) => (weeklyStats.value[date]?.canceled_meetings || 0) + weeklyStats.value[date]?.missed_meetings)
      },
      {
        name: 'Active meetings',
        data: dates.map((date) => weeklyStats.value[date]?.active_meetings)
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
          columnWidth: '25%',
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
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
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
          minWidth: 15,
          maxWidth: 15,
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
            return val
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
  }
})

// Compute the total number of meetings for the week
const totalMeetingsInWeek = computed(() => {
  return Object.values(weeklyStats.value).reduce((sum, day) => sum + (day.total_meetings || 0), 0)
})

const monthsOrder = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

const netVolumeSale = computed(() => {
  return {
    series: [
      {
        name: 'Sales',
        data: monthsOrder.map((month) => annualStats.value[month]?.total_meetings || 0) // Extract data in correct order
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
  }
})

onMounted(async () => {
  // fetchSettings()
  getAnalytics()
  getWeeklyStats()
  getAnnualStats()
  getEvents()
})
</script>

<template>
  <b-row class="row-cols-2 row-cols-md-3 row-cols-lg-5">
    <b-col>
      <analytics-widget :value="analytics.total_appointments" description="All" Iconcolor="#3788D8" icon="calendar-alt"></analytics-widget>
    </b-col>
    <b-col>
      <analytics-widget :value="analytics.active" description="Active" Iconcolor="#097B3E" icon="calendar-check"></analytics-widget>
    </b-col>
    <b-col>
      <analytics-widget :value="analytics.pending" description="Pending" Iconcolor="#3788D8" icon="hourglass"></analytics-widget>
    </b-col>

    <b-col>
      <analytics-widget :value="analytics.attended" description="Completed" Iconcolor="#097B3E" icon="circle-check"></analytics-widget>
    </b-col>
    <b-col>
      <analytics-widget :value="analytics.canceled" description="Cancelled" Iconcolor="#d33" icon="calendar-xmark"></analytics-widget>
    </b-col>
  </b-row>

  <b-row>
    <b-col lg="8" md="6" sm="12">
      <TableView :height="height" />
    </b-col>
    <UpcomingEvents :events="timeline" />
  </b-row>

  <b-row>
    <b-col lg="4" md="12">
      <b-card class="card-block card-stretch card-height">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <p class="mb-0 text-dark">Total Meetings</p>
          <b-badge tag="a" variant=" bg-primary-subtle" class="badge rounded-pill" href="javascript:void(0);"> View </b-badge>
        </div>
        <small class="mb-2">This Week.</small>

        <div class="mb-3">
          <h2 class="counter">
            <Vue3autocounter ref="counter" separator="," :duration="2" :startAmount="0" :endAmount="totalMeetingsInWeek" />
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
            <div>{{ new Date().getFullYear() }}</div>
          </div>
        </template>
        <apexchart height="100%" type="line" class="dashboard-line-chart" :options="netVolumeSale.options" :series="netVolumeSale.series" />
      </b-card>
    </b-col>
  </b-row>
</template>
<style>
@media (min-width: 992px) {
  /* lg and above */
  .custom-card {
    min-height: 300px !important;
    max-height: 500px !important;
  }
}
</style>
