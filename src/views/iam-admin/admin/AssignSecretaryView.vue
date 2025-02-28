<script setup>
import { ref } from 'vue'
import AssignSecretary from '@/components/AssignSecretary.vue'

const assignModal = ref(null)

const showRoleModal = () => {
  assignModal.value.show()
  //pass the role name to the modal as value for roleName
}
</script>
<template>
  <!-- //button -->
  <button @click="showRoleModal" class="btn btn-primary">Assign Secretary</button>
  <AssignSecretary ref="assignModal" />
</template>
