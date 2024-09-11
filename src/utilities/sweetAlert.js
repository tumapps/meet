// src/utils/sweetAlert.js
import Swal from 'sweetalert2';

// Show alert function
export function showAlert({ title = 'Alert', text = '', icon = 'warning', confirmButtonText = 'OK' }) {
    return Swal.fire({
        title,
        text,
        icon,
        confirmButtonText,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#076232',
    });
}

// Show toast function
export function showToast({ title = '', icon = '#fff', timer = 3000, position = 'top-end' }, background = '', grow = '' ) {
    return Swal.fire({
        toast: true,
        title,
        icon,
        position,
        timer,
        background,
        grow,
        timerProgressBar: true,

    });
}
