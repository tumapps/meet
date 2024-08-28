// 

import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

export function useAutoLogout() {
    const router = useRouter();
    const warningZone = ref(false);
    let warningTimer = null;
    let logoutTimer = null;

    const WARNING_TIMEOUT = 15 * 1000; // 15 seconds for demonstration
    const LOGOUT_TIMEOUT = 16 * 1000; // 16 seconds for demonstration
    const events = ['click', 'mousemove', 'mousedown', 'scroll', 'keypress', 'load'];

    const setTimers = () => {
        // Set the warning timer
        warningTimer = setTimeout(() => {
            warningZone.value = true;
        }, WARNING_TIMEOUT);

        // Set the logout timer
        logoutTimer = setTimeout(logoutUser, LOGOUT_TIMEOUT);

        warningZone.value = false;
    };

    const logoutUser = () => {
        // Clear user token in local storage
        localStorage.removeItem('user.token');
        // Redirect to the lock screen or login page
        router.push('/Lockscreen');
    };

    const resetTimer = () => {
        // Clear both timers
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        setTimers(); // Reset the timers after user activity
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
        clearTimeout(warningTimer); // Clear active timers during unmount
        clearTimeout(logoutTimer);
    });

    return {
        warningZone
    };
}
