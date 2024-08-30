import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

export function useAutoLogout() {
    const router = useRouter();
    const warningZone = ref(false);
    let warningTimer = null;
    let logoutTimer = null;

    const WARNING_TIMEOUT = 15 * 60 ; // 15 minutes
    const LOGOUT_TIMEOUT = 20 * 60 ; // 16 minutes
    const events = ['click', 'mousemove', 'mousedown', 'scroll', 'keypress'];

    const setTimers = () => {
        warningTimer = setTimeout(() => {
            warningZone.value = true;
        }, WARNING_TIMEOUT - 1 * 60 * 1000); // Show warning 1 minute before logout

        logoutTimer = setTimeout(logoutUser, LOGOUT_TIMEOUT);
        warningZone.value = false;
    };

    const logoutUser = () => {
        localStorage.removeItem('user.token');
        router.push('/Lockscreen');
    };

    const resetTimer = () => {
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
        setTimers();
    };

    onMounted(() => {
        events.forEach(event => {
            window.addEventListener(event, resetTimer);
        });
        setTimers();
    });

    onUnmounted(() => {
        events.forEach(event => {
            window.removeEventListener(event, resetTimer);
        });
        clearTimeout(warningTimer);
        clearTimeout(logoutTimer);
    });

    return {
        warningZone,
        resetTimer // Include this to allow manual resetting
    };
}
