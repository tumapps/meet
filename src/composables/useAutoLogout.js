// src/composables/useAutoLogout.js
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

export function useAutoLogout() {
    const router = useRouter();
    const warningZone = ref(false);
    let warningTimer = null;
    let logoutTimer = null;

    const events = ['click', 'mousemove', 'mousedown', 'scroll', 'keypress', 'load'];

    const setTimers = () => {
        warningTimer = setTimeout(() => {
            warningZone.value = true;
        }, 15 * 60 * 1000); // 8 seconds for demonstration

        logoutTimer = setTimeout(logoutUser, 16 * 60 * 1000); // 15 seconds for demonstration

        warningZone.value = false;
    };

    const logoutUser = () => {
        // Clear user token in local storage
        localStorage.removeItem('user.token');
        // Redirect to the lock screen or login page
        router.push('/Lockscreen')
        // window.location.href = '/lockscreen'; // Adjust the URL as needed
    };

    const resetTimer = () => {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        setTimers();
    };

    // Setup event listeners when the component is mounted
    onMounted(() => {
        events.forEach((event) => {
            window.addEventListener(event, resetTimer);
        });
        setTimers();
    });

    // Cleanup event listeners when the component is unmounted
    onUnmounted(() => {
        events.forEach((event) => {
            window.removeEventListener(event, resetTimer);
        });
        resetTimer(); // Clear active timers
    });

    return {
        warningZone
    };
}
