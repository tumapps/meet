<template>
  <b-card no-body>
    <b-card-header>
      <div class="header-title">
        <h4 class="card-title">Friends</h4>
      </div>
    </b-card-header>
    <b-card-body>
      <ul class="list-inline m-0 p-0">
        <li class="d-flex mb-4 align-items-center" v-for="(item, index) in list" :key="index">
          <img :src="item.image" alt="story-img" class="rounded-pill avatar-40" loading="lazy" />
          <div class="ms-3 flex-grow-1">
            <h6>{{ item.name }}</h6>
            <p class="mb-0">{{ item.designation }}</p>
          </div>
          <b-dropdown variant="none">
            <b-dropdown-item variant="none" href="javascript:void(0);">Unfollow</b-dropdown-item>
            <b-dropdown-item variant="none" href="javascript:void(0);">Unfriend</b-dropdown-item>
            <b-dropdown-item variant="none" href="javascript:void(0);">block</b-dropdown-item>
          </b-dropdown>
        </li>
      </ul>
    </b-card-body>
  </b-card>
</template>
<script>
export default {
  props: {
    list: {
      type: Array,
      default: () => []
    },
    image: { type: String, required: true },
    name: { type: String, required: true },
    designation: { type: String, required: true }
  }
}
</script>
