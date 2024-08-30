<script setup>
import { useAutoLogout } from '@/composables/useAutoLogout';

const { warningZone, resetTimer } = useAutoLogout(); // Include resetTimer

</script>

<template>
    <div v-if="warningZone" class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body">
                You will be logged out soon due to inactivity.
                <div class="mt-2 pt-2 border-top">
                    <button type="button" class="btn btn-primary btn-sm" @click="resetTimer">
                        Stay logged in
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="toast">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.toast-container {
    z-index: 1050;
}
</style>
