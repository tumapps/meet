// src/utils/sweetAlert.js
import Swal from 'sweetalert2';

// Show alert function
export function showAlert({ title = 'Alert', text = '', icon = 'warning', confirmButtonText = 'OK' }) {
    return Swal.fire({
        title,
        text,
        icon,
        confirmButtonText,
    });
}

// Show toast function
export function showToast({ title = '', icon = 'success', timer = 3000, position = 'top-end' }) {
    return Swal.fire({
        toast: true,
        title,
        icon,
        position,
        showConfirmButton: false,
        timer,
        timerProgressBar: true,
    });
}
