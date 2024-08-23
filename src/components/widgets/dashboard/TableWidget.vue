<template>
  <div class="table-responsive">
    <b-table-simple id="settlements-table" class="mb-0">
      <b-tbody>
        <b-tr v-for="(item, index) in list" :key="index">
          <b-td>
            <div class="d-flex align-items-center">
              <h6 class="mb-0">{{ item.amount }}</h6>
            </div>
          </b-td>
          <b-td class="text-primary"> {{ item.transation_id }} </b-td>
          <b-td class="text-dark">{{ item.date }}</b-td>
          <b-td class="text-end">
            <b-badge variant="success" pill v-if="item.status === 1">Processed</b-badge>
            <b-badge variant="info text-white" pill v-if="item.status === 0">Process</b-badge>
            <b-badge variant="success" pill v-if="item.status === 3">Refunded</b-badge>
            <b-badge variant="danger" pill v-if="item.status === 2">Failed</b-badge>
          </b-td>
        </b-tr>
      </b-tbody>
    </b-table-simple>
    <div></div>
  </div>
</template>

<script>
export default {
  props: {
    list: {
      type: Array,
      default: () => []
    }
  }
}
</script>

<style lang="scss" scoped></style>
