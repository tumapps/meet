<script setup>
import { useAutoLogout } from '@/composables/useAutoLogout';
import { getCurrentInstance, watch } from 'vue';

// Get the current instance to access proxy
const { proxy } = getCurrentInstance();
const { warningZone, resetTimer } = useAutoLogout(); // Destructure the returned values from useAutoLogout

// Function to show alert when warningZone becomes true
const showAlert = () => {
    proxy.$showAlert({
        title: 'Still there?',
        text: 'You are about to be logged out!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
    });
};

// Watch the reactive `warningZone` and trigger showAlert when it changes to `true`
watch(warningZone, (newVal) => {
    if (newVal === true) {
        showAlert(); // Call showAlert when user is in the warning zone
    }
});

</script>

<template>
    <!-- Add any necessary HTML here -->

</template>

<style scoped>
.toast-container {
    z-index: 1050;
}
</style>
