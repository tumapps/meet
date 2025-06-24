<script setup>
import { defineProps, ref } from 'vue'
import { useRouter } from 'vue-router'
import ViewEvent from './viewEvent.vue'

const router = useRouter()

const props = defineProps({
  events: {
    type: Array,
    required: true
  }
})

// check if props.events is empty
const message = ref(null)
if (!props.events.length) {
  //console.log('No events')
  message.value = 'No Upcoming events'
}

const viewAllEvents = () => {
  router.push({ name: 'all-events' })
  //console.log('View all events')
}

const editevent = ref(null)
const event_id = ref(null)

const viewThisEvent = (id) => {
  //console.log('View event:', id)
  if (editevent.value) {
    event_id.value = id
    //console.log('event_id sending', event_id.value)
    editevent.value.show()
  }
}
</script>
<template>
  <ViewEvent ref="editevent" :event_id="event_id" />

  <b-col lg="4" md="6" sm="12">
    <div class="card custom-card">
      <div class="flex-wrap card-header d-flex justify-content-between">
        <div class="header-title">
          <h4>Upcoming Events</h4>
          <p class="mb-0">
            <svg class="me-2" width="24" height="24" viewBox="0 0 24 2I4">
              <path fill="#17904b" d="M13,20H11V8L5.5,13.5L4.08,12.08L12,4.16L19.92,12.08L18.5,13.5L13,8V20Z"></path>
            </svg>
            <span class="text-primary" @click="viewAllEvents" style="cursor: pointer">View All</span>
          </p>
        </div>
      </div>
      <div v-if="props.events.length !== 0" class="card-body" style="overflow-y: auto">
        <div class="d-flex profile-media align-items-top" style="cursor: pointer" v-for="(item, id) in props.events" :key="id" @click="viewThisEvent(item.id)">
          <div class="mt-1 profile-dots-pills border-primary"></div>
          <div class="ms-4">
            <h6 class="mb-1">
              {{ item.title }}
            </h6>
            <p class="mt-0 mb-0">
              <span class="text-primary">{{ item.start_date }}</span>
            </p>
            <span class="mb-0" style="color: #4798d8">{{ item.start_time }} - {{ item.end_time }}</span>
          </div>
        </div>
      </div>
      <div v-else class="card-body">
        <p class="text-center">{{ message }}</p>
      </div>
    </div>
  </b-col>
</template>
<style scoped>
@media(max-width:1199.98px){
  .custom-card{
    height: 269px;
  }
}
</style>
